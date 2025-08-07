<?php
// config/config.php
// Blood Bank Management System Configuration

// System Information
define('SYSTEM_NAME', 'Blood Bank Management & Forecasting System');
define('SYSTEM_VERSION', '1.0.0');
define('SYSTEM_AUTHOR', 'Development Team');

// Application Settings
define('APP_TIMEZONE', 'America/New_York'); // Change to your timezone
define('APP_DEBUG', false); // Set to false in production
define('APP_MAINTENANCE', false); // Set to true for maintenance mode

// Session Configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Set to 1 if using HTTPS
define('SESSION_TIMEOUT', 7200); // 2 hours in seconds

// Blood Bank Specific Settings
define('DEFAULT_BLOOD_EXPIRY_DAYS', 35); // Days until blood expires
define('EXPIRY_WARNING_DAYS', 7); // Days before expiry to show warning
define('CRITICAL_STOCK_LEVEL', 10); // Units below which stock is critical
define('LOW_STOCK_LEVEL', 20); // Units below which stock is low

// Blood Types Configuration
$BLOOD_TYPES = [
    'A+' => ['name' => 'A Positive', 'frequency' => 34, 'compatible_donors' => ['A+', 'A-', 'O+', 'O-']],
    'A-' => ['name' => 'A Negative', 'frequency' => 6, 'compatible_donors' => ['A-', 'O-']],
    'B+' => ['name' => 'B Positive', 'frequency' => 9, 'compatible_donors' => ['B+', 'B-', 'O+', 'O-']],
    'B-' => ['name' => 'B Negative', 'frequency' => 2, 'compatible_donors' => ['B-', 'O-']],
    'AB+' => ['name' => 'AB Positive', 'frequency' => 3, 'compatible_donors' => ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-']],
    'AB-' => ['name' => 'AB Negative', 'frequency' => 1, 'compatible_donors' => ['A-', 'B-', 'AB-', 'O-']],
    'O+' => ['name' => 'O Positive', 'frequency' => 38, 'compatible_donors' => ['O+', 'O-']],
    'O-' => ['name' => 'O Negative', 'frequency' => 7, 'compatible_donors' => ['O-']]
];

// Forecasting Configuration
define('FORECAST_MIN_DATA_POINTS', 30); // Minimum data points for forecasting
define('FORECAST_DEFAULT_PERIOD', 30); // Default forecast period in days
define('FORECAST_MAX_PERIOD', 365); // Maximum forecast period in days
define('FORECAST_CONFIDENCE_THRESHOLD', 0.6); // Minimum R-squared for reliable forecasts

// Forecasting Model Parameters
define('SEASONAL_ADJUSTMENT_ENABLED', true);
define('HOLIDAY_ADJUSTMENT_ENABLED', true);
define('WEEKEND_ADJUSTMENT_FACTOR', 0.7); // Reduce demand on weekends
define('HOLIDAY_ADJUSTMENT_FACTOR', 0.5); // Reduce demand on holidays

// Urgency Levels
$URGENCY_LEVELS = [
    'low' => ['name' => 'Low Priority', 'color' => '#10b981', 'response_time' => 72],
    'medium' => ['name' => 'Medium Priority', 'color' => '#f59e0b', 'response_time' => 24],
    'high' => ['name' => 'High Priority', 'color' => '#ef4444', 'response_time' => 6],
    'critical' => ['name' => 'Critical Emergency', 'color' => '#dc2626', 'response_time' => 1]
];

// File Upload Settings
define('MAX_UPLOAD_SIZE', 5242880); // 5MB in bytes
define('ALLOWED_FILE_TYPES', 'jpg,jpeg,png,pdf,doc,docx,xls,xlsx');
define('UPLOAD_PATH', __DIR__ . '/../uploads/');
define('EXPORT_PATH', __DIR__ . '/../exports/');

// Email Configuration (if needed for notifications)
define('MAIL_ENABLED', false); // Set to true to enable email notifications
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', '');
define('SMTP_PASSWORD', '');
define('SMTP_FROM_EMAIL', 'noreply@bloodbank.com');
define('SMTP_FROM_NAME', 'Blood Bank System');

// Notification Settings
define('ENABLE_EXPIRY_ALERTS', true);
define('ENABLE_LOW_STOCK_ALERTS', true);
define('ENABLE_CRITICAL_REQUEST_ALERTS', true);
define('ALERT_CHECK_INTERVAL', 3600); // Check for alerts every hour

// Pagination Settings
define('DEFAULT_RECORDS_PER_PAGE', 25);
define('MAX_RECORDS_PER_PAGE', 100);

// Chart and Visualization Settings
define('CHART_DEFAULT_COLORS', [
    '#ef4444', '#f97316', '#eab308', '#22c55e',
    '#06b6d4', '#3b82f6', '#8b5cf6', '#ec4899'
]);

// Security Settings
define('PASSWORD_MIN_LENGTH', 8);
define('PASSWORD_REQUIRE_UPPERCASE', true);
define('PASSWORD_REQUIRE_LOWERCASE', true);
define('PASSWORD_REQUIRE_NUMBERS', true);
define('PASSWORD_REQUIRE_SPECIAL', false);

define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_TIME', 1800); // 30 minutes

// API Settings (for future use)
define('API_ENABLED', false);
define('API_VERSION', '1.0');
define('API_RATE_LIMIT', 1000); // Requests per hour

// Logging Configuration
define('LOG_ENABLED', true);
define('LOG_LEVEL', 'INFO'); // DEBUG, INFO, WARNING, ERROR
define('LOG_FILE_PATH', __DIR__ . '/../logs/');
define('LOG_MAX_FILES', 30); // Keep logs for 30 days

// Backup Configuration
define('BACKUP_ENABLED', false);
define('BACKUP_SCHEDULE', 'daily'); // daily, weekly, monthly
define('BACKUP_PATH', __DIR__ . '/../backups/');
define('BACKUP_RETENTION_DAYS', 30);

// Performance Settings
define('ENABLE_QUERY_CACHE', true);
define('CACHE_EXPIRY_TIME', 3600); // 1 hour
define('ENABLE_COMPRESSION', true);

// Localization
define('DEFAULT_LANGUAGE', 'en');
define('SUPPORTED_LANGUAGES', ['en' => 'English', 'es' => 'Spanish', 'fr' => 'French']);
define('DATE_FORMAT', 'Y-m-d');
define('DATETIME_FORMAT', 'Y-m-d H:i:s');
define('DISPLAY_DATE_FORMAT', 'M j, Y');

// Feature Flags
define('FEATURE_FORECASTING', true);
define('FEATURE_ADVANCED_REPORTS', true);
define('FEATURE_MOBILE_APP', false);
define('FEATURE_BARCODE_SCANNING', false);

// Integration Settings
define('HOSPITAL_API_ENABLED', false);
define('HOSPITAL_API_ENDPOINT', '');
define('HOSPITAL_API_KEY', '');

// Utility Functions
function getBloodTypes() {
    global $BLOOD_TYPES;
    return $BLOOD_TYPES;
}

function getUrgencyLevels() {
    global $URGENCY_LEVELS;
    return $URGENCY_LEVELS;
}

function isBloodTypeCompatible($donor_type, $recipient_type) {
    global $BLOOD_TYPES;
    return in_array($donor_type, $BLOOD_TYPES[$recipient_type]['compatible_donors']);
}

function calculateExpiryDate($collection_date, $blood_type = null) {
    $expiry_days = DEFAULT_BLOOD_EXPIRY_DAYS;
    
    // Different blood products may have different expiry periods
    switch ($blood_type) {
        case 'platelets':
            $expiry_days = 5;
            break;
        case 'plasma':
            $expiry_days = 365;
            break;
        default:
            $expiry_days = DEFAULT_BLOOD_EXPIRY_DAYS;
    }
    
    return date('Y-m-d', strtotime($collection_date . " + {$expiry_days} days"));
}

function formatBloodType($blood_type) {
    global $BLOOD_TYPES;
    return $BLOOD_TYPES[$blood_type]['name'] ?? $blood_type;
}

function getUrgencyColor($urgency) {
    global $URGENCY_LEVELS;
    return $URGENCY_LEVELS[$urgency]['color'] ?? '#6b7280';
}

function isMaintenanceMode() {
    return APP_MAINTENANCE;
}

function isDebugMode() {
    return APP_DEBUG;
}

// Error Reporting
if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Timezone Setting
date_default_timezone_set(APP_TIMEZONE);

// Auto-create required directories
$directories = [
    UPLOAD_PATH,
    EXPORT_PATH,
    LOG_FILE_PATH . '../logs/',
    __DIR__ . '/../backups/'
];

foreach ($directories as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}

// Load environment-specific configuration if exists
$env_config = __DIR__ . '/config.local.php';
if (file_exists($env_config)) {
    include $env_config;
}

// Session Configuration
if (session_status() === PHP_SESSION_NONE) {
    session_start();
    
    // Session timeout check
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT)) {
        session_unset();
        session_destroy();
        session_start();
    }
    $_SESSION['last_activity'] = time();
}

// Global helper functions
function logMessage($level, $message, $context = []) {
    if (!LOG_ENABLED) return;
    
    $log_levels = ['DEBUG', 'INFO', 'WARNING', 'ERROR'];
    if (array_search($level, $log_levels) < array_search(LOG_LEVEL, $log_levels)) {
        return;
    }
    
    $timestamp = date('Y-m-d H:i:s');
    $log_entry = "[$timestamp] $level: $message";
    
    if (!empty($context)) {
        $log_entry .= ' Context: ' . json_encode($context);
    }
    
    $log_file = LOG_FILE_PATH . '/system_' . date('Y-m-d') . '.log';
    file_put_contents($log_file, $log_entry . PHP_EOL, FILE_APPEND | LOCK_EX);
}

function validatePassword($password) {
    $errors = [];
    
    if (strlen($password) < PASSWORD_MIN_LENGTH) {
        $errors[] = "Password must be at least " . PASSWORD_MIN_LENGTH . " characters long";
    }
    
    if (PASSWORD_REQUIRE_UPPERCASE && !preg_match('/[A-Z]/', $password)) {
        $errors[] = "Password must contain at least one uppercase letter";
    }
    
    if (PASSWORD_REQUIRE_LOWERCASE && !preg_match('/[a-z]/', $password)) {
        $errors[] = "Password must contain at least one lowercase letter";
    }
    
    if (PASSWORD_REQUIRE_NUMBERS && !preg_match('/\d/', $password)) {
        $errors[] = "Password must contain at least one number";
    }
    
    if (PASSWORD_REQUIRE_SPECIAL && !preg_match('/[^a-zA-Z0-9]/', $password)) {
        $errors[] = "Password must contain at least one special character";
    }
    
    return empty($errors) ? true : $errors;
}

function sanitizeInput($input) {
    if (is_array($input)) {
        return array_map('sanitizeInput', $input);
    }
    
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function formatBytes($bytes, $precision = 2) {
    $units = array('B', 'KB', 'MB', 'GB', 'TB');
    
    for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
        $bytes /= 1024;
    }
    
    return round($bytes, $precision) . ' ' . $units[$i];
}

function generateToken($length = 32) {
    return bin2hex(random_bytes($length / 2));
}

function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function isValidPhone($phone) {
    $phone = preg_replace('/[^0-9]/', '', $phone);
    return strlen($phone) >= 10;
}

function getSystemStats() {
    return [
        'version' => SYSTEM_VERSION,
        'php_version' => PHP_VERSION,
        'memory_usage' => memory_get_usage(true),
        'peak_memory' => memory_get_peak_usage(true),
        'server_time' => date('Y-m-d H:i:s'),
        'timezone' => APP_TIMEZONE,
        'debug_mode' => APP_DEBUG,
        'maintenance_mode' => APP_MAINTENANCE
    ];
}

function checkSystemHealth() {
    $health = [
        'status' => 'healthy',
        'checks' => []
    ];
    
    // Database check
    try {
        require_once 'database.php';
        $pdo->query('SELECT 1');
        $health['checks']['database'] = ['status' => 'ok', 'message' => 'Database connection successful'];
    } catch (Exception $e) {
        $health['checks']['database'] = ['status' => 'error', 'message' => 'Database connection failed'];
        $health['status'] = 'unhealthy';
    }
    
    // Directory permissions check
    $dirs_to_check = [UPLOAD_PATH, EXPORT_PATH];
    foreach ($dirs_to_check as $dir) {
        if (!is_writable($dir)) {
            $health['checks']['permissions'] = ['status' => 'error', 'message' => "Directory not writable: $dir"];
            $health['status'] = 'unhealthy';
        }
    }
    
    if (!isset($health['checks']['permissions'])) {
        $health['checks']['permissions'] = ['status' => 'ok', 'message' => 'All directories writable'];
    }
    
    // Memory check
    $memory_limit = ini_get('memory_limit');
    $memory_usage = memory_get_usage(true);
    $memory_percent = ($memory_usage / (int)$memory_limit) * 100;
    
    if ($memory_percent > 90) {
        $health['checks']['memory'] = ['status' => 'warning', 'message' => 'High memory usage: ' . round($memory_percent, 2) . '%'];
        if ($health['status'] === 'healthy') {
            $health['status'] = 'warning';
        }
    } else {
        $health['checks']['memory'] = ['status' => 'ok', 'message' => 'Memory usage normal: ' . round($memory_percent, 2) . '%'];
    }
    
    return $health;
}

// Development helpers (only in debug mode)
if (APP_DEBUG) {
    function dd($var) {
        echo '<pre>';
        var_dump($var);
        echo '</pre>';
        exit;
    }
    
    function debugLog($message, $data = null) {
        $debug_file = LOG_FILE_PATH . '/debug_' . date('Y-m-d') . '.log';
        $entry = '[' . date('H:i:s') . '] ' . $message;
        if ($data !== null) {
            $entry .= ' | Data: ' . print_r($data, true);
        }
        file_put_contents($debug_file, $entry . PHP_EOL, FILE_APPEND | LOCK_EX);
    }
    
    // Display all SQL queries in debug mode
  if (class_exists('PDO')) {
    class DebugPDO extends PDO {
        public function query($statement, $mode = PDO::ATTR_DEFAULT_FETCH_MODE, ...$fetch_mode_args) {
            debugLog("SQL Query: $statement");
            return parent::query($statement, $mode, ...$fetch_mode_args);
        }
    }
}

}

// Maintenance mode check
if (APP_MAINTENANCE && !isset($_SESSION['admin_override'])) {
    // Allow access to specific files even in maintenance mode
    $allowed_files = ['install.php', 'maintenance.php'];
    $current_file = basename($_SERVER['SCRIPT_NAME']);
    
    if (!in_array($current_file, $allowed_files)) {
        http_response_code(503);
        include __DIR__ . '/../maintenance.html';
        exit;
    }
}

// CSRF Protection
if (!function_exists('generateCSRFToken')) {
    function generateCSRFToken() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = generateToken();
        }
        return $_SESSION['csrf_token'];
    }
}

if (!function_exists('verifyCSRFToken')) {
    function verifyCSRFToken($token) {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
}

// Rate limiting helper
function checkRateLimit($identifier, $limit = 60, $window = 3600) {
    $cache_file = sys_get_temp_dir() . "/rate_limit_$identifier";
    $current_time = time();
    
    if (file_exists($cache_file)) {
        $data = json_decode(file_get_contents($cache_file), true);
        $window_start = $current_time - $window;
        
        // Clean old entries
        $data = array_filter($data, function($timestamp) use ($window_start) {
            return $timestamp > $window_start;
        });
        
        if (count($data) >= $limit) {
            return false; // Rate limit exceeded
        }
    } else {
        $data = [];
    }
    
    // Add current request
    $data[] = $current_time;
    file_put_contents($cache_file, json_encode($data));
    
    return true; // Within rate limit
}

// Global exception handler
if (!APP_DEBUG) {
    set_exception_handler(function($exception) {
        logMessage('ERROR', 'Uncaught exception: ' . $exception->getMessage(), [
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString()
        ]);
        
        http_response_code(500);
        if (file_exists(__DIR__ . '/../error.html')) {
            include __DIR__ . '/../error.html';
        } else {
            echo '<h1>System Error</h1><p>An unexpected error occurred. Please try again later.</p>';
        }
        exit;
    });
}

// Auto-cleanup old files
if (rand(1, 100) <= 5) { // 5% chance to run cleanup
    // Cleanup old logs
    if (is_dir(LOG_FILE_PATH)) {
        $log_files = glob(LOG_FILE_PATH . '/*.log');
        foreach ($log_files as $file) {
            if (filemtime($file) < strtotime('-' . (LOG_MAX_FILES ?? 30) . ' days')) {
                unlink($file);
            }
        }
    }
    
    // Cleanup old temporary files
    $temp_files = glob(sys_get_temp_dir() . '/rate_limit_*');
    foreach ($temp_files as $file) {
        if (filemtime($file) < strtotime('-1 hour')) {
            unlink($file);
        }
    }
}

// Initialize system
logMessage('INFO', 'System initialized', [
    'user_ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
    'request_uri' => $_SERVER['REQUEST_URI'] ?? 'unknown'
]);
?>