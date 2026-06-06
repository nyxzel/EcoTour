<?php
session_start();
require_once 'database.php';

// AUTH GUARD
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: logout.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// ── 1. STAT CARDS ──────────────────────────────────────────────────────────

// Total visitors (all time from visitors_log)
$totalVisitors = (int) $pdo->query(
    "SELECT COALESCE(SUM(total_visitors), 0) FROM visitors_log"
)->fetchColumn();

// Monthly bookings (current month, any status except cancelled/rejected)
$monthlyBookings = (int) $pdo->query(
    "SELECT COUNT(*) FROM bookings
     WHERE MONTH(updated_at) = MONTH(CURDATE())
       AND YEAR(updated_at)  = YEAR(CURDATE())
       AND status NOT IN ('cancelled','rejected')"
)->fetchColumn();

// Active environmental alerts (env_status with Caution or Critical, last 30 days)
$envAlerts = (int) $pdo->query(
    "SELECT COUNT(*) FROM env_status
     WHERE condition_level IN ('Caution','Critical')
       AND recorded_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)"
)->fetchColumn();

// Average review rating → tourist satisfaction % (rating out of 5 → %)
$avgRating = (float) $pdo->query(
    "SELECT COALESCE(AVG(rating), 0) FROM reviews"
)->fetchColumn();
$satisfaction = $avgRating > 0 ? round(($avgRating / 5) * 100) : 0;

// ── 2. QUICK ANALYTICS ─────────────────────────────────────────────────────

// Most visited site (by sum of visitors_log)
$mostVisited = $pdo->query(
    "SELECT e.name, COALESCE(SUM(v.total_visitors), 0) AS total
     FROM eco_sites e
     LEFT JOIN visitors_log v ON e.site_id = v.site_id
     GROUP BY e.site_id
     ORDER BY total DESC
     LIMIT 1"
)->fetch();

// Lowest environmental risk site (most recent Good/Excellent status, no Caution/Critical)
$lowestRisk = $pdo->query(
    "SELECT e.name
     FROM eco_sites e
     WHERE e.site_id NOT IN (
         SELECT site_id FROM env_status
         WHERE condition_level IN ('Caution','Critical')
           AND recorded_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
     )
     ORDER BY e.site_id
     LIMIT 1"
)->fetchColumn();
if (!$lowestRisk) $lowestRisk = 'N/A';

// Top booking week this month
$topWeek = $pdo->query(
    "SELECT WEEK(updated_at, 1) AS wk,
            YEARWEEK(updated_at, 1) AS yw,
            COUNT(*) AS cnt
     FROM bookings
     WHERE MONTH(updated_at) = MONTH(CURDATE())
       AND YEAR(updated_at)  = YEAR(CURDATE())
       AND status NOT IN ('cancelled','rejected')
     GROUP BY yw
     ORDER BY cnt DESC
     LIMIT 1"
)->fetch();

if ($topWeek) {
    // Human-readable week label (1st, 2nd, 3rd, 4th)
    $dayOfMonth  = date('j', strtotime('monday this week ' . $topWeek['yw']));
    $weekOfMonth = ceil($dayOfMonth / 7);
    $suffixes    = ['', 'st', 'nd', 'rd', 'th'];
    $suffix      = $suffixes[min($weekOfMonth, 4)];
    $topWeekLabel = $weekOfMonth . $suffix . ' Week of ' . date('F');
    $topWeekCount = $topWeek['cnt'];
} else {
    $topWeekLabel = 'No data yet';
    $topWeekCount = 0;
}

// ── 3. RECENT REPORTS TABLE ────────────────────────────────────────────────

// Pull recent reports joined with site name and manager name
$recentReports = $pdo->query(
    "SELECT r.report_id,
            r.issue_type,
            r.description,
            r.report_date,
            e.name  AS site_name,
            u.name  AS manager_name
     FROM reports r
     LEFT JOIN eco_sites e ON r.site_id  = e.site_id
     LEFT JOIN users     u ON r.manager_id = u.user_id
     ORDER BY r.report_date DESC
     LIMIT 10"
)->fetchAll();

// Pull recent env_status rows as environment reports
$recentEnv = $pdo->query(
    "SELECT es.status_id,
            e.name  AS site_name,
            es.condition_level,
            es.notes,
            DATE(es.recorded_at) AS report_date
     FROM env_status es
     LEFT JOIN eco_sites e ON es.site_id = e.site_id
     ORDER BY es.recorded_at DESC
     LIMIT 10"
)->fetchAll();

// Merge and sort by date desc
$allReports = [];

foreach ($recentReports as $r) {
    $allReports[] = [
        'name'     => htmlspecialchars($r['site_name'] . ' – ' . $r['issue_type']),
        'category' => 'Manager Report',
        'date'     => date('M j, Y', strtotime($r['report_date'])),
        'level'    => 'moderate', // manager reports default moderate
        'badge'    => 'Moderate',
    ];
}

foreach ($recentEnv as $r) {
    $level = match ($r['condition_level']) {
        'Critical'  => 'danger',
        'Caution'   => 'warning',
        'Good'      => 'success',
        'Excellent' => 'success',
        default     => 'warning',
    };
    $allReports[] = [
        'name'     => htmlspecialchars($r['site_name']) . ' – Environment',
        'category' => 'Environment',
        'date'     => date('M j, Y', strtotime($r['report_date'])),
        'level'    => $level,
        'badge'    => htmlspecialchars($r['condition_level']),
    ];
}

// Sort by date descending
usort($allReports, fn($a, $b) => strtotime($b['date']) <=> strtotime($a['date']));
$allReports = array_slice($allReports, 0, 10);

// ── 4. BADGE / PROGRESS HELPERS ────────────────────────────────────────────
$maxVisitors  = 10000; // scale for progress bar
$maxBookings  = 2000;
$maxAlerts    = 50;

$visitorPct  = min(100, round(($totalVisitors  / $maxVisitors)  * 100));
$bookingPct  = min(100, round(($monthlyBookings / $maxBookings)  * 100));
$alertPct    = min(100, round(($envAlerts       / $maxAlerts)    * 100));
?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Reports | EcoTour+</title>

    <link rel="stylesheet" href="css/adminreports.css">

    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

</head>

<body>

    <!-- SIDEBAR -->

    <div class="sidebar" id="sidebar">

        <div class="logo">
            <i class="fas fa-leaf"></i>

            <div>
                <span class="logo-text">EcoTour+</span>
                <span class="logo-sub">DAVAO DEL NORTE</span>
            </div>
        </div>

        <a href="admin_dashboard.php"><i class="fas fa-chart-line"></i><span class="menu-text">Dashboard</span></a>
        <a href="admin_users.php"><i class="fas fa-users"></i><span class="menu-text">Users</span></a>
        <a href="admin_managers.php"><i class="fas fa-user-shield"></i><span class="menu-text">Managers</span></a>
        <a href="admin_locations.php" class="active"><i class="fas fa-map-location-dot"></i><span class="menu-text">Locations</span></a>
        <a href="admin_bookings.php"><i class="fas fa-calendar-check"></i><span class="menu-text">Bookings</span></a>
        <div class="sidebar-divider"></div>
        <a href="admin_env.php"><i class="fas fa-triangle-exclamation"></i><span class="menu-text">Environment</span></a>
        <a href="admin_reports.php"><i class="fas fa-chart-pie"></i><span class="menu-text">Reports</span></a>
        <div class="sidebar-divider" style="margin-top:auto;"></div>
        <a href="logout.php"><i class="fas fa-right-from-bracket"></i><span class="menu-text">Logout</span></a>

    </div>

    <!-- TOPBAR -->

    <div class="topbar" id="topbar">

        <button class="btn-toggle" id="toggleBtn">
            <i class="fas fa-bars"></i>
        </button>

        <h4>Welcome, Tourism Admin</h4>

    </div>

    <!-- MAIN CONTENT -->

    <div class="main-content" id="mainContent">

        <p class="section-label">
            OVERVIEW — REPORTS & ANALYTICS
        </p>

        <!-- HERO -->

        <div class="welcome-card">

            <div>

                <h2>Reports & Analytics Dashboard</h2>

                <p>
                    Monitor tourism statistics, visitor trends,
                    environmental alerts, and booking performance
                    across Davao del Norte eco-tourism sites.
                </p>

            </div>

            <i class="fas fa-chart-pie"></i>

        </div>

        <!-- STATS -->

        <div class="stats-grid">

            <div class="dashboard-card">

                <div class="card-icon green">
                    <i class="fas fa-smile"></i>
                </div>

                <h3><?= $satisfaction > 0 ? $satisfaction . '%' : 'N/A' ?></h3>

                <p>Tourist Satisfaction</p>

                <div class="progress-bar">
                    <div class="progress-fill green-fill" style="width:<?= $satisfaction ?>%"></div>
                </div>

            </div>

            <div class="dashboard-card">

                <div class="card-icon orange">
                    <i class="fas fa-users"></i>
                </div>

                <h3><?= number_format($totalVisitors) ?></h3>

                <p>Total Visitors</p>

                <div class="progress-bar">
                    <div class="progress-fill orange-fill" style="width:<?= $visitorPct ?>%"></div>
                </div>

            </div>

            <div class="dashboard-card">

                <div class="card-icon green">
                    <i class="fas fa-calendar-check"></i>
                </div>

                <h3><?= number_format($monthlyBookings) ?></h3>

                <p>Monthly Bookings</p>

                <div class="progress-bar">
                    <div class="progress-fill green-fill" style="width:<?= $bookingPct ?>%"></div>
                </div>

            </div>

            <div class="dashboard-card">

                <div class="card-icon orange">
                    <i class="fas fa-triangle-exclamation"></i>
                </div>

                <h3><?= $envAlerts ?></h3>

                <p>Environmental Alerts</p>

                <div class="progress-bar">
                    <div class="progress-fill orange-fill" style="width:<?= $alertPct ?>%"></div>
                </div>

            </div>

        </div>

        <!-- QUICK ANALYTICS -->

        <div class="analytics-grid">

            <div class="analytics-card">

                <h3>Most Visited Site</h3>

                <h2><?= htmlspecialchars($mostVisited['name'] ?? 'N/A') ?></h2>

                <p><?= number_format((int)($mostVisited['total'] ?? 0)) ?> total visitors logged</p>

            </div>

            <div class="analytics-card">

                <h3>Lowest Environmental Risk</h3>

                <h2><?= htmlspecialchars($lowestRisk) ?></h2>

                <p>No active Caution / Critical alerts</p>

            </div>

            <div class="analytics-card">

                <h3>Top Booking Week</h3>

                <h2><?= htmlspecialchars($topWeekLabel) ?></h2>

                <p><?= $topWeekCount ?> confirmed booking<?= $topWeekCount !== 1 ? 's' : '' ?> this week</p>

            </div>

        </div>

        <!-- REPORT TABLE -->

        <div class="report-card">

            <div class="report-header">

                <div>

                    <h3>Recent Tourism Reports</h3>

                    <span>
                        Latest tourism and environmental analytics
                    </span>

                </div>

            </div>

            <div class="table-responsive">

                <table class="table">

                    <thead>

                        <tr>
                            <th>Report Name</th>
                            <th>Category</th>
                            <th>Date</th>
                            <th>Status</th>
                        </tr>

                    </thead>

                    <tbody>

                        <?php if (empty($allReports)): ?>
                            <tr>
                                <td colspan="4" style="text-align:center;color:var(--text-muted);padding:32px;">
                                    No reports found.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($allReports as $row): ?>
                                <tr>

                                    <td><?= $row['name'] ?></td>

                                    <td><?= $row['category'] ?></td>

                                    <td><?= $row['date'] ?></td>

                                    <td>
                                        <span class="badge <?= $row['level'] ?>">
                                            <?= $row['badge'] ?>
                                        </span>
                                    </td>

                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </div>

    </div>

    <script>
        const sidebar = document.getElementById("sidebar");
        const topbar = document.getElementById("topbar");
        const mainContent = document.getElementById("mainContent");

        document.getElementById("toggleBtn").onclick = () => {

            sidebar.classList.toggle("collapsed");
            topbar.classList.toggle("shifted");
            mainContent.classList.toggle("shifted");

        };
    </script>

</body>

</html>