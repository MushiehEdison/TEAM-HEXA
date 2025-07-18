<?php
require_once 'includes/portal-auth.php';
requirePatientAuth();
require_once 'includes/portal-functions.php';

$patientId = $_SESSION['patient_id'];
$filter = $_GET['filter'] ?? 'upcoming'; // Default to showing upcoming reminders
$message = '';

// Get reminders based on filter
$reminders = [];
if ($filter === 'upcoming') {
    $reminders = getPatientReminders($patientId, 'pending');
} elseif ($filter === 'past') {
    $stmt = $conn->prepare("
        SELECT * FROM reminders 
        WHERE patient_id = ? AND due_date < NOW() 
        ORDER BY due_date DESC
    ");
    $stmt->bind_param("i", $patientId);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $reminders[] = $row;
    }
} else {
    $reminders = getPatientReminders($patientId);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Portal - Reminders</title>
    <link rel="stylesheet" href="assets/css/portal.css">
</head>
<body>
    <div class="portal-container">
        <?php include 'portal-header.php'; ?>
        
        <main class="portal-main">
            <div class="portal-content">
                <h1>Your Reminders</h1>
                
                <?php if ($message): ?>
                    <div class="alert alert-success"><?php echo $message; ?></div>
                <?php endif; ?>
                
                <div class="reminder-filters">
                    <a href="?filter=upcoming" class="btn <?php echo $filter === 'upcoming' ? 'btn-primary' : 'btn-secondary'; ?>">
                        Upcoming Reminders
                    </a>
                    <a href="?filter=past" class="btn <?php echo $filter === 'past' ? 'btn-primary' : 'btn-secondary'; ?>">
                        Past Reminders
                    </a>
                    <a href="?filter=all" class="btn <?php echo $filter === 'all' ? 'btn-primary' : 'btn-secondary'; ?>">
                        All Reminders
                    </a>
                </div>
                
                <?php if (count($reminders) > 0): ?>
                    <div class="reminders-list">
                        <?php foreach ($reminders as $reminder): 
                            $isPast = strtotime($reminder['due_date']) < time();
                        ?>
                        <div class="reminder-card reminder-type-<?php echo $reminder['reminder_type']; ?> <?php echo $isPast ? 'past-reminder' : ''; ?>">
                            <div class="reminder-icon">
                                <?php switch($reminder['reminder_type']) {
                                    case 'appointment': echo '📅'; break;
                                    case 'medication': echo '💊'; break;
                                    case 'followup': echo '🔄'; break;
                                    case 'payment': echo '💳'; break;
                                    default: echo '⏰';
                                } ?>
                            </div>
                            
                            <div class="reminder-content">
                                <h3><?php echo htmlspecialchars($reminder['reminder_message']); ?></h3>
                                
                                <div class="reminder-meta">
                                    <span class="reminder-type">
                                        <?php echo ucfirst($reminder['reminder_type']); ?>
                                    </span>
                                    
                                    <span class="reminder-date">
                                        <?php if ($isPast): ?>
                                            Was due: <?php echo date('M j, Y g:i A', strtotime($reminder['due_date'])); ?>
                                        <?php else: ?>
                                            Due: <?php echo date('M j, Y g:i A', strtotime($reminder['due_date'])); ?>
                                        <?php endif; ?>
                                    </span>
                                    
                                    <span class="reminder-status status-<?php echo $reminder['status']; ?>">
                                        <?php echo ucfirst($reminder['status']); ?>
                                    </span>
                                </div>
                            </div>
                            
                            <?php if (!$isPast && $reminder['status'] === 'pending'): ?>
                                <div class="reminder-actions">
                                    <button class="btn btn-sm btn-mark-complete" 
                                            data-reminder-id="<?php echo $reminder['reminder_id']; ?>">
                                        Mark as Complete
                                    </button>
                                </div>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="no-reminders">
                        <p>No reminders found.</p>
                        <?php if ($filter === 'upcoming'): ?>
                            <p>You currently have no upcoming reminders.</p>
                        <?php elseif ($filter === 'past'): ?>
                            <p>You have no past reminders.</p>
                        <?php else: ?>
                            <p>You have no reminders at this time.</p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
    
    <script src="assets/js/portal.js"></script>
    <script>
        // Handle mark as complete button
        document.querySelectorAll('.btn-mark-complete').forEach(button => {
            button.addEventListener('click', function() {
                const reminderId = this.dataset.reminderId;
                const reminderCard = this.closest('.reminder-card');
                
                if (confirm('Mark this reminder as complete?')) {
                    fetch('api/mark-reminder-complete.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `reminder_id=${reminderId}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Update the UI
                            reminderCard.querySelector('.reminder-status').textContent = 'Completed';
                            reminderCard.querySelector('.reminder-status').className = 'reminder-status status-completed';
                            this.remove();
                            
                            // Show success message
                            alert('Reminder marked as complete!');
                        } else {
                            alert('Error: ' + (data.message || 'Failed to update reminder'));
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred. Please try again.');
                    });
                }
            });
        });
    </script>
</body>
</html>