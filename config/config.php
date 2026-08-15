<?php
/**
 * Application Configuration
 *
 * Core application settings and constants
 */

// Application Details
define('APP_NAME', 'Hostel Management System');
define('APP_VERSION', '1.0.0');
$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$scriptDir = isset($_SERVER['SCRIPT_NAME']) ? rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\') : '/HostelProject/HostelProject';
define('APP_URL', getenv('APP_URL') ?: ($protocol . '://' . $host . ($scriptDir === '/' ? '' : $scriptDir)));
define('APP_ROOT', dirname(dirname(__FILE__)));
define('APP_ENV', 'development');

// Session Configuration
define('SESSION_TIMEOUT', 1800); // 30 minutes
define('SESSION_NAME', 'hms_session');

// Security Configuration
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOCK_TIME', 300); // 5 minutes

// Pagination
define('ITEMS_PER_PAGE', 10);

// Upload Paths
define('UPLOAD_DIR', APP_ROOT . '/uploads/');
define('UPLOAD_URL', APP_URL . '/uploads/');
define('UPLOAD_LIMIT', 5242880); // 5MB

// Allowed File Types
define('ALLOWED_PHOTO_TYPES', ['jpg', 'jpeg', 'png', 'gif']);
define('ALLOWED_DOCUMENT_TYPES', ['pdf', 'doc', 'docx', 'xls', 'xlsx']);

// Email Configuration
define('SYSTEM_EMAIL', 'noreply@hostel.com');
define('ADMIN_EMAIL', 'admin@hostel.com');
define('SUPPORT_EMAIL', 'support@hostel.com');

// Date Format
define('DATE_FORMAT', 'Y-m-d');
define('DATETIME_FORMAT', 'Y-m-d H:i:s');
define('TIME_FORMAT', 'H:i:s');

// User Roles
define('ROLE_ADMIN', 'admin');
define('ROLE_WARDEN', 'warden');
define('ROLE_STUDENT', 'student');

// Allowed Roles
$ALLOWED_ROLES = [
    ROLE_ADMIN => 'Administrator',
    ROLE_WARDEN => 'Warden',
    ROLE_STUDENT => 'Student'
];

// Fee Types
$FEE_TYPES = [
    'rent' => 'Room Rent',
    'mess' => 'Mess Charges',
    'maintenance' => 'Maintenance',
    'other' => 'Other'
];

// Complaint Categories
$COMPLAINT_CATEGORIES = [
    'room_condition' => 'Room Condition',
    'plumbing' => 'Plumbing Issue',
    'electrical' => 'Electrical Issue',
    'cleanliness' => 'Cleanliness',
    'noise' => 'Noise Complaint',
    'bullying' => 'Bullying',
    'other' => 'Other'
];

// Complaint Priority
$COMPLAINT_PRIORITY = [
    'low' => 'Low',
    'medium' => 'Medium',
    'high' => 'High',
    'urgent' => 'Urgent'
];

// Complaint Status
$COMPLAINT_STATUS = [
    'open' => 'Open',
    'in_progress' => 'In Progress',
    'resolved' => 'Resolved',
    'closed' => 'Closed',
    'rejected' => 'Rejected'
];

// Room Types
$ROOM_TYPES = [
    'single' => 'Single Bed',
    'double' => 'Double Bed',
    'triple' => 'Triple Bed',
    'four-bed' => 'Four Bed'
];

// Room Status
$ROOM_STATUS = [
    'available' => 'Available',
    'occupied' => 'Occupied',
    'maintenance' => 'Under Maintenance'
];

// Payment Methods
$PAYMENT_METHODS = [
    'cash' => 'Cash',
    'cheque' => 'Cheque',
    'online' => 'Online',
    'bank_transfer' => 'Bank Transfer'
];
