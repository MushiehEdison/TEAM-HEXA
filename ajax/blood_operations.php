<?php
// ajax/blood_operations.php
session_start();
require_once '../classes/Database.php';
require_once '../classes/Auth.php';
require_once '../classes/BloodBank.php';

header('Content-Type: application/json');

$auth = new Auth();
if (!$auth->isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

$bloodBank = new BloodBank();
$action = $_POST['action'] ?? '';

switch ($action) {
    case 'add_donation':
        $result = $bloodBank->addDonation(
            $_POST['donor_id'] ?? null,
            $_POST['blood_type'],
            $_POST['quantity'],
            $_POST['donation_date'],
            $_POST['hemoglobin_level'] ?? null,
            $_POST['blood_pressure'] ?? null,
            $_POST['temperature'] ?? null,
            $_POST['notes'] ?? ''
        );
        echo json_encode($result);
        break;
        
    case 'add_request':
        $result = $bloodBank->addBloodRequest(
            $_POST['patient_name'],
            $_POST['blood_type'],
            $_POST['quantity_needed'],
            $_POST['urgency'],
            $_POST['hospital_name'],
            $_POST['contact_person'],
            $_POST['phone'],
            $_POST['request_date'],
            $_POST['needed_by']
        );
        echo json_encode($result);
        break;
        
    case 'fulfill_request':
        $result = $bloodBank->fulfillBloodRequest(
            $_POST['request_id'],
            $auth->getCurrentUser()['id']
        );
        echo json_encode($result);
        break;
        
    case 'update_request_status':
        $result = $bloodBank->updateRequestStatus(
            $_POST['request_id'],
            $_POST['status']
        );
        echo json_encode($result);
        break;
        
    case 'add_donor':
        $result = $bloodBank->addDonor(
            $_POST['full_name'],
            $_POST['phone'],
            $_POST['email'],
            $_POST['blood_type'],
            $_POST['address']
        );
        echo json_encode($result);
        break;
        
    case 'add_inventory':
        $result = $bloodBank->addBloodInventory(
            $_POST['blood_type'],
            $_POST['quantity'],
            $_POST['collection_date'],
            $_POST['expiry_date'],
            $_POST['donor_id'] ?? null
        );
        echo json_encode($result);
        break;
        
    case 'get_inventory':
        $inventory = $bloodBank->getBloodInventory($_POST['blood_type'] ?? null);
        echo json_encode(['success' => true, 'data' => $inventory]);
        break;
        
    case 'get_requests':
        $requests = $bloodBank->getBloodRequests($_POST['status'] ?? null);
        echo json_encode(['success' => true, 'data' => $requests]);
        break;
        
    case 'get_donors':
        $donors = $bloodBank->getDonors();
        echo json_encode(['success' => true, 'data' => $donors]);
        break;
        
    case 'get_donations':
        $donations = $bloodBank->getDonations();
        echo json_encode(['success' => true, 'data' => $donations]);
        break;
        
    case 'get_dashboard_stats':
        $stats = $bloodBank->getDashboardStats();
        echo json_encode(['success' => true, 'data' => $stats]);
        break;
        
    case 'mark_expired':
        $result = $bloodBank->markExpiredBlood();
        echo json_encode(['success' => $result, 'message' => $result ? 'Expired blood marked successfully' : 'Failed to mark expired blood']);
        break;
        
    case 'get_expiring':
        $expiring = $bloodBank->getExpiringBlood($_POST['days'] ?? 7);
        echo json_encode(['success' => true, 'data' => $expiring]);
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}
?>