<?php
require_once '../../config/database.php';
require_once '../includes/staff-auth.php';
requireStaffAuth();

if (!$_SESSION['staff_permissions']['manage_appointments']) {
    header("Location: ../dashboard.php");
    exit();
}

// Filter parameters
$status = $_GET['status'] ?? 'upcoming';
$provider = $_GET['provider'] ?? '';
$date = $_GET['date'] ?? '';
$page = max(1, $_GET['page'] ?? 1);
$perPage = 15;
$offset = ($page - 1) * $perPage;

// Build query
$query = "SELECT a.*, p.first_name, p.last_name, p.phone, pr.first_name as provider_first, pr.last_name as provider_last 
          FROM appointments a
          JOIN patients p ON a.patient_id = p.patient_id
          JOIN providers pr ON a.provider_id = pr.provider_id";

$countQuery = "SELECT COUNT(*) as total 
               FROM appointments a
               JOIN patients p ON a.patient_id = p.patient_id
               JOIN providers pr ON a.provider_id = pr.provider_id";

$where = [];
$params = [];
$types = '';

// Status filter
if ($status === 'upcoming') {
    $where[] = "a.appointment_date >= CURDATE()";
} elseif ($status === 'past') {
    $where[] = "a.appointment_date < CURDATE()";
} elseif ($status === 'canceled') {
    $where[] = "a.status = 'canceled'";
}

// Provider filter
if ($provider) {
    $where[] = "a.provider_id = ?";
    $params[] = $provider;
    $types .= 'i';
}

// Date filter
if ($date) {
    $where[] = "DATE(a.appointment_date) = ?";
    $params[] = $date;
    $types .= 's';
}

// Combine where clauses
if (!empty($where)) {
    $query .= " WHERE " . implode(" AND ", $where);
    $countQuery .= " WHERE " . implode(" AND ", $where);
}

// Ordering and pagination
$query .= " ORDER BY a.appointment_date " . ($status === 'past' ? 'DESC' : 'ASC');
$query .= " LIMIT ? OFFSET ?";
$params[] = $perPage;
$params[] = $offset;
$types .= 'ii';

// Get appointments
$stmt = $conn->prepare($query);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$appointments = $result->fetch_all(MYSQLI_ASSOC);

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

// Get providers for filter dropdown
$providers = [];
$result = $conn->query("SELECT provider_id, first_name, last_name FROM providers ORDER BY last_name");
while ($row = $result->fetch_assoc()) {
    $providers[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Portal - Appointments</title>
    <link rel="stylesheet" href="../assets/css/staff.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Appointments</h2>
                <a href="schedule.php" class="btn btn-primary">Schedule New</a>
            </div>
            
            <div class="filters">
                <form method="GET" class="filter-form">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="status">Status</label>
                            <select id="status" name="status">
                                <option value="upcoming" <?php echo $status === 'upcoming' ? 'selected' : ''; ?>>Upcoming</option>
                                <option value="past" <?php echo $status === 'past' ? 'selected' : ''; ?>>Past</option>
                                <option value="canceled" <?php echo $status === 'canceled' ? 'selected' : ''; ?>>Canceled</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="provider">Provider</label>
                            <select id="provider" name="provider">
                                <option value="">All Providers</option>
                                <?php foreach ($providers as $provider): ?>
                                    <option value="<?php echo $provider['provider_id']; ?>" 
                                        <?php echo $_GET['provider'] == $provider['provider_id'] ? 'selected' : ''; ?>>
                                        Dr. <?php echo $provider['last_name']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="date">Date</label>
                            <input type="date" id="date" name="date" value="<?php echo htmlspecialchars($date); ?>">
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">Apply Filters</button>
                            <a href="index.php" class="btn btn-secondary">Reset</a>
                        </div>
                    </div>
                </form>
            </div>
            
            <?php if (count($appointments) > 0): ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Date & Time</th>
                            <th>Patient</th>
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
                            <td><?php echo htmlspecialchars($appt['first_name'] . ' ' . $appt['last_name']); ?></td>
                            <td>Dr. <?php echo htmlspecialchars($appt['provider_last']); ?></td>
                            <td><?php echo htmlspecialchars($appt['appointment_type']); ?></td>
                            <td>
                                <span class="status-badge status-<?php echo $appt['status']; ?>">
                                    <?php echo ucfirst($appt['status']); ?>
                                </span>
                            </td>
                            <td>
                                <a href="view.php?id=<?php echo $appt['appointment_id']; ?>" class="btn btn-sm">View</a>
                                <?php if ($appt['status'] === 'scheduled'): ?>
                                    <a href="edit.php?id=<?php echo $appt['appointment_id']; ?>" class="btn btn-sm btn-secondary">Edit</a>
                                    <a href="cancel.php?id=<?php echo $appt['appointment_id']; ?>" class="btn btn-sm btn-danger" data-confirm="Are you sure you want to cancel this appointment?">Cancel</a>
                                <?php endif; ?>
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
                <p>No appointments found matching your criteria.</p>
            <?php endif; ?>
        </div>
    </div>
    
    <script src="../assets/js/staff.js"></script>
</body>
</html>