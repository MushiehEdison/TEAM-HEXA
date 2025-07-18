<?php
require_once(__DIR__ . '/../config/database.php');
require_once(__DIR__ . '/../includes/twilio-helper.php');
require_once(__DIR__ . '/../staff/includes/reminder-functions.php');


// Initialize Twilio SMS helper
$smsHelper = new TwilioSMSHelper();

// Get pending reminders
$pendingReminders = getPendingReminders($conn);

foreach ($pendingReminders as $reminder) {
    // Skip if patient doesn't have a phone number
    if (empty($reminder['phone_number'])) {
        continue;
    }
    
    // Format phone number (ensure it has country code)
    $phoneNumber = formatPhoneNumber($reminder['phone_number']);
    
    // Create personalized message
    $personalizedMessage = createPersonalizedMessage($reminder);
    
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
        
        echo "SMS sent successfully to {$reminder['first_name']} {$reminder['last_name']} ({$phoneNumber})\n";
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
        
        echo "Failed to send SMS to {$reminder['first_name']} {$reminder['last_name']}: {$result['error']}\n";
    }
    
    // Small delay to avoid rate limiting
    sleep(1);
}

function formatPhoneNumber($phone) {
    // Remove all non-numeric characters
    $phone = preg_replace('/[^0-9]/', '', $phone);
    
    // Add country code if not present (assuming US +1)
    if (strlen($phone) === 10) {
        $phone = '1' . $phone;
    }
    
    return '+' . $phone;
}

function createPersonalizedMessage($reminder) {
    $patientName = $reminder['first_name'];
    $message = $reminder['reminder_message'];
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
