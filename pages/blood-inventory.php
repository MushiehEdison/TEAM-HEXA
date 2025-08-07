<?php
// pages/blood-inventory.php
session_start();
require_once '../classes/Database.php';
require_once '../classes/Auth.php';
require_once '../classes/BloodBank.php';

$auth = new Auth();
$auth->requireLogin();

$bloodBank = new BloodBank();
$current_user = $auth->getCurrentUser();

// Get filter parameter
$filter = $_GET['filter'] ?? 'all';

// Get blood inventory based on filter
if ($filter === 'expiring') {
    $inventory = $bloodBank->getExpiringBlood(7);
} else {
    $inventory = $bloodBank->getBloodInventory();
}

$blood_summary = $bloodBank->getBloodSummary();

// Helper function to safely access array or object properties
function safeGet($data, $key, $default = '') {
    if (is_object($data)) {
        return isset($data->$key) ? $data->$key : $default;
    } elseif (is_array($data)) {
        return isset($data[$key]) ? $data[$key] : $default;
    }
    return $default;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blood Inventory - Blood Bank Management System</title>
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
                    <li class="active">
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
                    <h1>Blood Inventory</h1>
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
                        <a href="blood-inventory.php" class="filter-tab <?php echo $filter === 'all' ? 'active' : ''; ?>">
                            <i class="fas fa-list"></i> All Inventory
                        </a>
                        <a href="blood-inventory.php?filter=expiring" class="filter-tab <?php echo $filter === 'expiring' ? 'active' : ''; ?>">
                            <i class="fas fa-clock"></i> Expiring Soon
                        </a>
                    </div>
                    
                    <button class="btn btn-primary" onclick="openAddInventoryModal()">
                        <i class="fas fa-plus"></i> Add Blood Unit
                    </button>
                </div>

                <!-- Blood Summary Cards -->
                <div class="stats-grid">
                    <?php foreach ($blood_summary as $summary): ?>
                    <div class="stat-card">
                        <div class="stat-icon blood-units">
                            <span class="blood-type-large"><?php echo htmlspecialchars(safeGet($summary, 'blood_type')); ?></span>
                        </div>
                        <div class="stat-content">
                            <h3><?php echo safeGet($summary, 'available_units', 0); ?></h3>
                            <p>Available Units</p>
                            <?php if (safeGet($summary, 'expiring_soon', 0) > 0): ?>
                            <small class="expiring-warning">
                                <i class="fas fa-exclamation-triangle"></i>
                                <?php echo safeGet($summary, 'expiring_soon', 0); ?> expiring soon
                            </small>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Inventory Table -->
                <div class="table-container">
                    <div class="table-header">
                        <h3><i class="fas fa-flask"></i> Blood Inventory</h3>
                        <div class="table-actions">
                            <div class="search-box">
                                <input type="text" placeholder="Search..." id="searchInventory">
                                <i class="fas fa-search"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="data-table" id="inventoryTable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Blood Type</th>
                                    <th>Quantity</th>
                                    <th>Collection Date</th>
                                    <th>Expiry Date</th>
                                    <th>Days Left</th>
                                    <th>Donor</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($inventory)): ?>
                                    <?php foreach ($inventory as $item): ?>
                                    <?php 
                                    $expiry_date = safeGet($item, 'expiry_date');
                                    $collection_date = safeGet($item, 'collection_date');
                                    $item_id = safeGet($item, 'id', 0);
                                    $blood_type = safeGet($item, 'blood_type', 'Unknown');
                                    $quantity = safeGet($item, 'quantity', 0);
                                    $donor_name = safeGet($item, 'donor_name', 'Unknown');
                                    $status = safeGet($item, 'status', 'unknown');
                                    
                                    $days_left = 0;
                                    if ($expiry_date) {
                                        $days_left = (strtotime($expiry_date) - time()) / (60 * 60 * 24);
                                        $days_left = max(0, floor($days_left));
                                    }
                                    ?>
                                    <tr class="<?php echo $days_left <= 3 ? 'urgent-expiry' : ''; ?>">
                                        <td>#<?php echo str_pad($item_id, 4, '0', STR_PAD_LEFT); ?></td>
                                        <td><span class="blood-type"><?php echo htmlspecialchars($blood_type); ?></span></td>
                                        <td><?php echo $quantity; ?> units</td>
                                        <td><?php echo $collection_date ? date('M j, Y', strtotime($collection_date)) : 'N/A'; ?></td>
                                        <td><?php echo $expiry_date ? date('M j, Y', strtotime($expiry_date)) : 'N/A'; ?></td>
                                        <td>
                                            <span class="days-left <?php 
                                                echo $days_left <= 3 ? 'critical' : ($days_left <= 7 ? 'warning' : ''); 
                                            ?>">
                                                <?php echo $days_left; ?> days
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($donor_name); ?></td>
                                        <td>
                                            <span class="status status-<?php echo $status; ?>">
                                                <?php echo ucfirst($status); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <button class="btn-sm btn-secondary" onclick="viewInventoryDetails(<?php echo $item_id; ?>)">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <?php if ($status === 'available'): ?>
                                                <button class="btn-sm btn-warning" onclick="markAsUsed(<?php echo $item_id; ?>)">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="9" class="text-center">No inventory items found.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Add Inventory Modal -->
    <div id="addInventoryModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Add Blood Unit to Inventory</h3>
                <button class="modal-close" onclick="closeAddInventoryModal()">&times;</button>
            </div>
            
            <form id="addInventoryForm">
                <div class="form-grid two-columns">
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
                        <input type="number" name="quantity" min="1" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="collection_date">Collection Date *</label>
                        <input type="date" name="collection_date" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="expiry_date">Expiry Date *</label>
                        <input type="date" name="expiry_date" required>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeAddInventoryModal()">
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Add to Inventory
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="../assets/js/main.js"></script>
    <script>
        // Initialize data table
        if (typeof DataTable !== 'undefined') {
            new DataTable('inventoryTable', {
                searchable: true,
                sortable: true,
                pagination: true,
                pageSize: 20
            });
        }

        // Add inventory modal functions
        function openAddInventoryModal() {
            document.getElementById('addInventoryModal').classList.add('active');
        }

        function closeAddInventoryModal() {
            document.getElementById('addInventoryModal').classList.remove('active');
            document.getElementById('addInventoryForm').reset();
        }

        // Handle add inventory form submission
        document.getElementById('addInventoryForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const data = Object.fromEntries(formData);
            
            try {
                const response = await fetch('../ajax/blood_operations.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'add_inventory',
                        ...data
                    })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    alert(result.message);
                    closeAddInventoryModal();
                    setTimeout(() => location.reload(), 1500);
                } else {
                    alert(result.message || 'Error adding inventory');
                }
            } catch (error) {
                alert('Error: ' + error.message);
            }
        });

        // View inventory details
        function viewInventoryDetails(id) {
            alert('Feature coming soon!');
        }

        // Mark as used
        async function markAsUsed(id) {
            if (confirm('Mark this blood unit as used?')) {
                try {
                    const response = await fetch('../ajax/blood_operations.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            action: 'mark_as_used',
                            id: id
                        })
                    });
                    
                    const result = await response.json();
                    
                    if (result.success) {
                        alert(result.message);
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        alert(result.message || 'Error updating inventory');
                    }
                } catch (error) {
                    alert('Error: ' + error.message);
                }
            }
        }

        // Search functionality
        document.getElementById('searchInventory').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const rows = document.querySelectorAll('#inventoryTable tbody tr');
            
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

        .blood-type-large {
            font-weight: bold;
            font-size: 1.25rem;
        }

        .expiring-warning {
            color: #d97706;
            font-size: 0.8rem;
            display: block;
            margin-top: 0.25rem;
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

        .urgent-expiry {
            background-color: #fef3f2;
        }

        .days-left.critical {
            color: #dc2626;
            font-weight: bold;
        }

        .days-left.warning {
            color: #d97706;
            font-weight: bold;
        }

        .text-center {
            text-align: center;
            padding: 2rem;
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