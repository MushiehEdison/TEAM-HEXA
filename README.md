# Patient Reminder Appointment and Feedback System

A comprehensive web-based patient management system built with PHP that handles appointment reminders, medical records, and patient feedback. The system automatically sends email reminders via SMTP and provides a complete digital health record management solution.

## 🚀 Features

### Core Functionality
- **Appointment Management**: Schedule, modify, and track patient appointments
- **Automated Email Reminders**: SMTP-based email notifications sent when reminders are configured
- **Medical Records Management**: Create, edit, and maintain comprehensive patient medical records
- **File Management**: Upload and organize medical documents, images, and folders
- **Patient Feedback System**: Collect and manage patient feedback and reviews
- **User Authentication**: Secure login system for patients and healthcare providers

### Key Capabilities
- **Multi-file Upload**: Support for various file types (PDF, images, documents)
- **Folder Organization**: Hierarchical file and document organization
- **Email Notifications**: Customizable reminder emails with appointment details
- **Responsive Design**: Mobile-friendly interface
- **Data Security**: Secure handling of sensitive medical information

## 📋 Requirements

### System Requirements
- PHP 7.4 or higher
- MySQL 5.7+ or MariaDB 10.2+
- Web server (Apache/Nginx)
- SMTP server access for email functionality

### PHP Extensions
- mysqli or PDO
- mbstring
- fileinfo
- openssl (for secure email transmission)
- curl (optional, for additional integrations)

## ⚙️ Installation

### 1. Clone or Download
```bash
git clone [repository-url]
cd patient-reminder-system
```

### 2. Database Setup
1. Create a MySQL database for the application
2. Import the provided SQL file:
```sql
mysql -u username -p database_name < database/schema.sql
```

### 3. Configuration
1. Copy the configuration template:
```bash
cp config/config.example.php config/config.php
```

2. Edit `config/config.php` with your settings:
```php
<?php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'patient_system');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');

// SMTP Configuration
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'your-email@gmail.com');
define('SMTP_PASS', 'your-app-password');
define('SMTP_SECURE', 'tls');

// File Upload Settings
define('MAX_FILE_SIZE', 10485760); // 10MB
define('UPLOAD_PATH', 'uploads/');
?>
```

### 4. Set Permissions
```bash
chmod 755 uploads/
chmod 755 config/
chmod 644 config/config.php
```

### 5. Web Server Configuration
Ensure your web server points to the project directory and has PHP enabled.

## 📁 Project Structure

```
patient-reminder-system/
├── config/
│   ├── config.php          # Main configuration file
│   └── database.php        # Database connection
├── includes/
│   ├── auth.php           # Authentication functions
│   ├── email.php          # SMTP email functions
│   └── functions.php      # Utility functions
├── assets/
│   ├── css/              # Stylesheets
│   ├── js/               # JavaScript files
│   └── images/           # System images
├── uploads/              # File upload directory
├── pages/
│   ├── dashboard.php     # Main dashboard
│   ├── appointments.php  # Appointment management
│   ├── records.php       # Medical records
│   ├── reminders.php     # Reminder settings
│   └── feedback.php      # Feedback system
├── database/
│   └── schema.sql        # Database structure
├── index.php            # Main entry point
├── login.php            # User authentication
└── README.md           # This file
```

## 🔧 Configuration

### SMTP Setup
The system supports various SMTP providers:

**Gmail Configuration:**
```php
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_SECURE', 'tls');
```

**Outlook/Hotmail:**
```php
define('SMTP_HOST', 'smtp-mail.outlook.com');
define('SMTP_PORT', 587);
define('SMTP_SECURE', 'tls');
```

### File Upload Configuration
Customize file upload settings in `config/config.php`:
```php
// Allowed file types
$allowed_types = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'txt'];

// Maximum file size (in bytes)
define('MAX_FILE_SIZE', 10485760); // 10MB
```

## 💻 Usage

### For Healthcare Providers
1. **Login** to the system with your credentials
2. **Manage Appointments**: Create, edit, and schedule patient appointments
3. **Set Reminders**: Configure automatic email reminders for appointments
4. **Access Records**: View and update patient medical records
5. **Review Feedback**: Monitor patient feedback and ratings

### For Patients
1. **Create Account** or login with existing credentials
2. **View Appointments**: Check scheduled appointments and receive reminders
3. **Medical Records**: Access your medical history and uploaded documents
4. **Upload Files**: Add medical documents, test results, and images
5. **Provide Feedback**: Rate services and leave feedback

### Email Reminders
The system automatically sends reminders based on configured settings:
- **24 hours before** appointment (default)
- **1 hour before** appointment (optional)
- **Custom intervals** can be configured

## 🔒 Security Features

- **Password Hashing**: All passwords are securely hashed
- **Session Management**: Secure session handling
- **File Validation**: Upload files are validated for type and size
- **SQL Injection Protection**: Parameterized queries used throughout
- **XSS Prevention**: Input sanitization and output encoding

## 📧 Email System

### Reminder Email Features
- **HTML Templates**: Professional email templates
- **Appointment Details**: Date, time, doctor, and location information
- **Personalization**: Patient-specific information
- **Delivery Tracking**: Monitor email delivery status

### Setup Instructions
1. Enable 2-factor authentication on your email account
2. Generate an app-specific password
3. Use the app password in SMTP configuration
4. Test email functionality through the admin panel

## 🗄️ Database Schema

The system uses the following main tables:
- `users` - Patient and staff information
- `appointments` - Appointment scheduling data
- `medical_records` - Patient medical history
- `file_uploads` - Uploaded document tracking
- `reminders` - Email reminder configurations
- `feedback` - Patient feedback and ratings

## 🔧 Troubleshooting

### Common Issues

**Email Not Sending:**
- Verify SMTP credentials and settings
- Check if firewall blocks SMTP ports
- Ensure app passwords are used for Gmail

**File Upload Errors:**
- Check directory permissions (755 for uploads folder)
- Verify PHP upload limits in php.ini
- Ensure sufficient disk space

**Database Connection Issues:**
- Verify database credentials
- Check if MySQL service is running
- Ensure database exists and is accessible

### Log Files
Check the following for error information:
- PHP error logs
- Web server error logs
- Application logs in `logs/` directory

## 📱 Browser Support

- Chrome 60+
- Firefox 55+
- Safari 12+
- Edge 79+
- Internet Explorer 11 (limited support)




## 👥 Support

For support and questions:
- Email: anyanwugowill7@gmail.com




---

**Note**: Always backup your database before updating the system. Ensure you have proper security measures in place when handling sensitive medical information.
