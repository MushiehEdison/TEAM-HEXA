<?php
// ajax/staff_operations.php
session_start();
require_once '../classes/Database.php';
require_once '../classes/Auth.php';
require_once '../classes/Staff.php';

header('Content-Type: application/json');

$auth = new Auth();
if (!$auth->isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

// Check if user is admin for most operations
if (!$auth->isAdmin() && !in_array($_POST['action'] ?? '', ['get_profile', 'update_profile', 'change_password'])) {
    echo json_encode(['success' => false, 'message' => 'Admin access required']);
    exit();
}

$staff = new Staff();
$action = $_POST['action'] ?? '';

switch ($action) {
    case 'create_staff':
        $result = $staff->createStaff(
            $_POST['username'],
            $_POST['email'],
            $_POST['password'],
            $_POST['full_name'],
            $_POST['role'] ?? 'staff'
        );
        echo json_encode($result);
        break;
        
    case 'update_staff':
        $result = $staff->updateStaff(
            $_POST['staff_id'],
            $_POST['username'],
            $_POST['email'],
            $_POST['full_name'],
            $_POST['role'],
            $_POST['status']
        );
        echo json_encode($result);
        break;
        
    case 'delete_staff':
        $result = $staff->deleteStaff($_POST['staff_id']);
        echo json_encode($result);
        break;
        
    case 'toggle_status':
        $result = $staff->toggleStatus($_POST['staff_id']);
        echo json_encode($result);
        break;
        
    case 'reset_password':
        $result = $staff->resetPassword(
            $_POST['staff_id'],
            $_POST['new_password']
        );
        echo json_encode($result);
        break;
        
    case 'get_all_staff':
        $allStaff = $staff->getAllStaff();
        echo json_encode(['success' => true, 'data' => $allStaff]);
        break;
        
    case 'get_staff':
        $staffData = $staff->getStaffById($_POST['staff_id']);
        echo json_encode(['success' => true, 'data' => $staffData]);
        break;
        
    case 'get_staff_stats':
        $stats = $staff->getStaffStats();
        echo json_encode(['success' => true, 'data' => $stats]);
        break;
        
    case 'get_activity_log':
        $log = $staff->getActivityLog($_POST['limit'] ?? 50);
        echo json_encode(['success' => true, 'data' => $log]);
        break;
        
    case 'change_password':
        // Allow users to change their own password
        $current_user = $auth->getCurrentUser();
        if ($_POST['staff_id'] != $current_user['id'] && !$auth->isAdmin()) {
            echo json_encode(['success' => false, 'message' => 'You can only change your own password']);
            break;
        }
        
        $result = $auth->updatePassword(
            $_POST['staff_id'],
            $_POST['current_password'],
            $_POST['new_password']
        );
        echo json_encode($result);
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}
?>