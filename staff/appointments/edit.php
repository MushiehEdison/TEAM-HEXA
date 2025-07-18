<?php
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

// Fetch appointment details
$stmt = $conn->prepare(
    "SELECT a.*, p.first_name as patient_first, p.last_name as patient_last
     FROM appointments a
     JOIN patients p ON a.patient_id = p.patient_id
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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $appointmentDate = $_POST['appointment_date'];
    $providerId = $_POST['provider_id'];
    $appointmentType = $_POST['appointment_type'];
    $notes = $_POST['notes'];
    
    // Validate inputs
    $errors = [];
    if (empty($appointmentDate)) $errors[] = 'Appointment date is required';
    if (empty($providerId)) $errors[] = 'Provider is required';
    if (empty($appointmentType)) $errors[] = 'Appointment type is required';
    
    if (empty($errors)) {
        $updateStmt = $conn->prepare(
            "UPDATE appointments 
             SET appointment_date = ?, provider_id = ?, appointment_type = ?, notes = ?
             WHERE appointment_id = ?"
        );
        $updateStmt->bind_param(
            'sisss',
            $appointmentDate,
            $providerId,
            $appointmentType,
            $notes,
            $appointmentId
        );
        
        if ($updateStmt->execute()) {
            $_SESSION['success_message'] = 'Appointment updated successfully';
            header("Location: view.php?id=$appointmentId");
            exit();
        } else {
            $errors[] = 'Failed to update appointment: ' . $conn->error;
        }
    }
}

// Get providers for dropdown
$providers = [];
$result = $conn->query("SELECT provider_id, first_name, last_name, specialty FROM providers ORDER BY last_name");
while ($row = $result->fetch_assoc()) {
    $providers[] = $row;
}

// Get appointment types
$appointmentTypes = ['Checkup', 'Follow-up', 'Consultation', 'Procedure', 'Vaccination', 'Other'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Appointment - Staff Portal</title>
    <link rel="stylesheet" href="../assets/css/staff.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Edit Appointment</h2>
                <a href="view.php?id=<?php echo $appointmentId; ?>" class="btn btn-secondary">Back to View</a>
            </div>
            
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <form method="POST" class="appointment-form">
                <div class="form-row">
                    <div class="form-group">
                        <label for="patient">Patient</label>
                        <input type="text" id="patient" value="<?php echo htmlspecialchars($appointment['patient_first'] . ' ' . $appointment['patient_last']); ?>" readonly>
                    </div>
                    
                    <div class="form-group">
                        <label for="appointment_date">Date & Time</label>
                        <input type="datetime-local" id="appointment_date" name="appointment_date" 
                               value="<?php echo date('Y-m-d\TH:i', strtotime($appointment['appointment_date'])); ?>" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="provider_id">Provider</label>
                        <select id="provider_id" name="provider_id" required>
                            <option value="">Select Provider</option>
                            <?php foreach ($providers as $provider): ?>
                                <option value="<?php echo $provider['provider_id']; ?>"
                                    <?php echo $provider['provider_id'] == $appointment['provider_id'] ? 'selected' : ''; ?>>
                                    Dr. <?php echo htmlspecialchars($provider['last_name']); ?> (<?php echo htmlspecialchars($provider['specialty']); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="appointment_type">Appointment Type</label>
                        <select id="appointment_type" name="appointment_type" required>
                            <option value="">Select Type</option>
                            <?php foreach ($appointmentTypes as $type): ?>
                                <option value="<?php echo $type; ?>"
                                    <?php echo $type == $appointment['appointment_type'] ? 'selected' : ''; ?>>
                                    <?php echo $type; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="notes">Notes</label>
                    <textarea id="notes" name="notes" rows="3"><?php echo htmlspecialchars($appointment['notes']); ?></textarea>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                    <a href="view.php?id=<?php echo $appointmentId; ?>" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
    
    <script src="../assets/js/staff.js"></script>
</body>
</html>