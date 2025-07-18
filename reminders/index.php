<?php
require_once '../includes/auth.php';
requireAuth();
require_once '../includes/functions.php';

// Handle reminder actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_reminder'])) {
        $patientId = sanitizeInput($_POST['patient_id']);
        $reminderType = sanitizeInput($_POST['reminder_type']);
        $message = sanitizeInput($_POST['message']);
        $dueDate = sanitizeInput($_POST['due_date']);
        
        if (createReminder($patientId, $reminderType, $message, $dueDate)) {
            $success = "Reminder created successfully!";
        } else {
            $error = "Failed to create reminder.";
        }
    } elseif (isset($_POST['update_status'])) {
        $reminderId = sanitizeInput($_POST['reminder_id']);
        $status = sanitizeInput($_POST['status']);
        
        $stmt = $conn->prepare("UPDATE reminders SET status = ? WHERE reminder_id = ?");
        $stmt->bind_param("si", $status, $reminderId);
        
        if ($stmt->execute()) {
            $success = "Reminder status updated!";
        } else {
            $error = "Failed to update reminder status.";
        }
    }
}

// Get all reminders
$reminders = getAllReminders();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient System - Reminders</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/reminders.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <main class="container">
        <h1>Patient Reminders</h1>
        
        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <div class="reminder-management">
            <section class="add-reminder">
                <h2>Create New Reminder</h2>
                <form method="POST">
                    <div class="form-group">
                        <label for="patient_id">Patient</label>
                        <select id="patient_id" name="patient_id" required>
                            <option value="">Select Patient</option>
                            <?php foreach (getAllPatients() as $patient): ?>
                            <option value="<?php echo $patient['patient_id']; ?>">
                                <?php echo $patient['first_name'] . ' ' . $patient['last_name']; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="reminder_type">Reminder Type</label>
                        <select id="reminder_type" name="reminder_type" required>
                            <option value="appointment">Appointment</option>
                            <option value="medication">Medication</option>
                            <option value="followup">Follow-up</option>
                            <option value="payment">Payment</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="message">Message</label>
                        <textarea id="message" name="message" required></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="due_date">Due Date</label>
                        <input type="datetime-local" id="due_date" name="due_date" required>
                    </div>
                    
                    <button type="submit" name="add_reminder" class="btn btn-primary">Create Reminder</button>
                </form>
            </section>
            
            <section class="reminder-list">
                <h2>Upcoming Reminders</h2>
                
                <div class="reminder-filters">
                    <a href="?status=all" class="btn">All</a>
                    <a href="?status=pending" class="btn">Pending</a>
                    <a href="?status=sent" class="btn">Sent</a>
                    <a href="?status=completed" class="btn">Completed</a>
                </div>
                
                <div class="reminders">
                    <?php foreach ($reminders as $reminder): ?>
                    <div class="reminder-card">
                        <div class="reminder-header">
                            <h3><?php echo $reminder['first_name'] . ' ' . $reminder['last_name']; ?></h3>
                            <span class="reminder-type <?php echo $reminder['reminder_type']; ?>">
                                <?php echo ucfirst($reminder['reminder_type']); ?>
                            </span>
                            <span class="status-badge <?php echo $reminder['status']; ?>">
                                <?php echo ucfirst($reminder['status']); ?>
                            </span>
                        </div>
                        
                        <div class="reminder-content">
                            <p><?php echo $reminder['reminder_message']; ?></p>
                            <div class="due-date">
                                <strong>Due:</strong> 
                                <?php echo date('M d, Y h:i A', strtotime($reminder['due_date'])); ?>
                            </div>
                        </div>
                        
                        <div class="reminder-actions">
                            <form method="POST" class="status-form">
                                <input type="hidden" name="reminder_id" value="<?php echo $reminder['reminder_id']; ?>">
                                <select name="status">
                                    <option value="pending" <?php echo $reminder['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="sent" <?php echo $reminder['status'] == 'sent' ? 'selected' : ''; ?>>Sent</option>
                                    <option value="completed" <?php echo $reminder['status'] == 'completed' ? 'selected' : ''; ?>>Completed</option>
                                </select>
                                <button type="submit" name="update_status" class="btn btn-sm">Update</button>
                            </form>
                            
                            <a href="#" class="btn btn-sm btn-danger">Delete</a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </section>
        </div>
    </main>
    
    <script src="../assets/js/reminders.js"></script>
</body>
</html>