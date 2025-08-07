<?php
// pages/staff.php
session_start();
require_once '../classes/Database.php';
require_once '../classes/Auth.php';

$auth = new Auth();
$auth->requireLogin();
// $auth->requireAdmin(); // Only admins can access staff management

$db = new Database();
$current_user = $auth->getCurrentUser();

// Handle all AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    try {
        switch ($_POST['action']) {
            case 'add_staff':
                $response = handleAddStaff($db, $_POST);
                break;
                
            case 'update_staff':
                $response = handleUpdateStaff($db, $_POST);
                break;
                
            case 'toggle_staff_status':
                $response = handleToggleStaffStatus($db, $_POST['id']);
                break;
                
            case 'get_staff_details':
                $response = handleGetStaffDetails($db, $_POST['id']);
                break;
                
            case 'add_donor':
                $response = handleAddDonor($db, $_POST);
                break;
                
            case 'update_donor':
                $response = handleUpdateDonor($db, $_POST);
                break;
                
            case 'get_donor_details':
                $response = handleGetDonorDetails($db, $_POST['id']);
                break;
                
            default:
                $response = ['success' => false, 'message' => 'Invalid action'];
        }
    } catch (Exception $e) {
        $response = ['success' => false, 'message' => $e->getMessage()];
    }
    
    echo json_encode($response);
    exit;
}

// Handle GET requests for page display
$tab = $_GET['tab'] ?? 'staff';
$staff = getAllStaff($db);
$donors = getAllDonors($db);

// ==================== FUNCTIONS ====================

function getAllStaff($db) {
    $query = "SELECT * FROM users ORDER BY full_name ASC";
    $db->query($query);
    return $db->resultSet();
}

function getAllDonors($db) {
    $query = "SELECT * FROM donors ORDER BY full_name ASC";
    $db->query($query);
    return $db->resultSet();
}

function handleAddStaff($db, $data) {
    // Validate required fields
    $required = ['username', 'email', 'password', 'full_name'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            throw new Exception("$field is required");
        }
    }

    // Check if username or email already exists
    $query = "SELECT id FROM users WHERE username = :username OR email = :email";
    $db->query($query);
    $db->bind(':username', $data['username']);
    $db->bind(':email', $data['email']);
    $existing = $db->single();
    
    if ($existing) {
        throw new Exception("Username or email already exists");
    }

    // Hash password
    $hashed_password = password_hash($data['password'], PASSWORD_DEFAULT);

    // Insert staff record
    $query = "INSERT INTO users 
              (username, email, password, full_name, role, status, created_at)
              VALUES 
              (:username, :email, :password, :full_name, :role, 'active', NOW())";
    
    $db->query($query);
    $db->bind(':username', $data['username']);
    $db->bind(':email', $data['email']);
    $db->bind(':password', $hashed_password);
    $db->bind(':full_name', $data['full_name']);
    $db->bind(':role', $data['role'] ?? 'staff');
    
    if ($db->execute()) {
        return ['success' => true, 'message' => 'Staff added successfully'];
    }
    
    throw new Exception("Failed to add staff");
}

function handleUpdateStaff($db, $data) {
    // Validate required fields
    $required = ['id', 'username', 'email', 'full_name'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            throw new Exception("$field is required");
        }
    }

    // Check if username or email already exists for another staff
    $query = "SELECT id FROM users WHERE (username = :username OR email = :email) AND id != :id";
    $db->query($query);
    $db->bind(':username', $data['username']);
    $db->bind(':email', $data['email']);
    $db->bind(':id', $data['id']);
    $existing = $db->single();
    
    if ($existing) {
        throw new Exception("Username or email already exists for another staff");
    }

    // Prepare base query
    $query = "UPDATE users SET 
              username = :username,
              email = :email,
              full_name = :full_name,
              role = :role,
              updated_at = NOW()
              WHERE id = :id";
    
    // Add password update if provided
    if (!empty($data['password'])) {
        $hashed_password = password_hash($data['password'], PASSWORD_DEFAULT);
        $query = str_replace("updated_at = NOW()", "password = :password, updated_at = NOW()", $query);
    }
    
    $db->query($query);
    $db->bind(':username', $data['username']);
    $db->bind(':email', $data['email']);
    $db->bind(':full_name', $data['full_name']);
    $db->bind(':role', $data['role'] ?? 'staff');
    $db->bind(':id', $data['id']);
    
    if (!empty($data['password'])) {
        $db->bind(':password', $hashed_password);
    }
    
    if ($db->execute()) {
        return ['success' => true, 'message' => 'Staff updated successfully'];
    }
    
    throw new Exception("Failed to update staff");
}

function handleToggleStaffStatus($db, $id) {
    $query = "UPDATE users SET 
              status = CASE WHEN status = 'active' THEN 'inactive' ELSE 'active' END,
              updated_at = NOW()
              WHERE id = :id";
    
    $db->query($query);
    $db->bind(':id', $id);
    
    if ($db->execute()) {
        return ['success' => true, 'message' => 'Staff status updated'];
    }
    
    throw new Exception("Failed to update staff status");
}

function handleGetStaffDetails($db, $id) {
    $query = "SELECT * FROM users WHERE id = :id";
    $db->query($query);
    $db->bind(':id', $id);
    $staff = $db->single();
    
    if (!$staff) {
        return ['success' => false, 'message' => 'Staff not found'];
    }
    
    // Never return the password hash
    unset($staff->password);
    
    return ['success' => true, 'staff' => $staff];
}

function handleAddDonor($db, $data) {
    // Validate required fields
    $required = ['full_name', 'blood_type'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            throw new Exception("$field is required");
        }
    }

    // Insert donor record
    $query = "INSERT INTO donors 
              (full_name, phone, email, blood_type, address, created_at)
              VALUES 
              (:full_name, :phone, :email, :blood_type, :address, NOW())";
    
    $db->query($query);
    $db->bind(':full_name', $data['full_name']);
    $db->bind(':phone', $data['phone'] ?? null);
    $db->bind(':email', $data['email'] ?? null);
    $db->bind(':blood_type', $data['blood_type']);
    $db->bind(':address', $data['address'] ?? null);
    
    if ($db->execute()) {
        return ['success' => true, 'message' => 'Donor added successfully'];
    }
    
    throw new Exception("Failed to add donor");
}

function handleUpdateDonor($db, $data) {
    // Validate required fields
    $required = ['id', 'full_name', 'blood_type'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            throw new Exception("$field is required");
        }
    }

    // Update donor record
    $query = "UPDATE donors SET 
              full_name = :full_name,
              phone = :phone,
              email = :email,
              blood_type = :blood_type,
              address = :address
              WHERE id = :id";
    
    $db->query($query);
    $db->bind(':full_name', $data['full_name']);
    $db->bind(':phone', $data['phone'] ?? null);
    $db->bind(':email', $data['email'] ?? null);
    $db->bind(':blood_type', $data['blood_type']);
    $db->bind(':address', $data['address'] ?? null);
    $db->bind(':id', $data['id']);
    
    if ($db->execute()) {
        return ['success' => true, 'message' => 'Donor updated successfully'];
    }
    
    throw new Exception("Failed to update donor");
}

function handleGetDonorDetails($db, $id) {
    $query = "SELECT * FROM donors WHERE id = :id";
    $db->query($query);
    $db->bind(':id', $id);
    $donor = $db->single();
    
    if (!$donor) {
        return ['success' => false, 'message' => 'Donor not found'];
    }
    
    return ['success' => true, 'donor' => $donor];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff & Donor Management - Blood Bank Management System</title>
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
                    <li class="active">
                        <a href="staff.php">
                            <i class="fas fa-users"></i>
                            <span>Staff Management</span>
                        </a>
                    </li>
                    <li>
                        <a href="reports.php">
                            <i class="fas fa-file-alt"></i>
                            <span>Reports</span>
                        </a>
                    </li>
                    <li>
                        <a href="settings.php">
                            <i class="fas fa-cog"></i>
                            <span>Settings</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Header -->
            <header class="main-header">
                <div class="header-left">
                    <button class="sidebar-toggle" onclick="toggleSidebar()">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h1>Staff & Donor Management</h1>
                </div>
                
                <div class="header-right">
                    <div class="user-menu">
                        <div class="user-info">
                            <span class="user-name"><?php echo htmlspecialchars($current_user['full_name']); ?></span>
                            <span class="user-role"><?php echo ucfirst($current_user['role']); ?></span>
                        </div>
                        <div class="user-avatar">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="dropdown">
                            <a href="settings.php"><i class="fas fa-user-cog"></i> Profile</a>
                            <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <div class="dashboard-content">
                <!-- Tabs -->
                <div class="tabs">
                    <a href="staff.php?tab=staff" class="tab <?php echo $tab === 'staff' ? 'active' : ''; ?>">
                        <i class="fas fa-users-cog"></i> Staff
                    </a>
                    <a href="staff.php?tab=donors" class="tab <?php echo $tab === 'donors' ? 'active' : ''; ?>">
                        <i class="fas fa-hand-holding-heart"></i> Donors
                    </a>
                </div>

                <!-- Action Bar -->
                <div class="page-actions">
                    <div class="search-box">
                        <input type="text" placeholder="Search <?php echo $tab === 'staff' ? 'staff' : 'donors'; ?>..." 
                               id="searchInput">
                        <i class="fas fa-search"></i>
                    </div>
                    
                    <button class="btn btn-primary" onclick="openAddModal()">
                        <i class="fas fa-plus"></i> Add <?php echo $tab === 'staff' ? 'Staff' : 'Donor'; ?>
                    </button>
                </div>

                <!-- Staff Table -->
                <?php if ($tab === 'staff'): ?>
                <div class="table-container">
                    <div class="table-responsive">
                        <table class="data-table" id="staffTable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Username</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($staff as $staff_member): ?>
                                <tr>
                                    <td>#<?php echo str_pad($staff_member['id'], 4, '0', STR_PAD_LEFT); ?></td>
                                    <td><?php echo htmlspecialchars($staff_member['full_name']); ?></td>
                                    <td><?php echo htmlspecialchars($staff_member['username']); ?></td>
                                    <td><?php echo htmlspecialchars($staff_member['email']); ?></td>
                                    <td>
                                        <span class="role role-<?php echo $staff_member['role']; ?>">
                                            <?php echo ucfirst($staff_member['role']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="status status-<?php echo $staff_member['status']; ?>">
                                            <?php echo ucfirst($staff_member['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="btn-sm btn-secondary" onclick="viewDetails(<?php echo $staff_member['id']; ?>)">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn-sm btn-primary" onclick="editRecord(<?php echo $staff_member['id']; ?>)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn-sm btn-<?php echo $staff_member['status'] === 'active' ? 'warning' : 'success'; ?>" 
                                                    onclick="toggleStatus(<?php echo $staff_member['id']; ?>)">
                                                <i class="fas fa-<?php echo $staff_member['status'] === 'active' ? 'ban' : 'check'; ?>"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Donors Table -->
                <?php if ($tab === 'donors'): ?>
                <div class="table-container">
                    <div class="table-responsive">
                        <table class="data-table" id="donorsTable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Phone</th>
                                    <th>Email</th>
                                    <th>Blood Type</th>
                                    <th>Last Donation</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($donors as $donor): ?>
                                <tr>
                                    <td>#<?php echo str_pad($donor['id'], 4, '0', STR_PAD_LEFT); ?></td>
                                    <td><?php echo htmlspecialchars($donor['full_name']); ?></td>
                                    <td><?php echo htmlspecialchars($donor['phone'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($donor['email'] ?? 'N/A'); ?></td>
                                    <td><span class="blood-type"><?php echo $donor['blood_type']; ?></span></td>
                                    <td>
                                        <?php echo $donor['last_donation_date'] ? 
                                            date('M j, Y', strtotime($donor['last_donation_date'])) : 'Never'; ?>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="btn-sm btn-secondary" onclick="viewDetails(<?php echo $donor['id']; ?>)">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn-sm btn-primary" onclick="editRecord(<?php echo $donor['id']; ?>)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <!-- Add/Edit Modal -->
    <div id="managementModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modalTitle">Add New Staff</h3>
                <button class="modal-close" onclick="closeModal()">&times;</button>
            </div>
            
            <form id="managementForm">
                <input type="hidden" name="id" id="recordId">
                <input type="hidden" name="action" id="formAction">
                
                <div class="form-grid" id="staffFormFields">
                    <div class="form-group">
                        <label for="full_name">Full Name *</label>
                        <input type="text" name="full_name" id="full_name" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="username">Username *</label>
                        <input type="text" name="username" id="username" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email *</label>
                        <input type="email" name="email" id="email" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="password" id="passwordLabel">Password *</label>
                        <input type="password" name="password" id="password" required>
                        <small id="passwordHelp" class="form-text">Leave blank to keep current password</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="role">Role *</label>
                        <select name="role" id="role" required>
                            <option value="staff">Staff</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-grid" id="donorFormFields" style="display: none;">
                    <div class="form-group">
                        <label for="donor_full_name">Full Name *</label>
                        <input type="text" name="full_name" id="donor_full_name" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="phone">Phone</label>
                        <input type="tel" name="phone" id="phone">
                    </div>
                    
                    <div class="form-group">
                        <label for="donor_email">Email</label>
                        <input type="email" name="email" id="donor_email">
                    </div>
                    
                    <div class="form-group">
                        <label for="blood_type">Blood Type *</label>
                        <select name="blood_type" id="blood_type" required>
                            <option value="">Select Blood Type</option>
                            <option value="A+">A+</option>
                            <option value="A-">A-</option>
                            <option value="B+">B+</option>
                            <option value="B-">B-</option>
                            <option value="AB+">AB+</option>
                            <option value="AB-">AB-</option>
                            <option value="O+">O+</option>
                            <option value="O-">O-</option>
                        </select>
                    </div>
                    
                    <div class="form-group full-width">
                        <label for="address">Address</label>
                        <textarea name="address" id="address" rows="2"></textarea>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Details Modal -->
    <div id="detailsModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="detailsTitle">Staff Details</h3>
                <button class="modal-close" onclick="closeDetailsModal()">&times;</button>
            </div>
            
            <div class="modal-body" id="detailsContent">
                <!-- Content will be loaded via AJAX -->
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeDetailsModal()">
                    Close
                </button>
            </div>
        </div>
    </div>

    <script src="../assets/js/main.js"></script>
    <script>
        // Initialize data tables
        new DataTable('staffTable', {
            searchable: true,
            sortable: true,
            pagination: true,
            pageSize: 20
        });

        new DataTable('donorsTable', {
            searchable: true,
            sortable: true,
            pagination: true,
            pageSize: 20
        });

        // Tab management
        const currentTab = '<?php echo $tab; ?>';

        // Modal functions
        function openAddModal() {
            document.getElementById('managementModal').classList.add('active');
            document.getElementById('recordId').value = '';
            document.getElementById('formAction').value = currentTab === 'staff' ? 'add_staff' : 'add_donor';
            document.getElementById('modalTitle').textContent = `Add New ${currentTab === 'staff' ? 'Staff' : 'Donor'}`;
            
            // Show the appropriate form fields
            if (currentTab === 'staff') {
                document.getElementById('staffFormFields').style.display = 'grid';
                document.getElementById('donorFormFields').style.display = 'none';
                document.getElementById('password').required = true;
                document.getElementById('passwordHelp').style.display = 'none';
            } else {
                document.getElementById('staffFormFields').style.display = 'none';
                document.getElementById('donorFormFields').style.display = 'grid';
            }
            
            // Reset form
            document.getElementById('managementForm').reset();
        }

        function closeModal() {
            document.getElementById('managementModal').classList.remove('active');
        }

        function closeDetailsModal() {
            document.getElementById('detailsModal').classList.remove('active');
        }

        // View details
        async function viewDetails(id) {
            const action = currentTab === 'staff' ? 'get_staff_details' : 'get_donor_details';
            const result = await AjaxUtil.get('staff.php', {
                action: action,
                id: id
            });
            
            if (result.success) {
                document.getElementById('detailsTitle').textContent = 
                    `${currentTab === 'staff' ? 'Staff' : 'Donor'} Details`;
                
                let html = '';
                if (currentTab === 'staff') {
                    const staff = result.staff;
                    html = `
                    <div class="detail-row">
                        <span class="detail-label">ID:</span>
                        <span class="detail-value">#${String(staff.id).padStart(4, '0')}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Full Name:</span>
                        <span class="detail-value">${escapeHtml(staff.full_name)}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Username:</span>
                        <span class="detail-value">${escapeHtml(staff.username)}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Email:</span>
                        <span class="detail-value">${escapeHtml(staff.email)}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Role:</span>
                        <span class="detail-value role role-${staff.role}">${capitalizeFirst(staff.role)}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Status:</span>
                        <span class="detail-value status status-${staff.status}">${capitalizeFirst(staff.status)}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Created At:</span>
                        <span class="detail-value">${formatDate(staff.created_at)}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Last Updated:</span>
                        <span class="detail-value">${staff.updated_at ? formatDate(staff.updated_at) : 'Never'}</span>
                    </div>`;
                } else {
                    const donor = result.donor;
                    html = `
                    <div class="detail-row">
                        <span class="detail-label">ID:</span>
                        <span class="detail-value">#${String(donor.id).padStart(4, '0')}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Full Name:</span>
                        <span class="detail-value">${escapeHtml(donor.full_name)}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Phone:</span>
                        <span class="detail-value">${donor.phone ? escapeHtml(donor.phone) : 'N/A'}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Email:</span>
                        <span class="detail-value">${donor.email ? escapeHtml(donor.email) : 'N/A'}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Blood Type:</span>
                        <span class="detail-value blood-type">${donor.blood_type}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Address:</span>
                        <span class="detail-value">${donor.address ? escapeHtml(donor.address) : 'N/A'}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Last Donation:</span>
                        <span class="detail-value">${donor.last_donation_date ? formatDate(donor.last_donation_date) : 'Never'}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Registered On:</span>
                        <span class="detail-value">${formatDate(donor.created_at)}</span>
                    </div>`;
                }
                
                document.getElementById('detailsContent').innerHTML = html;
                document.getElementById('detailsModal').classList.add('active');
            } else {
                AlertSystem.show(result.message, 'error');
            }
        }

        // Edit record
        async function editRecord(id) {
            const action = currentTab === 'staff' ? 'get_staff_details' : 'get_donor_details';
            const result = await AjaxUtil.get('staff.php', {
                action: action,
                id: id
            });
            
            if (result.success) {
                document.getElementById('managementModal').classList.add('active');
                document.getElementById('recordId').value = id;
                document.getElementById('formAction').value = currentTab === 'staff' ? 'update_staff' : 'update_donor';
                document.getElementById('modalTitle').textContent = `Edit ${currentTab === 'staff' ? 'Staff' : 'Donor'}`;
                
                // Show the appropriate form fields
                if (currentTab === 'staff') {
                    document.getElementById('staffFormFields').style.display = 'grid';
                    document.getElementById('donorFormFields').style.display = 'none';
                    
                    const staff = result.staff;
                    document.getElementById('full_name').value = staff.full_name;
                    document.getElementById('username').value = staff.username;
                    document.getElementById('email').value = staff.email;
                    document.getElementById('role').value = staff.role;
                    
                    // Password is not required for updates
                    document.getElementById('password').required = false;
                    document.getElementById('passwordHelp').style.display = 'block';
                } else {
                    document.getElementById('staffFormFields').style.display = 'none';
                    document.getElementById('donorFormFields').style.display = 'grid';
                    
                    const donor = result.donor;
                    document.getElementById('donor_full_name').value = donor.full_name;
                    document.getElementById('phone').value = donor.phone || '';
                    document.getElementById('donor_email').value = donor.email || '';
                    document.getElementById('blood_type').value = donor.blood_type;
                    document.getElementById('address').value = donor.address || '';
                }
            } else {
                AlertSystem.show(result.message, 'error');
            }
        }

        // Toggle staff status
        async function toggleStatus(id) {
            if (confirm('Are you sure you want to change this staff member\'s status?')) {
                const result = await AjaxUtil.post('staff.php', {
                    action: 'toggle_staff_status',
                    id: id
                });
                
                if (result.success) {
                    AlertSystem.show(result.message, 'success');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    AlertSystem.show(result.message, 'error');
                }
            }
        }

        // Handle form submission
        document.getElementById('managementForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const data = Object.fromEntries(formData);
            
            const result = await AjaxUtil.post('staff.php', data);
            
            if (result.success) {
                AlertSystem.show(result.message, 'success');
                closeModal();
                setTimeout(() => location.reload(), 1500);
            } else {
                AlertSystem.show(result.message, 'error');
            }
        });

        // Search functionality
        document.getElementById('searchInput').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const tableId = currentTab === 'staff' ? 'staffTable' : 'donorsTable';
            const rows = document.querySelectorAll(`#${tableId} tbody tr`);
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        });

        // Helper functions
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        function capitalizeFirst(str) {
            return str.charAt(0).toUpperCase() + str.slice(1);
        }

        function formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString('en-US', { 
                year: 'numeric', 
                month: 'short', 
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }
    </script>

    <style>
        .tabs {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
            border-bottom: 1px solid #eee;
            padding-bottom: 0.5rem;
        }

        .tab {
            padding: 0.75rem 1.5rem;
            background: #f8f9fa;
            color: #666;
            text-decoration: none;
            border-radius: 6px 6px 0 0;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 500;
        }

        .tab:hover,
        .tab.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .page-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .search-box {
            position: relative;
            width: 300px;
        }

        .search-box input {
            padding: 0.5rem 1rem 0.5rem 2.5rem;
            border: 1px solid #ddd;
            border-radius: 6px;
            width: 100%;
        }

        .search-box i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #666;
        }

        .role {
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .role-admin {
            background-color: #f0ad4e;
            color: white;
        }

        .role-staff {
            background-color: #5bc0de;
            color: white;
        }

        .status {
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .status-active {
            background-color: #5cb85c;
            color: white;
        }

        .status-inactive {
            background-color: #d9534f;
            color: white;
        }

        .action-buttons {
            display: flex;
            gap: 0.25rem;
        }

        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.8rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-sm:hover {
            opacity: 0.9;
        }

        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }

        .btn-primary {
            background-color: #007bff;
            color: white;
        }

        .btn-success {
            background-color: #28a745;
            color: white;
        }

        .btn-warning {
            background-color: #ffc107;
            color: #212529;
        }

        .btn-danger {
            background-color: #dc3545;
            color: white;
        }

        .detail-row {
            display: flex;
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #f0f0f0;
        }

        .detail-label {
            font-weight: 600;
            width: 150px;
            color: #555;
        }

        .detail-value {
            flex: 1;
        }

        .blood-type {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            background-color: #d9534f;
            color: white;
            border-radius: 4px;
            font-weight: 500;
        }

        @media (max-width: 768px) {
            .page-actions {
                flex-direction: column;
                gap: 1rem;
            }
            
            .search-box {
                width: 100%;
            }
            
            .detail-row {
                flex-direction: column;
            }
            
            .detail-label {
                width: 100%;
                margin-bottom: 0.25rem;
            }
        }
    </style>
</body>
</html>