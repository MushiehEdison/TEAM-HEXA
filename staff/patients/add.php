<?php
require_once '../../config/database.php';
require_once '../includes/staff-auth.php';
requireStaffAuth();
require_once '../includes/staff-functions.php';

if (!$_SESSION['staff_permissions']['manage_patients']) {
    header("Location: ../dashboard.php");
    exit();
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = sanitizeInput($_POST['first_name']);
    $lastName = sanitizeInput($_POST['last_name']);
    $email = sanitizeInput($_POST['email']);
    $phone = sanitizeInput($_POST['phone']);
    $dob = sanitizeInput($_POST['dob']);
    
    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Invalid email format";
    } 
    // Validate date of birth
    elseif (!validateDate($dob)) {
        $message = "Invalid date of birth format (YYYY-MM-DD)";
    } else {
        // Check if email already exists
        $stmt = $conn->prepare("SELECT patient_id FROM patients WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $message = "A patient with this email already exists";
        } else {
            if (addPatient($firstName, $lastName, $email, $phone, $dob)) {
                $message = "Patient added successfully!";
                // Clear form
                $_POST = [];
            } else {
                $message = "Failed to add patient. Please try again.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Portal - Add Patient</title>
    <link rel="stylesheet" href="../assets/css/staff.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Add New Patient</h2>
                <a href="index.php" class="btn btn-secondary">Back to Patients</a>
            </div>
            
            <?php if ($message): ?>
                <div class="alert <?php echo strpos($message, 'success') !== false ? 'alert-success' : 'alert-danger'; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" class="patient-form">
                <div class="form-row">
                    <div class="form-group">
                        <label for="first_name">First Name</label>
                        <input type="text" id="first_name" name="first_name" 
                               value="<?php echo $_POST['first_name'] ?? ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="last_name">Last Name</label>
                        <input type="text" id="last_name" name="last_name" 
                               value="<?php echo $_POST['last_name'] ?? ''; ?>" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" 
                               value="<?php echo $_POST['email'] ?? ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="phone">Phone</label>
                        <input type="tel" id="phone" name="phone" 
                               value="<?php echo $_POST['phone'] ?? ''; ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="dob">Date of Birth</label>
                    <input type="date" id="dob" name="dob" 
                           value="<?php echo $_POST['dob'] ?? ''; ?>" required>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Add Patient</button>
                    <button type="reset" class="btn btn-secondary">Reset</button>
                </div>
            </form>
        </div>
    </div>
    
    <script src="../assets/js/staff.js"></script>
    <script>
        // Set max date for date of birth (today)
        document.getElementById('dob').max = new Date().toISOString().split('T')[0];
    </script>
</body>
</html>