<?php
require_once '../includes/auth.php';
requireAuth();
require_once '../includes/functions.php';
require_once '../includes/reminder_functions.php';

// Set default date range (last 30 days)
$startDate = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d', strtotime('-30 days'));
$endDate = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');

// Get feedback statistics
$feedbackStats = [];
$stmt = $conn->prepare("
    SELECT 
        COUNT(*) as total,
        AVG(rating) as avg_rating,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
        SUM(CASE WHEN status = 'reviewed' THEN 1 ELSE 0 END) as reviewed,
        SUM(CASE WHEN status = 'resolved' THEN 1 ELSE 0 END) as resolved
    FROM feedback
    WHERE feedback_date BETWEEN ? AND ?
");
$stmt->bind_param("ss", $startDate, $endDate);
$stmt->execute();
$result = $stmt->get_result();
$feedbackStats = $result->fetch_assoc();

// Get reminder statistics
$reminderStats = [];
$stmt = $conn->prepare("
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
        SUM(CASE WHEN status = 'sent' THEN 1 ELSE 0 END) as sent,
        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
        SUM(CASE WHEN reminder_type = 'appointment' THEN 1 ELSE 0 END) as appointment,
        SUM(CASE WHEN reminder_type = 'medication' THEN 1 ELSE 0 END) as medication,
        SUM(CASE WHEN reminder_type = 'followup' THEN 1 ELSE 0 END) as followup,
        SUM(CASE WHEN reminder_type = 'payment' THEN 1 ELSE 0 END) as payment
    FROM reminders
    WHERE created_at BETWEEN ? AND ?
");
$stmt->bind_param("ss", $startDate, $endDate);
$stmt->execute();
$result = $stmt->get_result();
$reminderStats = $result->fetch_assoc();

// Get feedback trend data for chart
$feedbackTrend = [];
$stmt = $conn->prepare("
    SELECT 
        DATE(feedback_date) as day,
        COUNT(*) as count,
        AVG(rating) as avg_rating
    FROM feedback
    WHERE feedback_date BETWEEN ? AND ?
    GROUP BY DATE(feedback_date)
    ORDER BY DATE(feedback_date)
");
$stmt->bind_param("ss", $startDate, $endDate);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $feedbackTrend[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient System - Reports</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/reports.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <main class="container">
        <h1>System Reports</h1>
        
        <div class="report-filters">
            <form method="GET">
                <div class="filter-group">
                    <label for="start_date">Start Date</label>
                    <input type="date" id="start_date" name="start_date" value="<?php echo $startDate; ?>">
                </div>
                
                <div class="filter-group">
                    <label for="end_date">End Date</label>
                    <input type="date" id="end_date" name="end_date" value="<?php echo $endDate; ?>">
                </div>
                
                <button type="submit" class="btn btn-primary">Apply Filters</button>
                <button type="button" id="export-btn" class="btn btn-secondary">Export Report</button>
            </form>
        </div>
        
        <div class="report-sections">
            <section class="feedback-report">
                <h2>Feedback Statistics</h2>
                
                <div class="stats-grid">
                    <div class="stat-card">
                        <h3>Total Feedback</h3>
                        <p><?php echo $feedbackStats['total']; ?></p>
                    </div>
                    
                    <div class="stat-card">
                        <h3>Average Rating</h3>
                        <p><?php echo number_format($feedbackStats['avg_rating'], 1); ?>/5</p>
                    </div>
                    
                    <div class="stat-card">
                        <h3>Pending</h3>
                        <p><?php echo $feedbackStats['pending']; ?></p>
                    </div>
                    
                    <div class="stat-card">
                        <h3>Reviewed</h3>
                        <p><?php echo $feedbackStats['reviewed']; ?></p>
                    </div>
                    
                    <div class="stat-card">
                        <h3>Resolved</h3>
                        <p><?php echo $feedbackStats['resolved']; ?></p>
                    </div>
                </div>
                
                <div class="chart-container">
                    <canvas id="feedbackTrendChart"></canvas>
                </div>
            </section>
            
            <section class="reminder-report">
                <h2>Reminder Statistics</h2>
                
                <div class="stats-grid">
                    <div class="stat-card">
                        <h3>Total Reminders</h3>
                        <p><?php echo $reminderStats['total']; ?></p>
                    </div>
                    
                    <div class="stat-card">
                        <h3>Pending</h3>
                        <p><?php echo $reminderStats['pending']; ?></p>
                    </div>
                    
                    <div class="stat-card">
                        <h3>Sent</h3>
                        <p><?php echo $reminderStats['sent']; ?></p>
                    </div>
                    
                    <div class="stat-card">
                        <h3>Completed</h3>
                        <p><?php echo $reminderStats['completed']; ?></p>
                    </div>
                </div>
                
                <div class="reminder-type-stats">
                    <h3>By Reminder Type</h3>
                    <div class="type-grid">
                        <div>
                            <h4>Appointments</h4>
                            <p><?php echo $reminderStats['appointment']; ?></p>
                        </div>
                        <div>
                            <h4>Medications</h4>
                            <p><?php echo $reminderStats['medication']; ?></p>
                        </div>
                        <div>
                            <h4>Follow-ups</h4>
                            <p><?php echo $reminderStats['followup']; ?></p>
                        </div>
                        <div>
                            <h4>Payments</h4>
                            <p><?php echo $reminderStats['payment']; ?></p>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </main>
    
    <script>
        // Feedback Trend Chart
        const feedbackCtx = document.getElementById('feedbackTrendChart').getContext('2d');
        const feedbackTrendChart = new Chart(feedbackCtx, {
            type: 'line',
            data: {
                labels: [<?php echo implode(',', array_map(function($item) { return "'" . date('M j', strtotime($item['day'])) . "'"; }, $feedbackTrend)); ?>],
                datasets: [
                    {
                        label: 'Number of Feedback',
                        data: [<?php echo implode(',', array_column($feedbackTrend, 'count')); ?>],
                        borderColor: 'rgba(54, 162, 235, 1)',
                        backgroundColor: 'rgba(54, 162, 235, 0.2)',
                        tension: 0.1,
                        yAxisID: 'y'
                    },
                    {
                        label: 'Average Rating',
                        data: [<?php echo implode(',', array_column($feedbackTrend, 'avg_rating')); ?>],
                        borderColor: 'rgba(255, 159, 64, 1)',
                        backgroundColor: 'rgba(255, 159, 64, 0.2)',
                        tension: 0.1,
                        yAxisID: 'y1'
                    }
                ]
            },
            options: {
                responsive: true,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        title: {
                            display: true,
                            text: 'Number of Feedback'
                        }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        min: 0,
                        max: 5,
                        title: {
                            display: true,
                            text: 'Average Rating'
                        },
                        grid: {
                            drawOnChartArea: false
                        }
                    }
                }
            }
        });
        
        // Export button functionality
        document.getElementById('export-btn').addEventListener('click', function() {
            // This would typically make an AJAX call to generate a PDF or CSV
            alert('Export functionality would be implemented here');
        });
    </script>
</body>
</html>