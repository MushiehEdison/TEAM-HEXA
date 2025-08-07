<?php
// pages/donations.php
session_start();
require_once '../classes/Database.php';
require_once '../classes/Auth.php';

$auth = new Auth();
$auth->requireLogin();

$db = new Database();
$current_user = $auth->getCurrentUser();

// Handle all AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    try {
        switch ($_POST['action']) {
            case 'add_donation':
                $response = handleAddDonation($db, $_POST);
                break;
                
            case 'approve_donation':
                $response = handleApproveDonation($db, $_POST['id']);
                break;
                
            case 'reject_donation':
                $response = handleRejectDonation($db, $_POST['id'], $_POST['reason']);
                break;
                
            case 'get_donation_details':
                $response = handleGetDonationDetails($db, $_POST['id']);
                break;
                
            case 'get_donors':
                $response = handleGetDonors($db);
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
$filter = $_GET['filter'] ?? 'all';

// Get donations based on filter
if ($filter === 'today') {
    $donations = getTodaysDonations($db);
} else if ($filter === 'pending') {
    $donations = getPendingDonations($db);
} else {
    $donations = getAllDonations($db);
}

$stats = getDonationStats($db);

// ==================== FUNCTIONS ====================

function getAllDonations($db) {
    $query = "SELECT d.*, donor.full_name as donor_name 
              FROM donations d
              LEFT JOIN donors donor ON d.donor_id = donor.id
              ORDER BY d.donation_date DESC";
    $db->query($query);
    return $db->resultSet();
}

function getTodaysDonations($db) {
    $query = "SELECT d.*, donor.full_name as donor_name 
              FROM donations d
              LEFT JOIN donors donor ON d.donor_id = donor.id
              WHERE DATE(d.donation_date) = CURDATE()
              ORDER BY d.donation_date DESC";
    $db->query($query);
    return $db->resultSet();
}

function getPendingDonations($db) {
    $query = "SELECT d.*, donor.full_name as donor_name 
              FROM donations d
              LEFT JOIN donors donor ON d.donor_id = donor.id
              WHERE d.status = 'pending'
              ORDER BY d.donation_date DESC";
    $db->query($query);
    return $db->resultSet();
}

function getDonationStats($db) {
    $stats = [];

    // Helper function to get count value regardless of return type
    $getCount = function($result) {
        if (is_object($result)) {
            return $result->total ?? 0;
        } elseif (is_array($result)) {
            return $result['total'] ?? 0;
        }
        return 0;
    };

    // Total donations
    $query = "SELECT COUNT(*) as total FROM donations";
    $db->query($query);
    $result = $db->single();
    $stats['total_donations'] = $getCount($result);

    // Today's donations
    $query = "SELECT COUNT(*) as total FROM donations WHERE DATE(donation_date) = CURDATE()";
    $db->query($query);
    $result = $db->single();
    $stats['today_donations'] = $getCount($result);

    // Pending donations
    $query = "SELECT COUNT(*) as total FROM donations WHERE status = 'pending'";
    $db->query($query);
    $result = $db->single();
    $stats['pending_donations'] = $getCount($result);

    // Eligible donors
    $query = "SELECT COUNT(*) as total FROM donors 
              WHERE (last_donation_date IS NULL OR DATEDIFF(CURDATE(), last_donation_date) >= 56)";
    $db->query($query);
    $result = $db->single();
    $stats['eligible_donors'] = $getCount($result);

    return $stats;
}

function handleAddDonation($db, $data) {
    // Validate required fields
    $required = ['donor_id', 'blood_type', 'quantity', 'donation_date', 
                'hemoglobin_level', 'blood_pressure', 'temperature'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            throw new Exception("$field is required");
        }
    }

    // Start transaction
    $db->beginTransaction();

    try {
        // Insert donation record
        $query = "INSERT INTO donations 
                  (donor_id, blood_type, quantity, donation_date, hemoglobin_level, 
                   blood_pressure, temperature, status, notes, created_at)
                  VALUES 
                  (:donor_id, :blood_type, :quantity, :donation_date, :hemoglobin_level, 
                   :blood_pressure, :temperature, 'pending', :notes, NOW())";
        
        $db->query($query);
        $db->bind(':donor_id', $data['donor_id']);
        $db->bind(':blood_type', $data['blood_type']);
        $db->bind(':quantity', $data['quantity']);
        $db->bind(':donation_date', $data['donation_date']);
        $db->bind(':hemoglobin_level', $data['hemoglobin_level']);
        $db->bind(':blood_pressure', $data['blood_pressure']);
        $db->bind(':temperature', $data['temperature']);
        $db->bind(':notes', $data['notes'] ?? null);
        
        $db->execute();
        $donation_id = $db->lastInsertId();

        // Update donor's last donation date
        $query = "UPDATE donors SET last_donation_date = :donation_date WHERE id = :donor_id";
        $db->query($query);
        $db->bind(':donation_date', $data['donation_date']);
        $db->bind(':donor_id', $data['donor_id']);
        $db->execute();

        // Commit transaction
        $db->commit();

        return ['success' => true, 'message' => 'Donation recorded successfully'];
    } catch (Exception $e) {
        $db->rollBack();
        throw $e;
    }
}

function handleApproveDonation($db, $id) {
    // Start transaction
    $db->beginTransaction();

    try {
        // Get donation details
        $query = "SELECT * FROM donations WHERE id = :id";
        $db->query($query);
        $db->bind(':id', $id);
        $donation = $db->single();

        if (!$donation) {
            throw new Exception("Donation not found");
        }

        if ($donation->status !== 'pending') {
            throw new Exception("Only pending donations can be approved");
        }

        // Calculate expiry date (42 days from donation date)
        $expiry_date = date('Y-m-d', strtotime($donation->donation_date . ' +42 days'));

        // Add to blood inventory
        $query = "INSERT INTO blood_inventory 
                  (donation_id, blood_type, quantity, collection_date, expiry_date, status, created_at)
                  VALUES 
                  (:donation_id, :blood_type, :quantity, :collection_date, :expiry_date, 'available', NOW())";
        
        $db->query($query);
        $db->bind(':donation_id', $id);
        $db->bind(':blood_type', $donation->blood_type);
        $db->bind(':quantity', $donation->quantity);
        $db->bind(':collection_date', $donation->donation_date);
        $db->bind(':expiry_date', $expiry_date);
        $db->execute();

        // Update donation status
        $query = "UPDATE donations SET status = 'approved', updated_at = NOW() WHERE id = :id";
        $db->query($query);
        $db->bind(':id', $id);
        $db->execute();

        // Commit transaction
        $db->commit();

        return ['success' => true, 'message' => 'Donation approved and added to inventory'];
    } catch (Exception $e) {
        $db->rollBack();
        throw $e;
    }
}

function handleRejectDonation($db, $id, $reason) {
    $query = "UPDATE donations 
              SET status = 'rejected', notes = CONCAT(IFNULL(notes, ''), :reason), 
                  updated_at = NOW()
              WHERE id = :id";
    $db->query($query);
    $db->bind(':id', $id);
    $db->bind(':reason', "\n\nRejection Reason: " . $reason);
    $db->execute();

    return ['success' => true, 'message' => 'Donation rejected'];
}

function handleGetDonationDetails($db, $id) {
    $query = "SELECT d.*, donor.full_name as donor_name, donor.phone, donor.email
              FROM donations d
              LEFT JOIN donors donor ON d.donor_id = donor.id
              WHERE d.id = :id";
    $db->query($query);
    $db->bind(':id', $id);
    $donation = $db->single();

    if (!$donation) {
        return ['success' => false, 'message' => 'Donation not found'];
    }

    $html = '
    <div class="donation-details">
        <div class="detail-row">
            <span class="detail-label">Donor:</span>
            <span class="detail-value">' . htmlspecialchars($donation->donor_name) . '</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Blood Type:</span>
            <span class="detail-value blood-type">' . $donation->blood_type . '</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Quantity:</span>
            <span class="detail-value">' . $donation->quantity . ' units</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Donation Date:</span>
            <span class="detail-value">' . date('M j, Y h:i A', strtotime($donation->donation_date)) . '</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Hemoglobin:</span>
            <span class="detail-value">' . $donation->hemoglobin_level . ' g/dL</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Blood Pressure:</span>
            <span class="detail-value">' . $donation->blood_pressure . '</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Temperature:</span>
            <span class="detail-value">' . $donation->temperature . '°C</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Status:</span>
            <span class="detail-value status status-' . $donation->status . '">' . ucfirst($donation->status) . '</span>
        </div>';

    if (!empty($donation->notes)) {
        $html .= '
        <div class="detail-row">
            <span class="detail-label">Notes:</span>
            <span class="detail-value">' . nl2br(htmlspecialchars($donation->notes)) . '</span>
        </div>';
    }

    $html .= '</div>';

    return ['success' => true, 'html' => $html];
}

function handleGetDonors($db) {
    $query = "SELECT id, full_name, blood_type 
              FROM donors 
              WHERE (last_donation_date IS NULL OR DATEDIFF(CURDATE(), last_donation_date) >= 56)
              ORDER BY full_name";
    $db->query($query);
    $donors = $db->resultSet();

    return ['success' => true, 'donors' => $donors];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donations - Blood Bank Management System</title>
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
                    <li class="active">
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
                    <h1>Donations</h1>
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
                <!-- Action Bar -->
                <div class="page-actions">
                    <div class="filter-tabs">
                        <a href="donations.php" class="filter-tab <?php echo $filter === 'all' ? 'active' : ''; ?>">
                            <i class="fas fa-list"></i> All Donations
                        </a>
                        <a href="donations.php?filter=today" class="filter-tab <?php echo $filter === 'today' ? 'active' : ''; ?>">
                            <i class="fas fa-calendar-day"></i> Today's Donations
                        </a>
                        <a href="donations.php?filter=pending" class="filter-tab <?php echo $filter === 'pending' ? 'active' : ''; ?>">
                            <i class="fas fa-clock"></i> Pending
                        </a>
                    </div>
                    
                    <button class="btn btn-primary" onclick="openAddDonationModal()">
                        <i class="fas fa-plus"></i> New Donation
                    </button>
                </div>

                <!-- Donation Stats Cards -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon total-donations">
                            <i class="fas fa-hand-holding-heart"></i>
                        </div>
                        <div class="stat-content">
                            <h3><?php echo $stats['total_donations']; ?></h3>
                            <p>Total Donations</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon today-donations">
                            <i class="fas fa-calendar-day"></i>
                        </div>
                        <div class="stat-content">
                            <h3><?php echo $stats['today_donations']; ?></h3>
                            <p>Today's Donations</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon pending-donations">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="stat-content">
                            <h3><?php echo $stats['pending_donations']; ?></h3>
                            <p>Pending Donations</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon eligible-donors">
                            <i class="fas fa-user-check"></i>
                        </div>
                        <div class="stat-content">
                            <h3><?php echo $stats['eligible_donors']; ?></h3>
                            <p>Eligible Donors</p>
                        </div>
                    </div>
                </div>

                <!-- Donations Table -->
                <div class="table-container">
                    <div class="table-header">
                        <h3>
                            <?php if ($filter === 'today'): ?>
                                <i class="fas fa-calendar-day"></i> Today's Donations
                            <?php elseif ($filter === 'pending'): ?>
                                <i class="fas fa-clock"></i> Pending Donations
                            <?php else: ?>
                                <i class="fas fa-hand-holding-heart"></i> All Donations
                            <?php endif; ?>
                        </h3>
                        <div class="table-actions">
                            <div class="search-box">
                                <input type="text" placeholder="Search donations..." id="searchDonations">
                                <i class="fas fa-search"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="data-table" id="donationsTable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Donor</th>
                                    <th>Blood Type</th>
                                    <th>Quantity</th>
                                    <th>Donation Date</th>
                                    <th>Hemoglobin</th>
                                    <th>Vitals</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($donations as $donation): ?>
                                <tr>
                                    <td>#<?php echo str_pad($donation['id'], 4, '0', STR_PAD_LEFT); ?></td>
                                    <td>
                                        <div class="user-info">
                                            <span class="user-name"><?php echo htmlspecialchars($donation['donor_name']); ?></span>
                                            <small><?php echo htmlspecialchars($donation['donor_id']); ?></small>
                                        </div>
                                    </td>
                                    <td><span class="blood-type"><?php echo $donation['blood_type']; ?></span></td>
                                    <td><?php echo $donation['quantity']; ?> units</td>
                                    <td><?php echo date('M j, Y', strtotime($donation['donation_date'])); ?></td>
                                    <td><?php echo $donation['hemoglobin_level']; ?> g/dL</td>
                                    <td>
                                        <small>BP: <?php echo $donation['blood_pressure']; ?></small><br>
                                        <small>Temp: <?php echo $donation['temperature']; ?>°C</small>
                                    </td>
                                    <td>
                                        <span class="status status-<?php echo $donation['status']; ?>">
                                            <?php echo ucfirst($donation['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="btn-sm btn-secondary" onclick="viewDonationDetails(<?php echo $donation['id']; ?>)">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <?php if ($donation['status'] === 'pending'): ?>
                                            <button class="btn-sm btn-success" onclick="approveDonation(<?php echo $donation['id']; ?>)">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button class="btn-sm btn-danger" onclick="rejectDonation(<?php echo $donation['id']; ?>)">
                                                <i class="fas fa-times"></i>
                                            </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Add Donation Modal -->
    <div id="addDonationModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Record New Donation</h3>
                <button class="modal-close" onclick="closeAddDonationModal()">&times;</button>
            </div>
            
            <form id="addDonationForm">
                <div class="form-grid two-columns">
                    <div class="form-group">
                        <label for="donor_id">Donor *</label>
                        <select name="donor_id" id="donorSelect" required>
                            <option value="">Select Donor</option>
                            <!-- Options will be populated via AJAX -->
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="blood_type">Blood Type *</label>
                        <select name="blood_type" required>
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
                    
                    <div class="form-group">
                        <label for="quantity">Quantity (units) *</label>
                        <input type="number" name="quantity" min="1" max="2" value="1" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="donation_date">Donation Date *</label>
                        <input type="datetime-local" name="donation_date" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="hemoglobin_level">Hemoglobin Level (g/dL) *</label>
                        <input type="number" name="hemoglobin_level" step="0.1" min="12" max="20" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="blood_pressure">Blood Pressure *</label>
                        <input type="text" name="blood_pressure" placeholder="e.g. 120/80" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="temperature">Temperature (°C) *</label>
                        <input type="number" name="temperature" step="0.1" min="35" max="40" required>
                    </div>
                    
                    <div class="form-group full-width">
                        <label for="notes">Notes</label>
                        <textarea name="notes" rows="3"></textarea>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeAddDonationModal()">
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Record Donation
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Donation Details Modal -->
    <div id="donationDetailsModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Donation Details</h3>
                <button class="modal-close" onclick="closeDonationDetailsModal()">&times;</button>
            </div>
            
            <div class="modal-body" id="donationDetailsContent">
                <!-- Content will be loaded via AJAX -->
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeDonationDetailsModal()">
                    Close
                </button>
            </div>
        </div>
    </div>

    <script src="../assets/js/main.js"></script>
    <script>
        // Initialize data table
        new DataTable('donationsTable', {
            searchable: true,
            sortable: true,
            pagination: true,
            pageSize: 20
        });

        // Add donation modal functions
        function openAddDonationModal() {
            // Load donors list
            loadDonors();
            
            // Set default date/time to now
            const now = new Date();
            const formattedDateTime = now.toISOString().slice(0, 16);
            document.querySelector('input[name="donation_date"]').value = formattedDateTime;
            
            document.getElementById('addDonationModal').classList.add('active');
        }

        function closeAddDonationModal() {
            document.getElementById('addDonationModal').classList.remove('active');
            document.getElementById('addDonationForm').reset();
        }

        // Load donors for select dropdown
       async function loadDonors() {
    const result = await AjaxUtil.get('donations.php', {  // Changed from '../ajax/donation_operations.php' to 'donations.php'
        action: 'get_donors'
    });
    
    if (result.success) {
        const select = document.getElementById('donorSelect');
        select.innerHTML = '<option value="">Select Donor</option>';
        
        result.donors.forEach(donor => {
            const option = document.createElement('option');
            option.value = donor.id;
            option.textContent = `${donor.full_name} (${donor.blood_type})`;
            select.appendChild(option);
        });
    }
}

        // Handle add donation form submission
        document.getElementById('addDonationForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const data = Object.fromEntries(formData);
            
            const result = await AjaxUtil.post('../ajax/donation_operations.php', {
                action: 'add_donation',
                ...data
            });
            
            if (result.success) {
                AlertSystem.show(result.message, 'success');
                closeAddDonationModal();
                setTimeout(() => location.reload(), 1500);
            } else {
                AlertSystem.show(result.message, 'error');
            }
        });

        // View donation details
        async function viewDonationDetails(id) {
            const result = await AjaxUtil.get('../ajax/donation_operations.php', {
                action: 'get_donation_details',
                id: id
            });
            
            if (result.success) {
                document.getElementById('donationDetailsContent').innerHTML = result.html;
                document.getElementById('donationDetailsModal').classList.add('active');
            } else {
                AlertSystem.show(result.message, 'error');
            }
        }

        function closeDonationDetailsModal() {
            document.getElementById('donationDetailsModal').classList.remove('active');
        }

        // Approve donation
        async function approveDonation(id) {
            if (confirm('Approve this donation and add to inventory?')) {
                const result = await AjaxUtil.post('../ajax/donation_operations.php', {
                    action: 'approve_donation',
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

        // Reject donation
        async function rejectDonation(id) {
            const reason = prompt('Please enter reason for rejection:');
            if (reason !== null) {
                const result = await AjaxUtil.post('../ajax/donation_operations.php', {
                    action: 'reject_donation',
                    id: id,
                    reason: reason
                });
                
                if (result.success) {
                    AlertSystem.show(result.message, 'success');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    AlertSystem.show(result.message, 'error');
                }
            }
        }

        // Search donations
        document.getElementById('searchDonations').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const rows = document.querySelectorAll('#donationsTable tbody tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        });
    </script>

    <style>
        .filter-tabs {
            display: flex;
            gap: 0.5rem;
        }

        .filter-tab {
            padding: 0.5rem 1rem;
            background: #f8f9fa;
            color: #666;
            text-decoration: none;
            border-radius: 6px;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .filter-tab:hover,
        .filter-tab.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .page-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
        }

        .stat-icon.total-donations {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .stat-icon.today-donations {
            background: linear-gradient(135deg, #f6d365 0%, #fda085 100%);
        }

        .stat-icon.pending-donations {
            background: linear-gradient(135deg, #a18cd1 0%, #fbc2eb 100%);
        }

        .stat-icon.eligible-donors {
            background: linear-gradient(135deg, #84fab0 0%, #8fd3f4 100%);
        }

        .user-info {
            display: flex;
            flex-direction: column;
        }

        .user-info .user-name {
            font-weight: 500;
        }

        .user-info small {
            color: #666;
            font-size: 0.8rem;
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

        .table-actions {
            display: flex;
            gap: 0.5rem;
        }

        .search-box {
            position: relative;
        }

        .search-box input {
            padding: 0.5rem 1rem 0.5rem 2rem;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .search-box i {
            position: absolute;
            left: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            color: #666;
        }

        @media (max-width: 768px) {
            .page-actions {
                flex-direction: column;
                gap: 1rem;
            }

            .filter-tabs {
                width: 100%;
                justify-content: space-around;
            }
        }
    </style>
</body>
</html>