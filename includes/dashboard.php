<?php
require_once 'includes/auth.php';
requireAuth();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient System - Dashboard</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/dashboard.css">
</head>
<body>
    <div class="dashboard-container">
        <header class="dashboard-header">
            <h1>Patient Management System</h1>
            <nav>
                <ul>
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="patients.php">Patients</a></li>
                    <li><a href="feedback/">Feedback</a></li>
                    <li><a href="reminders/">Reminders</a></li>
                    <li><a href="logout.php">Logout</a></li>
                </ul>
            </nav>
        </header>
        
        <main class="dashboard-main">
            <div class="stats-container">
                <div class="stat-card">
                    <h3>Total Patients</h3>
                    <p>150</p>
                </div>
                
                <div class="stat-card">
                    <h3>Pending Feedback</h3>
                    <p>12</p>
                </div>
                
                <div class="stat-card">
                    <h3>Upcoming Reminders</h3>
                    <p>8</p>
                </div>
            </div>
            
            <div class="recent-activity">
                <h2>Recent Activity</h2>
                <div class="activity-list">
                    <!-- Recent activity items would go here -->
                </div>
            </div>
        </main>
    </div>
    
    <script src="assets/js/dashboard.js"></script>
</body>
</html>