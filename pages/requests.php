<?php
// pages/blood-request.php
session_start();
require_once '../classes/Database.php';
require_once '../classes/Auth.php';
require_once '../classes/BloodRequest.php';

$auth = new Auth();
$auth->requireLogin();

$request = new BloodRequest();
$current_user = $auth->getCurrentUser();

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    try {
        switch ($_POST['action']) {
            case 'add_request':
                $response = $request->addRequest($_POST);
                break;
                
            case 'approve_request':
                $response = $request->approveRequest($_POST['id']);
                break;
                
            case 'reject_request':
                $response = $request->rejectRequest($_POST['id'], $_POST['reason']);
                break;
                
            case 'fulfill_request':
                $response = $request->fulfillRequest($_POST['id']);
                break;
                
            case 'get_request_details':
                $response = $request->getRequestDetails($_POST['id']);
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

// Get filter parameter
$filter = $_GET['filter'] ?? 'all';

// Get requests based on filter
if ($filter === 'pending') {
    $requests = $request->getPendingRequests();
} else if ($filter === 'approved') {
    $requests = $request->getApprovedRequests();
} else if ($filter === 'fulfilled') {
    $requests = $request->getFulfilledRequests();
} else {
    $requests = $request->getAllRequests();
}

$stats = $request->getRequestStats();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blood Requests - Blood Bank Management System</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <style>
        /* Main Styles matching inventory page */
        .blood-type {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-weight: bold;
            background-color: #f0f0f0;
        }
        
        .status {
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .status-approved {
            background-color: #d4edda;
            color: #155724;
        }
        
        .status-rejected {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .status-fulfilled {
            background-color: #cce5ff;
            color: #004085;
        }
        
        .urgency-low {
            color: #28a745;
        }
        
        .urgency-medium {
            color: #ffc107;
        }
        
        .urgency-high {
            color: #fd7e14;
        }
        
        .urgency-critical {
            color: #dc3545;
            font-weight: bold;
        }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: white;
            border-radius: 8px;
            padding: 1rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
        }
        
        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
            color: white;
            font-size: 1.5rem;
        }
        
        .stat-icon.total-requests {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .stat-icon.pending-requests {
            background: linear-gradient(135deg, #f6d365 0%, #fda085 100%);
        }
        
        .stat-icon.approved-requests {
            background: linear-gradient(135deg, #84fab0 0%, #8fd3f4 100%);
        }
        
        .stat-icon.fulfilled-requests {
            background: linear-gradient(135deg, #a18cd1 0%, #fbc2eb 100%);
        }
        
        .stat-content h3 {
            margin: 0;
            font-size: 1.5rem;
            color: #333;
        }
        
        .stat-content p {
            margin: 0.25rem 0 0;
            color: #666;
            font-size: 0.9rem;
        }

        /* Table Styles */
        .table-container {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .table-header {
            padding: 1rem;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .table-header h3 {
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
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
            width: 200px;
        }
        
        .search-box i {
            position: absolute;
            left: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            color: #666;
        }
        
        .table-responsive {
            overflow-x: auto;
        }
        
        .data-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .data-table th {
            background: #f8f9fa;
            padding: 0.75rem 1rem;
            text-align: left;
            font-weight: 600;
            color: #555;
        }
        
        .data-table td {
            padding: 0.75rem 1rem;
            border-bottom: 1px solid #eee;
            vertical-align: middle;
        }
        
        .data-table tr:last-child td {
            border-bottom: none;
        }
        
        .data-table tr:hover {
            background-color: #f9f9f9;
        }
        
        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 0.25rem;
        }
        
        .btn {
            padding: 0.5rem 1rem;
            border-radius: 4px;
            border: none;
            cursor: pointer;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .btn-primary:hover {
            opacity: 0.9;
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn-success {
            background: #28a745;
            color: white;
        }
        
        .btn-danger {
            background: #dc3545;
            color: white;
        }
        
        .btn-warning {
            background: #ffc107;
            color: #212529;
        }
        
        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.8rem;
        }
        
        /* Filter Tabs */
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
        
        /* Page Actions */
        .page-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }
        
        /* Modal Styles */
        .modal {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s;
        }
        
        .modal.active {
            opacity: 1;
            visibility: visible;
        }
        
        .modal-content {
            background: white;
            border-radius: 8px;
            width: 90%;
            max-width: 600px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        
        .modal-header {
            padding: 1rem;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .modal-header h3 {
            margin: 0;
        }
        
        .modal-close {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: #666;
        }
        
        .modal-body {
            padding: 1rem;
        }
        
        .modal-footer {
            padding: 1rem;
            border-top: 1px solid #eee;
            display: flex;
            justify-content: flex-end;
            gap: 0.5rem;
        }
        
        /* Form Styles */
        .form-grid {
            display: grid;
            gap: 1rem;
        }
        
        .two-columns {
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        }
        
        .form-group {
            margin-bottom: 1rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #555;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
        }
        
        .form-group textarea {
            min-height: 100px;
        }
        
        .full-width {
            grid-column: 1 / -1;
        }
        
        /* Request Details */
        .request-details {
            padding: 1rem;
        }
        
        .detail-row {
            display: flex;
            margin-bottom: 0.75rem;
            align-items: center;
        }
        
        .detail-label {
            font-weight: 600;
            width: 150px;
            color: #555;
        }
        
        .detail-value {
            flex: 1;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .page-actions {
                flex-direction: column;
                gap: 1rem;
            }
            
            .filter-tabs {
                width: 100%;
                justify-content: space-around;
            }
            
            .stats-grid {
                grid-template-columns: 1fr 1fr;
            }
            
            .modal-content {
                width: 95%;
            }
            
            .detail-row {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .detail-label {
                width: 100%;
                margin-bottom: 0.25rem;
            }
        }
    </style>
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
                    <li class="active">
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
                    <h1>Blood Requests</h1>
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
                        <a href="requests.php" class="filter-tab <?php echo $filter === 'all' ? 'active' : ''; ?>">
                            <i class="fas fa-list"></i> All Requests
                        </a>
                        <a href="requests.php?filter=pending" class="filter-tab <?php echo $filter === 'pending' ? 'active' : ''; ?>">
                            <i class="fas fa-clock"></i> Pending
                        </a>
                        <a href="requests.php?filter=approved" class="filter-tab <?php echo $filter === 'approved' ? 'active' : ''; ?>">
                            <i class="fas fa-check-circle"></i> Approved
                        </a>
                        <a href="requests.php?filter=fulfilled" class="filter-tab <?php echo $filter === 'fulfilled' ? 'active' : ''; ?>">
                            <i class="fas fa-check-double"></i> Fulfilled
                        </a>
                    </div>
                    
                    <button class="btn btn-primary" onclick="openAddRequestModal()">
                        <i class="fas fa-plus"></i> New Request
                    </button>
                </div>

                <!-- Request Stats Cards -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon total-requests">
                            <i class="fas fa-clipboard-list"></i>
                        </div>
                        <div class="stat-content">
                            <h3><?php echo $stats['total_requests']; ?></h3>
                            <p>Total Requests</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon pending-requests">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="stat-content">
                            <h3><?php echo $stats['pending_requests']; ?></h3>
                            <p>Pending Requests</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon approved-requests">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="stat-content">
                            <h3><?php echo $stats['approved_requests']; ?></h3>
                            <p>Approved Requests</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon fulfilled-requests">
                            <i class="fas fa-check-double"></i>
                        </div>
                        <div class="stat-content">
                            <h3><?php echo $stats['fulfilled_requests']; ?></h3>
                            <p>Fulfilled Requests</p>
                        </div>
                    </div>
                </div>

                <!-- Requests Table -->
                <div class="table-container">
                    <div class="table-header">
                        <h3>
                            <?php if ($filter === 'pending'): ?>
                                <i class="fas fa-clock"></i> Pending Requests
                            <?php elseif ($filter === 'approved'): ?>
                                <i class="fas fa-check-circle"></i> Approved Requests
                            <?php elseif ($filter === 'fulfilled'): ?>
                                <i class="fas fa-check-double"></i> Fulfilled Requests
                            <?php else: ?>
                                <i class="fas fa-clipboard-list"></i> All Blood Requests
                            <?php endif; ?>
                        </h3>
                        <div class="table-actions">
                            <div class="search-box">
                                <input type="text" placeholder="Search requests..." id="searchRequests">
                                <i class="fas fa-search"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="data-table" id="requestsTable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Patient</th>
                                    <th>Blood Type</th>
                                    <th>Quantity</th>
                                    <th>Urgency</th>
                                    <th>Request Date</th>
                                    <th>Needed By</th>
                                    <th>Hospital</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($requests as $req): ?>
                                <tr>
                                    <td>#<?php echo str_pad($req['id'], 4, '0', STR_PAD_LEFT); ?></td>
                                    <td><?php echo htmlspecialchars($req['patient_name']); ?></td>
                                    <td><span class="blood-type"><?php echo $req['blood_type']; ?></span></td>
                                    <td><?php echo $req['quantity_needed']; ?> units</td>
                                    <td>
                                        <span class="urgency-<?php echo strtolower($req['urgency']); ?>">
                                            <?php echo ucfirst($req['urgency']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M j, Y', strtotime($req['request_date'])); ?></td>
                                    <td><?php echo date('M j, Y', strtotime($req['needed_by'])); ?></td>
                                    <td><?php echo htmlspecialchars($req['hospital_name']); ?></td>
                                    <td>
                                        <span class="status status-<?php echo $req['status']; ?>">
                                            <?php echo ucfirst($req['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="btn-sm btn-secondary" onclick="viewRequestDetails(<?php echo $req['id']; ?>)">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <?php if ($req['status'] === 'pending' && $auth->isAdmin()): ?>
                                            <button class="btn-sm btn-success" onclick="approveRequest(<?php echo $req['id']; ?>)">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button class="btn-sm btn-danger" onclick="rejectRequest(<?php echo $req['id']; ?>)">
                                                <i class="fas fa-times"></i>
                                            </button>
                                            <?php endif; ?>
                                            <?php if ($req['status'] === 'approved'): ?>
                                            <button class="btn-sm btn-primary" onclick="fulfillRequest(<?php echo $req['id']; ?>)">
                                                <i class="fas fa-check-double"></i>
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

    <!-- Add Request Modal -->
    <div id="addRequestModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>New Blood Request</h3>
                <button class="modal-close" onclick="closeAddRequestModal()">&times;</button>
            </div>
            
            <form id="addRequestForm">
                <div class="form-grid two-columns">
                    <div class="form-group">
                        <label for="patient_name">Patient Name *</label>
                        <input type="text" name="patient_name" required>
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
                        <label for="quantity_needed">Quantity Needed (units) *</label>
                        <input type="number" name="quantity_needed" min="1" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="urgency">Urgency *</label>
                        <select name="urgency" required>
                            <option value="low">Low</option>
                            <option value="medium" selected>Medium</option>
                            <option value="high">High</option>
                            <option value="critical">Critical</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="hospital_name">Hospital Name *</label>
                        <input type="text" name="hospital_name" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="contact_person">Contact Person *</label>
                        <input type="text" name="contact_person" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="phone">Contact Phone *</label>
                        <input type="text" name="phone" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="request_date">Request Date *</label>
                        <input type="date" name="request_date" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="needed_by">Needed By Date *</label>
                        <input type="date" name="needed_by" required>
                    </div>
                    
                    <div class="form-group full-width">
                        <label for="notes">Notes</label>
                        <textarea name="notes" rows="3"></textarea>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeAddRequestModal()">
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Submit Request
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Request Details Modal -->
    <div id="requestDetailsModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Request Details</h3>
                <button class="modal-close" onclick="closeRequestDetailsModal()">&times;</button>
            </div>
            
            <div class="modal-body" id="requestDetailsContent">
                <!-- Content will be loaded via AJAX -->
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeRequestDetailsModal()">
                    Close
                </button>
            </div>
        </div>
    </div>

    <script src="../assets/js/main.js"></script>
    <script>
        // Initialize data table
        function initializeDataTable() {
            const table = document.getElementById('requestsTable');
            const headers = table.querySelectorAll('th');
            const rows = Array.from(table.querySelectorAll('tbody tr'));
            
            headers.forEach((header, index) => {
                header.addEventListener('click', () => {
                    const direction = header.dataset.sort === 'asc' ? 'desc' : 'asc';
                    
                    // Remove sort indicators from all headers
                    headers.forEach(h => {
                        h.dataset.sort = '';
                        h.querySelector('.sort-icon')?.remove();
                    });
                    
                    // Add sort indicator to current header
                    header.dataset.sort = direction;
                    const icon = document.createElement('i');
                    icon.className = `fas fa-sort-${direction === 'asc' ? 'up' : 'down'} sort-icon`;
                    icon.style.marginLeft = '0.5rem';
                    header.appendChild(icon);
                    
                    // Sort rows
                    rows.sort((a, b) => {
                        const aValue = a.children[index].textContent.trim();
                        const bValue = b.children[index].textContent.trim();
                        
                        if (!isNaN(aValue)) {
                            return direction === 'asc' ? aValue - bValue : bValue - aValue;
                        } else {
                            return direction === 'asc' 
                                ? aValue.localeCompare(bValue) 
                                : bValue.localeCompare(aValue);
                        }
                    });
                    
                    // Re-append sorted rows
                    rows.forEach(row => table.querySelector('tbody').appendChild(row));
                });
            });
        }
        
        // Initialize when DOM is loaded
        document.addEventListener('DOMContentLoaded', function() {
            initializeDataTable();
            
            // Set default dates for request form
            const today = new Date().toISOString().split('T')[0];
            document.querySelector('input[name="request_date"]').value = today;
            
            const nextWeek = new Date();
            nextWeek.setDate(nextWeek.getDate() + 7);
            document.querySelector('input[name="needed_by"]').value = nextWeek.toISOString().split('T')[0];
        });
        
        // Modal functions
        function openAddRequestModal() {
            document.getElementById('addRequestModal').classList.add('active');
        }
        
        function closeAddRequestModal() {
            document.getElementById('addRequestModal').classList.remove('active');
            document.getElementById('addRequestForm').reset();
        }
        
        function closeRequestDetailsModal() {
            document.getElementById('requestDetailsModal').classList.remove('active');
        }
        
        // Form submission
        document.getElementById('addRequestForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
            
            try {
                const formData = new FormData(this);
                const data = new URLSearchParams(formData);
                data.append('action', 'add_request');
                
                const response = await fetch('blood-request.php', {
                    method: 'POST',
                    body: data
                });
                const result = await response.json();
                
                if (result.success) {
                    alert(result.message);
                    closeAddRequestModal();
                    setTimeout(() => location.reload(), 1500);
                } else {
                    alert(result.message);
                }
            } catch (error) {
                alert('Error: ' + error.message);
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-save"></i> Submit Request';
            }
        });
        
        // View request details
        async function viewRequestDetails(id) {
            try {
                const response = await fetch('blood-request.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=get_request_details&id=${id}`
                });
                const result = await response.json();
                
                if (result.success) {
                    document.getElementById('requestDetailsContent').innerHTML = result.html;
                    document.getElementById('requestDetailsModal').classList.add('active');
                } else {
                    alert(result.message);
                }
            } catch (error) {
                alert('Error: ' + error.message);
            }
        }
        
        // Approve request
        async function approveRequest(id) {
            if (confirm('Approve this blood request?')) {
                try {
                    const response = await fetch('blood-request.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `action=approve_request&id=${id}`
                    });
                    const result = await response.json();
                    
                    if (result.success) {
                        alert(result.message);
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        alert(result.message);
                    }
                } catch (error) {
                    alert('Error: ' + error.message);
                }
            }
        }
        
        // Reject request
        async function rejectRequest(id) {
            const reason = prompt('Please enter reason for rejection:');
            if (reason !== null) {
                try {
                    const response = await fetch('blood-request.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `action=reject_request&id=${id}&reason=${encodeURIComponent(reason)}`
                    });
                    const result = await response.json();
                    
                    if (result.success) {
                        alert(result.message);
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        alert(result.message);
                    }
                } catch (error) {
                    alert('Error: ' + error.message);
                }
            }
        }
        
        // Fulfill request
        async function fulfillRequest(id) {
            if (confirm('Mark this request as fulfilled?')) {
                try {
                    const response = await fetch('blood-request.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `action=fulfill_request&id=${id}`
                    });
                    const result = await response.json();
                    
                    if (result.success) {
                        alert(result.message);
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        alert(result.message);
                    }
                } catch (error) {
                    alert('Error: ' + error.message);
                }
            }
        }
        
        // Search functionality
        document.getElementById('searchRequests').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const rows = document.querySelectorAll('#requestsTable tbody tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        });
        
        // Toggle sidebar
        function toggleSidebar() {
            document.querySelector('.sidebar').classList.toggle('collapsed');
            document.querySelector('.main-content').classList.toggle('expanded');
        }
    </script>
</body>
</html>