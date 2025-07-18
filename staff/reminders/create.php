<?php
require_once '../../config/database.php';
require_once '../includes/staff-auth.php';
requireStaffAuth();

if (!$_SESSION['staff_permissions']['manage_reminders']) {
    header("Location: ../dashboard.php");
    exit();
}

$message = '';
$patientId = $_GET['patient_id'] ?? null;
$patients = [];

// Fetch all patients
$result = $conn->query("SELECT patient_id, first_name, last_name FROM patients ORDER BY last_name");
while ($row = $result->fetch_assoc()) {
    $patients[] = $row;
}

// Define getPatientDetails helper
function getPatientDetails($conn, $patientId) {
    $stmt = $conn->prepare("SELECT * FROM patients WHERE patient_id = ?");
    $stmt->bind_param("i", $patientId);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $patientId = $_POST['patient_id'];
    $reminderType = $_POST['reminder_type'];
    $messageText = $_POST['message'];
    $dueDate = $_POST['due_date'];
    $dueTime = $_POST['due_time'];
    
    $dueDateTime = date('Y-m-d H:i:s', strtotime("$dueDate $dueTime"));
    $createdBy = $_SESSION['staff_id'];
    
    if (createReminder($conn, $patientId, $reminderType, $messageText, $dueDateTime, $createdBy)) {
        $message = "Reminder created successfully!";
        if (!isset($_GET['patient_id'])) {
            $_POST = [];
        }
    } else {
        $message = "Failed to create reminder. Please try again.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Portal - Create Reminder</title>
    <link rel="stylesheet" href="../assets/css/staff.css">
</head>
<body>
<?php include '../includes/header.php'; ?>

<div class="container">
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Create New Reminder</h2>
            <a href="index.php" class="btn btn-secondary">Back to Reminders</a>
        </div>

        <?php if ($message): ?>
            <div class="alert <?php echo strpos($message, 'successfully') !== false ? 'alert-success' : 'alert-danger'; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="reminder-form">
            <div class="form-group">
                <label for="patient_id">Patient</label>
                <select id="patient_id" name="patient_id" required <?php echo $patientId ? 'disabled' : ''; ?>>
                    <?php if ($patientId): ?>
                        <?php 
                            $patient = getPatientDetails($conn, $patientId);
                            if ($patient): ?>
                            <option value="<?php echo $patientId; ?>" selected>
                                <?php echo htmlspecialchars($patient['last_name'] . ', ' . $patient['first_name']); ?>
                            </option>
                        <?php endif; ?>
                    <?php else: ?>
                        <option value="">Select Patient</option>
                        <?php foreach ($patients as $p): ?>
                            <option value="<?php echo $p['patient_id']; ?>" <?php echo ($_POST['patient_id'] ?? '') == $p['patient_id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($p['last_name'] . ', ' . $p['first_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
                <?php if ($patientId): ?>
                    <input type="hidden" name="patient_id" value="<?php echo $patientId; ?>">
                <?php endif; ?>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="reminder_type">Reminder Type</label>
                    <select id="reminder_type" name="reminder_type" required>
                        <option value="">Select Type</option>
                        <option value="appointment" <?php echo ($_POST['reminder_type'] ?? '') === 'appointment' ? 'selected' : ''; ?>>Appointment</option>
                        <option value="medication" <?php echo ($_POST['reminder_type'] ?? '') === 'medication' ? 'selected' : ''; ?>>Medication</option>
                        <option value="followup" <?php echo ($_POST['reminder_type'] ?? '') === 'followup' ? 'selected' : ''; ?>>Follow-up</option>
                        <option value="payment" <?php echo ($_POST['reminder_type'] ?? '') === 'payment' ? 'selected' : ''; ?>>Payment</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="due_date">Due Date</label>
                    <input type="date" id="due_date" name="due_date" value="<?php echo $_POST['due_date'] ?? date('Y-m-d'); ?>" required>
                </div>

                <div class="form-group">
                    <label for="due_time">Due Time</label>
                    <input type="time" id="due_time" name="due_time" value="<?php echo $_POST['due_time'] ?? '09:00'; ?>" required>
                </div>
            </div>

            <div class="form-group">
                <label for="message">Message</label>
                <textarea id="message" name="message" rows="4" required><?php echo $_POST['message'] ?? ''; ?></textarea>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Create Reminder</button>
                <button type="reset" class="btn btn-secondary">Reset</button>
            </div>
        </form>
    </div>
</div>

<script src="../assets/js/staff.js"></script>
<script>
    // Set minimum date to today
    document.getElementById('due_date').min = new Date().toISOString().split('T')[0];

    // Auto set due time to next hour if not submitted
    const now = new Date();
    const nextHour = new Date(now.getTime() + 60 * 60 * 1000);
    const hours = nextHour.getHours().toString().padStart(2, '0');
    const minutes = '00';

    <?php if (!isset($_POST['due_time'])): ?>
        document.getElementById('due_time').value = `${hours}:${minutes}`;
    <?php endif; ?>
</script>
</body>
</html>
