<?php
require_once '../../config/database.php';
require_once '../../includes/staff-auth.php';

header('Content-Type: application/json');

if (!isStaffLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

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

// Get provider's working hours (in a real system, this would come from a schedule table)
$workingStart = '09:00:00';
$workingEnd = '17:00:00';

// Get booked appointments
$stmt = $conn->prepare("
    SELECT appointment_date, duration 
    FROM appointments 
    WHERE provider_id = ? 
    AND DATE(appointment_date) = ?
    AND status != 'canceled'
    ORDER BY appointment_date
");
$stmt->bind_param("is", $providerId, $date);
$stmt->execute();
$result = $stmt->get_result();

$bookedSlots = [];
while ($row = $result->fetch_assoc()) {
    $bookedSlots[] = [
        'start' => $row['appointment_date'],
        'end' => date('Y-m-d H:i:s', strtotime($row['appointment_date']) + $row['duration'] * 60)
    ];
}

// Generate all possible slots (every 30 minutes)
$dateTime = new DateTime($date . ' ' . $workingStart);
$endTime = new DateTime($date . ' ' . $workingEnd);
$interval = new DateInterval('PT30M');

$availableSlots = [];
$currentSlot = clone $dateTime;

while ($currentSlot < $endTime) {
    $slotStart = $currentSlot->format('Y-m-d H:i:s');
    $slotEnd = clone $currentSlot;
    $slotEnd->add($interval);
    $slotEnd = $slotEnd->format('Y-m-d H:i:s');
    
    $isAvailable = true;
    
    // Check if slot overlaps with any booked appointments
    foreach ($bookedSlots as $booked) {
        if (!($slotEnd <= $booked['start'] || $slotStart >= $booked['end'])) {
            $isAvailable = false;
            break;
        }
    }
    
    if ($isAvailable) {
        $availableSlots[] = $slotStart;
    }
    
    $currentSlot->add($interval);
}

echo json_encode($availableSlots);
?>