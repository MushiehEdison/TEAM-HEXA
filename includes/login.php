<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient System - Login</title>
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
            max-width: 450px;
            padding: 40px;
            text-align: center;
            position: relative;
            overflow: hidden;
            animation: fadeIn 0.6s ease-out;
            transform-origin: center;
        }

        .login-container::before {
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
        .login-container h1 {
            color: var(--primary-blue);
            margin-bottom: 10px;
            font-size: 28px;
            font-weight: 600;
            position: relative;
            display: inline-block;
        }

        .login-container h1::after {
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

        .login-container h2 {
            color: var(--dark-gray);
            margin-bottom: 30px;
            font-size: 22px;
            font-weight: 500;
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
            padding: 14px 16px 14px 45px;
            border: 2px solid #e9ecef;
            border-radius: var(--border-radius);
            font-size: 15px;
            transition: var(--transition);
            background-color: var(--light-gray);
        }

        .form-group input:focus {
            outline: none;
            border-color: var(--primary-blue);
            background-color: var(--white);
            box-shadow: 0 0 0 4px rgba(58, 123, 213, 0.2);
        }

        .form-group i {
            position: absolute;
            left: 15px;
            top: 40px;
            color: var(--primary-pink);
            font-size: 18px;
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

        .btn-primary::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, var(--light-pink), var(--primary-pink));
            opacity: 0;
            z-index: -1;
            transition: var(--transition);
        }

        .btn-primary:hover::before {
            opacity: 1;
        }

        /* ===== ALERTS ===== */
        .alert {
            padding: 15px;
            margin-bottom: 25px;
            border-radius: var(--border-radius);
            font-size: 14px;
            text-align: center;
            animation: slideDown 0.4s ease-out;
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

        /* ===== FOOTER LINKS ===== */
        .login-footer {
            margin-top: 25px;
            font-size: 14px;
            color: var(--dark-gray);
        }

        .login-footer a {
            color: var(--primary-blue);
            text-decoration: none;
            transition: var(--transition);
            font-weight: 500;
        }

        .login-footer a:hover {
            color: var(--dark-blue);
            text-decoration: underline;
        }

        /* ===== RESPONSIVE DESIGN ===== */
        @media (max-width: 576px) {
            .login-container {
                padding: 30px 20px;
            }
            
            .login-container h1 {
                font-size: 24px;
            }
            
            .login-container h2 {
                font-size: 20px;
                margin-bottom: 25px;
            }
            
            .form-group input {
                padding: 12px 12px 12px 40px;
            }
            
            .form-group i {
                font-size: 16px;
                top: 37px;
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
        <h1>Patient Management System</h1>
        <h2>Login to Your Account</h2>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form method="POST" action="login.php" id="loginForm">
            <div class="form-group">
                <label for="username">Username</label>
                <i class="fas fa-user"></i>
                <input type="text" id="username" name="username" required placeholder="Enter your username">
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <i class="fas fa-lock"></i>
                <input type="password" id="password" name="password" required placeholder="Enter your password">
            </div>
            
            <button type="submit" class="btn btn-primary" id="loginBtn">Login <i class="fas fa-arrow-right"></i></button>
            
            <div class="login-footer">
                <a href="forgot-password.php">Forgot password?</a> • <a href="register.php">Create account</a>
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

        // Add animation to inputs when focused
        document.querySelectorAll('.form-group input').forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.querySelector('i').style.color = 'var(--primary-blue)';
                this.parentElement.querySelector('i').style.transform = 'scale(1.2)';
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.querySelector('i').style.color = 'var(--primary-pink)';
                this.parentElement.querySelector('i').style.transform = 'scale(1)';
            });
        });
    </script>
</body>
</html>