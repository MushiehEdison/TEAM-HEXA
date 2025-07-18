<?php
// config/twilio.php - Twilio configuration
class TwilioConfig {
    const ACCOUNT_SID = 'ACa9eda75befdbffae465556f13a2e66e8';
    const AUTH_TOKEN = 'e63e47eb7ad379bf0ef8e80e06180449';
    const FROM_NUMBER = '+17722764245';// Your Twilio phone number
}

// includes/twilio-helper.php - Twilio SMS helper functions
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/twilio.php';

use Twilio\Rest\Client;

// class TwilioSMSHelper {
//     private $client;
    
//     public function __construct() {
//         $this->client = new Client(TwilioConfig::ACCOUNT_SID, TwilioConfig::AUTH_TOKEN);
//     }
    
//     public function sendSMS($to, $message) {
//         try {
//             $sms = $this->client->messages->create(
//                 $to,
//                 [
//                     'from' => TwilioConfig::FROM_NUMBER,
//                     'body' => $message
//                 ]
//             );
            
//             return [
//                 'success' => true,
//                 'message_sid' => $sms->sid,
//                 'status' => $sms->status
//             ];
//         } catch (Exception $e) {
//             return [
//                 'success' => false,
//                 'error' => $e->getMessage()
//             ];
//         }
//     }
// }

// includes/reminder-functions.php - Updated reminder functions
function createReminder($conn, $patientId, $reminderType, $messageText, $dueDateTime, $createdBy) {
    $stmt = $conn->prepare("
        INSERT INTO reminders (patient_id, reminder_type, reminder_message, due_date, status, created_by, created_at) 
        VALUES (?, ?, ?, ?, 'pending', ?, NOW())
    ");
    
    $stmt->bind_param("isssi", $patientId, $reminderType, $messageText, $dueDateTime, $createdBy);
    return $stmt->execute();
}

function getPendingReminders($conn) {
    $query = "
        SELECT r.*, p.first_name, p.last_name, p.phone_number, p.email
        FROM reminders r
        JOIN patients p ON r.patient_id = p.patient_id
        WHERE r.status = 'pending' 
        AND r.due_date <= NOW()
        ORDER BY r.due_date ASC
    ";
    
    $result = $conn->query($query);
    return $result->fetch_all(MYSQLI_ASSOC);
}

function markReminderAsSent($conn, $reminderId, $messageSid = null) {
    $stmt = $conn->prepare("
        UPDATE reminders 
        SET status = 'sent', sent_at = NOW(), message_sid = ?
        WHERE reminder_id = ?
    ");
    
    $stmt->bind_param("si", $messageSid, $reminderId);
    return $stmt->execute();
}

function logSMSActivity($conn, $reminderId, $patientId, $phoneNumber, $message, $status, $messageSid = null, $error = null) {
    $stmt = $conn->prepare("
        INSERT INTO sms_logs (reminder_id, patient_id, phone_number, message, status, message_sid, error_message, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
    ");
    
    $stmt->bind_param("iisssss", $reminderId, $patientId, $phoneNumber, $message, $status, $messageSid, $error);
    return $stmt->execute();
}