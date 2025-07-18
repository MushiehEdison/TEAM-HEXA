<?php
session_start();
require_once '../config/database.php';
require_once '../staff/includes/staff-auth.php'; // Contains your verifyStaffLogin function

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitizeInput($_POST['username']);
    $password = sanitizeInput($_POST['password']);
    
    $staff = verifyStaffLogin($username, $password);
    
    if ($staff) {
        // Login successful - redirect to dashboard
        header("Location: dashboard.php");
        exit();
    } else {
        // Login failed - set error message
        $error = "Invalid username or password";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Portal - Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* ===== VARIABLES ===== */
        :root {
            --primary-blue: #3a7bd5;
            --primary-teal: #00d2ff;
            --dark-blue: #1a4b8c;
            --light-gray: #f8f9fa;
            --white: #ffffff;
            --dark-gray: #343a40;
            --error-red: #ff6b6b;
            --border-radius: 10px;
            --box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }

        /* ===== BASE STYLES ===== */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
        }

        body {
            background: linear-gradient(135deg, var(--primary-blue), var(--primary-teal));
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        /* ===== LOGIN CONTAINER ===== */
        .login-container {
            width: 100%;
            max-width: 420px;
        }

        .login-box {
            background: var(--white);
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            overflow: hidden;
            animation: fadeIn 0.5s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* ===== HEADER ===== */
        .login-header {
            text-align: center;
            padding: 30px;
            background: linear-gradient(135deg, var(--primary-blue), var(--dark-blue));
            color: var(--white);
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
            margin-bottom: 5px;
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
        }

        .btn-primary {
            background: linear-gradient(to right, var(--primary-blue), var(--primary-teal));
            color: var(--white);
        }

        .btn-primary:hover {
            background: linear-gradient(to right, var(--dark-blue), var(--primary-blue));
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .btn-primary:active {
            transform: translateY(0);
        }

        /* ===== FOOTER ===== */
        .login-footer {
            text-align: center;
            padding: 0 30px 30px;
        }

        .login-footer a {
            color: var(--primary-blue);
            text-decoration: none;
            font-weight: 500;
            transition: var(--transition);
        }

        .login-footer a:hover {
            color: var(--dark-blue);
            text-decoration: underline;
        }

        /* ===== ALERTS ===== */
        .alert {
            padding: 15px;
            margin: 0 30px 25px;
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
            background-color: rgba(255, 107, 107, 0.15);
            color: var(--error-red);
            border: 1px solid rgba(255, 107, 107, 0.3);
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 576px) {
            .login-header {
                padding: 25px 20px;
            }
            
            .login-form {
                padding: 25px 20px;
            }
            
            .login-header h1 {
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
    <div class="login-container">
        <div class="login-box">
            <div class="login-header">
                <i class="fas fa-heartbeat"></i>
                <h1>Staff Portal</h1>
            </div>
            
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <form method="POST" class="login-form" id="loginForm">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required 
                           value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" 
                           placeholder="Enter your username">
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required 
                           placeholder="Enter your password">
                </div>
                
                <button type="submit" class="btn btn-primary" id="loginBtn">Login</button>
            </form>
            
            <div class="login-footer">
                <a href="forgot-password.php">Forgot Password?</a>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('loginForm').addEventListener('submit', function() {
            const btn = document.getElementById('loginBtn');
            btn.classList.add('btn-loading');
            btn.innerHTML = '';
            
            // Disable the button to prevent multiple submissions
            btn.disabled = true;
        });

        // [Keep your existing JavaScript for input effects]
    </script>
</body>
</html>