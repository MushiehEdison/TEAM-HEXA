<?php
require_once '../config/database.php';
require_once 'portal-auth.php';

// Get patient details
function getPatientDetails($patientId) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT * FROM patients WHERE patient_id = ?");
    $stmt->bind_param("i", $patientId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_assoc();
}

// Get upcoming appointments
function getPatientAppointments($patientId, $limit = null) {
    global $conn;
    
    $query = "SELECT a.*, p.first_name, p.last_name, p.specialty 
              FROM appointments a
              JOIN providers p ON a.provider_id = p.provider_id
              WHERE a.patient_id = ? AND a.appointment_date >= NOW()
              ORDER BY a.appointment_date ASC";
              
    if ($limit) {
        $query .= " LIMIT ?";
    }
    
    $stmt = $conn->prepare($query);
    
    if ($limit) {
        $stmt->bind_param("ii", $patientId, $limit);
    } else {
        $stmt->bind_param("i", $patientId);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $appointments = [];
    while ($row = $result->fetch_assoc()) {
        $appointments[] = $row;
    }
    
    return $appointments;
}

// Get medical records
function getPatientMedicalRecords($patientId, $type = null) {
    global $conn;
    
    $query = "SELECT m.*, u.username as recorded_by_name 
              FROM medical_records m
              JOIN users u ON m.recorded_by = u.user_id
              WHERE m.patient_id = ?";
              
    if ($type) {
        $query .= " AND m.record_type = ?";
    }
    
    $query .= " ORDER BY m.date_recorded DESC";
    
    $stmt = $conn->prepare($query);
    
    if ($type) {
        $stmt->bind_param("is", $patientId, $type);
    } else {
        $stmt->bind_param("i", $patientId);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $records = [];
    while ($row = $result->fetch_assoc()) {
        $records[] = $row;
    }
    
    return $records;
}

// Submit patient feedback
function submitPatientFeedback($patientId, $feedbackText, $rating) {
    global $conn;
    
    $stmt = $conn->prepare("INSERT INTO feedback (patient_id, feedback_text, rating) VALUES (?, ?, ?)");
    $stmt->bind_param("isi", $patientId, $feedbackText, $rating);
    
    return $stmt->execute();
}

// Get patient reminders
function getPatientReminders($patientId, $status = null) {
    global $conn;
    
    $query = "SELECT * FROM reminders WHERE patient_id = ?";
    
    if ($status) {
        $query .= " AND status = ?";
    }
    
    $query .= " ORDER BY due_date ASC";
    
    $stmt = $conn->prepare($query);
    
    if ($status) {
        $stmt->bind_param("is", $patientId, $status);
    } else {
        $stmt->bind_param("i", $patientId);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $reminders = [];
    while ($row = $result->fetch_assoc()) {
        $reminders[] = $row;
    }
    
    return $reminders;
}

// Update patient profile
function updatePatientProfile($patientId, $firstName, $lastName, $phone, $email) {
    global $conn;
    
    $stmt = $conn->prepare("UPDATE patients SET first_name = ?, last_name = ?, phone = ?, email = ? WHERE patient_id = ?");
    $stmt->bind_param("ssssi", $firstName, $lastName, $phone, $email, $patientId);
    
    return $stmt->execute();
}

// Get available appointment slots
function getAvailableAppointments($providerId, $date) {
    global $conn;
    
    $startDate = $date . ' 00:00:00';
    $endDate = $date . ' 23:59:59';
    
    // Get provider's working hours (in a real system, this would come from a schedule table)
    $workingStart = '09:00:00';
    $workingEnd = '17:00:00';
    
    // Get booked appointments
    $stmt = $conn->prepare("
        SELECT appointment_date, duration 
        FROM appointments 
        WHERE provider_id = ? 
        AND appointment_date BETWEEN ? AND ?
        AND status != 'canceled'
        ORDER BY appointment_date
    ");
    $stmt->bind_param("iss", $providerId, $startDate, $endDate);
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
    
    return $availableSlots;
}

// Book an appointment
function bookAppointment($patientId, $providerId, $appointmentType, $appointmentDate, $duration = 30) {
    global $conn;
    
    $stmt = $conn->prepare("
        INSERT INTO appointments 
        (patient_id, provider_id, appointment_type, appointment_date, duration) 
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("iissi", $patientId, $providerId, $appointmentType, $appointmentDate, $duration);
    
    return $stmt->execute();
}

// Cancel an appointment
function cancelAppointment($appointmentId, $patientId) {
    global $conn;
    
    $stmt = $conn->prepare("
        UPDATE appointments 
        SET status = 'canceled' 
        WHERE appointment_id = ? AND patient_id = ?
    ");
    $stmt->bind_param("ii", $appointmentId, $patientId);
    
    return $stmt->execute();
}
?>