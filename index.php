<?php
// index.php
session_start();

// Check if database is set up
try {
    require_once 'classes/Database.php';
    $database = new Database();
    $db = $database->connect();
    
    // Test if tables exist
    $stmt = $db->query("SHOW TABLES LIKE 'users'");
    if ($stmt->rowCount() == 0) {
        // Redirect to installation
        header('Location: install.php');
        exit();
    }
} catch (Exception $e) {
    // Database not configured, redirect to installation
    header('Location: install.php');
    exit();
}

// Check if user is logged in
require_once 'classes/Auth.php';
$auth = new Auth();

if ($auth->isLoggedIn()) {
    // User is logged in, redirect to dashboard
    header('Location: pages/dashboard.php');
} else {
    // User is not logged in, redirect to login
    header('Location: login.php');
}

exit();
?>