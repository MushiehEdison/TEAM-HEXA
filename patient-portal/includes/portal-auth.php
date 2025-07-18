<?php
session_start();

// Include database connection
require_once __DIR__ . '/../../config/database.php';

/**
 * Check if a patient is logged in
 */
function isPatientLoggedIn() {
    return isset($_SESSION['patient_id']);
}

/**
 * Require patient authentication to access a page
 */
function requirePatientAuth() {
    if (!isPatientLoggedIn()) {
        header("Location: index.php");
        exit();
    }
}

/**
 * Log out the patient
 */
function logout() {
    session_unset();
    session_destroy();
    header("Location: ../index.php");
    exit();
}

/**
 * Verify patient login credentials
 *
 * @param string $email
 * @param string $dob
 * @return array|false
 */
function verifyPatientLogin($email, $dob) {
    global $conn;

    // Safety check
    if (!$conn) {
        die("Database connection not available");
    }

    $stmt = $conn->prepare("SELECT * FROM patients WHERE email = ? AND dob = ?");
    $stmt->bind_param("ss", $email, $dob);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        return $result->fetch_assoc();
    }

    return false;
}
