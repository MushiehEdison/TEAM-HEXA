<?php
require_once 'includes/portal-auth.php';
requirePatientAuth();
require_once 'includes/portal-functions.php';

$patientId = $_SESSION['patient_id'];
$patient = getPatientDetails($patientId);
$upcomingAppointments = getPatientAppointments($patientId, 3);
$upcomingReminders = getPatientReminders($patientId, 'pending');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MyHealth Dashboard</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #5d5fef;
            --primary-light: #e0e1ff;
            --primary-dark: #3a3cd9;
            --accent: #ff6b9e;
            --accent-light: #ffd6e7;
            --success: #4caf50;
            --warning: #ff9800;
            --danger: #f44336;
            --white: #ffffff;
            --gray-50: #f9fafb;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-500: #6b7280;
            --gray-700: #374151;
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.12);
            --shadow-md: 0 4px 6px rgba(0,0,0,0.1);
            --shadow-lg: 0 10px 25px rgba(0,0,0,0.1);
            --radius-sm: 8px;
            --radius-md: 12px;
            --radius-lg: 16px;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--gray-50);
            color: var(--gray-700);
            line-height: 1.6;
        }

        .dashboard-container {
            display: grid;
            grid-template-columns: 240px 1fr;
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            background: var(--white);
            box-shadow: var(--shadow-md);
            padding: 1.5rem 0;
            position: sticky;
            top: 0;
            height: 100vh;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 0 1.5rem 1.5rem;
            margin-bottom: 1rem;
            border-bottom: 1px solid var(--gray-100);
        }

        .brand-logo {
            width: 36px;
            height: 36px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--white);
            font-weight: 600;
        }

        .brand-name {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--primary-dark);
        }

        .nav-menu {
            list-style: none;
            padding: 0 1rem;
        }

        .nav-item {
            margin-bottom: 4px;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 0.75rem 1rem;
            border-radius: var(--radius-sm);
            color: var(--gray-700);
            text-decoration: none;
            transition: var(--transition);
        }

        .nav-link:hover, .nav-link.active {
            background-color: var(--primary-light);
            color: var(--primary-dark);
        }

        .nav-link.active {
            font-weight: 500;
        }

        .nav-icon {
            width: 24px;
            text-align: center;
            color: var(--gray-500);
        }

        .nav-link:hover .nav-icon,
        .nav-link.active .nav-icon {
            color: var(--primary-dark);
        }

        /* Main Content */
        .main-content {
            padding: 2rem;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .welcome-message h1 {
            font-size: 1.75rem;
            font-weight: 600;
            color: var(--gray-700);
            margin-bottom: 0.5rem;
        }

        .welcome-message p {
            color: var(--gray-500);
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .avatar {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background-color: var(--primary-light);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary-dark);
            font-weight: 500;
            font-size: 1.25rem;
        }

        /* Dashboard Grid */
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(12, 1fr);
            gap: 1.5rem;
        }

        /* Quick Actions */
        .quick-actions {
            grid-column: span 4;
        }

        .section-card {
            background: var(--white);
            border-radius: var(--radius-md);
            box-shadow: var(--shadow-sm);
            padding: 1.5rem;
            height: 100%;
            transition: var(--transition);
        }

        .section-card:hover {
            box-shadow: var(--shadow-md);
            transform: translateY(-2px);
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .section-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--gray-700);
            position: relative;
            padding-bottom: 0.75rem;
        }

        .section-title::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: 0;
            width: 40px;
            height: 3px;
            background: linear-gradient(90deg, var(--primary), var(--accent));
            border-radius: 3px;
        }

        .action-buttons {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 1rem;
        }

        .action-btn {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 1.5rem 1rem;
            background: var(--white);
            border: 1px solid var(--gray-200);
            border-radius: var(--radius-sm);
            color: var(--primary-dark);
            text-decoration: none;
            transition: var(--transition);
        }

        .action-btn:hover {
            background: var(--primary-light);
            border-color: var(--primary);
            transform: translateY(-3px);
            box-shadow: var(--shadow-sm);
        }

        .action-icon {
            width: 40px;
            height: 40px;
            background: var(--primary-light);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 0.75rem;
            color: var(--primary-dark);
            font-size: 1.25rem;
        }

        .action-label {
            font-size: 0.875rem;
            font-weight: 500;
            text-align: center;
        }

        /* Appointments */
        .upcoming-appointments {
            grid-column: span 5;
        }

        .appointment-list {
            list-style: none;
        }

        .appointment-item {
            display: flex;
            align-items: center;
            padding: 1rem;
            margin-bottom: 0.75rem;
            background: var(--white);
            border-radius: var(--radius-sm);
            box-shadow: var(--shadow-sm);
            transition: var(--transition);
        }

        .appointment-item:hover {
            box-shadow: var(--shadow-md);
        }

        .appointment-date {
            min-width: 80px;
            text-align: center;
            margin-right: 1rem;
            padding: 0.5rem;
            background: var(--primary-light);
            border-radius: var(--radius-sm);
            color: var(--primary-dark);
            font-weight: 500;
        }

        .appointment-day {
            font-size: 1.5rem;
            line-height: 1;
        }

        .appointment-month {
            font-size: 0.75rem;
            text-transform: uppercase;
            opacity: 0.8;
        }

        .appointment-time {
            font-size: 0.875rem;
            margin-top: 4px;
        }

        .appointment-details {
            flex: 1;
        }

        .appointment-title {
            font-weight: 500;
            margin-bottom: 0.25rem;
        }

        .appointment-meta {
            font-size: 0.875rem;
            color: var(--gray-500);
        }

        .appointment-actions {
            margin-left: auto;
        }

        .btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: var(--radius-sm);
            font-family: inherit;
            font-size: 0.875rem;
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
        }

        .btn-sm {
            padding: 0.375rem 0.75rem;
            font-size: 0.75rem;
        }

        .btn-primary {
            background: var(--primary);
            color: var(--white);
        }

        .btn-primary:hover {
            background: var(--primary-dark);
        }

        .btn-outline {
            background: transparent;
            border: 1px solid var(--gray-200);
            color: var(--gray-700);
        }

        .btn-outline:hover {
            background: var(--gray-100);
        }

        .view-all {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-top: 1rem;
            color: var(--primary-dark);
            font-size: 0.875rem;
            font-weight: 500;
            text-decoration: none;
            transition: var(--transition);
        }

        .view-all:hover {
            color: var(--primary);
        }

        /* Reminders */
        .health-reminders {
            grid-column: span 3;
        }

        .reminder-list {
            list-style: none;
        }

        .reminder-item {
            padding: 1rem;
            margin-bottom: 0.75rem;
            background: var(--white);
            border-radius: var(--radius-sm);
            box-shadow: var(--shadow-sm);
            transition: var(--transition);
        }

        .reminder-item:hover {
            box-shadow: var(--shadow-md);
        }

        .reminder-header {
            display: flex;
            align-items: center;
            margin-bottom: 0.5rem;
        }

        .reminder-icon {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 0.75rem;
            color: var(--white);
            font-size: 0.875rem;
        }

        .reminder-icon.appointment {
            background: var(--accent);
        }

        .reminder-icon.medication {
            background: var(--primary);
        }

        .reminder-icon.followup {
            background: var(--success);
        }

        .reminder-icon.payment {
            background: var(--warning);
        }

        .reminder-title {
            font-weight: 500;
            font-size: 0.875rem;
        }

        .reminder-due {
            font-size: 0.75rem;
            color: var(--gray-500);
        }

        /* Empty States */
        .empty-state {
            padding: 2rem 1rem;
            text-align: center;
            color: var(--gray-500);
        }

        .empty-icon {
            font-size: 2rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }

        .empty-text {
            margin-bottom: 1.5rem;
        }

        /* Responsive */
        @media (max-width: 1200px) {
            .dashboard-grid {
                grid-template-columns: repeat(6, 1fr);
            }
            .quick-actions {
                grid-column: span 6;
            }
            .upcoming-appointments {
                grid-column: span 4;
            }
            .health-reminders {
                grid-column: span 2;
            }
        }

        @media (max-width: 992px) {
            .dashboard-container {
                grid-template-columns: 1fr;
            }
            .sidebar {
                height: auto;
                position: static;
            }
            .dashboard-grid {
                grid-template-columns: 1fr;
            }
            .quick-actions, .upcoming-appointments, .health-reminders {
                grid-column: span 1;
            }
            .action-buttons {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }
            .action-buttons {
                grid-template-columns: 1fr 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar Navigation -->
        <aside class="sidebar">
            <div class="brand">
                <div class="brand-logo">MH</div>
                <div class="brand-name">MyHealth</div>
            </div>
            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="../index.php" class="nav-link active">
                        <span class="nav-icon"><i class="fas fa-home"></i></span>
                        <span>Home</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="appointments.php" class="nav-link">
                        <span class="nav-icon"><i class="fas fa-calendar-alt"></i></span>
                        <span>Appointments</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="medical-records.php" class="nav-link">
                        <span class="nav-icon"><i class="fas fa-file-medical"></i></span>
                        <span>Medical Records</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="reminders.php" class="nav-link">
                        <span class="nav-icon"><i class="fas fa-bell"></i></span>
                        <span>Reminders</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="feedback.php" class="nav-link">
                        <span class="nav-icon"><i class="fas fa-comment-dots"></i></span>
                        <span>Feedback</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="profile.php" class="nav-link">
                        <span class="nav-icon"><i class="fas fa-user-circle"></i></span>
                        <span>Profile</span>
                    </a>
                </li>
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <header class="header">
                <div class="welcome-message">
                    <h1>Welcome back, <?php echo explode(' ', $_SESSION['patient_name'])[0]; ?></h1>
                    <p>Here's what's happening with your health today</p>
                </div>
                <div class="user-profile">
                    <div class="avatar">
                        <?php echo strtoupper(substr($patient['first_name'], 0, 1) . strtoupper(substr($patient['last_name'], 0, 1))); ?>
                    </div>
                </div>
            </header>

            <div class="dashboard-grid">
                <!-- Quick Actions Section -->
                <section class="quick-actions">
                    <div class="section-card">
                        <div class="section-header">
                            <h2 class="section-title">Quick Actions</h2>
                        </div>
                        <div class="action-buttons">
                            <a href="appointments.php?action=book" class="action-btn">
                                <div class="action-icon">
                                    <i class="fas fa-calendar-plus"></i>
                                </div>
                                <span class="action-label">Book Appointment</span>
                            </a>
                            <a href="medical-records.php" class="action-btn">
                                <div class="action-icon">
                                    <i class="fas fa-file-medical-alt"></i>
                                </div>
                                <span class="action-label">View Records</span>
                            </a>
                            <a href="feedback.php" class="action-btn">
                                <div class="action-icon">
                                    <i class="fas fa-comment-medical"></i>
                                </div>
                                <span class="action-label">Give Feedback</span>
                            </a>
                        </div>
                    </div>
                </section>

                <!-- Upcoming Appointments Section -->
                <section class="upcoming-appointments">
                    <div class="section-card">
                        <div class="section-header">
                            <h2 class="section-title">Upcoming Appointments</h2>
                        </div>
                        
                        <?php if (count($upcomingAppointments) > 0): ?>
                            <ul class="appointment-list">
                                <?php foreach ($upcomingAppointments as $appt): ?>
                                <li class="appointment-item">
                                    <div class="appointment-date">
                                        <div class="appointment-day"><?php echo date('j', strtotime($appt['appointment_date'])); ?></div>
                                        <div class="appointment-month"><?php echo date('M', strtotime($appt['appointment_date'])); ?></div>
                                        <div class="appointment-time"><?php echo date('g:i A', strtotime($appt['appointment_date'])); ?></div>
                                    </div>
                                    <div class="appointment-details">
                                        <h3 class="appointment-title"><?php echo $appt['appointment_type']; ?></h3>
                                        <p class="appointment-meta">Dr. <?php echo $appt['last_name']; ?> • <?php echo $appt['specialty']; ?></p>
                                    </div>
                                    <div class="appointment-actions">
                                        <a href="appointments.php?action=view&id=<?php echo $appt['appointment_id']; ?>" class="btn btn-sm btn-outline">Details</a>
                                    </div>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                            <a href="appointments.php" class="view-all">
                                View all appointments <i class="fas fa-chevron-right"></i>
                            </a>
                        <?php else: ?>
                            <div class="empty-state">
                                <div class="empty-icon">
                                    <i class="far fa-calendar-check"></i>
                                </div>
                                <p class="empty-text">No upcoming appointments scheduled</p>
                                <a href="appointments.php?action=book" class="btn btn-primary">Book an Appointment</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </section>

                <!-- Health Reminders Section -->
                <section class="health-reminders">
                    <div class="section-card">
                        <div class="section-header">
                            <h2 class="section-title">Health Reminders</h2>
                        </div>
                        
                        <?php if (count($upcomingReminders) > 0): ?>
                            <ul class="reminder-list">
                                <?php foreach ($upcomingReminders as $reminder): ?>
                                <li class="reminder-item">
                                    <div class="reminder-header">
                                        <div class="reminder-icon <?php echo $reminder['reminder_type']; ?>">
                                            <?php switch($reminder['reminder_type']) {
                                                case 'appointment': echo '<i class="fas fa-calendar-alt"></i>'; break;
                                                case 'medication': echo '<i class="fas fa-pills"></i>'; break;
                                                case 'followup': echo '<i class="fas fa-sync-alt"></i>'; break;
                                                case 'payment': echo '<i class="fas fa-credit-card"></i>'; break;
                                                default: echo '<i class="fas fa-bell"></i>';
                                            } ?>
                                        </div>
                                        <h3 class="reminder-title"><?php echo $reminder['reminder_message']; ?></h3>
                                    </div>
                                    <p class="reminder-due">Due: <?php echo date('M j, g:i A', strtotime($reminder['due_date'])); ?></p>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                            <a href="reminders.php" class="view-all">
                                View all reminders <i class="fas fa-chevron-right"></i>
                            </a>
                        <?php else: ?>
                            <div class="empty-state">
                                <div class="empty-icon">
                                    <i class="far fa-bell-slash"></i>
                                </div>
                                <p class="empty-text">No pending reminders</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </section>
            </div>
        </main>
    </div>

    <script>
        // Simple animation for cards on load
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.section-card');
            cards.forEach((card, index) => {
                setTimeout(() => {
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 100);
            });
        });
    </script>
</body>
</html>