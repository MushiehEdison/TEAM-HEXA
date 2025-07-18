<?php
require_once '../../config/database.php';
require_once '../includes/staff-auth.php';
requireStaffAuth();

if (!$_SESSION['staff_permissions']['manage_patients']) {
    header("Location: ../dashboard.php");
    exit();
}

// Search and filter
$search = $_GET['search'] ?? '';
$page = max(1, $_GET['page'] ?? 1);
$perPage = 20;
$offset = ($page - 1) * $perPage;

// Build query
$query = "SELECT * FROM patients";
$countQuery = "SELECT COUNT(*) as total FROM patients";
$params = [];
$types = '';

if ($search) {
    $query .= " WHERE first_name LIKE ? OR last_name LIKE ? OR email LIKE ? OR phone LIKE ?";
    $countQuery .= " WHERE first_name LIKE ? OR last_name LIKE ? OR email LIKE ? OR phone LIKE ?";
    $searchTerm = "%$search%";
    $params = array_fill(0, 4, $searchTerm);
    $types = str_repeat('s', 4);
}

$query .= " ORDER BY last_name, first_name LIMIT ? OFFSET ?";
$params[] = $perPage;
$params[] = $offset;
$types .= 'ii';

// Get patients
$stmt = $conn->prepare($query);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$patients = $result->fetch_all(MYSQLI_ASSOC);

// Get total count
$stmt = $conn->prepare($countQuery);
if ($search) {
    $stmt->bind_param(str_repeat('s', 4), ...array_fill(0, 4, $searchTerm));
}
$stmt->execute();
$total = $stmt->get_result()->fetch_assoc()['total'];
$totalPages = ceil($total / $perPage);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Portal - Patients</title>
    <link rel="stylesheet" href="../assets/css/staff.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Patients</h2>
                <a href="add.php" class="btn btn-primary">Add New Patient</a>
            </div>
            
            <div class="search-bar">
                <form method="GET" class="search-form">
                    <div class="form-group">
                        <input type="text" name="search" placeholder="Search patients..." value="<?php echo htmlspecialchars($search); ?>">
                        <button type="submit" class="btn btn-primary">Search</button>
                        <?php if ($search): ?>
                            <a href="index.php" class="btn btn-secondary">Clear</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
            
            <?php if (count($patients) > 0): ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Date of Birth</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($patients as $patient): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($patient['first_name'] . ' ' . htmlspecialchars($patient['last_name'])); ?></td>
                            <td><?php echo htmlspecialchars($patient['email']); ?></td>
                            <td><?php echo htmlspecialchars($patient['phone']); ?></td>
                            <td><?php echo date('M j, Y', strtotime($patient['dob'])); ?></td>
                            <td>
                                <a href="view.php?id=<?php echo $patient['patient_id']; ?>" class="btn btn-sm">View</a>
                                <a href="edit.php?id=<?php echo $patient['patient_id']; ?>" class="btn btn-sm btn-secondary">Edit</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?php echo $page - 1; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>" class="btn">Previous</a>
                    <?php endif; ?>
                    
                    <span>Page <?php echo $page; ?> of <?php echo $totalPages; ?></span>
                    
                    <?php if ($page < $totalPages): ?>
                        <a href="?page=<?php echo $page + 1; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>" class="btn">Next</a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <p>No patients found.</p>
            <?php endif; ?>
        </div>
    </div>
    
    <script src="../assets/js/staff.js"></script>
</body>
</html>