<?php
// logout.php
session_start();

// Clear all session data
session_unset();
session_destroy();

// Redirect to login page with logout message
header('Location: login.php?message=logged_out');
exit();
?>