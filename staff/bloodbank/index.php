<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../includes/staff-auth.php';
requireStaffAuth();

// Check permissions
if (!$_SESSION['staff_permissions']['view_bloodstock']) {
    header("Location: ../dashboard.php");
    exit();
}

// Get current blood stock
$bloodStock = [];
$result = $conn->query("
    SELECT bg.group_name, bi.units_available 
    FROM blood_inventory bi
    JOIN blood_groups bg ON bi.blood_group_id = bg.id
    ORDER BY bg.group_name
");
while ($row = $result->fetch_assoc()) {
    $bloodStock[] = $row;
}

// Get pending requests
$pendingRequests = [];
if ($_SESSION['staff_permissions']['process_bloodrequests']) {
    $result = $conn->query("
        SELECT br.id, p.first_name, p.last_name, bg.group_name, br.units_requested, br.request_date
        FROM blood_requests br
        JOIN blood_groups bg ON br.blood_group_id = bg.id
        LEFT JOIN patients p ON br.patient_id = p.patient_id
        WHERE br.status = 'pending'
        ORDER BY br.request_date DESC
        LIMIT 5
    ");
    while ($row = $result->fetch_assoc()) {
        $pendingRequests[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blood Bank Management</title>
    <link rel="stylesheet" href="../assets/css/staff.css">
    <style>
        .blood-group-card {
            border-left: 5px solid #e74c3c;
        }
        .critical-stock {
            background-color: #ffdddd;
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Blood Bank Stock</h2>
                <?php if ($_SESSION['staff_permissions']['manage_bloodbank']): ?>
                    <a href="add-donation.php" class="btn btn-primary">Record New Donation</a>
                <?php endif; ?>
            </div>
            
            <div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));">
                <?php foreach ($bloodStock as $group): ?>
                <div class="stat-card <?php echo $group['units_available'] < 5 ? 'critical-stock' : ''; ?>">
                    <div class="stat-icon" style="color: #e74c3c;">
                        <i class="fas fa-tint"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-title">Group <?php echo $group['group_name']; ?></div>
                        <div class="stat-value"><?php echo $group['units_available']; ?> units</div>
                    </div>
                    <a href="group.php?group=<?php echo $group['group_name']; ?>" class="stat-link">Details</a>
                </div>
                <?php endforeach; ?>
            </div>
            
            <?php if (!empty($pendingRequests) && $_SESSION['staff_permissions']['process_bloodrequests']): ?>
            <div class="section-card" style="margin-top: 30px;">
                <h3 class="section-title">Pending Blood Requests</h3>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Request ID</th>
                            <th>Patient</th>
                            <th>Blood Group</th>
                            <th>Units</th>
                            <th>Request Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pendingRequests as $request): ?>
                        <tr>
                            <td><?php echo $request['id']; ?></td>
                            <td><?php echo $request['first_name'] . ' ' . $request['last_name']; ?></td>
                            <td><?php echo $request['group_name']; ?></td>
                            <td><?php echo $request['units_requested']; ?></td>
                            <td><?php echo date('M j, Y H:i', strtotime($request['request_date'])); ?></td>
                            <td>
                                <a href="process-request.php?id=<?php echo $request['id']; ?>&action=approve" class="btn btn-sm btn-primary">Approve</a>
                                <a href="process-request.php?id=<?php echo $request['id']; ?>&action=reject" class="btn btn-sm btn-secondary">Reject</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>