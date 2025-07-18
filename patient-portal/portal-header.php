<?php require_once 'includes/portal-auth.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        :root {
            --primary-blue: #1a73e8;
            --dark-blue: #0d47a1;
            --light-blue: #e8f0fe;
            --accent-pink: #e91e63;
            --soft-pink: #fce4ec;
            --white: #ffffff;
            --gray-text: #5f6368;
            --light-gray: #f5f5f5;
        }
        
        .portal-header {
            background-color: var(--white);
            color: var(--gray-text);
            padding: 0.8rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            position: sticky;
            top: 0;
            z-index: 1000;
            border-bottom: 3px solid var(--light-blue);
        }
        
        .header-left, .header-right {
            display: flex;
            align-items: center;
            gap: 2rem;
        }
        
        .logo {
            height: 45px;
            width: auto;
        }
        
        .portal-title {
            color: var(--primary-blue);
            font-size: 1.5rem;
            font-weight: 600;
            margin: 0;
        }
        
        .portal-nav {
            display: flex;
            gap: 0.5rem;
        }
        
        .portal-nav a {
            color: var(--gray-text);
            text-decoration: none;
            padding: 0.6rem 1.2rem;
            border-radius: 20px;
            transition: all 0.3s ease;
            font-size: 0.95rem;
            font-weight: 500;
        }
        
        .portal-nav a:hover {
            background-color: var(--light-blue);
            color: var(--primary-blue);
        }
        
        .portal-nav a.active {
            background-color: var(--primary-blue);
            color: var(--white);
        }
        
        .welcome-message {
            font-weight: 500;
            color: var(--gray-text);
            margin-right: 1rem;
        }
        
        .profile-link {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background-color: var(--soft-pink);
            color: var(--accent-pink);
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .profile-link:hover {
            background-color: var(--accent-pink);
            color: var(--white);
        }
        
        .btn-logout {
            background-color: var(--light-blue);
            color: var(--primary-blue);
            padding: 0.5rem 1.2rem;
            border-radius: 20px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            border: 1px solid var(--light-blue);
        }
        
        .btn-logout:hover {
            background-color: var(--primary-blue);
            color: var(--white);
            border-color: var(--primary-blue);
        }
        
        #mobile-menu-toggle {
            display: none;
            background: none;
            border: none;
            color: var(--primary-blue);
            font-size: 1.5rem;
            cursor: pointer;
            padding: 0.5rem;
        }
        
        @media (max-width: 992px) {
            .portal-nav a span {
                display: none;
            }
            
            .portal-nav a i {
                margin-right: 0;
                font-size: 1.2rem;
            }
            
            .portal-nav a {
                padding: 0.6rem;
                border-radius: 50%;
                width: 40px;
                height: 40px;
                display: flex;
                align-items: center;
                justify-content: center;
            }
        }
        
        @media (max-width: 768px) {
            #mobile-menu-toggle {
                display: block;
            }
            
            .portal-nav {
                display: none;
                position: absolute;
                top: 70px;
                left: 0;
                right: 0;
                background-color: var(--white);
                flex-direction: column;
                padding: 1rem;
                box-shadow: 0 5px 10px rgba(0, 0, 0, 0.1);
                border-radius: 0 0 8px 8px;
            }
            
            .portal-nav.show {
                display: flex;
            }
            
            .portal-nav a {
                width: auto;
                height: auto;
                border-radius: 4px;
                padding: 0.8rem 1rem;
            }
            
            .portal-nav a span {
                display: inline;
            }
        }
    </style>
</head>
<body>
<header class="portal-header">
    <div class="header-left">
        <i class="fas fa-heartbeat"></i>
        
        
        <nav class="portal-nav" id="main-nav">
            <a href="dashboard.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
                <i class="fas fa-home"></i>
                <span>Dashboard</span>
            </a>
            <a href="appointments.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'appointments.php' ? 'active' : ''; ?>">
                <i class="fas fa-calendar-check"></i>
                <span>Appointments</span>
            </a>
            <a href="medical-records.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'medical-records.php' ? 'active' : ''; ?>">
                <i class="fas fa-file-medical"></i>
                <span>Records</span>
            </a>
            <a href="feedback.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'prescriptions.php' ? 'active' : ''; ?>">
                <i class="fas fa-prescription-bottle-alt"></i>
                <span>feedback.php</span>
            </a>
            <a href="reminders.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'billing.php' ? 'active' : ''; ?>">
                <i class="fas fa-file-invoice-dollar"></i>
                <span>reminders</span>
            </a>
        </nav>
    </div>
    
    <div class="header-right">
        <button id="mobile-menu-toggle" aria-label="Toggle menu">
            <i class="fas fa-bars"></i>
        </button>
        <span class="welcome-message">Welcome, <?php echo htmlspecialchars($_SESSION['patient_name']); ?></span>
        <a href="profile.php" class="profile-link" title="My Profile">
            <i class="fas fa-user"></i>
        </a>
        <a href="logout.php" class="btn btn-logout">
            <i class="fas fa-sign-out-alt"></i>
            <span>Logout</span>
        </a>
    </div>
</header>

<script>
    // Mobile menu toggle functionality
    document.getElementById('mobile-menu-toggle').addEventListener('click', function() {
        const nav = document.getElementById('main-nav');
        nav.classList.toggle('show');
    });
</script>

<!-- Include Font Awesome for icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</body>
</html>