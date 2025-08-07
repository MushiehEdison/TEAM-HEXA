<?php
// pages/reports.php
session_start();
require_once '../classes/Database.php';
require_once '../classes/Auth.php';

$auth = new Auth();
$auth->requireLogin();

$db = new Database();
$current_user = $auth->getCurrentUser();

// Date range filter
$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date = $_GET['end_date'] ?? date('Y-m-t');

// Get report data
$blood_inventory = getBloodInventoryReport($db, $start_date, $end_date);
$donations = getDonationsReport($db, $start_date, $end_date);
$donors = getDonorsReport($db);
$users = getUsersReport($db);

function getBloodInventoryReport($db, $start_date, $end_date) {
    $query = "SELECT 
                blood_type,
                SUM(CASE WHEN status = 'available' THEN quantity ELSE 0 END) as available,
                SUM(CASE WHEN status = 'used' THEN quantity ELSE 0 END) as used,
                SUM(CASE WHEN status = 'expired' THEN quantity ELSE 0 END) as expired
              FROM blood_inventory
              WHERE collection_date BETWEEN :start_date AND :end_date
              GROUP BY blood_type";
    
    $db->query($query);
    $db->bind(':start_date', $start_date);
    $db->bind(':end_date', $end_date);
    return $db->resultSet();
}

function getDonationsReport($db, $start_date, $end_date) {
    $query = "SELECT 
                DATE(donation_date) as date,
                blood_type,
                COUNT(*) as count,
                SUM(quantity) as total_quantity
              FROM donations
              WHERE donation_date BETWEEN :start_date AND :end_date
              GROUP BY DATE(donation_date), blood_type
              ORDER BY date";
    
    $db->query($query);
    $db->bind(':start_date', $start_date);
    $db->bind(':end_date', $end_date);
    return $db->resultSet();
}

function getDonorsReport($db) {
    $query = "SELECT 
                blood_type,
                COUNT(*) as count,
                SUM(CASE WHEN last_donation_date IS NULL THEN 1 ELSE 0 END) as never_donated,
                SUM(CASE WHEN last_donation_date IS NOT NULL AND 
                          DATEDIFF(CURDATE(), last_donation_date) <= 56 THEN 1 ELSE 0 END) as recently_donated
              FROM donors
              GROUP BY blood_type";
    
    $db->query($query);
    return $db->resultSet();
}

function getUsersReport($db) {
    $query = "SELECT 
                role,
                status,
                COUNT(*) as count
              FROM users
              GROUP BY role, status";
    
    $db->query($query);
    return $db->resultSet();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - Blood Bank Management System</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar (same as other pages) -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <div class="logo">
                    <i class="fas fa-tint"></i>
                    <span>Blood Bank</span>
                </div>
            </div>
            
            <nav class="sidebar-nav">
                <ul>
                    <li>
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
                    <li class="active">
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
            <header class="main-header">
                <!-- Your existing header code -->
                <h1>Reports</h1>
            </header>

            <div class="dashboard-content">
                <!-- Date Range Filter -->
                <div class="report-filters">
                    <form method="GET" class="filter-form">
                        <div class="form-group">
                            <label for="start_date">From</label>
                            <input type="date" name="start_date" id="start_date" value="<?= $start_date ?>">
                        </div>
                        <div class="form-group">
                            <label for="end_date">To</label>
                            <input type="date" name="end_date" id="end_date" value="<?= $end_date ?>">
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter"></i> Apply Filter
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="printReport()">
                            <i class="fas fa-print"></i> Print Report
                        </button>
                    </form>
                </div>

                <!-- Blood Inventory Report -->
                <div class="report-section">
                    <h3><i class="fas fa-flask"></i> Blood Inventory Summary</h3>
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Blood Type</th>
                                    <th>Available (units)</th>
                                    <th>Used (units)</th>
                                    <th>Expired (units)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($blood_inventory as $item): ?>
                                <tr>
                                    <td><span class="blood-type"><?= $item['blood_type'] ?></span></td>
                                    <td><?= $item['available'] ?></td>
                                    <td><?= $item['used'] ?></td>
                                    <td><?= $item['expired'] ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="chart-container">
                        <canvas id="inventoryChart"></canvas>
                    </div>
                </div>

                <!-- Donations Report -->
                <div class="report-section">
                    <h3><i class="fas fa-hand-holding-heart"></i> Donations Report</h3>
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Blood Type</th>
                                    <th>Donations</th>
                                    <th>Total Units</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($donations as $donation): ?>
                                <tr>
                                    <td><?= date('M j, Y', strtotime($donation['date'])) ?></td>
                                    <td><span class="blood-type"><?= $donation['blood_type'] ?></span></td>
                                    <td><?= $donation['count'] ?></td>
                                    <td><?= $donation['total_quantity'] ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="chart-container">
                        <canvas id="donationsChart"></canvas>
                    </div>
                </div>

                <!-- Donors Report -->
                <div class="report-section">
                    <h3><i class="fas fa-user-friends"></i> Donors Summary</h3>
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Blood Type</th>
                                    <th>Total Donors</th>
                                    <th>Never Donated</th>
                                    <th>Recently Donated</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($donors as $donor): ?>
                                <tr>
                                    <td><span class="blood-type"><?= $donor['blood_type'] ?></span></td>
                                    <td><?= $donor['count'] ?></td>
                                    <td><?= $donor['never_donated'] ?></td>
                                    <td><?= $donor['recently_donated'] ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="chart-container">
                        <canvas id="donorsChart"></canvas>
                    </div>
                </div>

                <!-- Users Report -->
                <?php if ($auth->isAdmin()): ?>
                <div class="report-section">
                    <h3><i class="fas fa-users-cog"></i> Users Summary</h3>
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Count</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><?= ucfirst($user['role']) ?></td>
                                    <td><span class="status status-<?= $user['status'] ?>"><?= ucfirst($user['status']) ?></span></td>
                                    <td><?= $user['count'] ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script>
        // Blood Inventory Chart
        const inventoryCtx = document.getElementById('inventoryChart').getContext('2d');
        new Chart(inventoryCtx, {
            type: 'bar',
            data: {
                labels: <?= json_encode(array_column($blood_inventory, 'blood_type')) ?>,
                datasets: [
                    {
                        label: 'Available',
                        data: <?= json_encode(array_column($blood_inventory, 'available')) ?>,
                        backgroundColor: '#4bc0c0'
                    },
                    {
                        label: 'Used',
                        data: <?= json_encode(array_column($blood_inventory, 'used')) ?>,
                        backgroundColor: '#ff6384'
                    },
                    {
                        label: 'Expired',
                        data: <?= json_encode(array_column($blood_inventory, 'expired')) ?>,
                        backgroundColor: '#ffcd56'
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: 'Blood Inventory Summary'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Donations Chart
        const donationsCtx = document.getElementById('donationsChart').getContext('2d');
        new Chart(donationsCtx, {
            type: 'line',
            data: {
                labels: <?= json_encode(array_unique(array_column($donations, 'date'))) ?>,
                datasets: [
                    {
                        label: 'Donations Count',
                        data: <?= json_encode(array_column($donations, 'count')) ?>,
                        borderColor: '#36a2eb',
                        backgroundColor: 'rgba(54, 162, 235, 0.1)',
                        tension: 0.4
                    },
                    {
                        label: 'Total Units',
                        data: <?= json_encode(array_column($donations, 'total_quantity')) ?>,
                        borderColor: '#ff6384',
                        backgroundColor: 'rgba(255, 99, 132, 0.1)',
                        tension: 0.4
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: 'Daily Donations'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Donors Chart
        const donorsCtx = document.getElementById('donorsChart').getContext('2d');
        new Chart(donorsCtx, {
            type: 'doughnut',
            data: {
                labels: <?= json_encode(array_column($donors, 'blood_type')) ?>,
                datasets: [{
                    data: <?= json_encode(array_column($donors, 'count')) ?>,
                    backgroundColor: [
                        '#ff6384', '#36a2eb', '#ffce56', '#4bc0c0',
                        '#9966ff', '#ff9f40', '#8ac249', '#c9cbcf'
                    ]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: 'Donors by Blood Type'
                    }
                }
            }
        });

        function printReport() {
            window.print();
        }
    </script>

    <style>
        .report-filters {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 2rem;
        }

        .filter-form {
            display: flex;
            gap: 1rem;
            align-items: flex-end;
        }

        .report-section {
            background: white;
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .report-section h3 {
            margin-top: 0;
            margin-bottom: 1.5rem;
            color: #333;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .chart-container {
            margin-top: 2rem;
            height: 300px;
        }

        @media print {
            .sidebar, .main-header, .report-filters {
                display: none;
            }
            
            .main-content {
                margin-left: 0;
                width: 100%;
            }
            
            .report-section {
                page-break-inside: avoid;
            }
        }

        @media (max-width: 768px) {
            .filter-form {
                flex-direction: column;
                align-items: stretch;
            }
        }
    </style>
</body>
</html>