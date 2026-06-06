<?php
/*
 * tourist-dashboard.php — EcoTour+ Tourist Dashboard
 * DB-connected to real schema: eco_sites, bookings, schedules, teams, activity_log
 * Columns: eco_sites.name, eco_sites.type, eco_sites.location
 *          schedules.available_date, schedules.max_slots
 *          roles.role_name IN ('admin','site_manager','user')
 */

session_start();
require_once 'database.php';

// ── Auth guard ───────────────────────────────────────────────────────────────
if (empty($_SESSION['user_id'])) {
    header('Location: auth.php');
    exit;
}
$role_name = strtolower($_SESSION['role'] ?? 'user');
if ($role_name === 'admin') {
    header('Location: admin-dashboard.php');
    exit;
}
if ($role_name === 'site_manager') {
    header('Location: manager-dashboard.php');
    exit;
}

$user_id    = (int)   $_SESSION['user_id'];
$user_name  = htmlspecialchars($_SESSION['user_name']  ?? 'Traveler');
$user_email = htmlspecialchars($_SESSION['user_email'] ?? '');

// ── Stats ────────────────────────────────────────────────────────────────────
$st = $pdo->prepare("
    SELECT
        COUNT(*)                                                AS total_bookings,
        SUM(b.status = 'confirmed')                             AS confirmed,
        SUM(b.status = 'pending')                               AS pending,
        SUM(b.status = 'cancelled' OR b.status = 'rejected')   AS cancelled,
        SUM(b.status = 'completed')                             AS completed,
        COALESCE(SUM(b.total_members), 0)                       AS total_visitors
    FROM bookings b
    JOIN teams t ON t.team_id = b.team_id
    WHERE t.leader_id = :uid
");
$st->execute([':uid' => $user_id]);
$stats = $st->fetch();

// ── Upcoming bookings ────────────────────────────────────────────────────────
$uq = $pdo->prepare("
    SELECT
        b.booking_reference,
        b.status,
        b.total_members,
        es.name        AS site_name,
        es.type        AS site_type,
        es.location,
        s.available_date
    FROM   bookings b
    JOIN   teams    t  ON t.team_id      = b.team_id
    JOIN   eco_sites es ON es.site_id    = b.site_id
    JOIN   schedules s  ON s.schedule_id = b.schedule_id
    WHERE  t.leader_id = :uid
      AND  b.status   IN ('pending','confirmed')
      AND  s.available_date >= CURDATE()
    ORDER  BY s.available_date ASC
    LIMIT  4
");
$uq->execute([':uid' => $user_id]);
$upcoming = $uq->fetchAll();

// ── Recent activity ──────────────────────────────────────────────────────────
$lq = $pdo->prepare("
    SELECT action, time_stamp
    FROM   activity_log
    WHERE  user_id = :uid
    ORDER  BY time_stamp DESC
    LIMIT  8
");
$lq->execute([':uid' => $user_id]);
$logs = $lq->fetchAll();

// ── Booking history ──────────────────────────────────────────────────────────
$hq = $pdo->prepare("
    SELECT
        b.booking_id,
        b.booking_reference,
        b.status,
        b.total_members,
        b.rejection_reason,
        t.team_name,
        es.name          AS site_name,
        es.type          AS site_type,
        es.location,
        es.entrance_fee,
        s.available_date
    FROM   bookings b
    JOIN   teams    t  ON t.team_id      = b.team_id
    JOIN   eco_sites es ON es.site_id    = b.site_id
    JOIN   schedules s  ON s.schedule_id = b.schedule_id
    WHERE  t.leader_id = :uid
    ORDER  BY b.booking_id DESC
    LIMIT  30
");
$hq->execute([':uid' => $user_id]);
$history = $hq->fetchAll();

function badge_cls(string $s): string
{
    return match ($s) {
        'confirmed' => 'badge-confirmed',
        'pending'   => 'badge-pending',
        'completed' => 'badge-completed',
        default     => 'badge-cancelled',
    };
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Dashboard — EcoTour+</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600;9..40,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/tourist-dashboard.css">
</head>

<body>

    <!-- ══ SIDEBAR ══ -->
    <aside class="sidebar" id="sidebar">
        <div class="sb-brand">
            <a href="ecotour.php" class="sb-brand-link">
                <div class="sb-logo">
                    <svg viewBox="0 0 24 24" fill="none">
                        <path d="M12 2C8 2 4 5.5 4 10c0 3.5 2.5 7 8 10 5.5-3 8-6.5 8-10 0-4.5-4-8-8-8z" fill="currentColor" />
                        <path d="M12 4.5c1.2 1.8 2 4 2 5.5 0 2-1 3.5-2 4.5-1-1-2-2.5-2-4.5 0-1.5.8-3.7 2-5.5z" fill="rgba(255,255,255,.45)" />
                    </svg>
                </div>
                <span>EcoTour<em>+</em></span>
            </a>
        </div>

        <nav class="sb-nav">
            <p class="sb-label">Main</p>
            <a href="tourist-dashboard.php" class="sb-item active">
                <svg viewBox="0 0 24 24">
                    <rect x="3" y="3" width="7" height="7" rx="1.5" />
                    <rect x="14" y="3" width="7" height="7" rx="1.5" />
                    <rect x="3" y="14" width="7" height="7" rx="1.5" />
                    <rect x="14" y="14" width="7" height="7" rx="1.5" />
                </svg>
                Dashboard
            </a>
            <a href="destinations.php" class="sb-item">
                <svg viewBox="0 0 24 24">
                    <circle cx="11" cy="11" r="8" />
                    <path d="m21 21-4.35-4.35" />
                </svg>
                Explore Destinations
            </a>
            <a href="#history" class="sb-item">
                <svg viewBox="0 0 24 24">
                    <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z" />
                    <polyline points="14 2 14 8 20 8" />
                    <line x1="16" y1="13" x2="8" y2="13" />
                    <line x1="16" y1="17" x2="8" y2="17" />
                </svg>
                My Bookings
                <?php if (!empty($stats['pending']) && $stats['pending'] > 0): ?>
                    <span class="sb-badge"><?= (int)$stats['pending'] ?></span>
                <?php endif; ?>
            </a>

            <p class="sb-label" style="margin-top:1.5rem;">Account</p>
            <a href="logout.php" class="sb-item sb-logout">
                <svg viewBox="0 0 24 24">
                    <path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4" />
                    <polyline points="16 17 21 12 16 7" />
                    <line x1="21" y1="12" x2="9" y2="12" />
                </svg>
                Log Out
            </a>
        </nav>

        <div class="sb-user">
            <div class="sb-avatar"><?= mb_strtoupper(mb_substr($user_name, 0, 1)) ?></div>
            <div class="sb-user-text">
                <strong><?= $user_name ?></strong>
                <span><?= $user_email ?></span>
            </div>
        </div>
    </aside>

    <!-- ══ MAIN ══ -->
    <div class="dash-wrap">

        <!-- Topbar -->
        <header class="dash-topbar">
            <button class="dash-ham" id="sidebarToggle" aria-label="Toggle menu">
                <span></span><span></span><span></span>
            </button>
            <div class="dash-greeting">
                <h1>Welcome back, <em><?= explode(' ', $user_name)[0] ?></em></h1>
                <p><?= date('l, F j, Y') ?> &middot; Davao del Norte</p>
            </div>
            <a href="destinations.php" class="dash-cta">
                <svg viewBox="0 0 24 24">
                    <path d="M12 5v14M5 12h14" />
                </svg>
                Book a Visit
            </a>
        </header>

        <div class="dash-body">

            <!-- STATS -->
            <div class="stat-row">
                <div class="stat-card">
                    <div class="stat-ico ico-total">
                        <svg viewBox="0 0 24 24">
                            <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2" />
                            <rect x="9" y="3" width="6" height="4" rx="1" />
                        </svg>
                    </div>
                    <div class="stat-info">
                        <span class="stat-lbl">Total Bookings</span>
                        <span class="stat-num"><?= (int)($stats['total_bookings'] ?? 0) ?></span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-ico ico-confirmed">
                        <svg viewBox="0 0 24 24">
                            <path d="M22 11.08V12a10 10 0 11-5.93-9.14" />
                            <polyline points="22 4 12 14.01 9 11.01" />
                        </svg>
                    </div>
                    <div class="stat-info">
                        <span class="stat-lbl">Confirmed</span>
                        <span class="stat-num"><?= (int)($stats['confirmed'] ?? 0) ?></span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-ico ico-pending">
                        <svg viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="10" />
                            <polyline points="12 6 12 12 16 14" />
                        </svg>
                    </div>
                    <div class="stat-info">
                        <span class="stat-lbl">Awaiting Approval</span>
                        <span class="stat-num"><?= (int)($stats['pending'] ?? 0) ?></span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-ico ico-done">
                        <svg viewBox="0 0 24 24">
                            <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2" />
                            <circle cx="9" cy="7" r="4" />
                            <path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75" />
                        </svg>
                    </div>
                    <div class="stat-info">
                        <span class="stat-lbl">Completed Visits</span>
                        <span class="stat-num"><?= (int)($stats['completed'] ?? 0) ?></span>
                    </div>
                </div>
            </div>

            <!-- MID ROW -->
            <div class="mid-cols">

                <!-- Upcoming -->
                <section class="panel">
                    <div class="panel-hd">
                        <h2>
                            <svg viewBox="0 0 24 24">
                                <rect x="3" y="4" width="18" height="18" rx="2" />
                                <line x1="16" y1="2" x2="16" y2="6" />
                                <line x1="8" y1="2" x2="8" y2="6" />
                                <line x1="3" y1="10" x2="21" y2="10" />
                            </svg>
                            Upcoming Visits
                        </h2>
                        <a href="destinations.php" class="panel-more">+ Book More</a>
                    </div>
                    <?php if (empty($upcoming)): ?>
                        <div class="empty-panel">
                            <svg viewBox="0 0 24 24">
                                <rect x="3" y="4" width="18" height="18" rx="2" />
                                <line x1="16" y1="2" x2="16" y2="6" />
                                <line x1="8" y1="2" x2="8" y2="6" />
                                <line x1="3" y1="10" x2="21" y2="10" />
                            </svg>
                            <p>No upcoming visits yet.</p>
                            <a href="destinations.php" class="btn-outline-sm">Explore Destinations</a>
                        </div>
                    <?php else: ?>
                        <ul class="upcoming-list">
                            <?php foreach ($upcoming as $u):
                                $days = max(0, (int)ceil((strtotime($u['available_date']) - time()) / 86400));
                            ?>
                                <li class="upcoming-item">
                                    <div class="up-date">
                                        <span><?= date('d', strtotime($u['available_date'])) ?></span>
                                        <small><?= date('M', strtotime($u['available_date'])) ?></small>
                                    </div>
                                    <div class="up-info">
                                        <strong><?= htmlspecialchars($u['site_name']) ?></strong>
                                        <span>
                                            <svg viewBox="0 0 24 24">
                                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z" />
                                                <circle cx="12" cy="10" r="3" />
                                            </svg>
                                            <?= htmlspecialchars($u['location']) ?>
                                        </span>
                                        <span>
                                            <svg viewBox="0 0 24 24">
                                                <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2" />
                                                <circle cx="9" cy="7" r="4" />
                                            </svg>
                                            <?= (int)$u['total_members'] ?> visitor<?= $u['total_members'] != 1 ? 's' : '' ?>
                                        </span>
                                    </div>
                                    <div class="up-right">
                                        <span class="badge <?= badge_cls($u['status']) ?>"><?= ucfirst($u['status']) ?></span>
                                        <span class="days-chip"><?= $days === 0 ? 'Today' : "{$days}d away" ?></span>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </section>

                <!-- Activity -->
                <section class="panel">
                    <div class="panel-hd">
                        <h2>
                            <svg viewBox="0 0 24 24">
                                <polyline points="22 12 18 12 15 21 9 3 6 12 2 12" />
                            </svg>
                            Recent Activity
                        </h2>
                    </div>
                    <?php if (empty($logs)): ?>
                        <div class="empty-panel">
                            <svg viewBox="0 0 24 24">
                                <polyline points="22 12 18 12 15 21 9 3 6 12 2 12" />
                            </svg>
                            <p>No activity recorded yet.</p>
                        </div>
                    <?php else: ?>
                        <ul class="activity-list">
                            <?php foreach ($logs as $log): ?>
                                <li class="activity-item">
                                    <div class="act-dot"></div>
                                    <div class="act-body">
                                        <span><?= htmlspecialchars($log['action']) ?></span>
                                        <time><?= date('M j, Y · g:i A', strtotime($log['time_stamp'])) ?></time>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </section>

            </div><!-- /mid-cols -->

            <!-- HISTORY TABLE -->
            <section class="panel panel-wide" id="history">
                <div class="panel-hd">
                    <h2>
                        <svg viewBox="0 0 24 24">
                            <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z" />
                            <polyline points="14 2 14 8 20 8" />
                        </svg>
                        Booking History
                    </h2>
                    <div class="tbl-search">
                        <svg viewBox="0 0 24 24">
                            <circle cx="11" cy="11" r="8" />
                            <path d="m21 21-4.35-4.35" />
                        </svg>
                        <input type="text" id="tblSearch" placeholder="Search reference or site…">
                    </div>
                </div>

                <?php if (empty($history)): ?>
                    <div class="empty-panel" style="padding:3rem;">
                        <svg viewBox="0 0 24 24">
                            <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z" />
                            <polyline points="14 2 14 8 20 8" />
                        </svg>
                        <p>No booking history yet.</p>
                        <a href="destinations.php" class="btn-outline-sm">Start Exploring</a>
                    </div>
                <?php else: ?>
                    <div class="tbl-scroll">
                        <table class="hist-table" id="histTable">
                            <thead>
                                <tr>
                                    <th>Reference</th>
                                    <th>Destination</th>
                                    <th>Visit Date</th>
                                    <th>Visitors</th>
                                    <th>Team</th>
                                    <th>Est. Fee</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($history as $b): ?>
                                    <tr>
                                        <td><code class="ref"><?= htmlspecialchars($b['booking_reference']) ?></code></td>
                                        <td>
                                            <div class="site-cell">
                                                <div class="site-ico">
                                                    <svg viewBox="0 0 24 24">
                                                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z" />
                                                        <circle cx="12" cy="10" r="3" />
                                                    </svg>
                                                </div>
                                                <div>
                                                    <strong><?= htmlspecialchars($b['site_name']) ?></strong>
                                                    <small><?= htmlspecialchars($b['location']) ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td><?= date('M j, Y', strtotime($b['available_date'])) ?></td>
                                        <td><span class="pax"><?= (int)$b['total_members'] ?> pax</span></td>
                                        <td><?= htmlspecialchars($b['team_name']) ?></td>
                                        <td>
                                            <?php $est = $b['entrance_fee'] * $b['total_members']; ?>
                                            <?= $est == 0 ? '<span class="is-free">Free</span>' : '₱' . number_format($est, 0) ?>
                                        </td>
                                        <td>
                                            <span class="badge <?= badge_cls($b['status']) ?>"><?= ucfirst($b['status']) ?></span>
                                            <?php if ($b['status'] === 'rejected' && !empty($b['rejection_reason'])): ?>
                                                <span class="rej-tip" title="<?= htmlspecialchars($b['rejection_reason']) ?>">
                                                    <svg viewBox="0 0 24 24">
                                                        <circle cx="12" cy="12" r="10" />
                                                        <line x1="12" y1="8" x2="12" y2="12" />
                                                        <line x1="12" y1="16" x2="12.01" y2="16" />
                                                    </svg>
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </section>

            <!-- SDG STRIP -->
            <div class="sdg-strip">
                <div class="sdg-strip-ico">
                    <svg viewBox="0 0 24 24">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
                        <path d="M9 12l2 2 4-4" />
                    </svg>
                </div>
                <div class="sdg-strip-text">
                    <strong>Leave No Trace</strong>
                    <p>Every booking supports Davao del Norte's SDG 15 commitment. Stay on marked trails, pack out what you pack in, and respect all wildlife.</p>
                </div>
                <a href="destinations.php" class="sdg-strip-btn">Book Responsibly</a>
            </div>

        </div><!-- /dash-body -->
    </div><!-- /dash-wrap -->

    <script>
        document.getElementById('sidebarToggle').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('open');
        });
        const tblSearch = document.getElementById('tblSearch');
        if (tblSearch) {
            tblSearch.addEventListener('input', function() {
                const q = this.value.toLowerCase();
                document.querySelectorAll('#histTable tbody tr').forEach(r => {
                    r.style.display = r.textContent.toLowerCase().includes(q) ? '' : 'none';
                });
            });
        }
    </script>
</body>

</html>