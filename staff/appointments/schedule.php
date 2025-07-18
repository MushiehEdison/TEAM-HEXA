<?php
require_once '../../config/database.php';
require_once '../includes/staff-auth.php';
requireStaffAuth();

if (!$_SESSION['staff_permissions']['manage_appointments']) {
    header("Location: ../dashboard.php");
    exit();
}

$message = '';
$patients = [];
$providers = [];
$appointmentTypes = [];

// Get patients
$result = $conn->query("SELECT patient_id, first_name, last_name FROM patients ORDER BY last_name");
while ($row = $result->fetch_assoc()) {
    $patients[] = $row;
}

// Get providers
$result = $conn->query("SELECT provider_id, first_name, last_name, specialty FROM providers WHERE is_active = TRUE ORDER BY last_name");
while ($row = $result->fetch_assoc()) {
    $providers[] = $row;
}

// Get appointment types
$result = $conn->query("SELECT * FROM appointment_types ORDER BY name");
while ($row = $result->fetch_assoc()) {
    $appointmentTypes[] = $row;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $patientId = $_POST['patient_id'];
    $providerId = $_POST['provider_id'];
    $type = $_POST['type'];
    $date = $_POST['date'];
    $time = $_POST['time'];
    $notes = $_POST['notes'] ?? '';
    
    $appointmentDate = date('Y-m-d H:i:s', strtotime("$date $time"));
    
    // Get duration from appointment type
    $duration = 30; // default
    foreach ($appointmentTypes as $apptType) {
        if ($apptType['name'] === $type) {
            $duration = $apptType['duration'];
            break;
        }
    }
    
    $stmt = $conn->prepare("INSERT INTO appointments 
                           (patient_id, provider_id, appointment_type, appointment_date, duration, notes) 
                           VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iissis", $patientId, $providerId, $type, $appointmentDate, $duration, $notes);
    
    if ($stmt->execute()) {
        $message = "Appointment scheduled successfully!";
        // Clear form or redirect if needed
    } else {
        $message = "Failed to schedule appointment. Please try again.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Portal - Schedule Appointment</title>
    <link rel="stylesheet" href="../assets/css/staff.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Schedule New Appointment</h2>
                <a href="index.php" class="btn btn-secondary">Back to Appointments</a>
            </div>
            
            <?php if ($message): ?>
                <div class="alert <?php echo strpos($message, 'success') !== false ? 'alert-success' : 'alert-danger'; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" class="appointment-form">
                <div class="form-row">
                    <div class="form-group">
                        <label for="patient_id">Patient</label>
                        <select id="patient_id" name="patient_id" required>
                            <option value="">Select Patient</option>
                            <?php foreach ($patients as $patient): ?>
                                <option value="<?php echo $patient['patient_id']; ?>">
                                    <?php echo htmlspecialchars($patient['last_name'] . ', ' . $patient['first_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="provider_id">Provider</label>
                        <select id="provider_id" name="provider_id" required>
                            <option value="">Select Provider</option>
                            <?php foreach ($providers as $provider): ?>
                                <option value="<?php echo $provider['provider_id']; ?>">
                                    Dr. <?php echo htmlspecialchars($provider['last_name']); ?> (<?php echo htmlspecialchars($provider['specialty']); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="type">Appointment Type</label>
                        <select id="type" name="type" required>
                            <option value="">Select Type</option>
                            <?php foreach ($appointmentTypes as $type): ?>
                                <option value="<?php echo htmlspecialchars($type['name']); ?>">
                                    <?php echo htmlspecialchars($type['name']); ?> (<?php echo $type['duration']; ?> mins)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="date">Date</label>
                        <input type="date" id="date" name="date" required min="<?php echo date('Y-m-d'); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="time">Time</label>
                        <input type="time" id="time" name="time" required step="900"> <!-- 15 minute intervals -->
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="notes">Notes</label>
                    <textarea id="notes" name="notes" rows="3"></textarea>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Schedule Appointment</button>
                    <button type="reset" class="btn btn-secondary">Reset</button>
                </div>
            </form>
            
            <div id="availability-results" style="margin-top: 20px; display: none;">
                <h3>Available Time Slots</h3>
                <div id="time-slots" class="time-slots"></div>
            </div>
        </div>
    </div>
    
    <script src="../assets/js/staff.js"></script>
    <script>
        // Check availability when provider or date changes
        document.getElementById('provider_id').addEventListener('change', checkAvailability);
        document.getElementById('date').addEventListener('change', checkAvailability);
        
        function checkAvailability() {
            const providerId = document.getElementById('provider_id').value;
            const date = document.getElementById('date').value;
            
            if (providerId && date) {
                fetch(`../api/get-provider-availability.php?provider_id=${providerId}&date=${date}`)
                    .then(response => response.json())
                    .then(slots => {
                        const container = document.getElementById('time-slots');
                        container.innerHTML = '';
                        
                        if (slots.length > 0) {
                            slots.forEach(slot => {
                                const time = new Date(slot);
                                const timeString = time.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                                
                                const button = document.createElement('button');
                                button.type = 'button';
                                button.className = 'time-slot-btn';
                                button.textContent = timeString;
                                button.dataset.time = slot;
                                
                                button.addEventListener('click', function() {
                                    document.querySelectorAll('.time-slot-btn').forEach(btn => {
                                        btn.classList.remove('active');
                                    });
                                    this.classList.add('active');
                                    document.getElementById('time').value = timeString;
                                });
                                
                                container.appendChild(button);
                            });
                            
                            document.getElementById('availability-results').style.display = 'block';
                        } else {
                            container.innerHTML = '<p>No available slots for this date.</p>';
                            document.getElementById('availability-results').style.display = 'block';
                        }
                    });
            }
        }
    </script>
</body>
</html>