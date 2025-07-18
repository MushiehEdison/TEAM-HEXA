<?php
require_once '../config/database.php';
require_once 'functions.php';

// Function to send email reminder
function sendEmailReminder($patientEmail, $subject, $message) {
    $headers = "From: no-reply@yourclinic.com\r\n";
    $headers .= "Reply-To: no-reply@yourclinic.com\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    
    $htmlMessage = "
    <html>
    <head>
        <title>$subject</title>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background-color: #2c3e50; color: white; padding: 10px; text-align: center; }
            .content { padding: 20px; }
            .footer { margin-top: 20px; font-size: 12px; color: #7f8c8d; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h2>Your Clinic Name</h2>
            </div>
            <div class='content'>
                $message
            </div>
            <div class='footer'>
                <p>Please do not reply to this automated message.</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    return mail($patientEmail, $subject, $htmlMessage, $headers);
}

// Function to process pending reminders
function processPendingReminders() {
    global $conn;
    
    // Get reminders due within the next 24 hours that haven't been sent
    $query = "SELECT r.*, p.email, p.phone, p.first_name, p.last_name 
              FROM reminders r 
              JOIN patients p ON r.patient_id = p.patient_id 
              WHERE r.status = 'pending' 
              AND r.due_date BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 24 HOUR)";
    
    $result = $conn->query($query);
    
    while ($reminder = $result->fetch_assoc()) {
        // Send email reminder
        $subject = "Reminder: " . ucfirst($reminder['reminder_type']);
        $message = "Dear " . $reminder['first_name'] . ",<br><br>";
        $message .= "This is a reminder about: " . $reminder['reminder_message'] . "<br><br>";
        $message .= "Due: " . date('F j, Y g:i A', strtotime($reminder['due_date'])) . "<br><br>";
        $message .= "Thank you,<br>Your Clinic Team";
        
        if (sendEmailReminder($reminder['email'], $subject, $message)) {
            // Update status to sent
            $stmt = $conn->prepare("UPDATE reminders SET status = 'sent' WHERE reminder_id = ?");
            $stmt->bind_param("i", $reminder['reminder_id']);
            $stmt->execute();
        }
        
        // Here you would add SMS integration similarly
    }
}

// Function to get patients who haven't given feedback after appointment
function getPatientsForFeedbackRequest() {
    global $conn;
    
    // This would query appointments from the last week without feedback
    $query = "SELECT p.* FROM patients p
              JOIN appointments a ON p.patient_id = a.patient_id
              LEFT JOIN feedback f ON p.patient_id = f.patient_id AND f.feedback_date > a.appointment_date
              WHERE a.appointment_date BETWEEN DATE_SUB(NOW(), INTERVAL 7 DAY) AND NOW()
              AND f.feedback_id IS NULL
              GROUP BY p.patient_id";
    
    $result = $conn->query($query);
    
    $patients = array();
    while ($row = $result->fetch_assoc()) {
        $patients[] = $row;
    }
    
    return $patients;
}
?>