<?php
require_once '../config/database.php';

// Function to sanitize input data
function sanitizeInput($data) {
    global $conn;
    return htmlspecialchars(strip_tags(trim($data)));
}

// Function to add a new patient
function addPatient($firstName, $lastName, $email, $phone, $dob) {
    global $conn;
    
    $stmt = $conn->prepare("INSERT INTO patients (first_name, last_name, email, phone, dob) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $firstName, $lastName, $email, $phone, $dob);
    
    return $stmt->execute();
}

// Function to submit feedback
function submitFeedback($patientId, $feedbackText, $rating) {
    global $conn;
    
    $stmt = $conn->prepare("INSERT INTO feedback (patient_id, feedback_text, rating) VALUES (?, ?, ?)");
    $stmt->bind_param("isi", $patientId, $feedbackText, $rating);
    
    return $stmt->execute();
}

// Function to create a reminder
function createReminder($patientId, $reminderType, $reminderMessage, $dueDate) {
    global $conn;
    
    $stmt = $conn->prepare("INSERT INTO reminders (patient_id, reminder_type, reminder_message, due_date) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $patientId, $reminderType, $reminderMessage, $dueDate);
    
    return $stmt->execute();
}

// Function to get all feedback
function getAllFeedback($status = null) {
    global $conn;
    
    $query = "SELECT f.*, p.first_name, p.last_name FROM feedback f JOIN patients p ON f.patient_id = p.patient_id";
    
    if ($status) {
        $query .= " WHERE f.status = '$status'";
    }
    
    $result = $conn->query($query);
    
    $feedback = array();
    while ($row = $result->fetch_assoc()) {
        $feedback[] = $row;
    }
    
    return $feedback;
}

// Function to get all reminders
function getAllReminders($status = null) {
    global $conn;
    
    $query = "SELECT r.*, p.first_name, p.last_name FROM reminders r JOIN patients p ON r.patient_id = p.patient_id";
    
    if ($status) {
        $query .= " WHERE r.status = '$status'";
    }
    
    $result = $conn->query($query);
    
    $reminders = array();
    while ($row = $result->fetch_assoc()) {
        $reminders[] = $row;
    }
    
    return $reminders;
}

// Function to authenticate users
function authenticateUser($username, $password) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            return $user;
        }
    }
    
    return false;
}
?>