<?php
require_once '../../config/database.php';
require_once '../includes/staff-auth.php';
requireStaffAuth();

if (!$_SESSION['staff_permissions']['manage_reminders']) {
    header("Location: ../dashboard.php");
    exit();
}

// Filter parameters
$status = $_GET['status'] ?? 'pending';
$type = $_GET['type'] ?? '';
$page = max(1, $_GET['page'] ?? 1);
$perPage = 20;
$offset = ($page - 1) * $perPage;

// Build query
$query = "SELECT r.*, p.first_name, p.last_name 
          FROM reminders r
          JOIN patients p ON r.patient_id = p.patient_id";

$countQuery = "SELECT COUNT(*) as total 
               FROM reminders r
               JOIN patients p ON r.patient_id = p.patient_id";

$where = [];
$params = [];
$types = '';

// Status filter
if ($status !== 'all') {
    $where[] = "r.status = ?";
    $params[] = $status;
    $types .= 's';
}

// Type filter
if ($type) {
    $where[] = "r.reminder_type = ?";
    $params[] = $type;
    $types .= 's';
}

// Combine where clauses
if (!empty($where)) {
    $query .= " WHERE " . implode(" AND ", $where);
    $countQuery .= " WHERE " . implode(" AND ", $where);
}

// Ordering and pagination
$query .= " ORDER BY r.due_date ASC";
$query .= " LIMIT ? OFFSET ?";
$params[] = $perPage;
$params[] = $offset;
$types .= 'ii';

// Get reminders
$stmt = $conn->prepare($query);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$reminders = $result->fetch_all(MYSQLI_ASSOC);

// Get total count
$stmt = $conn->prepare($countQuery);
if ($params) {
    // Remove limit/offset params for count query
    $countParams = array_slice($params, 0, count($params) - 2);
    $countTypes = substr($types, 0, -2);
    if ($countParams) {
        $stmt->bind_param($countTypes, ...$countParams);
    }
}
$stmt->execute();
$total = $stmt->get_result()->fetch_assoc()['total'];
$totalPages = ceil($total / $perPage);

// Get patients for quick filter
$patients = [];
$result = $conn->query("SELECT patient_id, first_name, last_name FROM patients ORDER BY last_name LIMIT 50");
while ($row = $result->fetch_assoc()) {
    $patients[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Portal - Reminders</title>
    <link rel="stylesheet" href="../assets/css/staff.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Reminders</h2>
                <a href="create.php" class="btn btn-primary">Create New</a>
            </div>
            
            <div class="filters">
                <form method="GET" class="filter-form">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="status">Status</label>
                            <select id="status" name="status">
                                <option value="all">All Statuses</option>
                                <option value="pending" <?php echo $status === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="sent" <?php echo $status === 'sent' ? 'selected' : ''; ?>>Sent</option>
                                <option value="completed" <?php echo $status === 'completed' ? 'selected' : ''; ?>>Completed</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="type">Type</label>
                            <select id="type" name="type">
                                <option value="">All Types</option>
                                <option value="appointment" <?php echo $type === 'appointment' ? 'selected' : ''; ?>>Appointment</option>
                                <option value="medication" <?php echo $type === 'medication' ? 'selected' : ''; ?>>Medication</option>
                                <option value="followup" <?php echo $type === 'followup' ? 'selected' : ''; ?>>Follow-up</option>
                                <option value="payment" <?php echo $type === 'payment' ? 'selected' : ''; ?>>Payment</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">Apply Filters</button>
                            <a href="index.php" class="btn btn-secondary">Reset</a>
                        </div>
                    </div>
                </form>
            </div>
            
            <?php if (count($reminders) > 0): ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Patient</th>
                            <th>Message</th>
                            <th>Type</th>
                            <th>Due Date</th>
                            <th>Status</th>
                         
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reminders as $reminder): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($reminder['last_name'] . ', ' . $reminder['first_name']); ?></td>
                            <td><?php echo htmlspecialchars($reminder['reminder_message']); ?></td>
                            <td><?php echo ucfirst($reminder['reminder_type']); ?></td>
                            <td><?php echo date('M j, Y g:i A', strtotime($reminder['due_date'])); ?></td>
                            <td>
                                <span class="status-badge status-<?php echo $reminder['status']; ?>">
                                    <?php echo ucfirst($reminder['status']); ?>
                                </span>
                            </td>
                            
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>" class="btn">Previous</a>
                    <?php endif; ?>
                    
                    <span>Page <?php echo $page; ?> of <?php echo $totalPages; ?></span>
                    
                    <?php if ($page < $totalPages): ?>
                        <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>" class="btn">Next</a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <p>No reminders found matching your criteria.</p>
            <?php endif; ?>
        </div>
    </div>
    
    <script src="../assets/js/staff.js"></script>
</body>
</html>