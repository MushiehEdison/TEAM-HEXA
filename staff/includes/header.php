<?php 
// Error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'staff-auth.php';

// Initialize default permissions if not set
if (!isset($_SESSION['staff_permissions'])) {
    $_SESSION['staff_permissions'] = [
        'manage_patients' => false,
        'manage_appointments' => false,
        'manage_reminders' => false,
        'view_feedback' => false,
        'generate_reports' => false
    ];
}

// Safely get current page
$current_page = basename($_SERVER['SCRIPT_NAME'] ?? 'dashboard.php');
$current_uri = $_SERVER['REQUEST_URI'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Portal - <?php echo htmlspecialchars(ucfirst(str_replace('.php', '', $current_page))); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-blue: #2a7fba;
            --dark-blue: #1a4b8c;
            --light-blue: #e3f2fd;
            --white: #ffffff;
            --light-gray: #f5f7fa;
            --medium-gray: #e0e0e0;
            --dark-gray: #333333;
            --text-gray: #555555;
            --shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
        }

        body {
            background-color: var(--light-gray);
            color: var(--dark-gray);
        }

        .staff-header {
            background-color: var(--white);
            box-shadow: var(--shadow);
            padding: 0 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: 70px;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .logo {
            height: 40px;
            width: auto;
        }

        .staff-header h1 {
            font-size: 1.5rem;
            color: var(--primary-blue);
            font-weight: 600;
        }

        .main-nav ul {
            display: flex;
            list-style: none;
            gap: 1.5rem;
        }

        .main-nav a {
            text-decoration: none;
            color: var(--text-gray);
            font-weight: 500;
            padding: 0.5rem 0;
            position: relative;
            transition: var(--transition);
        }

        .main-nav a:hover,
        .main-nav a.active {
            color: var(--primary-blue);
        }

        .main-nav a.active::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 3px;
            background-color: var(--primary-blue);
            border-radius: 3px 3px 0 0;
        }

        .user-menu {
            position: relative;
        }

        .user-dropdown {
            display: flex;
            flex-direction: column;
            cursor: pointer;
        }

        .user-name {
            font-weight: 600;
            color: var(--dark-gray);
        }

        .user-role {
            font-size: 0.8rem;
            color: var(--primary-blue);
            text-align: right;
        }

        .dropdown-content {
            position: absolute;
            right: 0;
            top: 100%;
            background-color: var(--white);
            min-width: 160px;
            box-shadow: var(--shadow);
            border-radius: 0.5rem;
            padding: 0.5rem 0;
            visibility: hidden;
            opacity: 0;
            transition: var(--transition);
            z-index: 1;
        }

        .user-dropdown:hover .dropdown-content {
            visibility: visible;
            opacity: 1;
        }

        .dropdown-content a {
            color: var(--text-gray);
            padding: 0.75rem 1rem;
            text-decoration: none;
            display: block;
            transition: var(--transition);
        }

        .dropdown-content a:hover {
            background-color: var(--light-blue);
            color: var(--primary-blue);
        }

        .container {
            margin-top: 70px;
            padding: 2rem;
        }

        @media (max-width: 992px) {
            .staff-header {
                flex-direction: column;
                height: auto;
                padding: 1rem;
                gap: 1rem;
            }

            .main-nav ul {
                flex-wrap: wrap;
                justify-content: center;
                gap: 1rem;
            }

            .user-menu {
                margin-top: 0.5rem;
            }
        }

        @media (max-width: 576px) {
            .header-left {
                flex-direction: column;
                gap: 0.5rem;
                text-align: center;
            }

            .main-nav ul {
                gap: 0.5rem;
            }

            .container {
                padding: 1rem;
            }
        }
    </style>
</head>
<body>
    <header class="staff-header">
        <div class="header-left">
             <i class="fas fa-heartbeat"></i>
            <h1>Staff Portal</h1>
        </div>
        
        <nav class="main-nav">
            <ul>
                <li><a href="../dashboard.php">Dashboard</a></li>
                
                <?php if ($_SESSION['staff_permissions']['manage_patients'] ?? false): ?>
                    <li><a href="../patients/index.php">Patients</a></li>
                <?php endif; ?>
                
                <?php if ($_SESSION['staff_permissions']['manage_appointments'] ?? false): ?>
                    <li><a href="../appointments/index.php" >Appointments</a></li>
                <?php endif; ?>
                
                <?php if ($_SESSION['staff_permissions']['manage_reminders'] ?? false): ?>
                    <li><a href="../reminders/index.php" >Reminders</a></li>
                <?php endif; ?>
                
                <?php if ($_SESSION['staff_permissions']['view_feedback'] ?? false): ?>
                    <li><a href="../feedback/">Feedback</a></li>
                <?php endif; ?>
                
                <?php if ($_SESSION['staff_permissions']['generate_reports'] ?? false): ?>
                    <li><a href="../reports/index.php">Reports</a></li>
                <?php endif; ?>
            </ul>
        </nav>
        
        <div class="user-menu">
            <div class="user-dropdown">
                <span class="user-name"><?php echo htmlspecialchars($_SESSION['staff_name'] ?? 'User'); ?></span>
                <span class="user-role"><?php echo isset($_SESSION['staff_role']) ? htmlspecialchars(ucfirst($_SESSION['staff_role'])) : 'Staff'; ?></span>
                <div class="dropdown-content">
                    <a href="../profile.php"><i class="fas fa-user-cog"></i> Profile</a>
                    <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </div>
            </div>
        </div>
    </header>
    
    <main class="container">