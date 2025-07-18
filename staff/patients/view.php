<?php
require_once '../../config/database.php';
require_once '../includes/staff-auth.php';
requireStaffAuth();
require_once '../includes/staff-functions.php';

if (!$_SESSION['staff_permissions']['manage_patients']) {
    header("Location: ../dashboard.php");
    exit();
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$patientId = (int)$_GET['id'];
$patient = getPatientDetails($patientId);

if (!$patient) {
    header("Location: index.php");
    exit();
}

// Get patient's upcoming appointments
$upcomingAppointments = [];
$stmt = $conn->prepare("
    SELECT a.*, pr.first_name as provider_first, pr.last_name as provider_last 
    FROM appointments a
    JOIN providers pr ON a.provider_id = pr.provider_id
    WHERE a.patient_id = ? AND a.appointment_date >= NOW()
    ORDER BY a.appointment_date ASC
    LIMIT 5
");
$stmt->bind_param("i", $patientId);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $upcomingAppointments[] = $row;
}

// Get patient's recent reminders
$recentReminders = [];
$stmt = $conn->prepare("
    SELECT * FROM reminders 
    WHERE patient_id = ? 
    ORDER BY due_date DESC
    LIMIT 5
");
$stmt->bind_param("i", $patientId);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $recentReminders[] = $row;
}

// Get patient's feedback
$patientFeedback = [];
$stmt = $conn->prepare("
    SELECT * FROM feedback 
    WHERE patient_id = ? 
    ORDER BY feedback_date DESC
");
$stmt->bind_param("i", $patientId);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $patientFeedback[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Portal - Patient Details</title>
    <link rel="stylesheet" href="../assets/css/staff.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Patient Details</h2>
                <div class="patient-actions">
                    <a href="edit.php?id=<?php echo $patientId; ?>" class="btn btn-secondary">Edit</a>
                    <a href="index.php" class="btn">Back to Patients</a>
                </div>
            </div>
            
            <div class="patient-details">
                <div class="detail-section">
                    <h3>Personal Information</h3>
                    <div class="detail-grid">
                        <div class="detail-item">
                            <span class="detail-label">Name:</span>
                            <span class="detail-value"><?php echo htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Email:</span>
                            <span class="detail-value"><?php echo htmlspecialchars($patient['email']); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Phone:</span>
                            <span class="detail-value"><?php echo htmlspecialchars($patient['phone']); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Date of Birth:</span>
                            <span class="detail-value"><?php echo date('F j, Y', strtotime($patient['dob'])); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Age:</span>
                            <span class="detail-value">
                                <?php 
                                    $dob = new DateTime($patient['dob']);
                                    $now = new DateTime();
                                    echo $dob->diff($now)->y;
                                ?> years
                            </span>
                        </div>
                    </div>
                </div>
                
                <div class="detail-sections">
                    <div class="detail-subsection">
                        <h3>Upcoming Appointments</h3>
                        <?php if (count($upcomingAppointments) > 0): ?>
                            <ul class="appointment-list">
                                <?php foreach ($upcomingAppointments as $appt): ?>
                                <li>
                                    <div class="appointment-date">
                                        <?php echo date('M j, Y g:i A', strtotime($appt['appointment_date'])); ?>
                                    </div>
                                    <div class="appointment-details">
                                        <strong><?php echo htmlspecialchars($appt['appointment_type']); ?></strong>
                                        with Dr. <?php echo htmlspecialchars($appt['provider_last']); ?>
                                    </div>
                                    <div class="appointment-actions">
                                        <a href="../appointments/view.php?id=<?php echo $appt['appointment_id']; ?>" class="btn btn-sm">View</a>
                                    </div>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                            <a href="../appointments/?patient=<?php echo $patientId; ?>" class="btn">View All Appointments</a>
                        <?php else: ?>
                            <p>No upcoming appointments scheduled.</p>
                            <a href="../appointments/schedule.php?patient_id=<?php echo $patientId; ?>" class="btn btn-primary">Schedule Appointment</a>
                        <?php endif; ?>
                    </div>
                    
                    <div class="detail-subsection">
                        <h3>Recent Reminders</h3>
                        <?php if (count($recentReminders) > 0): ?>
                            <ul class="reminder-list">
                                <?php foreach ($recentReminders as $reminder): ?>
                                <li class="reminder-<?php echo $reminder['reminder_type']; ?>">
                                    <div class="reminder-content">
                                        <strong><?php echo htmlspecialchars($reminder['reminder_message']); ?></strong>
                                        <div class="reminder-meta">
                                            <span class="reminder-type"><?php echo ucfirst($reminder['reminder_type']); ?></span>
                                            <span class="reminder-date">
                                                Due: <?php echo date('M j, Y g:i A', strtotime($reminder['due_date'])); ?>
                                            </span>
                                            <span class="reminder-status status-<?php echo $reminder['status']; ?>">
                                                <?php echo ucfirst($reminder['status']); ?>
                                            </span>
                                        </div>
                                    </div>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                            <a href="../reminders/?patient=<?php echo $patientId; ?>" class="btn">View All Reminders</a>
                            <a href="../reminders/create.php?patient_id=<?php echo $patientId; ?>" class="btn btn-primary">Create Reminder</a>
                        <?php else: ?>
                            <p>No reminders found.</p>
                            <a href="../reminders/create.php?patient_id=<?php echo $patientId; ?>" class="btn btn-primary">Create Reminder</a>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="detail-section">
                    <h3>Patient Feedback</h3>
                    <?php if (count($patientFeedback) > 0): ?>
                        <div class="feedback-list">
                            <?php foreach ($patientFeedback as $feedback): ?>
                            <div class="feedback-item">
                                <div class="feedback-header">
                                    <div class="feedback-rating">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <span class="star <?php echo $i <= $feedback['rating'] ? 'filled' : ''; ?>">★</span>
                                        <?php endfor; ?>
                                    </div>
                                    <span class="feedback-date"><?php echo date('M j, Y', strtotime($feedback['feedback_date'])); ?></span>
                                    <span class="status-badge status-<?php echo $feedback['status']; ?>">
                                        <?php echo ucfirst($feedback['status']); ?>
                                    </span>
                                </div>
                                <div class="feedback-content">
                                    <p><?php echo nl2br(htmlspecialchars($feedback['feedback_text'])); ?></p>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p>No feedback submitted by this patient.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <script src="../assets/js/staff.js"></script>
</body>
</html>