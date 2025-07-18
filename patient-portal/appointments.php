<?php
require_once 'includes/portal-auth.php';
requirePatientAuth();
require_once 'includes/portal-functions.php';

$patientId = $_SESSION['patient_id'];
$action = $_GET['action'] ?? 'list';
$message = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['book_appointment'])) {
        $providerId = $_POST['provider_id'];
        $appointmentType = $_POST['appointment_type'];
        $appointmentDate = $_POST['appointment_date'];
        
        if (bookAppointment($patientId, $providerId, $appointmentType, $appointmentDate)) {
            $message = "Appointment booked successfully!";
            $action = 'list'; // Show the list after booking
        } else {
            $message = "Failed to book appointment. Please try again.";
        }
    } elseif (isset($_POST['cancel_appointment'])) {
        $appointmentId = $_POST['appointment_id'];
        
        if (cancelAppointment($appointmentId, $patientId)) {
            $message = "Appointment canceled successfully.";
        } else {
            $message = "Failed to cancel appointment. Please try again.";
        }
    }
}

// Get providers for booking form
$providers = [];
$stmt = $conn->prepare("SELECT * FROM providers WHERE is_active = TRUE ORDER BY last_name, first_name");
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $providers[] = $row;
}

// Get appointment details if viewing a specific one
$appointmentDetails = null;
if (isset($_GET['id'])) {
    $appointmentId = $_GET['id'];
    $stmt = $conn->prepare("
        SELECT a.*, p.first_name, p.last_name, p.specialty, p.phone as provider_phone
        FROM appointments a
        JOIN providers p ON a.provider_id = p.provider_id
        WHERE a.appointment_id = ? AND a.patient_id = ?
    ");
    $stmt->bind_param("ii", $appointmentId, $patientId);
    $stmt->execute();
    $result = $stmt->get_result();
    $appointmentDetails = $result->fetch_assoc();
}

// Get all appointments for the list view
$appointments = getPatientAppointments($patientId);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Portal - Appointments</title>
    <link rel="stylesheet" href="assets/css/portal.css">
</head>
<body>
    <div class="portal-container">
        <?php include 'portal-header.php'; ?>
        
        <main class="portal-main">
            <div class="portal-content">
                <h1>Appointments</h1>
                
                <?php if ($message): ?>
                    <div class="alert <?php echo strpos($message, 'success') !== false ? 'alert-success' : 'alert-danger'; ?>">
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($action === 'book'): ?>
                    <section class="book-appointment">
                        <h2>Book New Appointment</h2>
                        <form method="POST">
                            <div class="form-group">
                                <label for="provider_id">Provider</label>
                                <select id="provider_id" name="provider_id" required>
                                    <option value="">Select a Provider</option>
                                    <?php foreach ($providers as $provider): ?>
                                        <option value="<?php echo $provider['provider_id']; ?>">
                                            Dr. <?php echo $provider['last_name']; ?> (<?php echo $provider['specialty']; ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="appointment_type">Appointment Type</label>
                                <select id="appointment_type" name="appointment_type" required>
                                    <option value="">Select Type</option>
                                    <option value="General Checkup">General Checkup</option>
                                    <option value="Follow-up">Follow-up</option>
                                    <option value="Consultation">Consultation</option>
                                    <option value="Procedure">Procedure</option>
                                    <option value="Vaccination">Vaccination</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="appointment_date">Preferred Date</label>
                                <input type="date" id="appointment_date" name="appointment_date" required>
                            </div>
                            
                            <div class="form-group" id="time-slots-container" style="display: none;">
                                <label>Available Time Slots</label>
                                <div class="time-slots" id="time-slots">
                                    <!-- Time slots will be populated via JavaScript -->
                                </div>
                            </div>
                            
                            <input type="hidden" id="selected_time" name="appointment_date" value="">
                            
                            <button type="submit" name="book_appointment" class="btn btn-primary" disabled id="book-button">
                                Book Appointment
                            </button>
                            <a href="appointments.php" class="btn btn-cancel">Cancel</a>
                        </form>
                    </section>
                <?php elseif ($action === 'view' && $appointmentDetails): ?>
                    <section class="appointment-details">
                        <h2>Appointment Details</h2>
                        
                        <div class="detail-card">
                            <div class="detail-row">
                                <span class="detail-label">Date & Time:</span>
                                <span class="detail-value">
                                    <?php echo date('F j, Y g:i A', strtotime($appointmentDetails['appointment_date'])); ?>
                                </span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Provider:</span>
                                <span class="detail-value">
                                    Dr. <?php echo $appointmentDetails['last_name']; ?>
                                    (<?php echo $appointmentDetails['specialty']; ?>)
                                </span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Type:</span>
                                <span class="detail-value"><?php echo $appointmentDetails['appointment_type']; ?></span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Status:</span>
                                <span class="detail-value status-<?php echo $appointmentDetails['status']; ?>">
                                    <?php echo ucfirst($appointmentDetails['status']); ?>
                                </span>
                            </div>
                            
                            <?php if ($appointmentDetails['status'] === 'scheduled'): ?>
                                <div class="appointment-actions">
                                    <form method="POST" onsubmit="return confirm('Are you sure you want to cancel this appointment?');">
                                        <input type="hidden" name="appointment_id" value="<?php echo $appointmentDetails['appointment_id']; ?>">
                                        <button type="submit" name="cancel_appointment" class="btn btn-danger">
                                            Cancel Appointment
                                        </button>
                                    </form>
                                </div>
                            <?php endif; ?>
                        </div>
                    </section>
                <?php else: ?>
                    <section class="appointment-list-section">
                        <div class="section-header">
                            <h2>Your Appointments</h2>
                            <a href="appointments.php?action=book" class="btn btn-primary">
                                Book New Appointment
                            </a>
                        </div>
                        
                        <?php if (count($appointments) > 0): ?>
                            <table class="appointment-table">
                                <thead>
                                    <tr>
                                        <th>Date & Time</th>
                                        <th>Provider</th>
                                        <th>Type</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($appointments as $appt): ?>
                                    <tr>
                                        <td><?php echo date('M j, Y g:i A', strtotime($appt['appointment_date'])); ?></td>
                                        <td>Dr. <?php echo $appt['last_name']; ?></td>
                                        <td><?php echo $appt['appointment_type']; ?></td>
                                        <td>
                                            <span class="status-badge status-<?php echo $appt['status']; ?>">
                                                <?php echo ucfirst($appt['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="appointments.php?action=view&id=<?php echo $appt['appointment_id']; ?>" class="btn btn-sm">
                                                View
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <p>You have no appointments scheduled.</p>
                        <?php endif; ?>
                    </section>
                <?php endif; ?>
            </div>
        </main>
    </div>
    
    <script src="assets/js/portal.js"></script>
    <script>
        // Handle appointment booking form
        document.getElementById('provider_id').addEventListener('change', function() {
            checkAvailability();
        });
        
        document.getElementById('appointment_date').addEventListener('change', function() {
            checkAvailability();
        });
        
        function checkAvailability() {
            const providerId = document.getElementById('provider_id').value;
            const date = document.getElementById('appointment_date').value;
            
            if (providerId && date) {
                fetch(`api/get-available-slots.php?provider_id=${providerId}&date=${date}`)
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
                                button.className = 'time-slot';
                                button.textContent = timeString;
                                button.dataset.time = slot;
                                
                                button.addEventListener('click', function() {
                                    // Remove active class from all buttons
                                    document.querySelectorAll('.time-slot').forEach(btn => {
                                        btn.classList.remove('active');
                                    });
                                    
                                    // Add active class to clicked button
                                    this.classList.add('active');
                                    
                                    // Set the hidden input value
                                    document.getElementById('selected_time').value = this.dataset.time;
                                    
                                    // Enable the book button
                                    document.getElementById('book-button').disabled = false;
                                });
                                
                                container.appendChild(button);
                            });
                            
                            document.getElementById('time-slots-container').style.display = 'block';
                        } else {
                            const message = document.createElement('p');
                            message.textContent = 'No available slots for this date. Please choose another date.';
                            container.appendChild(message);
                            document.getElementById('time-slots-container').style.display = 'block';
                        }
                    });
            }
        }
    </script>
</body>
</html>