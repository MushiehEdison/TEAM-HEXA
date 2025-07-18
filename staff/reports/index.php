<?php
require_once '../../config/database.php';
require_once '../includes/staff-auth.php';
requireStaffAuth();

if (!$_SESSION['staff_permissions']['generate_reports']) {
    header("Location: ../dashboard.php");
    exit();
}

// Default date range (last 30 days)
$startDate = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
$endDate = $_GET['end_date'] ?? date('Y-m-d');

// Validate dates
if (!strtotime($startDate)) {
    $startDate = date('Y-m-d', strtotime('-30 days'));
}
if (!strtotime($endDate)) {
    $endDate = date('Y-m-d');
}

// Prepare dates for SQL (prevent SQL injection)
$safeStartDate = $conn->real_escape_string($startDate);
$safeEndDate = $conn->real_escape_string($endDate);

// Get report data
$reportData = [];

// Appointments by type
$result = $conn->query("
    SELECT appointment_type, COUNT(*) as count 
    FROM appointments 
    WHERE appointment_date BETWEEN '$safeStartDate' AND '$safeEndDate 23:59:59'
    GROUP BY appointment_type
    ORDER BY count DESC
");
$reportData['appointments_by_type'] = $result->fetch_all(MYSQLI_ASSOC);

// Feedback by rating (initialize all ratings first)
$feedbackRatings = [
    '5' => 0,
    '4' => 0,
    '3' => 0,
    '2' => 0,
    '1' => 0
];

$result = $conn->query("
    SELECT rating, COUNT(*) as count 
    FROM feedback 
    WHERE feedback_date BETWEEN '$safeStartDate' AND '$safeEndDate 23:59:59'
    GROUP BY rating
    ORDER BY rating DESC
");

while ($row = $result->fetch_assoc()) {
    $feedbackRatings[$row['rating']] = $row['count'];
}
$reportData['feedback_by_rating'] = $feedbackRatings;

// Reminders by type
$result = $conn->query("
    SELECT reminder_type, COUNT(*) as count 
    FROM reminders 
    WHERE created_at BETWEEN '$safeStartDate' AND '$safeEndDate 23:59:59'
    GROUP BY reminder_type
    ORDER BY count DESC
");
$reportData['reminders_by_type'] = $result->fetch_all(MYSQLI_ASSOC);

// Patient growth
$result = $conn->query("
    SELECT DATE(created_at) as date, COUNT(*) as count 
    FROM patients 
    WHERE created_at BETWEEN '$safeStartDate' AND '$safeEndDate 23:59:59'
    GROUP BY DATE(created_at)
    ORDER BY date
");
$reportData['patient_growth'] = $result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Portal - Reports</title>
    <link rel="stylesheet" href="../assets/css/staff.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <!-- Rest of your HTML remains the same until the feedback chart JavaScript -->
    
    <script>
        // Feedback Ratings Chart - corrected version
        const feedbackCtx = document.getElementById('feedbackChart').getContext('2d');
        const feedbackChart = new Chart(feedbackCtx, {
            type: 'pie',
            data: {
                labels: ['5 Stars', '4 Stars', '3 Stars', '2 Stars', '1 Star'],
                datasets: [{
                    data: [
                        <?php echo $reportData['feedback_by_rating']['5']; ?>,
                        <?php echo $reportData['feedback_by_rating']['4']; ?>,
                        <?php echo $reportData['feedback_by_rating']['3']; ?>,
                        <?php echo $reportData['feedback_by_rating']['2']; ?>,
                        <?php echo $reportData['feedback_by_rating']['1']; ?>
                    ],
                    backgroundColor: [
                        'rgba(46, 204, 113, 0.7)',
                        'rgba(52, 152, 219, 0.7)',
                        'rgba(241, 196, 15, 0.7)',
                        'rgba(230, 126, 34, 0.7)',
                        'rgba(231, 76, 60, 0.7)'
                    ],
                    borderColor: [
                        'rgba(46, 204, 113, 1)',
                        'rgba(52, 152, 219, 1)',
                        'rgba(241, 196, 15, 1)',
                        'rgba(230, 126, 34, 1)',
                        'rgba(231, 76, 60, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true
            }
        });
        
        // Rest of your JavaScript remains the same
    </script>
</body>
</html>