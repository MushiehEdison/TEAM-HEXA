<!-- <?php
session_start();



function isStaffLoggedIn() {
    return isset($_SESSION['staff_id']);
}

function requireStaffAuth() {
    if (!isStaffLoggedIn()) {
        header("Location: ../staff/login.php");
        exit();
    }
}

function sanitizeInput($data) {
    global $conn;
    return htmlspecialchars(strip_tags(trim($conn->real_escape_string($data))));
}

function createReminder($conn, $patientId, $reminderType, $messageText, $dueDateTime, $createdBy) {
    $stmt = $conn->prepare("INSERT INTO reminders (patient_id, reminder_type, reminder_text, reminder_date, created_by)
                            VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("isssi", $patientId, $reminderType, $messageText, $dueDateTime, $createdBy);
    return $stmt->execute();
}




function staffLogout() {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit();
}

function verifyStaffLogin($username, $password) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT user_id, username, password, role, first_name, last_name 
                           FROM users 
                           WHERE username = ? AND role IN ('admin', 'staff')");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $staff = $result->fetch_assoc();
        if (password_verify($password, $staff['password'])) {
            // Set session variables
            $_SESSION['staff_id'] = $staff['user_id'];
            $_SESSION['staff_username'] = $staff['username'];
            $_SESSION['staff_name'] = $staff['first_name'] . ' ' . $staff['last_name'];
            $_SESSION['staff_role'] = $staff['role'];
            $_SESSION['staff_permissions'] = getStaffPermissions($staff['role']);
            
            // Update last login
            $update = $conn->prepare("UPDATE users SET last_login = NOW() WHERE user_id = ?");
            $update->bind_param("i", $staff['user_id']);
            $update->execute();
            
            return $staff; // Return staff data on success
        }
    }
    return false; // Return false on failure
}

function getStaffPermissions($role) {
    // Define permissions based on role (no separate permissions table needed)
    $basePermissions = [
        'manage_patients' => true,
        'manage_appointments' => true,
        'manage_reminders' => true,
        'view_feedback' => true,
        'generate_reports' => true
    ];
    
    // Admin gets additional permissions
    if ($role === 'admin') {
        $basePermissions['manage_staff'] = true;
        $basePermissions['manage_settings'] = true;
    } else {
        $basePermissions['manage_staff'] = false;
        $basePermissions['manage_settings'] = false;
    }
    
    return $basePermissions;
}

// Example usage in other files:
// requireStaffAuth(); // At top of staff pages
// if ($_SESSION['staff_permissions']['manage_patients']) { /* do something */ }
?> -->