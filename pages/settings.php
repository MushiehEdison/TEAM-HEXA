<?php
// pages/profile.php
session_start();
require_once '../classes/Database.php';
require_once '../classes/Auth.php';

$auth = new Auth();
$auth->requireLogin();

$db = new Database();
$current_user = $auth->getCurrentUser();

// Initialize optional fields with default values if not set
$current_user['donor_id'] = $current_user['donor_id'] ?? null;
$current_user['phone'] = $current_user['phone'] ?? '';
$current_user['created_at'] = $current_user['created_at'] ?? date('Y-m-d H:i:s');

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $response = handleProfileUpdate($db, $current_user['id'], $_POST);
        if ($response['success']) {
            $_SESSION['success_message'] = $response['message'];
            header("Location: profile.php");
            exit;
        } else {
            $error = $response['message'];
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Get user donations if they're a donor
$donations = [];
if (!empty($current_user['donor_id'])) {
    $query = "SELECT * FROM donations WHERE donor_id = :donor_id ORDER BY donation_date DESC LIMIT 5";
    $db->query($query);
    $db->bind(':donor_id', $current_user['donor_id']);
    $donations = $db->resultSet();
}

function handleProfileUpdate($db, $user_id, $data) {
    // Validate required fields
    $required = ['full_name', 'email'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            throw new Exception("$field is required");
        }
    }

    // Check if email already exists for another user
    $query = "SELECT id FROM users WHERE email = :email AND id != :id";
    $db->query($query);
    $db->bind(':email', $data['email']);
    $db->bind(':id', $user_id);
    $existing = $db->single();
    
    if ($existing) {
        throw new Exception("Email already exists for another user");
    }

    // Prepare base query
    $query = "UPDATE users SET 
              full_name = :full_name,
              email = :email,
              phone = :phone,
              updated_at = NOW()";
    
    // Add password update if provided
    if (!empty($data['password'])) {
        if ($data['password'] !== $data['confirm_password']) {
            throw new Exception("Passwords do not match");
        }
        $hashed_password = password_hash($data['password'], PASSWORD_DEFAULT);
        $query .= ", password = :password";
    }
    
    $query .= " WHERE id = :id";
    
    $db->query($query);
    $db->bind(':full_name', $data['full_name']);
    $db->bind(':email', $data['email']);
    $db->bind(':phone', $data['phone'] ?? '');
    $db->bind(':id', $user_id);
    
    if (!empty($data['password'])) {
        $db->bind(':password', $hashed_password);
    }
    
    if ($db->execute()) {
        return ['success' => true, 'message' => 'Profile updated successfully'];
    }
    
    throw new Exception("Failed to update profile");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Blood Bank Management System</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <div class="logo">
                    <i class="fas fa-tint"></i>
                    <span>Blood Bank</span>
                </div>
            </div>
            
            <nav class="sidebar-nav">
                <ul>
                    <li>
                        <a href="dashboard.php">
                            <i class="fas fa-chart-bar"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li>
                        <a href="blood-inventory.php">
                            <i class="fas fa-flask"></i>
                            <span>Blood Inventory</span>
                        </a>
                    </li>
                    <li>
                        <a href="donations.php">
                            <i class="fas fa-hand-holding-heart"></i>
                            <span>Donations</span>
                        </a>
                    </li>
                    <li>
                        <a href="requests.php">
                            <i class="fas fa-clipboard-list"></i>
                            <span>Blood Requests</span>
                        </a>
                    </li>
                    <li>
                        <a href="forecasting.php">
                            <i class="fas fa-chart-line"></i>
                            <span>Forecasting</span>
                        </a>
                    </li>
                    <?php if ($auth->isAdmin()): ?>
                    <li>
                        <a href="staff.php">
                            <i class="fas fa-users"></i>
                            <span>Staff Management</span>
                        </a>
                    </li>
                    <?php endif; ?>
                    <li>
                        <a href="reports.php">
                            <i class="fas fa-file-alt"></i>
                            <span>Reports</span>
                        </a>
                    </li>
                    <li class="active">
                        <a href="profile.php">
                            <i class="fas fa-user"></i>
                            <span>Profile</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <header class="main-header">
                <div class="header-left">
                    <button class="sidebar-toggle" onclick="toggleSidebar()">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h1>My Profile</h1>
                </div>
                
                <div class="header-right">
                    <div class="user-menu">
                        <div class="user-info">
                            <span class="user-name"><?= htmlspecialchars($current_user['full_name']) ?></span>
                            <span class="user-role"><?= ucfirst($current_user['role']) ?></span>
                        </div>
                        <div class="user-avatar">
                            <i class="fas fa-user"></i>
                        </div>
                    </div>
                </div>
            </header>

            <div class="dashboard-content">
                <?php if (isset($_SESSION['success_message'])): ?>
                    <div class="alert alert-success">
                        <?= htmlspecialchars($_SESSION['success_message']) ?>
                        <?php unset($_SESSION['success_message']); ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($error)): ?>
                    <div class="alert alert-danger">
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <div class="profile-container">
                    <div class="profile-section">
                        <div class="profile-header">
                            <div class="profile-avatar">
                                <i class="fas fa-user-circle"></i>
                            </div>
                            <div class="profile-info">
                                <h2><?= htmlspecialchars($current_user['full_name']) ?></h2>
                                <p class="role-badge"><?= ucfirst($current_user['role']) ?></p>
                                <p>Member since <?= date('M Y', strtotime($current_user['created_at'])) ?></p>
                            </div>
                        </div>

                        <form method="POST" class="profile-form">
                            <div class="form-grid two-columns">
                                <div class="form-group">
                                    <label for="full_name">Full Name *</label>
                                    <input type="text" name="full_name" id="full_name" 
                                           value="<?= htmlspecialchars($current_user['full_name']) ?>" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="email">Email *</label>
                                    <input type="email" name="email" id="email" 
                                           value="<?= htmlspecialchars($current_user['email']) ?>" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="phone">Phone</label>
                                    <input type="tel" name="phone" id="phone" 
                                           value="<?= htmlspecialchars($current_user['phone']) ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label for="username">Username</label>
                                    <input type="text" id="username" 
                                           value="<?= htmlspecialchars($current_user['username']) ?>" disabled>
                                </div>
                            </div>
                            
                            <div class="form-section">
                                <h3>Change Password</h3>
                                <div class="form-grid two-columns">
                                    <div class="form-group">
                                        <label for="password">New Password</label>
                                        <input type="password" name="password" id="password">
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="confirm_password">Confirm Password</label>
                                        <input type="password" name="confirm_password" id="confirm_password">
                                    </div>
                                </div>
                                <small>Leave blank to keep current password</small>
                            </div>
                            
                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Update Profile
                                </button>
                            </div>
                        </form>
                    </div>

                    <?php if (!empty($current_user['donor_id'])): ?>
                    <div class="donor-section">
                        <h3><i class="fas fa-hand-holding-heart"></i> My Donations</h3>
                        
                        <?php if (empty($donations)): ?>
                            <p>You haven't made any donations yet.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="data-table">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Blood Type</th>
                                            <th>Quantity</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($donations as $donation): ?>
                                        <tr>
                                            <td><?= date('M j, Y', strtotime($donation['donation_date'])) ?></td>
                                            <td><span class="blood-type"><?= $donation['blood_type'] ?></span></td>
                                            <td><?= $donation['quantity'] ?> units</td>
                                            <td>
                                                <span class="status status-<?= $donation['status'] ?>">
                                                    <?= ucfirst($donation['status']) ?>
                                                </span>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="text-center">
                                <a href="donations.php?filter=my" class="btn btn-secondary">
                                    View All My Donations
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <script src="../assets/js/main.js"></script>
    <style>
        .profile-container {
            display: grid;
            gap: 2rem;
        }

        .profile-section {
            background: white;
            border-radius: 8px;
            padding: 2rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .profile-header {
            display: flex;
            gap: 1.5rem;
            align-items: center;
            margin-bottom: 2rem;
        }

        .profile-avatar {
            font-size: 4rem;
            color: #667eea;
        }

        .profile-info h2 {
            margin: 0 0 0.5rem;
            color: #333;
        }

        .role-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            background: #667eea;
            color: white;
            border-radius: 1rem;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .profile-form {
            margin-top: 2rem;
        }

        .form-section {
            margin: 2rem 0;
            padding: 1.5rem;
            background: #f8f9fa;
            border-radius: 8px;
        }

        .form-section h3 {
            margin-top: 0;
            margin-bottom: 1.5rem;
            color: #333;
        }

        .form-actions {
            margin-top: 2rem;
            text-align: right;
        }

        .donor-section {
            background: white;
            border-radius: 8px;
            padding: 2rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .donor-section h3 {
            margin-top: 0;
            margin-bottom: 1.5rem;
            color: #333;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .text-center {
            text-align: center;
            margin-top: 1.5rem;
        }

        @media (max-width: 768px) {
            .profile-header {
                flex-direction: column;
                text-align: center;
            }
            
            .form-grid.two-columns {
                grid-template-columns: 1fr;
            }
        }
    </style>
</body>
</html>