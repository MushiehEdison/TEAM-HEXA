<?php
// Start session at the very beginning
session_start();

require_once '../../config/database.php';
require_once '../includes/staff-auth.php';
requireStaffAuth();

if (!$_SESSION['staff_permissions']['manage_appointments']) {
    header("Location: ../dashboard.php");
    exit();
}

// Get appointment ID
$appointmentId = $_GET['id'] ?? null;
if (!$appointmentId) {
    header("Location: index.php");
    exit();
}

// Initialize variables
$appointment = null;
$history = null;

try {
    // Fetch appointment details
    $stmt = $conn->prepare(
        "SELECT a.*, 
                p.first_name as patient_first, p.last_name as patient_last, p.phone, p.email,
                pr.first_name as provider_first, pr.last_name as provider_last, pr.specialty
         FROM appointments a
         JOIN patients p ON a.patient_id = p.patient_id
         JOIN providers pr ON a.provider_id = pr.provider_id
         WHERE a.appointment_id = ?"
    );
    
    $stmt->bind_param('i', $appointmentId);
    $stmt->execute();
    $result = $stmt->get_result();
    $appointment = $result->fetch_assoc();

    if (!$appointment) {
        header("Location: index.php");
        exit();
    }

    // Fetch patient's medical history summary
    $historyStmt = $conn->prepare(
        "SELECT COUNT(*) as total_appointments,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN status = 'canceled' THEN 1 ELSE 0 END) as canceled
         FROM appointments
         WHERE patient_id = ?"
    );
    $historyStmt->bind_param('i', $appointment['patient_id']);
    $historyStmt->execute();
    $historyResult = $historyStmt->get_result();
    $history = $historyResult->fetch_assoc();
    
} catch (Exception $e) {
    // Log error and redirect
    error_log("Database error: " . $e->getMessage());
    header("Location: index.php?error=1");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Appointment - Staff Portal</title>
    <link rel="stylesheet" href="../assets/css/staff.css">
    <style>
        :root {
            --primary-blue: #3498db;
            --dark-blue: #2980b9;
            --light-blue: #e1f0fa;
            --primary-pink: #e83e8c;
            --light-pink: #fae1ee;
            --dark-pink: #d35480;
            --text-dark: #333;
            --text-light: #555;
            --white: #fff;
            --gray: #f5f5f5;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f9f9f9;
            color: var(--text-dark);
            line-height: 1.6;
        }
        
        .container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 0 15px;
        }
        
        .card {
            background-color: var(--white);
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            margin-bottom: 30px;
        }
        
        .card-header {
            background: linear-gradient(135deg, var(--primary-blue), var(--primary-pink));
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: var(--white);
        }
        
        .card-title {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }
        
        .appointment-details {
            padding: 25px;
        }
        
        .detail-row {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .detail-column {
            flex: 1;
            min-width: 300px;
            background-color: var(--gray);
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        }
        
        .detail-column h3 {
            color: var(--primary-blue);
            margin-top: 0;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--light-pink);
        }
        
        .detail-item {
            margin-bottom: 15px;
        }
        
        .detail-label {
            font-weight: 600;
            color: var(--primary-pink);
            display: block;
            margin-bottom: 5px;
        }
        
        .detail-value {
            display: block;
            color: var(--text-light);
            padding-left: 10px;
        }
        
        .status-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 500;
            color: white;
        }
        
        .status-scheduled {
            background-color: var(--primary-blue);
        }
        
        .status-completed {
            background-color: #2ecc71;
        }
        
        .status-canceled {
            background-color: #e74c3c;
        }
        
        .action-buttons {
            display: flex;
            gap: 15px;
            margin-top: 20px;
            flex-wrap: wrap;
        }
        
        .btn {
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            display: inline-block;
            text-align: center;
        }
        
        .btn-primary {
            background-color: var(--primary-blue);
            color: white;
            border: 2px solid var(--primary-blue);
        }
        
        .btn-primary:hover {
            background-color: var(--dark-blue);
            border-color: var(--dark-blue);
        }
        
        .btn-secondary {
            background-color: var(--white);
            color: var(--primary-blue);
            border: 2px solid var(--primary-blue);
        }
        
        .btn-secondary:hover {
            background-color: var(--light-blue);
        }
        
        .btn-danger {
            background-color: var(--primary-pink);
            color: white;
            border: 2px solid var(--primary-pink);
        }
        
        .btn-danger:hover {
            background-color: var(--dark-pink);
            border-color: var(--dark-pink);
        }
        
        @media (max-width: 768px) {
            .detail-row {
                flex-direction: column;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Appointment Details</h2>
                <a href="index.php" class="btn btn-secondary">Back to List</a>
            </div>
            
            <div class="appointment-details">
                <div class="detail-row">
                    <div class="detail-column">
                        <h3>Appointment Information</h3>
                        <div class="detail-item">
                            <span class="detail-label">Date & Time:</span>
                            <span class="detail-value"><?php echo date('F j, Y g:i A', strtotime($appointment['appointment_date'])); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Status:</span>
                            <span class="status-badge status-<?php echo $appointment['status']; ?>">
                                <?php echo ucfirst($appointment['status']); ?>
                            </span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Type:</span>
                            <span class="detail-value"><?php echo htmlspecialchars($appointment['appointment_type']); ?></span>
                        </div>
                     
                        <div class="detail-item">
                            <span class="detail-label">Notes:</span>
                            <span class="detail-value"><?php echo nl2br(htmlspecialchars($appointment['notes'])); ?></span>
                        </div>
                    </div>
                    
                    <div class="detail-column">
                        <h3>Patient Information</h3>
                        <div class="detail-item">
                            <span class="detail-label">Name:</span>
                            <span class="detail-value"><?php echo htmlspecialchars($appointment['patient_first'] . ' ' . $appointment['patient_last']); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Phone:</span>
                            <span class="detail-value"><?php echo htmlspecialchars($appointment['phone']); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Email:</span>
                            <span class="detail-value"><?php echo htmlspecialchars($appointment['email']); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Appointment History:</span>
                            <span class="detail-value">
                                <?php echo $history['total_appointments']; ?> total, 
                                <?php echo $history['completed']; ?> completed, 
                                <?php echo $history['canceled']; ?> canceled
                            </span>
                        </div>
                    </div>
                    
                    <div class="detail-column">
                        <h3>Provider Information</h3>
                        <div class="detail-item">
                            <span class="detail-label">Provider:</span>
                            <span class="detail-value">Dr. <?php echo htmlspecialchars($appointment['provider_first'] . ' ' . $appointment['provider_last']); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Specialty:</span>
                            <span class="detail-value"><?php echo htmlspecialchars($appointment['specialty']); ?></span>
                        </div>
                    </div>
                </div>
                
                <div class="action-buttons">
                    <a href="edit.php?id=<?php echo $appointmentId; ?>" class="btn btn-primary">Edit Appointment</a>
                    <?php if ($appointment['status'] === 'scheduled'): ?>
                        <a href="cancel.php?id=<?php echo $appointmentId; ?>" class="btn btn-danger" data-confirm="Are you sure you want to cancel this appointment?">Cancel Appointment</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <script src="../assets/js/staff.js"></script>
</body>
</html>