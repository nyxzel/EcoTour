<?php
/*
 * booking.php — EcoTour+ Booking Handler
 * Accepts POST from booking-modal.php via fetch().
 * Uses session for logged-in user_id — no client-side spoofing.
 * Maps to: teams, team_members, bookings, schedules, activity_log
 */

session_start();
require_once 'database.php';

header('Content-Type: application/json');

// ── Only allow POST ──────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

// ── Auth check — must be logged in ──────────────────────────────────────────
if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'You must be logged in to book.']);
    exit;
}

$user_id = (int) $_SESSION['user_id'];

// ── Collect & sanitize input ─────────────────────────────────────────────────
$booking_type  = trim($_POST['booking_type']  ?? '');
$site_id       = (int) ($_POST['site_id']     ?? 0);
$schedule_id   = (int) ($_POST['schedule_id'] ?? 0);
$team_name     = trim($_POST['team_name']     ?? '');
$visit_date    = trim($_POST['visit_date']    ?? '');
$special_notes = trim($_POST['special_notes'] ?? '');

$members_json  = $_POST['members'] ?? '[]';
$members       = json_decode($members_json, true);
if (!is_array($members)) $members = [];

// ── Validate ─────────────────────────────────────────────────────────────────
$errors = [];

if (!in_array($booking_type, ['solo', 'group'], true))
    $errors[] = 'Invalid booking type.';

if ($site_id <= 0)
    $errors[] = 'Invalid site selected.';

if ($schedule_id <= 0)
    $errors[] = 'Please select a visit schedule.';

if (empty($visit_date) || !strtotime($visit_date))
    $errors[] = 'Please select a valid visit date.';

if ($booking_type === 'group') {
    if (empty($team_name))
        $errors[] = 'Team / group name is required.';
    if (count($members) < 1)
        $errors[] = 'Please add at least one group member.';
    foreach ($members as $i => $m) {
        if (empty(trim($m['name'] ?? '')))
            $errors[] = 'Member #' . ($i + 1) . ' is missing a name.';
    }
}

if (!empty($errors)) {
    http_response_code(422);
    echo json_encode(['success' => false, 'errors' => $errors]);
    exit;
}

// ── Process booking inside a transaction ─────────────────────────────────────
try {

    // 1. Verify schedule belongs to the site and get slot limits
    $stmt = $pdo->prepare("
        SELECT s.schedule_id, s.max_slots,
               COALESCE(SUM(b.total_members), 0) AS already_booked
        FROM   schedules s
        LEFT JOIN bookings b
               ON b.schedule_id = s.schedule_id
              AND b.status IN ('pending', 'confirmed')
        WHERE  s.schedule_id = :sid
          AND  s.site_id     = :site_id
        GROUP  BY s.schedule_id
    ");
    $stmt->execute([':sid' => $schedule_id, ':site_id' => $site_id]);
    $slot = $stmt->fetch();

    if (!$slot) {
        echo json_encode(['success' => false, 'message' => 'Selected schedule was not found for this site.']);
        exit;
    }

    // Calculate how many visitors this booking will add
    if ($booking_type === 'solo') {
        $total_visitors = 1;
    } else {
        $include_leader = !empty($_POST['include_leader']) ? 1 : 0;
        $total_visitors = count($members) + $include_leader;
    }

    $remaining = $slot['max_slots'] - $slot['already_booked'];
    if ($total_visitors > $remaining) {
        echo json_encode([
            'success' => false,
            'message' => "Only {$remaining} slot(s) remaining for this schedule. Please reduce your group size or choose another date."
        ]);
        exit;
    }

    $pdo->beginTransaction();

    // 2. Create team record (solo and group both use teams for DB consistency)
    $display_team = ($booking_type === 'solo') ? 'Solo Visit' : $team_name;

    $stmt = $pdo->prepare("
        INSERT INTO teams (team_name, leader_id, created_at)
        VALUES (:team_name, :leader_id, NOW())
    ");
    $stmt->execute([':team_name' => $display_team, ':leader_id' => $user_id]);
    $team_id = (int) $pdo->lastInsertId();

    // 3. Add the logged-in user as leader / only member for solo
    $stmt = $pdo->prepare("
        INSERT INTO team_members (team_id, user_id, guest_name, age, is_minor)
        SELECT :team_id, u.user_id, u.name, NULL, 0
        FROM   users u
        WHERE  u.user_id = :uid
    ");
    $stmt->execute([':team_id' => $team_id, ':uid' => $user_id]);

    // 4. Add extra group members (guest rows, no user_id)
    if ($booking_type === 'group') {
        $mem_stmt = $pdo->prepare("
            INSERT INTO team_members (team_id, user_id, guest_name, age, is_minor)
            VALUES (:team_id, NULL, :guest_name, :age, :is_minor)
        ");
        foreach ($members as $m) {
            $mem_stmt->execute([
                ':team_id'    => $team_id,
                ':guest_name' => trim($m['name']),
                ':age'        => (int) ($m['age'] ?? 0),
                ':is_minor'   => (int) (bool) ($m['is_minor'] ?? false),
            ]);
        }
    }

    // 5. Generate unique booking reference: ECO-YYYYMMDD-XXXXX
    do {
        $ref = 'ECO-' . date('Ymd') . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 5));
        $check = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE booking_reference = :ref");
        $check->execute([':ref' => $ref]);
    } while ($check->fetchColumn() > 0); // extremely unlikely collision, but safe

    // 6. Insert booking
    $stmt = $pdo->prepare("
        INSERT INTO bookings (team_id, site_id, schedule_id, total_members, status, booking_reference)
        VALUES (:team_id, :site_id, :schedule_id, :total_members, 'pending', :ref)
    ");
    $stmt->execute([
        ':team_id'       => $team_id,
        ':site_id'       => $site_id,
        ':schedule_id'   => $schedule_id,
        ':total_members' => $total_visitors,
        ':ref'           => $ref,
    ]);
    $booking_id = (int) $pdo->lastInsertId();

    // 7. Log the activity
    $action_text = ucfirst($booking_type) . " booking {$ref} submitted for site ID {$site_id} ({$total_visitors} visitor(s))";
    $stmt = $pdo->prepare("
        INSERT INTO activity_log (user_id, action, time_stamp)
        VALUES (:user_id, :action, NOW())
    ");
    $stmt->execute([':user_id' => $user_id, ':action' => $action_text]);

    $pdo->commit();

    echo json_encode([
        'success'    => true,
        'booking_id' => $booking_id,
        'reference'  => $ref,
        'message'    => 'Booking submitted successfully. Awaiting site manager confirmation.',
    ]);
} catch (PDOException $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    error_log('EcoTour+ booking error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'A server error occurred. Please try again.']);
}
