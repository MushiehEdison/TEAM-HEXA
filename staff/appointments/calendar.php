<?php
require_once '../../config/database.php';
require_once '../../includes/staff-auth.php';
requireStaffAuth();

if (!$_SESSION['staff_permissions']['manage_appointments']) {
    header("Location: ../../dashboard.php");
    exit();
}

// Get providers for filter
$providers = [];
$result = $conn->query("SELECT provider_id, first_name, last_name FROM providers WHERE is_active = TRUE ORDER BY last_name");
while ($row = $result->fetch_assoc()) {
    $providers[] = $row;
}

// Get appointments for calendar
$appointments = [];
$query = "SELECT a.appointment_id, a.appointment_date, a.duration, a.appointment_type, a.status, 
                 p.first_name as patient_first, p.last_name as patient_last,
                 pr.first_name as provider_first, pr.last_name as provider_last
          FROM appointments a
          JOIN patients p ON a.patient_id = p.patient_id
          JOIN providers pr ON a.provider_id = pr.provider_id
          WHERE a.appointment_date >= CURDATE() - INTERVAL 30 DAY
          ORDER BY a.appointment_date";

$result = $conn->query($query);
while ($row = $result->fetch_assoc()) {
    $appointments[] = [
        'id' => $row['appointment_id'],
        'title' => $row['patient_last'] . ', ' . $row['patient_first'] . ' - ' . $row['appointment_type'],
        'start' => $row['appointment_date'],
        'end' => date('Y-m-d H:i:s', strtotime($row['appointment_date']) + $row['duration'] * 60),
        'type' => $row['appointment_type'],
        'status' => $row['status'],
        'provider' => $row['provider_last'],
        'className' => 'status-' . $row['status'] . ' type-' . strtolower(str_replace(' ', '-', $row['appointment_type']))
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Portal - Appointment Calendar</title>
    <link rel="stylesheet" href="../../assets/css/staff.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css">
</head>
<body>
    <?php include '../../includes/header.php'; ?>
    
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Appointment Calendar</h2>
                <div class="calendar-actions">
                    <a href="schedule.php" class="btn btn-primary">Schedule New</a>
                    <a href="index.php" class="btn btn-secondary">List View</a>
                </div>
            </div>
            
            <div class="calendar-filters">
                <form id="calendar-filter-form">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="calendar-provider">Provider</label>
                            <select id="calendar-provider">
                                <option value="">All Providers</option>
                                <?php foreach ($providers as $provider): ?>
                                    <option value="<?php echo $provider['provider_id']; ?>">
                                        Dr. <?php echo $provider['last_name']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="calendar-type">Appointment Type</label>
                            <select id="calendar-type">
                                <option value="">All Types</option>
                                <option value="General Consultation">General Consultation</option>
                                <option value="Follow-up Visit">Follow-up Visit</option>
                                <option value="Vaccination">Vaccination</option>
                                <option value="Procedure">Procedure</option>
                                <option value="Lab Test">Lab Test</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="calendar-status">Status</label>
                            <select id="calendar-status">
                                <option value="">All Statuses</option>
                                <option value="scheduled">Scheduled</option>
                                <option value="completed">Completed</option>
                                <option value="canceled">Canceled</option>
                            </select>
                        </div>
                        
                        <button type="button" id="reset-filters" class="btn btn-secondary">Reset Filters</button>
                    </div>
                </form>
            </div>
            
            <div id="calendar"></div>
        </div>
    </div>
    
    <!-- Appointment Details Modal -->
    <div id="appointment-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modal-title">Appointment Details</h3>
                <span class="close">&times;</span>
            </div>
            <div class="modal-body">
                <div id="modal-details"></div>
            </div>
            <div class="modal-footer">
                <a href="#" id="view-appointment" class="btn btn-primary">View Details</a>
                <a href="#" id="edit-appointment" class="btn btn-secondary">Edit</a>
                <a href="#" id="cancel-appointment" class="btn btn-danger">Cancel</a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
    <script src="../../assets/js/staff.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const calendarEl = document.getElementById('calendar');
            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                events: <?php echo json_encode($appointments); ?>,
                eventClick: function(info) {
                    const event = info.event;
                    document.getElementById('modal-title').textContent = event.title;
                    
                    const details = `
                        <div class="detail-row">
                            <span class="detail-label">Date:</span>
                            <span class="detail-value">${event.start.toLocaleString()}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Duration:</span>
                            <span class="detail-value">${(event.end - event.start) / (1000 * 60)} minutes</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Type:</span>
                            <span class="detail-value">${event.extendedProps.type}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Provider:</span>
                            <span class="detail-value">Dr. ${event.extendedProps.provider}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Status:</span>
                            <span class="detail-value status-badge status-${event.extendedProps.status}">
                                ${event.extendedProps.status.charAt(0).toUpperCase() + event.extendedProps.status.slice(1)}
                            </span>
                        </div>
                    `;
                    
                    document.getElementById('modal-details').innerHTML = details;
                    document.getElementById('view-appointment').href = `view.php?id=${event.id}`;
                    document.getElementById('edit-appointment').href = `edit.php?id=${event.id}`;
                    document.getElementById('cancel-appointment').href = `cancel.php?id=${event.id}`;
                    
                    const modal = document.getElementById('appointment-modal');
                    modal.style.display = 'block';
                    
                    // Hide cancel button if already canceled
                    if (event.extendedProps.status === 'canceled') {
                        document.getElementById('cancel-appointment').style.display = 'none';
                    }
                },
                eventDidMount: function(info) {
                    // Add tooltip
                    const tooltip = new bootstrap.Tooltip(info.el, {
                        title: `${info.event.title}\n${info.event.start.toLocaleString()}\nStatus: ${info.event.extendedProps.status}`,
                        placement: 'top',
                        trigger: 'hover',
                        container: 'body'
                    });
                }
            });
            
            calendar.render();
            
            // Filter functionality
            document.getElementById('calendar-provider').addEventListener('change', filterCalendar);
            document.getElementById('calendar-type').addEventListener('change', filterCalendar);
            document.getElementById('calendar-status').addEventListener('change', filterCalendar);
            document.getElementById('reset-filters').addEventListener('click', resetFilters);
            
            function filterCalendar() {
                const providerFilter = document.getElementById('calendar-provider').value;
                const typeFilter = document.getElementById('calendar-type').value;
                const statusFilter = document.getElementById('calendar-status').value;
                
                calendar.getEvents().forEach(event => {
                    let show = true;
                    
                    if (providerFilter && event.extendedProps.provider_id != providerFilter) {
                        show = false;
                    }
                    
                    if (typeFilter && event.extendedProps.type !== typeFilter) {
                        show = false;
                    }
                    
                    if (statusFilter && event.extendedProps.status !== statusFilter) {
                        show = false;
                    }
                    
                    event.setProp('display', show ? 'auto' : 'none');
                });
            }
            
            function resetFilters() {
                document.getElementById('calendar-provider').value = '';
                document.getElementById('calendar-type').value = '';
                document.getElementById('calendar-status').value = '';
                
                calendar.getEvents().forEach(event => {
                    event.setProp('display', 'auto');
                });
            }
            
            // Modal close functionality
            document.querySelector('.close').addEventListener('click', function() {
                document.getElementById('appointment-modal').style.display = 'none';
            });
            
            window.addEventListener('click', function(event) {
                const modal = document.getElementById('appointment-modal');
                if (event.target === modal) {
                    modal.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>