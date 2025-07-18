<?php
require_once '../../config/database.php';
require_once '../includes/staff-auth.php';
requireStaffAuth();

$page = max(1, $_GET['page'] ?? 1);
$perPage = 20;
$offset = ($page - 1) * $perPage;

// Get SMS logs with patient and reminder info
$query = "
    SELECT sl.*, p.first_name, p.last_name, r.reminder_type, r.reminder_message
    FROM sms_logs sl
    JOIN patients p ON sl.patient_id = p.patient_id
    JOIN reminders r ON sl.reminder_id = r.reminder_id
    ORDER BY sl.created_at DESC
    LIMIT ? OFFSET ?
";

$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $perPage, $offset);
$stmt->execute();
$logs = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get total count
$total = $conn->query("SELECT COUNT(*) as total FROM sms_logs")->fetch_assoc()['total'];
$totalPages = ceil($total / $perPage);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Portal - SMS Status</title>
    <link rel="stylesheet" href="../assets/css/staff.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">SMS Status & Logs</h2>
                <a href="index.php" class="btn btn-secondary">Back to Reminders</a>
            </div>
            
            <?php if (count($logs) > 0): ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Patient</th>
                            <th>Phone</th>
                            <th>Type</th>
                            <th>Message</th>
                            <th>Status</th>
                            <th>Sent At</th>
                            <th>Error</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($logs as $log): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($log['last_name'] . ', ' . $log['first_name']); ?></td>
                            <td><?php echo htmlspecialchars($log['phone_number']); ?></td>
                            <td><?php echo ucfirst($log['reminder_type']); ?></td>
                            <td><?php echo htmlspecialchars(substr($log['message'], 0, 50)) . '...'; ?></td>
                            <td>
                                <span class="status-badge status-<?php echo $log['status']; ?>">
                                    <?php echo ucfirst($log['status']); ?>
                                </span>
                            </td>
                            <td><?php echo date('M j, Y g:i A', strtotime($log['created_at'])); ?></td>
                            <td>
                                <?php if ($log['error_message']): ?>
                                    <span class="error-message" title="<?php echo htmlspecialchars($log['error_message']); ?>">
                                        Error
                                    </span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?php echo $page - 1; ?>" class="btn">Previous</a>
                    <?php endif; ?>
                    
                    <span>Page <?php echo $page; ?> of <?php echo $totalPages; ?></span>
                    
                    <?php if ($page < $totalPages): ?>
                        <a href="?page=<?php echo $page + 1; ?>" class="btn">Next</a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <p>No SMS logs found.</p>
            <?php endif; ?>
        </div>
    </div>
    
    <script src="../assets/js/staff.js"></script>
</body>
</html>