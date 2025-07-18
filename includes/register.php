<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = sanitizeInput($_POST['first_name']);
    $lastName = sanitizeInput($_POST['last_name']);
    $email = sanitizeInput($_POST['email']);
    $dob = sanitizeInput($_POST['dob']);
    $password = password_hash(sanitizeInput($_POST['password']), PASSWORD_BCRYPT);
    
    // Check if email already exists
    $stmt = $conn->prepare("SELECT * FROM patients WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $error = "Email already registered";
    } else {
        // Insert new patient
        $stmt = $conn->prepare("INSERT INTO patients (first_name, last_name, email, dob) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $firstName, $lastName, $email, $dob);
        
        if ($stmt->execute()) {
            // Also create user account
            $patientId = $conn->insert_id;
            $username = strtolower($firstName . $lastName);
            
            $stmt = $conn->prepare("INSERT INTO users (username, password, email, role) VALUES (?, ?, ?, 'patient')");
            $stmt->bind_param("sss", $username, $password, $email);
            
            if ($stmt->execute()) {
                $_SESSION['patient_id'] = $patientId;
                $_SESSION['patient_name'] = $firstName . ' ' . $lastName;
                header("Location: ../patient-protal/dashboard.php");
                exit();
            }
        } else {
            $error = "Registration failed. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MedCare - Patient Registration</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* ===== VARIABLES ===== */
        :root {
            --primary-blue: #3a7bd5;
            --primary-pink: #ff758c;
            --light-blue: #00d2ff;
            --light-pink: #ff7eb3;
            --dark-blue: #1a4b8c;
            --dark-pink: #e84393;
            --white: #ffffff;
            --light-gray: #f8f9fa;
            --dark-gray: #343a40;
            --border-radius: 12px;
            --box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }

        /* ===== BASE STYLES ===== */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', 'Segoe UI', sans-serif;
        }

        body {
            background: linear-gradient(135deg, var(--primary-blue), var(--light-blue));
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
            color: var(--dark-gray);
        }

        @font-face {
            font-family: 'Poppins';
            font-style: normal;
            font-weight: 400;
            src: url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap');
        }

        /* ===== REGISTRATION CONTAINER ===== */
        .registration-container {
            background: var(--white);
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            width: 100%;
            max-width: 500px;
            padding: 40px;
            text-align: center;
            position: relative;
            overflow: hidden;
            animation: fadeIn 0.6s ease-out;
        }

        .registration-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 8px;
            background: linear-gradient(90deg, var(--primary-pink), var(--light-pink));
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px) scale(0.98); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }

        /* ===== HEADER ===== */
        .registration-container h2 {
            color: var(--primary-blue);
            margin-bottom: 30px;
            font-size: 28px;
            font-weight: 600;
            position: relative;
            display: inline-block;
        }

        .registration-container h2::after {
            content: '';
            position: absolute;
            bottom: -8px;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 3px;
            background: linear-gradient(90deg, var(--primary-pink), var(--light-pink));
            border-radius: 3px;
        }

        /* ===== FORM STYLES ===== */
        .form-group {
            margin-bottom: 25px;
            text-align: left;
            position: relative;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--dark-gray);
            font-size: 15px;
        }

        .form-group input {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid #e9ecef;
            border-radius: var(--border-radius);
            font-size: 16px;
            transition: var(--transition);
            background-color: var(--light-gray);
        }

        .form-group input:focus {
            outline: none;
            border-color: var(--primary-blue);
            background-color: var(--white);
            box-shadow: 0 0 0 4px rgba(58, 123, 213, 0.2);
        }

        /* ===== DATE INPUT STYLING ===== */
        .form-group input[type="date"] {
            position: relative;
            padding-right: 40px;
        }

        .form-group input[type="date"]::-webkit-calendar-picker-indicator {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            width: auto;
            height: auto;
            color: transparent;
            background: transparent;
        }

        .form-group input[type="date"]::after {
            content: "\f073";
            font-family: "Font Awesome 6 Free";
            font-weight: 900;
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--primary-pink);
            pointer-events: none;
        }

        /* ===== PASSWORD STRENGTH ===== */
        .password-strength {
            height: 5px;
            background: #e9ecef;
            border-radius: 5px;
            margin-top: 8px;
            overflow: hidden;
            position: relative;
        }

        .strength-meter {
            height: 100%;
            width: 0;
            background: var(--primary-pink);
            transition: var(--transition);
        }

        /* ===== BUTTON ===== */
        .btn-register {
            display: inline-block;
            width: 100%;
            padding: 14px;
            border: none;
            border-radius: var(--border-radius);
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            text-align: center;
            text-decoration: none;
            margin-top: 10px;
            position: relative;
            overflow: hidden;
            z-index: 1;
        }

        .btn-primary {
            background: linear-gradient(90deg, var(--primary-pink), var(--light-pink));
            color: var(--white);
        }

        .btn-primary:hover {
            background: linear-gradient(90deg, var(--light-pink), var(--primary-pink));
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
        }

        .btn-primary:active {
            transform: translateY(0);
        }

        /* ===== ALERTS ===== */
        .alert {
            padding: 15px;
            margin-bottom: 25px;
            border-radius: var(--border-radius);
            font-size: 14px;
            text-align: center;
            animation: slideDown 0.3s ease-out;
        }

        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .alert-danger {
            background-color: rgba(255, 117, 140, 0.15);
            color: var(--dark-pink);
            border: 1px solid rgba(255, 117, 140, 0.3);
        }

        /* ===== LOGIN LINK ===== */
        .login-link {
            margin-top: 25px;
            text-align: center;
            font-size: 14px;
            color: var(--dark-gray);
        }

        .login-link a {
            color: var(--primary-blue);
            text-decoration: none;
            font-weight: 500;
            transition: var(--transition);
        }

        .login-link a:hover {
            color: var(--dark-blue);
            text-decoration: underline;
        }

        /* ===== RESPONSIVE DESIGN ===== */
        @media (max-width: 576px) {
            .registration-container {
                padding: 30px 20px;
            }
            
            .registration-container h2 {
                font-size: 24px;
            }
        }

        /* ===== LOADING STATE ===== */
        .btn-loading {
            position: relative;
            pointer-events: none;
        }

        .btn-loading::after {
            content: "";
            position: absolute;
            width: 20px;
            height: 20px;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            margin: auto;
            border: 3px solid transparent;
            border-top-color: white;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="registration-container">
        <h2>Create Your MedCare Account</h2>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form method="POST" action="register.php" id="registerForm">
            <div class="form-group">
                <label for="first_name">First Name</label>
                <input type="text" id="first_name" name="first_name" required placeholder="Enter your first name">
            </div>
            
            <div class="form-group">
                <label for="last_name">Last Name</label>
                <input type="text" id="last_name" name="last_name" required placeholder="Enter your last name">
            </div>
            
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" required placeholder="your@email.com">
            </div>
            
            <div class="form-group">
                <label for="dob">Date of Birth</label>
                <input type="date" id="dob" name="dob" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password (min 8 characters)</label>
                <input type="password" id="password" name="password" required minlength="8" placeholder="Create a password">
                <div class="password-strength">
                    <div class="strength-meter" id="strengthMeter"></div>
                </div>
            </div>
            
            <button type="submit" class="btn-register btn-primary" id="registerBtn">Register</button>
            
            <div class="login-link">
                Already have an account? <a href="../patient-portal/login.php">Login here</a>
            </div>
        </form>
    </div>

    <script>
        // Add loading state to button
        document.getElementById('registerForm').addEventListener('submit', function() {
            const btn = document.getElementById('registerBtn');
            btn.classList.add('btn-loading');
            btn.innerHTML = ''; // Remove text while loading
        });

        // Set max date for date of birth (must be at least 1 year old)
        const dobInput = document.getElementById('dob');
        const today = new Date();
        const maxDate = new Date();
        maxDate.setFullYear(today.getFullYear() - 1);
        
        dobInput.max = maxDate.toISOString().split('T')[0];
        dobInput.min = '1900-01-01'; // Set reasonable minimum date

        // Password strength indicator
        const passwordInput = document.getElementById('password');
        const strengthMeter = document.getElementById('strengthMeter');
        
        passwordInput.addEventListener('input', function() {
            const password = this.value;
            let strength = 0;
            
            // Length check
            if (password.length >= 8) strength += 1;
            if (password.length >= 12) strength += 1;
            
            // Character type checks
            if (/[A-Z]/.test(password)) strength += 1;
            if (/[0-9]/.test(password)) strength += 1;
            if (/[^A-Za-z0-9]/.test(password)) strength += 1;
            
            // Update strength meter
            const width = (strength / 5) * 100;
            strengthMeter.style.width = width + '%';
            
            // Update color based on strength
            if (strength <= 2) {
                strengthMeter.style.backgroundColor = 'var(--primary-pink)';
            } else if (strength <= 4) {
                strengthMeter.style.backgroundColor = 'var(--light-blue)';
            } else {
                strengthMeter.style.backgroundColor = 'var(--primary-blue)';
            }
        });

        // Add focus effects to inputs
        document.querySelectorAll('.form-group input').forEach(input => {
            input.addEventListener('focus', function() {
                this.style.borderColor = 'var(--primary-blue)';
                this.style.boxShadow = '0 0 0 4px rgba(58, 123, 213, 0.2)';
            });
            
            input.addEventListener('blur', function() {
                this.style.borderColor = '#e9ecef';
                this.style.boxShadow = 'none';
            });
        });
    </script>
</body>
</html>