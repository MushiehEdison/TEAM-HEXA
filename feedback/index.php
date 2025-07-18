<?php
require_once '../includes/auth.php';
requireAuth();
require_once '../includes/functions.php';

// Handle feedback status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $feedbackId = sanitizeInput($_POST['feedback_id']);
    $status = sanitizeInput($_POST['status']);
    
    $stmt = $conn->prepare("UPDATE feedback SET status = ? WHERE feedback_id = ?");
    $stmt->bind_param("si", $status, $feedbackId);
    
    if ($stmt->execute()) {
        $success = "Feedback status updated successfully!";
    } else {
        $error = "Failed to update feedback status.";
    }
}

// Get all feedback
$feedback = getAllFeedback();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient System - Feedback</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/feedback.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <main class="container">
        <h1>Patient Feedback</h1>
        
        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <div class="feedback-filters">
            <a href="?status=all" class="btn">All</a>
            <a href="?status=pending" class="btn">Pending</a>
            <a href="?status=reviewed" class="btn">Reviewed</a>
            <a href="?status=resolved" class="btn">Resolved</a>
        </div>
        
        <div class="feedback-list">
            <?php foreach ($feedback as $item): ?>
            <div class="feedback-item">
                <div class="feedback-header">
                    <h3><?php echo $item['first_name'] . ' ' . $item['last_name']; ?></h3>
                    <div class="rating">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <span class="star <?php echo $i <= $item['rating'] ? 'filled' : ''; ?>">★</span>
                        <?php endfor; ?>
                    </div>
                    <span class="status-badge <?php echo $item['status']; ?>"><?php echo ucfirst($item['status']); ?></span>
                </div>
                
                <div class="feedback-content">
                    <p><?php echo $item['feedback_text']; ?></p>
                </div>
                
                <div class="feedback-footer">
                    <span class="date"><?php echo date('M d, Y', strtotime($item['feedback_date'])); ?></span>
                    
                    <form method="POST" class="status-form">
                        <input type="hidden" name="feedback_id" value="<?php echo $item['feedback_id']; ?>">
                        <select name="status">
                            <option value="pending" <?php echo $item['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="reviewed" <?php echo $item['status'] == 'reviewed' ? 'selected' : ''; ?>>Reviewed</option>
                            <option value="resolved" <?php echo $item['status'] == 'resolved' ? 'selected' : ''; ?>>Resolved</option>
                        </select>
                        <button type="submit" name="update_status" class="btn btn-sm">Update</button>
                    </form>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </main>
    
    <script src="../assets/js/feedback.js"></script>
</body>
</html>