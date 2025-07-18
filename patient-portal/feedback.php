<?php
require_once 'includes/portal-auth.php';
requirePatientAuth();
require_once 'includes/portal-functions.php';

$patientId = $_SESSION['patient_id'];
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $feedbackText = $_POST['feedback_text'];
    $rating = $_POST['rating'];
    
    if (submitPatientFeedback($patientId, $feedbackText, $rating)) {
        $message = "Thank you for your feedback!";
    } else {
        $message = "Failed to submit feedback. Please try again.";
    }
}

// Get patient's previous feedback
$patientFeedback = [];
$stmt = $conn->prepare("SELECT * FROM feedback WHERE patient_id = ? ORDER BY feedback_date DESC");
$stmt->bind_param("i", $patientId);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $patientFeedback[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Portal - Feedback</title>
    <link rel="stylesheet" href="assets/css/portal.css">
</head>
<body>
    <div class="portal-container">
        <?php include 'portal-header.php'; ?>
        
        <main class="portal-main">
            <div class="portal-content">
                <h1>Feedback</h1>
                
                <?php if ($message): ?>
                    <div class="alert <?php echo strpos($message, 'Thank') !== false ? 'alert-success' : 'alert-danger'; ?>">
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>
                
                <section class="feedback-form-section">
                    <h2>Share Your Feedback</h2>
                    <form method="POST">
                        <div class="form-group">
                            <label for="rating">Rating</label>
                            <div class="rating-input">
                                <input type="radio" id="star5" name="rating" value="5" required>
                                <label for="star5">★</label>
                                <input type="radio" id="star4" name="rating" value="4">
                                <label for="star4">★</label>
                                <input type="radio" id="star3" name="rating" value="3">
                                <label for="star3">★</label>
                                <input type="radio" id="star2" name="rating" value="2">
                                <label for="star2">★</label>
                                <input type="radio" id="star1" name="rating" value="1">
                                <label for="star1">★</label>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="feedback_text">Your Feedback</label>
                            <textarea id="feedback_text" name="feedback_text" rows="5" required></textarea>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Submit Feedback</button>
                    </form>
                </section>
                
                <section class="previous-feedback">
                    <h2>Your Previous Feedback</h2>
                    
                    <?php if (count($patientFeedback) > 0): ?>
                        <div class="feedback-list">
                            <?php foreach ($patientFeedback as $feedback): ?>
                            <div class="feedback-item">
                                <div class="feedback-header">
                                    <div class="feedback-rating">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <span class="star <?php echo $i <= $feedback['rating'] ? 'filled' : ''; ?>">★</span>
                                        <?php endfor; ?>
                                    </div>
                                    <span class="feedback-date">
                                        <?php echo date('M j, Y', strtotime($feedback['feedback_date'])); ?>
                                    </span>
                                </div>
                                <div class="feedback-content">
                                    <p><?php echo $feedback['feedback_text']; ?></p>
                                </div>
                                <div class="feedback-status status-<?php echo $feedback['status']; ?>">
                                    Status: <?php echo ucfirst($feedback['status']); ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p>You haven't submitted any feedback yet.</p>
                    <?php endif; ?>
                </section>
            </div>
        </main>
    </div>
</body>
</html>