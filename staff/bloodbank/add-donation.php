<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../includes/staff-auth.php';
requireStaffAuth();

if (!$_SESSION['staff_permissions']['manage_bloodbank']) {
    header("Location: ../dashboard.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $donorId = $_POST['donor_id'] ?? null;
    $bloodGroup = $_POST['blood_group'];
    $units = $_POST['units'];
    $donationDate = $_POST['donation_date'];
    
    // Calculate expiry date (42 days from donation)
    $expiryDate = date('Y-m-d', strtotime($donationDate . ' +42 days'));
    
    $stmt = $conn->prepare("INSERT INTO blood_donations 
        (donor_id, blood_group_id, units_donated, donation_date, expiry_date) 
        VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("iidss", $donorId, $bloodGroup, $units, $donationDate, $expiryDate);
    
    if ($stmt->execute()) {
        // Update inventory
        $updateStmt = $conn->prepare("UPDATE blood_inventory 
            SET units_available = units_available + ? 
            WHERE blood_group_id = ?");
        $updateStmt->bind_param("di", $units, $bloodGroup);
        $updateStmt->execute();
        
        $_SESSION['bloodbank_message'] = "Donation recorded successfully!";
        header("Location: index.php");
        exit();
    } else {
        $error = "Failed to record donation. Please try again.";
    }
}

// Get blood groups
$bloodGroups = [];
$result = $conn->query("SELECT id, group_name FROM blood_groups ORDER BY group_name");
while ($row = $result->fetch_assoc()) {
    $bloodGroups[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Record Blood Donation</title>
    <link rel="stylesheet" href="../assets/css/staff.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Record New Blood Donation</h2>
                <a href="index.php" class="btn btn-secondary">Back to Blood Bank</a>
            </div>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST" class="blood-form">
                <div class="form-row">
                    <div class="form-group">
                        <label for="donor_id">Donor ID (optional)</label>
                        <input type="number" id="donor_id" name="donor_id">
                    </div>
                    
                    <div class="form-group">
                        <label for="blood_group">Blood Group</label>
                        <select id="blood_group" name="blood_group" required>
                            <option value="">Select Blood Group</option>
                            <?php foreach ($bloodGroups as $group): ?>
                                <option value="<?php echo $group['id']; ?>">
                                    <?php echo $group['group_name']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="units">Units Donated</label>
                        <input type="number" id="units" name="units" step="0.5" min="0.5" max="2" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="donation_date">Donation Date</label>
                        <input type="date" id="donation_date" name="donation_date" 
                               value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Record Donation</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>