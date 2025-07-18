<?php
session_start(); // ✅ Make sure session is started
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/includes/staff-auth.php';
requireStaffAuth();

if (!isset($conn) || !$conn) {
    die("Database connection error. Please try again later.");
}

$userId = $_SESSION['staff_id'];
$message = '';

// Get staff details
try {
    $stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $staff = $result->fetch_assoc();

    if (!$staff) {
        throw new Exception("Staff member not found.");
    }
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ✅ Replaced deprecated FILTER_SANITIZE_STRING
    $firstName = htmlspecialchars(trim($_POST['first_name'] ?? ''));
    $lastName = htmlspecialchars(trim($_POST['last_name'] ?? ''));
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $phone = htmlspecialchars(trim($_POST['phone'] ?? ''));
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    try {
        $conn->begin_transaction();

        // Update basic info
        $stmt = $conn->prepare("UPDATE users SET first_name = ?, last_name = ?, email = ?, phone = ? WHERE user_id = ?");
        $stmt->bind_param("ssssi", $firstName, $lastName, $email, $phone, $userId);
        if (!$stmt->execute()) {
            throw new Exception("Failed to update profile.");
        }

        $_SESSION['staff_name'] = "$firstName $lastName";
        $message = "Profile updated successfully!";

        // Handle password change
        if (!empty($currentPassword) || !empty($newPassword) || !empty($confirmPassword)) {
            if (empty($currentPassword)) {
                throw new Exception("Current password is required to change password.");
            }

            if ($newPassword !== $confirmPassword) {
                throw new Exception("New passwords do not match.");
            }

            if (!password_verify($currentPassword, $staff['password'])) {
                throw new Exception("Current password is incorrect.");
            }

            $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");
            $stmt->bind_param("si", $hashedPassword, $userId);
            if (!$stmt->execute()) {
                throw new Exception("Failed to update password.");
            }

            $message = "Profile and password updated successfully!";
        }

        $conn->commit();

        // Refresh staff data
        $stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $staff = $result->fetch_assoc();

    } catch (Exception $e) {
        $conn->rollback();
        $message = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Portal - My Profile</title>
    <link rel="stylesheet" href="../assets/css/staff.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    <style>
        :root {
    --primary-blue: #3a7bd5;
    --primary-pink: #e84393;
    --light-blue: #e3f2fd;
    --light-pink: #fce4ec;
    --dark-blue: #1a4b8c;
    --dark-pink: #ad1457;
    --white: #ffffff;
    --light-gray: #f8f9fa;
    --medium-gray: #e0e0e0;
    --dark-gray: #333333;
    --text-gray: #555555;
    --border-radius: 10px;
    --box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    --transition: all 0.3s ease;
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
}

body {
    background-color: var(--light-gray);
    color: var(--dark-gray);
}

.container {
    max-width: 1200px;
    margin: 100px auto 50px;
    padding: 0 20px;
}

.card {
    background: var(--white);
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
    overflow: hidden;
    margin-bottom: 30px;
}

.card-header {
    background: linear-gradient(135deg, var(--primary-blue), var(--dark-blue));
    color: var(--white);
    padding: 20px 30px;
}

.card-title {
    font-size: 1.8rem;
    font-weight: 600;
}

.profile-form {
    padding: 30px;
}

.form-row {
    display: flex;
    flex-wrap: wrap;
    margin: 0 -15px 20px;
}

.form-group {
    flex: 1;
    min-width: 250px;
    padding: 0 15px;
    margin-bottom: 20px;
    position: relative;
}

label {
    display: block;
    margin-bottom: 8px;
    font-weight: 500;
    color: var(--dark-gray);
}

input[type="text"],
input[type="email"],
input[type="tel"],
input[type="password"] {
    width: 100%;
    padding: 12px 40px 12px 15px;
    border: 1px solid var(--medium-gray);
    border-radius: var(--border-radius);
    font-size: 1rem;
    transition: var(--transition);
    background-color: var(--light-gray);
}

input[type="text"]:focus,
input[type="email"]:focus,
input[type="tel"]:focus,
input[type="password"]:focus {
    outline: none;
    border-color: var(--primary-blue);
    background-color: var(--white);
    box-shadow: 0 0 0 3px rgba(58, 123, 213, 0.2);
}

input:disabled {
    background-color: #f0f0f0;
    color: var(--text-gray);
    cursor: not-allowed;
}

.password-toggle {
    position: absolute;
    right: 25px;
    top: 38px;
    cursor: pointer;
    color: var(--text-gray);
    z-index: 2;
}

.section-title {
    font-size: 1.4rem;
    color: var(--primary-pink);
    margin: 30px 0 10px;
    padding-bottom: 10px;
    border-bottom: 2px solid var(--light-pink);
}

.section-description {
    color: var(--text-gray);
    margin-bottom: 20px;
    font-size: 0.95rem;
}

.form-actions {
    display: flex;
    gap: 15px;
    margin-top: 30px;
}

.btn {
    padding: 12px 25px;
    border-radius: var(--border-radius);
    border: none;
    cursor: pointer;
    transition: var(--transition);
    font-size: 1rem;
    font-weight: 500;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.btn i {
    margin-right: 8px;
}

.btn-primary {
    background: linear-gradient(135deg, var(--primary-blue), var(--dark-blue));
    color: var(--white);
}

.btn-primary:hover {
    background: linear-gradient(135deg, var(--dark-blue), var(--primary-blue));
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
}

.btn-secondary {
    background: var(--white);
    color: var(--primary-blue);
    border: 1px solid var(--primary-blue);
}

.btn-secondary:hover {
    background: var(--light-blue);
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
}

.alert {
    padding: 15px 20px;
    margin: 0 30px 30px;
    border-radius: var(--border-radius);
    display: flex;
    align-items: center;
    animation: fadeIn 0.5s ease;
}

.alert i {
    margin-right: 10px;
    font-size: 1.2rem;
}

.alert-success {
    background-color: rgba(46, 204, 113, 0.15);
    color: #27ae60;
    border-left: 4px solid #2ecc71;
}

.alert-danger {
    background-color: rgba(231, 76, 60, 0.15);
    color: #c0392b;
    border-left: 4px solid #e74c3c;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-20px); }
    to { opacity: 1; transform: translateY(0); }
}

@media (max-width: 768px) {
    .container {
        margin-top: 80px;
        padding: 0 15px;
    }
    
    .card-header {
        padding: 15px 20px;
    }
    
    .profile-form {
        padding: 20px;
    }
    
    .form-group {
        flex: 100%;
    }
    
    .form-actions {
        flex-direction: column;
    }
    
    .btn {
        width: 100%;
    }
    
    .password-toggle {
        right: 20px;
    }
}
    </style>
    
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">My Profile</h2>
            </div>
            
            <?php if ($message): ?>
                <div class="alert <?php echo strpos($message, 'success') !== false ? 'alert-success' : 'alert-danger'; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" class="profile-form">
                <div class="form-row">
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" value="<?php echo htmlspecialchars($staff['username']); ?>" disabled>
                    </div>
                    
                    <div class="form-group">
                        <label for="role">Role</label>
                        <input type="text" id="role" value="<?php echo htmlspecialchars(ucfirst($staff['role'])); ?>" disabled>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="first_name">First Name</label>
                        <input type="text" id="first_name" name="first_name" 
                               value="<?php echo htmlspecialchars($staff['first_name']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="last_name">Last Name</label>
                        <input type="text" id="last_name" name="last_name" 
                               value="<?php echo htmlspecialchars($staff['last_name']); ?>" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" 
                               value="<?php echo htmlspecialchars($staff['email']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="phone">Phone</label>
                        <input type="tel" id="phone" name="phone" 
                               value="<?php echo htmlspecialchars($staff['phone']); ?>">
                    </div>
                </div>
                
                <h3 class="section-title">Change Password</h3>
                <p class="section-description">Leave blank to keep current password</p>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="current_password">Current Password</label>
                        <input type="password" id="current_password" name="current_password">
                    </div>
                    
                    <div class="form-group">
                        <label for="new_password">New Password</label>
                        <input type="password" id="new_password" name="new_password">
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password</label>
                        <input type="password" id="confirm_password" name="confirm_password">
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                    <button type="reset" class="btn btn-secondary">Reset</button>
                </div>
            </form>
        </div>
    </div>
    
    <script src="../assets/js/staff.js"></script>
</body>
</html>