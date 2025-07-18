<?php
require_once 'includes/portal-auth.php';
requirePatientAuth();
require_once 'includes/portal-functions.php';

$patientId = $_SESSION['patient_id'];
$patient = getPatientDetails($patientId);
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = $_POST['first_name'];
    $lastName = $_POST['last_name'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    
    if (updatePatientProfile($patientId, $firstName, $lastName, $phone, $email)) {
        $message = "Profile updated successfully!";
        $_SESSION['patient_name'] = $firstName . ' ' . $lastName;
        $_SESSION['patient_email'] = $email;
        $patient = getPatientDetails($patientId); // Refresh patient data
    } else {
        $message = "Failed to update profile. Please try again.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Portal - Profile</title>
    <link rel="stylesheet" href="assets/css/portal.css">
</head>
<body>
    <div class="portal-container">
        <?php include 'portal-header.php'; ?>
        
        <main class="portal-main">
            <div class="portal-content">
                <h1>Your Profile</h1>
                
                <?php if ($message): ?>
                    <div class="alert <?php echo strpos($message, 'success') !== false ? 'alert-success' : 'alert-danger'; ?>">
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>
                
                <section class="profile-section">
                    <form method="POST">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="first_name">First Name</label>
                                <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($patient['first_name']); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="last_name">Last Name</label>
                                <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($patient['last_name']); ?>" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($patient['email']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="phone">Phone Number</label>
                            <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($patient['phone']); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label>Date of Birth</label>
                            <p class="static-value"><?php echo date('F j, Y', strtotime($patient['dob'])); ?></p>
                            <small>Contact our office to update your date of birth.</small>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Update Profile</button>
                    </form>
                </section>
                
                <section class="security-section">
                    <h2>Security</h2>
                    <div class="security-actions">
                        <a href="change-password.php" class="btn btn-secondary">Change Password</a>
                        <a href="logout.php" class="btn btn-logout">Logout All Devices</a>
                    </div>
                </section>
            </div>
        </main>
    </div>
</body>
</html>