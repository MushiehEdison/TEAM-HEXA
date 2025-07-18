<?php
// CSRF Protection
function generateCsrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validateCsrfToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Input Validation
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function validateDate($date, $format = 'Y-m-d') {
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}

// Role-based Access Control
function requireRole($requiredRole) {
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== $requiredRole) {
        header("HTTP/1.1 403 Forbidden");
        die("Access denied. You don't have permission to access this resource.");
    }
}

// Password Hashing
function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT);
}

// Secure Session Configuration
function secureSession() {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', 1); // Enable this if using HTTPS
    ini_set('session.use_strict_mode', 1);
    session_start();
    
    // Regenerate session ID periodically
    if (!isset($_SESSION['last_regeneration'])) {
        session_regenerate_id(true);
        $_SESSION['last_regeneration'] = time();
    } elseif (time() - $_SESSION['last_regeneration'] > 1800) { // 30 minutes
        session_regenerate_id(true);
        $_SESSION['last_regeneration'] = time();
    }
}
?>