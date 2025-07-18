<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../includes/staff-auth.php';
requireStaffAuth();

if (!$_SESSION['staff_permissions']['process_bloodrequests']) {
    header("Location: ../dashboard.php");
    exit();
}

$requestId = $_GET['id'] ?? null;
$action = $_GET['action'] ?? null;

if (!$requestId || !in_array($action, ['approve', 'reject'])) {
    header("Location: index.php");
    exit();
}

// Get request details
$stmt = $conn->prepare("
    SELECT br.*, bg.group_name, bi.units_available
    FROM blood_requests br
    JOIN blood_groups bg ON br.blood_group_id = bg.id
    JOIN blood_inventory bi ON br.blood_group_id = bi.blood_group_id
    WHERE br.id = ? AND br.status = 'pending'
");
$stmt->bind_param("i", $requestId);
$stmt->execute();
$request = $stmt->get_result()->fetch_assoc();

if (!$request) {
    $_SESSION['bloodbank_message'] = "Request not found or already processed";
    header("Location: index.php");
    exit();
}

if ($action === 'approve') {
    // Check if enough units available
    if ($request['units_available'] < $request['units_requested']) {
        $_SESSION['bloodbank_message'] = "Not enough units available for this request";
        header("Location: index.php");
        exit();
    }
    
    // Update inventory
    $updateStmt = $conn->prepare("
        UPDATE blood_inventory 
        SET units_available = units_available - ? 
        WHERE blood_group_id = ?
    ");
    $updateStmt->bind_param("di", $request['units_requested'], $request['blood_group_id']);
    $updateStmt->execute();
    
    // Update request status
    $statusStmt = $conn->prepare("
        UPDATE blood_requests 
        SET status = 'fulfilled', approved_by = ? 
        WHERE id = ?
    ");
    $statusStmt->bind_param("ii", $_SESSION['staff_id'], $requestId);
    $statusStmt->execute();
    
    $_SESSION['bloodbank_message'] = "Request approved and inventory updated";
} else {
    // Reject request
    $stmt = $conn->prepare("
        UPDATE blood_requests 
        SET status = 'rejected', approved_by = ? 
        WHERE id = ?
    ");
    $stmt->bind_param("ii", $_SESSION['staff_id'], $requestId);
    $stmt->execute();
    
    $_SESSION['bloodbank_message'] = "Request has been rejected";
}

header("Location: index.php");
exit();