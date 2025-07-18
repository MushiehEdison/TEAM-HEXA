<?php 

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