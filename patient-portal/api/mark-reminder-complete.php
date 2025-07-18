<?php
// Correct path for require statements
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/portal-auth.php';

header("Content-Type: application/json");

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);  // Correct HTTP status code for Method Not Allowed
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

// Check if patient is logged in
if (!isPatientLoggedIn()) {  // Fixed function name
    http_response_code(401);  // Correct HTTP status code for Unauthorized
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

// Get reminder ID from POST data
$reminderId = $_POST['reminder_id'] ?? null;
if (!$reminderId) {
    http_response_code(400);  // Correct HTTP status code for Bad Request
    echo json_encode(['success' => false, 'message' => 'Reminder ID required']);
    exit();
}

try {
    // Get patient ID from session
    $patientId = $_SESSION['patient_id'];
    
    // Verify the reminder belongs to the patient and is pending, then mark as completed
    $stmt = $conn->prepare(
        "UPDATE reminders 
        SET status = 'completed', 
            completed_at = NOW() 
        WHERE id = :reminder_id 
        AND patient_id = :patient_id 
        AND status = 'pending'"
    );
    
    $stmt->bindParam(':reminder_id', $reminderId, PDO::PARAM_INT);
    $stmt->bindParam(':patient_id', $patientId, PDO::PARAM_INT);
    $stmt->execute();

    if ($stmt->rowCount() === 0) {
        http_response_code(404);  // Not Found if no reminder was updated
        echo json_encode(['success' => false, 'message' => 'Reminder not found or already completed']);
        exit();
    }

    echo json_encode(['success' => true, 'message' => 'Reminder marked as complete']);
    
} catch (PDOException $e) {
    http_response_code(500);  // Internal Server Error
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    error_log("Database error in mark-reminder-complete.php: " . $e->getMessage());
}