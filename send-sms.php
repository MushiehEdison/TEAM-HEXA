<?php
// send-sms.php - Place this file in your project root or cron folder
require_once 'config/database.php';
require_once 'includes/twilio-helper.php';

// Initialize Twilio SMS helper
$smsHelper = new TwilioSMSHelper();

// Get pending reminders
 $pendingReminders = getPendingReminders($conn);

echo "Found " . count($pendingReminders) . " pending reminders\n";

foreach ($pendingReminders as $reminder) {
    // Skip if patient doesn't have a phone number
    if (empty($reminder['phone_number'])) {
        echo "Skipping {$reminder['first_name']} {$reminder['last_name']} - no phone number\n";
        continue;
    }
    
    // Format phone number (ensure it has country code)
    $phoneNumber = formatPhoneNumber($reminder['phone_number']);
    
    // Validate formatted phone number
    if (!$phoneNumber) {
        echo "Skipping {$reminder['first_name']} {$reminder['last_name']} - invalid phone format: {$reminder['phone_number']}\n";
        continue;
    }
    
    // Create personalized message
    $personalizedMessage = createPersonalizedMessage($reminder);
    
    echo "Attempting to send SMS to {$reminder['first_name']} {$reminder['last_name']} at {$phoneNumber}\n";
    
    // Send SMS
    $result = $smsHelper->sendSMS($phoneNumber, $personalizedMessage);
    
    if ($result['success']) {
        // Mark reminder as sent
        markReminderAsSent($conn, $reminder['reminder_id'], $result['message_sid']);
        
        // Log successful SMS
        logSMSActivity(
            $conn,
            $reminder['reminder_id'],
            $reminder['patient_id'],
            $phoneNumber,
            $personalizedMessage,
            'sent',
            $result['message_sid']
        );
        
        echo "✓ SMS sent successfully to {$reminder['first_name']} {$reminder['last_name']} ({$phoneNumber})\n";
    } else {
        // Log failed SMS
        logSMSActivity(
            $conn,
            $reminder['reminder_id'],
            $reminder['patient_id'],
            $phoneNumber,
            $personalizedMessage,
            'failed',
            null,
            $result['error']
        );
        
        echo "✗ Failed to send SMS to {$reminder['first_name']} {$reminder['last_name']}: {$result['error']}\n";
    }
    
    // Small delay to avoid rate limiting
    sleep(1);
}

echo "SMS sending process completed\n";

// Helper functions
function formatPhoneNumber($phone) {
    // Remove all non-numeric characters
    $phone = preg_replace('/[^0-9]/', '', $phone);
    
    // Check if phone number is valid length
    if (strlen($phone) < 10) {
        return false;
    }
    
    // Add country code if not present (assuming US +1)
    if (strlen($phone) === 10) {
        $phone = '1' . $phone;
    }
    
    return '+' . $phone;
}

function createPersonalizedMessage($reminder) {
    $patientName = $reminder['first_name'];
    $message = $reminder['reminder_text'] ?? $reminder['reminder_message'] ?? '';
    $type = $reminder['reminder_type'];
    
    // Create personalized message based on type
    switch ($type) {
        case 'appointment':
            return "Hi {$patientName}, this is a reminder about your upcoming appointment. {$message}";
        case 'medication':
            return "Hi {$patientName}, medication reminder: {$message}";
        case 'followup':
            return "Hi {$patientName}, follow-up reminder: {$message}";
        case 'payment':
            return "Hi {$patientName}, payment reminder: {$message}";
        default:
            return "Hi {$patientName}, {$message}";
    }
}

// function getPendingReminders($conn) {
//     $query = "
//         SELECT r.*, p.first_name, p.last_name, p.phone_number, p.email
//         FROM reminders r
//         JOIN patients p ON r.patient_id = p.patient_id
//         WHERE r.status = 'pending' 
//         AND r.due_date <= NOW()
//         ORDER BY r.due_date ASC
//     ";

//     $result = $conn->query($query);
//     if (!$result) {
//         echo "Database error: " . $conn->error . "\n";
//         return [];
//     }
    
//     return $result->fetch_all(MYSQLI_ASSOC);
// }

// function markReminderAsSent($conn, $reminderId, $messageSid = null) {
//     $stmt = $conn->prepare("
//         UPDATE reminders 
//         SET status = 'sent', sent_at = NOW(), message_sid = ? 
//         WHERE reminder_id = ?
//     ");
    
//     if (!$stmt) {
//         echo "Prepare error: " . $conn->error . "\n";
//         return false;
//     }
    
//     $stmt->bind_param("si", $messageSid, $reminderId);
//     $result = $stmt->execute();
    
//     if (!$result) {
//         echo "Execute error: " . $stmt->error . "\n";
//     }
    
//     return $result;
// }

// function logSMSActivity($conn, $reminderId, $patientId, $phoneNumber, $message, $status, $messageSid = null, $error = null) {
//     $stmt = $conn->prepare("
//         INSERT INTO sms_logs (reminder_id, patient_id, phone_number, message, status, message_sid, error_message, created_at)
//         VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
//     ");
    
//     if (!$stmt) {
//         echo "Prepare error for SMS log: " . $conn->error . "\n";
//         return false;
//     }
    
//     $stmt->bind_param("iisssss", $reminderId, $patientId, $phoneNumber, $message, $status, $messageSid, $error);
//     $result = $stmt->execute();
    
//     if (!$result) {
//         echo "Execute error for SMS log: " . $stmt->error . "\n";
//     }
    
//     return $result;
// }
?>