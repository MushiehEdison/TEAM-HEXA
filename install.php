<?php
// install.php
require_once 'classes/Database.php';

$message = '';
$error = '';

if ($_POST) {
    try {
        $database = new Database();
        $db = $database->connect();
        
        if ($db) {
            // Create tables
            $database->createTables();
            
            // Insert sample data
            insertSampleData($db);
            
            $message = 'Database installation completed successfully! You can now login with username: admin, password: admin123';
        } else {
            $error = 'Failed to connect to database. Please check your configuration.';
        }
    } catch (Exception $e) {
        $error = 'Installation failed: ' . $e->getMessage();
    }
}

function insertSampleData($db) {
    // Insert sample donors
    $donors = [
        ['John Doe', '123-456-7890', 'john@email.com', 'A+', '123 Main St'],
        ['Jane Smith', '234-567-8901', 'jane@email.com', 'B+', '456 Oak Ave'],
        ['Mike Johnson', '345-678-9012', 'mike@email.com', 'O+', '789 Pine St'],
        ['Sarah Wilson', '456-789-0123', 'sarah@email.com', 'AB+', '321 Elm St'],
        ['David Brown', '567-890-1234', 'david@email.com', 'A-', '654 Cedar Rd']
    ];
    
    foreach ($donors as $donor) {
        $query = "INSERT INTO donors (full_name, phone, email, blood_type, address) VALUES (?, ?, ?, ?, ?)";
        $stmt = $db->prepare($query);
        $stmt->execute($donor);
    }
    
    // Insert sample blood inventory
    $inventory = [
        ['A+', 15, '2024-01-15', '2024-02-19', 1],
        ['B+', 12, '2024-01-18', '2024-02-22', 2],
        ['O+', 25, '2024-01-20', '2024-02-24', 3],
        ['AB+', 8, '2024-01-22', '2024-02-26', 4],
        ['A-', 10, '2024-01-25', '2024-03-01', 5],
        ['B-', 6, '2024-01-28', '2024-03-04', null],
        ['O-', 18, '2024-01-30', '2024-03-06', null],
        ['AB-', 4, '2024-02-01', '2024-03-08', null]
    ];
    
    foreach ($inventory as $item) {
        $query = "INSERT INTO blood_inventory (blood_type, quantity, collection_date, expiry_date, donor_id) VALUES (?, ?, ?, ?, ?)";
        $stmt = $db->prepare($query);
        $stmt->execute($item);
    }
    
    // Insert sample blood requests
    $requests = [
        ['Emergency Patient', 'O+', 3, 'critical', 'City Hospital', 'Dr. Smith', '555-0001', '2024-02-01', '2024-02-02'],
        ['Surgery Patient', 'A+', 2, 'high', 'General Hospital', 'Dr. Jones', '555-0002', '2024-02-02', '2024-02-05'],
        ['Cancer Patient', 'B+', 4, 'medium', 'Cancer Center', 'Dr. Wilson', '555-0003', '2024-02-03', '2024-02-10'],
        ['Accident Victim', 'O-', 5, 'critical', 'Trauma Center', 'Dr. Brown', '555-0004', '2024-02-04', '2024-02-04']
    ];
    
    foreach ($requests as $request) {
        $query = "INSERT INTO blood_requests (patient_name, blood_type, quantity_needed, urgency, hospital_name, contact_person, phone, request_date, needed_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $db->prepare($query);
        $stmt->execute($request);
    }
    
    // Insert sample donations
    $donations = [
        [1, 'A+', 1, '2024-01-15', 14.5, '120/80', 98.6, 'approved', 'Healthy donor'],
        [2, 'B+', 1, '2024-01-18', 13.8, '118/75', 98.4, 'approved', 'Regular donor'],
        [3, 'O+', 1, '2024-01-20', 15.2, '125/82', 98.8, 'approved', 'First time donor'],
        [4, 'AB+', 1, '2024-01-22', 14.0, '115/70', 98.5, 'approved', 'Frequent donor'],
        [5, 'A-', 1, '2024-01-25', 13.5, '110/68', 98.3, 'approved', 'Healthy donor']
    ];
    
    foreach ($donations as $donation) {
        $query = "INSERT INTO donations (donor_id, blood_type, quantity, donation_date, hemoglobin_level, blood_pressure, temperature, status, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $db->prepare($query);
        $stmt->execute($donation);
    }
    
    // Generate forecasting data for the last 90 days
    $blood_types = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
    $start_date = date('Y-m-d', strtotime('-90 days'));
    
    for ($i = 0; $i < 90; $i++) {
        $date = date('Y-m-d', strtotime($start_date . ' + ' . $i . ' days'));
        $month = date('n', strtotime($date));
        
        // Determine season
        $season = 'winter';
        if (in_array($month, [3,4,5])) $season = 'spring';
        elseif (in_array($month, [6,7,8])) $season = 'summer';
        elseif (in_array($month, [9,10,11])) $season = 'fall';
        
        // Is weekend/holiday
        $holiday = date('N', strtotime($date)) > 5 ? 1 : 0;
        
        foreach ($blood_types as $blood_type) {
            // Generate realistic sample data with some randomness
            $base_quantity = rand(5, 20);
            $base_demand = rand(3, 15);
            $base_supply = rand(4, 18);
            $expiry_count = rand(0, 3);
            
            // Adjust for blood type frequency
            if (in_array($blood_type, ['O+', 'A+'])) {
                $base_quantity *= 1.5;
                $base_demand *= 1.5;
                $base_supply *= 1.5;
            } elseif (in_array($blood_type, ['AB-', 'B-'])) {
                $base_quantity *= 0.5;
                $base_demand *= 0.5;
                $base_supply *= 0.5;
            }
            
            $query = "INSERT INTO forecasting_data (date, blood_type, quantity, demand, supply, expiry_count, season, holiday) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $db->prepare($query);
            $stmt->execute([$date, $blood_type, $base_quantity, $base_demand, $base_supply, $expiry_count, $season, $holiday]);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blood Bank System Installation</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .install-container {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 500px;
        }
        
        .install-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .install-header i {
            font-size: 3rem;
            color: #dc3545;
            margin-bottom: 1rem;
        }
        
        .install-header h1 {
            color: #333;
            font-size: 1.8rem;
            margin-bottom: 0.5rem;
        }
        
        .install-header p {
            color: #666;
            font-size: 0.9rem;
        }
        
        .alert {
            padding: 1rem;
            border-radius: 5px;
            margin-bottom: 1rem;
        }
        
        .alert-success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        
        .alert-error {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        
        .btn {
            width: 100%;
            padding: 0.75rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            cursor: pointer;
            transition: transform 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }
        
        .btn:hover {
            transform: translateY(-2px);
        }
        
        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        
        .info-section {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 5px;
            margin-bottom: 1rem;
        }
        
        .info-section h3 {
            color: #495057;
            margin-bottom: 0.5rem;
            font-size: 1rem;
        }
        
        .info-section ul {
            margin: 0;
            padding-left: 1.5rem;
            color: #6c757d;
            font-size: 0.9rem;
        }
        
        .info-section li {
            margin-bottom: 0.25rem;
        }
        
        .login-info {
            background: #e3f2fd;
            border-left: 4px solid #2196f3;
            padding: 1rem;
            border-radius: 5px;
            margin-top: 1rem;
        }
        
        .login-info h4 {
            color: #1565c0;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }
        
        .login-info p {
            margin: 0.25rem 0;
            font-size: 0.8rem;
            color: #1976d2;
        }
        
        .footer {
            text-align: center;
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid #eee;
            color: #666;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="install-container">
        <div class="install-header">
            <i class="fas fa-database"></i>
            <h1>System Installation</h1>
            <p>Blood Bank Management & Forecasting System</p>
        </div>
        
        <?php if ($message): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($message); ?>
            </div>
            
            <div class="login-info">
                <h4><i class="fas fa-info-circle"></i> Login Credentials</h4>
                <p><strong>Username:</strong> admin</p>
                <p><strong>Password:</strong> admin123</p>
                <p><strong>URL:</strong> <a href="login.php">login.php</a></p>
            </div>
        <?php elseif ($error): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php else: ?>
            <div class="info-section">
                <h3><i class="fas fa-list"></i> Installation will create:</h3>
                <ul>
                    <li>Database tables for blood bank management</li>
                    <li>Admin user account (admin/admin123)</li>
                    <li>Sample donors and blood inventory</li>
                    <li>Sample blood requests and donations</li>
                    <li>Forecasting data for regression analysis</li>
                </ul>
            </div>
            
            <div class="info-section">
                <h3><i class="fas fa-cog"></i> Requirements:</h3>
                <ul>
                    <li>PHP 7.4 or higher</li>
                    <li>MySQL 5.7 or higher</li>
                    <li>PDO extension enabled</li>
                    <li>Write permissions for uploads/ directory</li>
                </ul>
            </div>
        <?php endif; ?>
        
        <?php if (!$message): ?>
        <form method="POST" action="">
            <button type="submit" class="btn" <?php echo $error ? '' : ''; ?>>
                <i class="fas fa-play"></i>
                <?php echo $error ? 'Retry Installation' : 'Start Installation'; ?>
            </button>
        </form>
        <?php else: ?>
        <a href="login.php" class="btn" style="text-decoration: none;">
            <i class="fas fa-sign-in-alt"></i>
            Go to Login Page
        </a>
        <?php endif; ?>
        
        <div class="footer">
            <p>&copy; 2024 Blood Bank Management System</p>
            <p>Complete system with Linear Regression Forecasting</p>
        </div>
    </div>
</body>
</html>