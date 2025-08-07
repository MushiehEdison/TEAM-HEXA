<?php
// pages/dashboard.php
session_start();
require_once '../classes/Database.php';
require_once '../classes/Auth.php';
require_once '../classes/BloodBank.php';
require_once '../classes/Staff.php';

$auth = new Auth();
$auth->requireLogin();

$bloodBank = new BloodBank();
$staff = new Staff();

// Get dashboard statistics
$stats = $bloodBank->getDashboardStats();
$blood_summary = $bloodBank->getBloodSummary();
$recent_requests = $bloodBank->getBloodRequests();
$recent_donations = $bloodBank->getDonations(10);
$expiring_blood = $bloodBank->getExpiringBlood(7);

$current_user = $auth->getCurrentUser();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Blood Bank Management System</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <div class="logo">
                    <i class="fas fa-tint"></i>
                    <span>Blood Bank</span>
                </div>
            </div>
            
            <nav class="sidebar-nav">
                <ul>
                    <li class="active">
                        <a href="dashboard.php">
                            <i class="fas fa-chart-bar"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li>
                        <a href="blood-inventory.php">
                            <i class="fas fa-flask"></i>
                            <span>Blood Inventory</span>
                        </a>
                    </li>
                    <li>
                        <a href="donations.php">
                            <i class="fas fa-hand-holding-heart"></i>
                            <span>Donations</span>
                        </a>
                    </li>
                    <li>
                        <a href="requests.php">
                            <i class="fas fa-clipboard-list"></i>
                            <span>Blood Requests</span>
                        </a>
                    </li>
                    <li>
                        <a href="forecasting.php">
                            <i class="fas fa-chart-line"></i>
                            <span>Forecasting</span>
                        </a>
                    </li>
                    <?php if ($auth->isAdmin()): ?>
                    <li>
                        <a href="staff.php">
                            <i class="fas fa-users"></i>
                            <span>Staff Management</span>
                        </a>
                    </li>
                    <?php endif; ?>
                    <li>
                        <a href="reports.php">
                            <i class="fas fa-file-alt"></i>
                            <span>Reports</span>
                        </a>
                    </li>
                    <li>
                        <a href="settings.php">
                            <i class="fas fa-cog"></i>
                            <span>Settings</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Header -->
            <header class="main-header">
                <div class="header-left">
                    <button class="sidebar-toggle" onclick="toggleSidebar()">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h1>Dashboard</h1>
                </div>
                
                <div class="header-right">
                    <div class="notifications">
                        <button class="notification-btn">
                            <i class="fas fa-bell"></i>
                            <?php if ($stats['critical_requests'] > 0 || $stats['expiring_units'] > 0): ?>
                            <span class="badge"><?php echo $stats['critical_requests'] + $stats['expiring_units']; ?></span>
                            <?php endif; ?>
                        </button>
                    </div>
                    
                    <div class="user-menu">
                        <div class="user-info">
                            <span class="user-name"><?php echo htmlspecialchars($current_user['full_name']); ?></span>
                            <span class="user-role"><?php echo ucfirst($current_user['role']); ?></span>
                        </div>
                        <div class="user-avatar">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="dropdown">
                            <a href="settings.php"><i class="fas fa-user-cog"></i> Profile</a>
                            <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Dashboard Content -->
            <div class="dashboard-content">
                <!-- Alert Section -->
                <?php if ($stats['critical_requests'] > 0 || $stats['expiring_units'] > 0): ?>
                <div class="alerts-section">
                    <?php if ($stats['critical_requests'] > 0): ?>
                    <div class="alert alert-critical">
                        <i class="fas fa-exclamation-triangle"></i>
                        <span><?php echo $stats['critical_requests']; ?> critical blood request(s) require immediate attention!</span>
                        <a href="requests.php?filter=critical" class="alert-action">View Requests</a>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($stats['expiring_units'] > 0): ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-clock"></i>
                        <span><?php echo $stats['expiring_units']; ?> blood unit(s) expiring within 7 days!</span>
                        <a href="blood-inventory.php?filter=expiring" class="alert-action">View Inventory</a>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <!-- Statistics Cards -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon blood-units">
                            <i class="fas fa-flask"></i>
                        </div>
                        <div class="stat-content">
                            <h3><?php echo array_sum(array_column($stats['blood_types'], 'total')); ?></h3>
                            <p>Total Blood Units</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon pending-requests">
                            <i class="fas fa-clipboard-list"></i>
                        </div>
                        <div class="stat-content">
                            <h3><?php echo $stats['pending_requests']; ?></h3>
                            <p>Pending Requests</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon donors">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-content">
                            <h3><?php echo $stats['total_donors']; ?></h3>
                            <p>Total Donors</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon recent-donations">
                            <i class="fas fa-heart"></i>
                        </div>
                        <div class="stat-content">
                            <h3><?php echo $stats['recent_donations']; ?></h3>
                            <p>Donations (30 days)</p>
                        </div>
                    </div>
                </div>

                <!-- Charts Section -->
                <div class="charts-section">
                    <div class="chart-container">
                        <div class="chart-header">
                            <h3>Blood Inventory by Type</h3>
                        </div>
                        <div class="chart-body">
                            <canvas id="bloodInventoryChart"></canvas>
                        </div>
                    </div>
                    
                    <div class="chart-container">
                        <div class="chart-header">
                            <h3>Monthly Donation Trends</h3>
                        </div>
                        <div class="chart-body">
                            <canvas id="donationTrendsChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Data Tables Section -->
                <div class="tables-section">
                    <!-- Recent Blood Requests -->
                    <div class="table-container">
                        <div class="table-header">
                            <h3>Recent Blood Requests</h3>
                            <a href="requests.php" class="view-all-btn">View All</a>
                        </div>
                        <div class="table-responsive">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Patient</th>
                                        <th>Blood Type</th>
                                        <th>Quantity</th>
                                        <th>Urgency</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach (array_slice($recent_requests, 0, 5) as $request): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($request['patient_name']); ?></td>
                                        <td><span class="blood-type"><?php echo $request['blood_type']; ?></span></td>
                                        <td><?php echo $request['quantity_needed']; ?> units</td>
                                        <td>
                                            <span class="urgency urgency-<?php echo $request['urgency']; ?>">
                                                <?php echo ucfirst($request['urgency']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="status status-<?php echo $request['status']; ?>">
                                                <?php echo ucfirst($request['status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('M j, Y', strtotime($request['request_date'])); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Expiring Blood Units -->
                    <?php if (!empty($expiring_blood)): ?>
                    <div class="table-container">
                        <div class="table-header">
                            <h3>Blood Units Expiring Soon</h3>
                            <a href="blood-inventory.php?filter=expiring" class="view-all-btn">View All</a>
                        </div>
                        <div class="table-responsive">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Blood Type</th>
                                        <th>Quantity</th>
                                        <th>Expiry Date</th>
                                        <th>Days Left</th>
                                        <th>Donor</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach (array_slice($expiring_blood, 0, 5) as $blood): ?>
                                    <?php $days_left = (strtotime($blood['expiry_date']) - time()) / (60 * 60 * 24); ?>
                                    <tr class="<?php echo $days_left <= 3 ? 'urgent-expiry' : ''; ?>">
                                        <td><span class="blood-type"><?php echo $blood['blood_type']; ?></span></td>
                                        <td><?php echo $blood['quantity']; ?> units</td>
                                        <td><?php echo date('M j, Y', strtotime($blood['expiry_date'])); ?></td>
                                        <td>
                                            <span class="days-left <?php echo $days_left <= 3 ? 'critical' : ($days_left <= 7 ? 'warning' : ''); ?>">
                                                <?php echo max(0, floor($days_left)); ?> days
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($blood['donor_name'] ?? 'Unknown'); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <script src="../assets/js/main.js"></script>
    <script>
        // Blood Inventory Chart
        const inventoryCtx = document.getElementById('bloodInventoryChart').getContext('2d');
        new Chart(inventoryCtx, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode(array_column($blood_summary, 'blood_type')); ?>,
                datasets: [{
                    data: <?php echo json_encode(array_column($blood_summary, 'available_units')); ?>,
                    backgroundColor: [
                        '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0',
                        '#9966FF', '#FF9F40', '#FF6384', '#C9CBCF'
                    ],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // Donation Trends Chart (Mock data for demonstration)
        const trendsCtx = document.getElementById('donationTrendsChart').getContext('2d');
        new Chart(trendsCtx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                datasets: [{
                    label: 'Donations',
                    data: [65, 59, 80, 81, 56, 55],
                    borderColor: '#36A2EB',
                    backgroundColor: 'rgba(54, 162, 235, 0.1)',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
</body>
</html>