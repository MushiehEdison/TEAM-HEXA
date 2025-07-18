<?php
require_once 'includes/portal-auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $dob = $_POST['dob'];
    
    $patient = verifyPatientLogin($email, $dob);
    
    if ($patient) {
        $_SESSION['patient_id'] = $patient['patient_id'];
        $_SESSION['patient_name'] = $patient['first_name'] . ' ' . $patient['last_name'];
        $_SESSION['patient_email'] = $patient['email'];
        
        header("Location: dashboard.php");
        exit();
    } else {
        $error = "Invalid login credentials. Please try again.";
    }
}

if (isPatientLoggedIn()) {
    header("Location: dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Portal - Login</title>
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

        /* ===== LOGIN CONTAINER ===== */
        .login-container {
            background: var(--white);
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            width: 100%;
            max-width: 500px;
            overflow: hidden;
            animation: fadeIn 0.6s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px) scale(0.98); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }

        /* ===== HEADER ===== */
        .login-header {
            text-align: center;
            padding: 40px 30px;
            background: linear-gradient(135deg, var(--primary-blue), var(--primary-pink));
            color: white;
        }

        .login-header .logo {
            width: 80px;
            height: 80px;
            object-fit: contain;
            margin-bottom: 15px;
            filter: brightness(0) invert(1);
        }

        .login-header h1 {
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .login-header p {
            font-size: 16px;
            opacity: 0.9;
            font-weight: 300;
        }

        /* ===== FORM ===== */
        .login-form {
            padding: 30px;
        }

        .form-group {
            margin-bottom: 25px;
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

        /* ===== BUTTON ===== */
        .btn {
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
            margin: 0 30px 25px;
            border-radius: 8px;
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

        /* ===== HELP LINKS ===== */
        .login-help {
            margin-top: 25px;
            text-align: center;
            font-size: 14px;
        }

        .login-help a {
            color: var(--primary-blue);
            text-decoration: none;
            font-weight: 500;
            transition: var(--transition);
        }

        .login-help a:hover {
            color: var(--dark-blue);
            text-decoration: underline;
        }

        .login-help p {
            color: var(--dark-gray);
            margin-top: 10px;
        }

        /* ===== RESPONSIVE DESIGN ===== */
        @media (max-width: 576px) {
            .login-header {
                padding: 30px 20px;
            }
            
            .login-form {
                padding: 25px 20px;
            }
            
            .login-header h1 {
                font-size: 24px;
            }
            
            .login-header p {
                font-size: 14px;
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
    <div class="login-container">
        <div class="login-header">
            <i class="fas fa-heartbeat"></i>
            <h1>Patient Portal</h1>
            <p>Access your health information anytime, anywhere</p>
        </div>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form class="login-form" method="POST" id="loginForm">
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" required placeholder="your@email.com">
            </div>
            
            <div class="form-group">
                <label for="dob">Date of Birth</label>
                <input type="date" id="dob" name="dob" required>
            </div>
            
            <button type="submit" class="btn btn-primary" id="loginBtn">Sign In</button>
            
            <div class="login-help">
                <a href="../includes/register.php">register?</a>
                <p>Don't have an account? Contact our office to register.</p>
            </div>
        </form>
    </div>

    <script>
        // Add loading state to button
        document.getElementById('loginForm').addEventListener('submit', function() {
            const btn = document.getElementById('loginBtn');
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