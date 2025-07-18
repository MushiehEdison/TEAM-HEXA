<?php
// Start session and include necessary files
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/includes/staff-auth.php';
requireStaffAuth();

// Verify database connection
if (!isset($conn) || !$conn) {
    die("Database connection error. Please try again later.");
}

// Initialize default permissions if not set
if (!isset($_SESSION['staff_permissions'])) {
    $_SESSION['staff_permissions'] = [
        'manage_patients' => false,
        'manage_appointments' => false,
        'manage_reminders' => false,
        'view_feedback' => false
    ];
}

// Get stats for dashboard
$stats = [];
try {
    $result = $conn->query("SELECT COUNT(*) as total FROM patients");
    $stats['patients'] = $result ? $result->fetch_assoc()['total'] : 0;

    $result = $conn->query("SELECT COUNT(*) as total FROM appointments WHERE appointment_date >= CURDATE()");
    $stats['upcoming_appointments'] = $result ? $result->fetch_assoc()['total'] : 0;

    $result = $conn->query("SELECT COUNT(*) as total FROM feedback WHERE status = 'pending'");
    $stats['pending_feedback'] = $result ? $result->fetch_assoc()['total'] : 0;

    $result = $conn->query("SELECT COUNT(*) as total FROM reminders WHERE status = 'pending' AND due_date BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 7 DAY)");
    $stats['upcoming_reminders'] = $result ? $result->fetch_assoc()['total'] : 0;

    // Get recent appointments
    $recentAppointments = [];
    $result = $conn->query("
        SELECT a.*, p.first_name, p.last_name, p.phone, pr.first_name as provider_first, pr.last_name as provider_last
        FROM appointments a
        JOIN patients p ON a.patient_id = p.patient_id
        JOIN providers pr ON a.provider_id = pr.provider_id
        WHERE a.appointment_date >= CURDATE()
        ORDER BY a.appointment_date ASC
        LIMIT 5
    ");
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $recentAppointments[] = $row;
        }
    }
} catch (Exception $e) {
    error_log("Dashboard error: " . $e->getMessage());
    $error = "Failed to load dashboard data. Please try again.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Portal - Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-blue:rgb(117, 172, 228);
            --primary-pink: #e84393;
            --light-blue:rgb(103, 148, 178);
            --light-pink: #fd79a8;
            --dark-blue:rgb(187, 70, 152);
            --dark-pink:rgb(235, 152, 181);
            --bg-color: #f5f7fa;
            --sidebar-width: 280px;
            --transition-speed: 0.3s;
            --border-radius: 8px;
            --box-shadow: 0 2px 10px rgba(197, 133, 224, 0.05);
            --box-shadow-hover: 0 5px 15px rgba(150, 130, 209, 0.1);
            --text-dark: #2c3e50;
            --text-light: #7f8c8d;
            --white: #ffffff;
            --success: #27ae60;
            --warning: #f39c12;
            --danger: #e74c3c;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Roboto', sans-serif;
        }

        body {
            background-color: var(--bg-color);
            color: var(--text-dark);
            display: flex;
            min-height: 100vh;
            line-height: 1.6;
        }

        /* Sidebar Styles */
        .sidebar {
            width: var(--sidebar-width);
            background: var(--dark-blue);
            color: var(--white);
            height: 100vh;
            position: fixed;
            padding: 0;
            transition: all var(--transition-speed) ease;
            z-index: 1000;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        }

        .sidebar-header {
            padding: 25px 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            display: flex;
            align-items: center;
        }

        .sidebar-header i {
            font-size: 24px;
            margin-right: 15px;
            color: var(--light-blue);
        }

        .sidebar-header h3 {
            font-weight: 500;
            font-size: 18px;
        }

        .sidebar-menu {
            padding: 20px 0;
        }

        .menu-item {
            padding: 12px 25px;
            display: flex;
            align-items: center;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            transition: all 0.2s ease;
            margin: 5px 0;
            font-size: 15px;
            font-weight: 400;
        }

        .menu-item:hover, 
        .menu-item.active {
            background-color: rgba(197, 86, 164, 0.05);
            color: var(--white);
            border-left: 4px solid var(--primary-pink);
        }

        .menu-item i {
            margin-right: 15px;
            font-size: 16px;
            width: 20px;
            text-align: center;
        }

        /* Main Content Styles */
        .main-content {
            margin-left: var(--sidebar-width);
            width: calc(100% - var(--sidebar-width));
            padding: 30px;
            transition: all var(--transition-speed) ease;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .header h1 {
            font-size: 28px;
            font-weight: 600;
            color: var(--text-dark);
        }

        .user-profile {
            display: flex;
            align-items: center;
            background: var(--white);
            padding: 8px 15px;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
        }

        .user-profile i {
            margin-right: 10px;
            color: var(--light-blue);
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: var(--white);
            border-radius: var(--border-radius);
            padding: 20px;
            box-shadow: var(--box-shadow);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            position: relative;
            overflow: hidden;
            border-top: 3px solid var(--primary-blue);
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--box-shadow-hover);
        }

        .stat-card.patients { border-top-color: var(--primary-blue); }
        .stat-card.appointments { border-top-color: var(--primary-pink); }
        .stat-card.feedback { border-top-color: var(--light-blue); }
        .stat-card.reminders { border-top-color: var(--light-pink); }

        .stat-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            color: var(--white);
            margin-bottom: 15px;
        }

        .stat-card.patients .stat-icon { background: var(--primary-blue); }
        .stat-card.appointments .stat-icon { background: var(--primary-pink); }
        .stat-card.feedback .stat-icon { background: var(--light-blue); }
        .stat-card.reminders .stat-icon { background: var(--light-pink); }

        .stat-value {
            font-size: 28px;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 5px;
        }

        .stat-title {
            color: var(--text-light);
            font-size: 14px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .stat-link {
            display: inline-block;
            margin-top: 15px;
            color: var(--light-blue);
            text-decoration: none;
            font-size: 13px;
            font-weight: 500;
            transition: color 0.2s ease;
        }

        .stat-link:hover {
            color: var(--dark-blue);
            text-decoration: underline;
        }

        .stat-link i {
            margin-left: 5px;
            font-size: 12px;
        }

        /* Dashboard Sections */
        .dashboard-sections {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 30px;
        }

        .section-card {
            background: var(--white);
            border-radius: var(--border-radius);
            padding: 25px;
            box-shadow: var(--box-shadow);
        }

        .section-title {
            font-size: 18px;
            margin-bottom: 20px;
            color: var(--text-dark);
            display: flex;
            align-items: center;
            font-weight: 600;
        }

        .section-title i {
            margin-right: 10px;
            color: var(--primary-blue);
        }

        /* Data Table */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }

        .data-table th {
            background-color: var(--primary-blue);
            color: var(--white);
            padding: 12px 15px;
            text-align: left;
            font-weight: 500;
        }

        .data-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #eee;
            color: var(--text-dark);
        }

        .data-table tr:last-child td {
            border-bottom: none;
        }

        .data-table tr:hover {
            background-color: rgba(52, 152, 219, 0.05);
        }

        /* Buttons */
        .btn {
            padding: 8px 16px;
            border-radius: var(--border-radius);
            border: none;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            font-weight: 500;
        }

        .btn-sm {
            padding: 6px 12px;
            font-size: 13px;
        }

        .btn-primary {
            background-color: var(--primary-blue);
            color: var(--white);
        }

        .btn-primary:hover {
            background-color: var(--dark-blue);
            transform: translateY(-1px);
        }

        .btn-pink {
            background-color: var(--primary-pink);
            color: var(--white);
        }

        .btn-pink:hover {
            background-color: var(--dark-pink);
        }

        /* Quick Actions */
        .quick-actions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 15px;
        }

        .action-btn {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background: var(--white);
            padding: 20px 10px;
            border-radius: var(--border-radius);
            text-decoration: none;
            color: var(--text-dark);
            transition: all 0.3s ease;
            box-shadow: var(--box-shadow);
            border: 1px solid #eee;
            text-align: center;
        }

        .action-btn:hover {
            transform: translateY(-3px);
            box-shadow: var(--box-shadow-hover);
            border-color: var(--primary-blue);
        }

        .action-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            margin-bottom: 10px;
            color: var(--white);
        }

        .action-btn:nth-child(1) .action-icon { background: var(--primary-blue); }
        .action-btn:nth-child(2) .action-icon { background: var(--primary-pink); }
        .action-btn:nth-child(3) .action-icon { background: var(--light-blue); }
        .action-btn:nth-child(4) .action-icon { background: var(--light-pink); }

        .action-btn span {
            font-size: 13px;
            font-weight: 500;
        }

        /* Alerts */
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: var(--border-radius);
            font-size: 14px;
        }

        .alert-danger {
            background-color: rgba(231, 76, 60, 0.1);
            color: var(--danger);
            border-left: 4px solid var(--danger);
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 40px 0;
            color: var(--text-light);
        }

        .empty-state i {
            font-size: 50px;
            margin-bottom: 15px;
            color: #ddd;
        }

        .empty-state p {
            font-size: 15px;
        }

        /* Responsive Design */
        @media (max-width: 1200px) {
            .dashboard-sections {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 992px) {
            .sidebar {
                width: 80px;
                overflow: hidden;
            }
            
            .sidebar-header span, 
            .menu-item span {
                display: none;
            }
            
            .menu-item {
                justify-content: center;
                padding: 15px 0;
            }
            
            .menu-item i {
                margin-right: 0;
                font-size: 18px;
            }
            
            .main-content {
                margin-left: 80px;
                width: calc(100% - 80px);
            }
        }

        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: 1fr 1fr;
            }
            
            .quick-actions-grid {
                grid-template-columns: 1fr 1fr;
            }
        }

        @media (max-width: 576px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            
            .main-content {
                padding: 20px;
            }
        }

        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .stat-card, .section-card {
            animation: fadeIn 0.5s ease forwards;
        }

        .stat-card:nth-child(1) { animation-delay: 0.1s; }
        .stat-card:nth-child(2) { animation-delay: 0.2s; }
        .stat-card:nth-child(3) { animation-delay: 0.3s; }
        .stat-card:nth-child(4) { animation-delay: 0.4s; }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <i class="fas fa-heartbeat"></i>
            <h3>Clinic Portal</h3>
        </div>
        
        <div class="sidebar-menu">
            <a href="../index.php" class="menu-item active">
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>

            <?php if (!empty($_SESSION['staff_permissions']['manage_bloodbank'])): ?>
                        <a href="../staff/bloodbank/add.php" class="action-btn">
                            <div class="action-icon" style="background: #e74c3c;">
                                <i class="fas fa-tint"></i>
                            </div>
                            <span>Add Blood</span>
                         </a>
                    <?php endif; ?>
            
            <?php if (!empty($_SESSION['staff_permissions']['manage_patients'])): ?>
            <a href="../staff/patients/index.php" class="menu-item">
                <i class="fas fa-user-injured"></i>
                <span>Patients</span>
            </a>
            <?php endif; ?>
            
            <?php if (!empty($_SESSION['staff_permissions']['manage_appointments'])): ?>
            <a href="../staff/appointments/index.php" class="menu-item">
                <i class="fas fa-calendar-check"></i>
                <span>Appointments</span>
            </a>
            <?php endif; ?>
            
            <?php if (!empty($_SESSION['staff_permissions']['manage_reminders'])): ?>
            <a href="../staff/reminders/index.php" class="menu-item">
                <i class="fas fa-bell"></i>
                <span>Reminders</span>
            </a>
            <?php endif; ?>
            
            <?php if (!empty($_SESSION['staff_permissions']['view_feedback'])): ?>
            <a href="../staff/feedback/index.php" class="menu-item">
                <i class="fas fa-comment-alt"></i>
                <span>Feedback</span>
            </a>
            <?php endif; ?>
            
            <a href="../staff/profile.php" class="menu-item">
                <i class="fas fa-user-cog"></i>
                <span>Profile</span>
            </a>
            
            <a href="logout.php" class="menu-item">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="main-content">
        <div class="header">
            <h1>Dashboard Overview</h1>
            <div class="user-profile">
                <i class="fas fa-user-shield"></i>
                <span><?php echo htmlspecialchars($_SESSION['staff_name'] ?? 'Staff Member'); ?></span>
            </div>
        </div>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <!-- Stats Grid -->
        <div class="stats-grid">
            <div class="stat-card patients">
                <div class="stat-icon">
                    <i class="fas fa-user-injured"></i>
                </div>
                <div class="stat-info">
                    <div>
                        <div class="stat-title">Total Patients</div>
                        <div class="stat-value"><?php echo number_format($stats['patients']); ?></div>
                    </div>
                </div>
                <a href="../staff/patients/index.php" class="stat-link">View Details <i class="fas fa-chevron-right"></i></a>
            </div>
            
            <div class="stat-card appointments">
                <div class="stat-icon">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <div class="stat-info">
                    <div>
                        <div class="stat-title">Upcoming Appointments</div>
                        <div class="stat-value"><?php echo number_format($stats['upcoming_appointments']); ?></div>
                    </div>
                </div>
                <a href="../staff/appointments/index.php" class="stat-link">View All <i class="fas fa-chevron-right"></i></a>
            </div>
            
            <div class="stat-card feedback">
                <div class="stat-icon">
                    <i class="fas fa-comment-alt"></i>
                </div>
                <div class="stat-info">
                    <div>
                        <div class="stat-title">Pending Feedback</div>
                        <div class="stat-value"><?php echo number_format($stats['pending_feedback']); ?></div>
                    </div>
                </div>
                <a href="../staff/feedback/index.php" class="stat-link">Review <i class="fas fa-chevron-right"></i></a>
            </div>
            
            <div class="stat-card reminders">
                <div class="stat-icon">
                    <i class="fas fa-bell"></i>
                </div>
                <div class="stat-info">
                    <div>
                        <div class="stat-title">Upcoming Reminders</div>
                        <div class="stat-value"><?php echo number_format($stats['upcoming_reminders']); ?></div>
                    </div>
                </div>
                <a href="../staff/reminders/index.php" class="stat-link">Manage <i class="fas fa-chevron-right"></i></a>
            </div>
        </div>
        
        <!-- Dashboard Sections -->
        <div class="dashboard-sections">
            <!-- Recent Appointments -->
            <section class="section-card">
                <h2 class="section-title">
                    <i class="fas fa-calendar-day"></i>
                    Upcoming Appointments
                </h2>
                
                <?php if (count($recentAppointments) > 0): ?>
                    <div style="overflow-x: auto;">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Date & Time</th>
                                    <th>Patient</th>
                                    <th>Provider</th>
                                    <th>Type</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentAppointments as $appt): ?>
                                <tr>
                                    <td><?php echo date('M j, Y g:i A', strtotime($appt['appointment_date'])); ?></td>
                                    <td><?php echo htmlspecialchars($appt['first_name'] . ' ' . $appt['last_name']); ?></td>
                                    <td>Dr. <?php echo htmlspecialchars($appt['provider_last']); ?></td>
                                    <td><?php echo htmlspecialchars($appt['appointment_type']); ?></td>
                                    <td>
                                        <a href="../staff/appointments/schedule.php?id=<?php echo $appt['appointment_id']; ?>" class="btn btn-sm btn-primary">View</a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div style="margin-top: 20px; text-align: right;">
                        <a href="../staff/appointments/index.php" class="btn btn-primary">View All Appointments</a>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-calendar-times"></i>
                        <p>No upcoming appointments scheduled</p>
                    </div>
                <?php endif; ?>
            </section>
            
            <!-- Quick Actions -->
            <section class="section-card">
                <h2 class="section-title">
                    <i class="fas fa-bolt"></i>
                    Quick Actions
                </h2>
                
                <div class="quick-actions-grid">
                    <?php if (!empty($_SESSION['staff_permissions']['manage_patients'])): ?>
                        <a href="../staff/patients/add.php" class="action-btn">
                            <div class="action-icon">
                                <i class="fas fa-user-plus"></i>
                            </div>
                            <span>Add Patient</span>
                        </a>
                    <?php endif; ?>
                    
                    <?php if (!empty($_SESSION['staff_permissions']['manage_appointments'])): ?>
                        <a href="../staff/appointments/schedule.php" class="action-btn">
                            <div class="action-icon">
                                <i class="fas fa-plus-square"></i>
                            </div>
                            <span>Schedule</span>
                        </a>
                    <?php endif; ?>
                    
                    <?php if (!empty($_SESSION['staff_permissions']['manage_reminders'])): ?>
                        <a href="../staff/reminders/create.php" class="action-btn">
                            <div class="action-icon">
                                <i class="fas fa-bell"></i>
                            </div>
                            <span>Reminder</span>
                        </a>
                    <?php endif; ?>
                    
                    <?php if (!empty($_SESSION['staff_permissions']['view_feedback'])): ?>
                        <a href="../staff/feedback/index.php" class="action-btn">
                            <div class="action-icon">
                                <i class="fas fa-comments"></i>
                            </div>
                            <span>Feedback</span>
                        </a>
                    <?php endif; ?>
                </div>
            </section>
        </div>
    </div>
    
    <script>
        // Make sure all links work properly
        document.addEventListener('DOMContentLoaded', function() {
            // Add click event to all links for debugging
            document.querySelectorAll('a').forEach(link => {
                link.addEventListener('click', function(e) {
                    if (this.getAttribute('href') === '#') {
                        e.preventDefault();
                    }
                });
            });
        });
    </script>
</body>
</html>