<?php
require_once 'includes/auth.php';
requireAuth();
?>

<header class="dashboard-header">
    <h1>Patient Management System</h1>
    <nav>
        <ul>
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="patients.php">Patients</a></li>
            <li><a href="feedback/">Feedback</a></li>
            <li><a href="reminders/">Reminders</a></li>
            <li><a href="logout.php">Logout (<?php echo $_SESSION['username']; ?>)</a></li>
        </ul>
    </nav>
</header>