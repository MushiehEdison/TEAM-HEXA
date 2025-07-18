<?php
require_once '../../config/database.php';
require_once '../../includes/portal-functions.php';

header('Content-Type: application/json');

if (!isset($_GET['provider_id']) || !isset($_GET['date'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required parameters']);
    exit();
}

$providerId = (int)$_GET['provider_id'];
$date = $_GET['date'];

if (!validateDate($date, 'Y-m-d')) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid date format']);
    exit();
}

$availableSlots = getAvailableAppointments($providerId, $date);
echo json_encode($availableSlots);
?>