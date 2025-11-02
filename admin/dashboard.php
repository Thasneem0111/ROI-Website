<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}
require_once 'db_connect.php';
// set active tab for sidebar
$active = 'dashboard';
// compute overview counts (wrap queries to avoid fatal errors if tables don't exist)
$totalUsers = 0;
$newUsersWeek = 0;
$totalClients = 0;
$totalBlogs = 0;
try {
    // total unique visitors
    $res = $conn->query("SELECT COUNT(DISTINCT visitor_key) AS c FROM visitors");
    if ($res) { $row = $res->fetch_assoc(); $totalUsers = intval($row['c']); if (method_exists($res,'free')) $res->free(); }
} catch (Throwable $e) { /* table may not exist yet */ }
try {
    // new users this ISO week
    $res = $conn->query("SELECT COUNT(DISTINCT visitor_key) AS c FROM visitors WHERE YEARWEEK(created_at,1)=YEARWEEK(CURDATE(),1)");
    if ($res) { $row = $res->fetch_assoc(); $newUsersWeek = intval($row['c']); if (method_exists($res,'free')) $res->free(); }
} catch (Throwable $e) { }
try {
    $res = $conn->query("SELECT COUNT(*) AS c FROM clients");
    if ($res) { $row = $res->fetch_assoc(); $totalClients = intval($row['c']); if (method_exists($res,'free')) $res->free(); }
} catch (Throwable $e) { }
try {
    $res = $conn->query("SELECT COUNT(*) AS c FROM blog");
    if ($res) { $row = $res->fetch_assoc(); $totalBlogs = intval($row['c']); if (method_exists($res,'free')) $res->free(); }
} catch (Throwable $e) { }

// total services and industries
$totalServices = 0;
$totalIndustries = 0;
try {
    $res = $conn->query("SELECT COUNT(*) AS c FROM services");
    if ($res) { $row = $res->fetch_assoc(); $totalServices = intval($row['c']); if (method_exists($res,'free')) $res->free(); }
} catch (Throwable $e) { }
try {
    $res = $conn->query("SELECT COUNT(*) AS c FROM industry");
    if ($res) { $row = $res->fetch_assoc(); $totalIndustries = intval($row['c']); if (method_exists($res,'free')) $res->free(); }
} catch (Throwable $e) { }

// Prepare month-wise visitor counts starting from November 2025 (12 months)
$chart_start = new DateTime('2025-11-01');
$months = [];
$keys = [];
for ($i = 0; $i < 12; $i++) {
    $dt = (clone $chart_start)->modify("+{$i} months");
    $months[] = $dt->format('M Y');
    $keys[] = $dt->format('Y-m');
}
$counts = array_fill(0, 12, 0);
try {
    // Query visitor counts grouped by year/month for the 12-month window
    $start_sql = $chart_start->format('Y-m-01 00:00:00');
    $end_sql = (clone $chart_start)->modify('+12 months')->format('Y-m-01 00:00:00');
    $stmt = $conn->prepare("SELECT YEAR(created_at) AS y, MONTH(created_at) AS m, COUNT(DISTINCT visitor_key) AS c FROM visitors WHERE created_at >= ? AND created_at < ? GROUP BY y,m");
    if ($stmt) {
        $stmt->bind_param('ss', $start_sql, $end_sql);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($r = $res->fetch_assoc()) {
            $k = sprintf('%04d-%02d', $r['y'], $r['m']);
            $idx = array_search($k, $keys, true);
            if ($idx !== false) { $counts[$idx] = intval($r['c']); }
        }
        $stmt->close();
    }
} catch (Throwable $e) {
    // visitors table might not exist yet — keep zeros
}

$chart_labels_json = json_encode($months);
$chart_data_json = json_encode($counts);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <title>ROI - Admin Dashboard</title>
    <link rel="icon" type="image/png" href="../images/logo.png">
    <link rel="stylesheet" href="./dashboard.css">
    <link rel="stylesheet" href="../styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <!-- Logout Confirmation Modal -->
    <div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0" style="justify-content:center;">
                    <h5 class="modal-title w-100 text-center" id="logoutModalLabel" style="font-weight:700;">Logout Confirmation</h5>
                    <button type="button" class="btn-close position-absolute end-0 me-3" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center" style="font-size:1.15rem; color:#222; padding:24px 12px 12px 12px;">
                    <i class="fa fa-sign-out-alt" style="font-size:2.2rem; color:#2ca6a4; margin-bottom:12px;"></i><br>
                    Are you sure you want to logout?
                </div>
                <div class="modal-footer border-0" style="justify-content:center; gap:18px; padding-bottom:24px;">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal" style="border-radius:8px;">Cancel</button>
                    <a href="logout.php" class="btn btn-primary px-4" style="border-radius:8px; background:#218a8a; border:none; color:#fff;">Yes</a>
                </div>
            </div>
        </div>
    </div>

    <div class="dashboard-container">
        <?php include __DIR__ . '/sidebar.php'; ?>

        <!-- Main Content -->
        <main class="main-panel">
            <!-- Top Bar -->
            <div class="top-bar">
                <h1>Overview</h1>
                <div class="top-bar-right">
                    <div class="search-bar">
                        <input type="text" placeholder="Search">
                        <button><i class="fa fa-search"></i></button>
                    </div>
                    <div class="admin-profile">
                        <img src="../images/man.png" alt="Admin" class="profile-pic">
                        <span>Mr. Kaleel</span>
                    </div>
                </div>
            </div>

            <!-- Overview Boxes and content copied from old dashboard -->
            <div class="overview-boxes">
                <div class="overview-box">
                    <div class="box-title">Total Users</div>
                    <div class="box-value"><?php echo number_format($totalUsers); ?></div>
                </div>
                <div class="overview-box">
                    <div class="box-title">Total Services</div>
                    <div class="box-value"><?php echo number_format($totalServices); ?></div>
                    <a href="services.php" class="box-link">View More</a>
                </div>
                <div class="overview-box">
                    <div class="box-title">Total Clients</div>
                    <div class="box-value"><?php echo number_format($totalClients); ?></div>
                    <a href="clients.php" class="box-link">View More</a>
                </div>
                <div class="overview-box">
                    <div class="box-title">Total Blogs</div>
                    <div class="box-value"><?php echo number_format($totalBlogs); ?></div>
                    <a href="blog.php" class="box-link">View More</a>
                </div>
                <div class="overview-box">
                    <div class="box-title">Total Industries</div>
                    <div class="box-value"><?php echo number_format($totalIndustries); ?></div>
                    <a href="industries.php" class="box-link">View More</a>
                </div>
            </div>

            <div class="recent-emails-section">
                <h2>Recent Emails</h2>
                <div class="recent-emails-list">
                    <div class="email-item">
                        <span class="email-name">Nicholas Patrick</span>
                        <span class="email-business">business name</span>
                        <span class="email-address">email@gmail.com</span>
                        <span class="email-phone">+974 1234 5678</span>
                        <span class="email-time">30 minutes ago</span>
                    </div>
                </div>
            </div>

            <div class="user-graph-section">
                <h2>Our Users</h2>
                <canvas id="userCountChart" width="400" height="200"></canvas>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var logoutBtn = document.getElementById('logoutBtn');
            var logoutModalEl = document.getElementById('logoutModal');
            var logoutModal = new bootstrap.Modal(logoutModalEl);
            logoutBtn.addEventListener('click', function() { logoutModal.show(); });

            // Chart - monthly visitors starting Nov 2025 (12 months)
            const ctx = document.getElementById('userCountChart').getContext('2d');
            const chartLabels = <?php echo $chart_labels_json ?? json_encode([]); ?>;
            const chartData = <?php echo $chart_data_json ?? json_encode(array_fill(0,12,0)); ?>;
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: chartLabels,
                    datasets: [{
                        label: 'Monthly Visitors',
                        data: chartData,
                        borderColor: '#2ca6a4',
                        backgroundColor: 'rgba(44,166,164,0.12)',
                        fill: true,
                        tension: 0.3,
                        pointRadius: 4,
                        pointBackgroundColor: '#2ca6a4'
                    }]
                },
                options: {
                    responsive: true,
                    plugins: { legend: { display: true } },
                    scales: {
                        x: { display: true, title: { display: false } },
                        y: { display: true, beginAtZero: true, title: { display: true, text: 'Visitors' } }
                    }
                }
            });
        });
    </script>
</body>
</html>
