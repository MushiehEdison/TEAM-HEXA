<?php
require_once 'includes/auth.php';
requireAuth();
require_once 'includes/functions.php';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_patient'])) {
        $firstName = sanitizeInput($_POST['first_name']);
        $lastName = sanitizeInput($_POST['last_name']);
        $email = sanitizeInput($_POST['email']);
        $phone = sanitizeInput($_POST['phone']);
        $dob = sanitizeInput($_POST['dob']);
        
        if (addPatient($firstName, $lastName, $email, $phone, $dob)) {
            $success = "Patient added successfully!";
        } else {
            $error = "Failed to add patient.";
        }
    }
}

// Get all patients
$patients = []; // This would come from a database query in a real application
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient System - Patients</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/patients.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main class="container">
        <h1>Patient Management</h1>
        
        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <div class="patient-management">
            <section class="add-patient">
                <h2>Add New Patient</h2>
                <form method="POST">
                    <div class="form-group">
                        <label for="first_name">First Name</label>
                        <input type="text" id="first_name" name="first_name" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="last_name">Last Name</label>
                        <input type="text" id="last_name" name="last_name" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="phone">Phone</label>
                        <input type="tel" id="phone" name="phone">
                    </div>
                    
                    <div class="form-group">
                        <label for="dob">Date of Birth</label>
                        <input type="date" id="dob" name="dob">
                    </div>
                    
                    <button type="submit" name="add_patient" class="btn btn-primary">Add Patient</button>
                </form>
            </section>
            
            <section class="patient-list">
                <h2>Patient List</h2>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($patients as $patient): ?>
                        <tr>
                            <td><?php echo $patient['patient_id']; ?></td>
                            <td><?php echo $patient['first_name'] . ' ' . $patient['last_name']; ?></td>
                            <td><?php echo $patient['email']; ?></td>
                            <td><?php echo $patient['phone']; ?></td>
                            <td>
                                <a href="patient_details.php?id=<?php echo $patient['patient_id']; ?>" class="btn btn-info">View</a>
                                <a href="#" class="btn btn-warning">Edit</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </section>
        </div>
    </main>
    
    <script src="assets/js/patients.js"></script>
</body>
</html>