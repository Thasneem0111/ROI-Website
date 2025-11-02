<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}
require_once 'db_connect.php';
// set active tab for sidebar
$active = 'dashboard';
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
                    <div class="box-value">25.1k</div>
                    <div class="box-change positive">+15%</div>
                    <a href="#" class="box-link">View Report</a>
                </div>
                <div class="overview-box">
                    <div class="box-title">New Users</div>
                    <div class="box-value">89/Wk</div>
                    <div class="box-change negative">-3.5%</div>
                    <a href="#" class="box-link">View Report</a>
                </div>
                <div class="overview-box">
                    <div class="box-title">Total Clients</div>
                    <div class="box-value">100</div>
                    <div class="box-change positive">+15%</div>
                    <a href="#" class="box-link">View More</a>
                </div>
                <div class="overview-box">
                    <div class="box-title">Total Blogs</div>
                    <div class="box-value">7</div>
                    <div class="box-change positive">+10%</div>
                    <a href="#" class="box-link">View More</a>
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

            // Chart
            const ctx = document.getElementById('userCountChart').getContext('2d');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['2015','2016','2017','2018','2019','2020'],
                    datasets: [{ label: 'Client Counts', data: [35,20,15,35,20,40], borderColor:'#2ca6a4', backgroundColor:'rgba(44,166,164,0.1)', fill:true }]
                },
                options: { responsive:true }
            });
        });
    </script>
</body>
</html>
