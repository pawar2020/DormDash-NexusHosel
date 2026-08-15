<?php
/**
 * Helper Functions
 *
 * Utility functions for the application
 */

/**
 * Sanitize input data
 */
function sanitizeInput($data) {
    if (is_array($data)) {
        return array_map('sanitizeInput', $data);
    }
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

/** Render a view through the shared application shell. */
function renderPage($view, array $data = []) {
    extract($data, EXTR_SKIP);
    require APP_ROOT . '/views/app.php';
}

/**
 * Escape output data
 */
function escapeOutput($data) {
    if ($data === null) return '';
    return htmlspecialchars((string)$data, ENT_QUOTES, 'UTF-8');
}

/**
 * Validate email
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate phone number
 */
function isValidPhone($phone) {
    return preg_match('/^[0-9]{10}$/', str_replace(['-', ' ', '(', ')'], '', $phone));
}

/**
 * Format currency
 */
function formatCurrency($amount) {
    return '₹ ' . number_format($amount, 2);
}

/**
 * Format date
 */
function formatDate($date) {
    if (empty($date)) return '-';
    return date('d-m-Y', strtotime($date));
}

/**
 * Format datetime
 */
function formatDateTime($datetime) {
    if (empty($datetime)) return '-';
    return date('d-m-Y H:i', strtotime($datetime));
}

/**
 * Format time
 */
function formatTime($time) {
    if (empty($time)) return '-';
    return date('H:i', strtotime($time));
}

/**
 * Get time ago format
 */
function timeAgo($date) {
    $timestamp = strtotime($date);
    $difference = time() - $timestamp;

    $intervals = [
        31536000 => 'year',
        2592000 => 'month',
        604800 => 'week',
        86400 => 'day',
        3600 => 'hour',
        60 => 'minute',
        1 => 'second'
    ];

    foreach ($intervals as $seconds => $name) {
        $value = floor($difference / $seconds);
        if ($value >= 1) {
            return $value . ' ' . $name . (($value > 1) ? 's' : '') . ' ago';
        }
    }

    return 'just now';
}

/**
 * Generate random string of specified length
 */
function generateRandomString($length = 10) {
    return substr(bin2hex(random_bytes((int) ceil($length / 2))), 0, $length);
}

/**
 * Hash password
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);
}

/**
 * Verify password
 */
function verifyPassword($password, $hash) {
    if (empty($hash)) return false;
    if (password_verify($password, $hash)) return true;
    return $password === $hash;
}

/**
 * Redirect to URL
 */
function redirect($url) {
    header('Location: ' . $url);
    exit;
}

/**
 * Check if file upload is valid
 */
function isValidFileUpload($file, $allowedTypes = [], $maxSize = 5242880) {
    if (!isset($file['name']) || empty($file['name'])) {
        return ['valid' => false, 'message' => 'No file selected'];
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['valid' => false, 'message' => 'Upload error: ' . $file['error']];
    }

    if ($file['size'] > $maxSize) {
        return ['valid' => false, 'message' => 'File size exceeds maximum limit'];
    }

    if (!empty($allowedTypes)) {
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowedTypes)) {
            return ['valid' => false, 'message' => 'File type not allowed'];
        }
    }

    return ['valid' => true];
}

/**
 * Upload file
 */
function uploadFile($file, $directory = 'general', $allowedTypes = [], $maxSize = 5242880) {
    $validation = isValidFileUpload($file, $allowedTypes, $maxSize);
    if (!$validation['valid']) {
        return ['success' => false, 'message' => $validation['message']];
    }

    if (!is_dir(UPLOAD_DIR . $directory)) {
        mkdir(UPLOAD_DIR . $directory, 0755, true);
    }

    $fileName = generateRandomString(16) . '.' . strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $filePath = UPLOAD_DIR . $directory . '/' . $fileName;

    if (move_uploaded_file($file['tmp_name'], $filePath)) {
        return [
            'success' => true,
            'filename' => $fileName,
            'path' => $directory . '/' . $fileName,
            'full_path' => $filePath
        ];
    }

    return ['success' => false, 'message' => 'Failed to upload file'];
}

/**
 * Delete file
 */
function deleteFile($path) {
    $fullPath = UPLOAD_DIR . $path;
    if (file_exists($fullPath)) {
        return unlink($fullPath);
    }
    return false;
}

/**
 * Pagination helper
 */
function getPaginationData($totalItems, $currentPage = 1, $itemsPerPage = ITEMS_PER_PAGE) {
    $totalPages = max(1, (int) ceil($totalItems / $itemsPerPage));
    $currentPage = max(1, min($currentPage, $totalPages));
    $offset = ($currentPage - 1) * $itemsPerPage;

    return [
        'total_items' => $totalItems,
        'total_pages' => $totalPages,
        'current_page' => $currentPage,
        'offset' => $offset,
        'items_per_page' => $itemsPerPage,
        'has_prev' => $currentPage > 1,
        'has_next' => $currentPage < $totalPages
    ];
}

/**
 * Generate PDF filename
 */
function generatePdfFilename($prefix = '') {
    $timestamp = date('YmdHis');
    return $prefix . '_' . $timestamp . '.pdf';
}

/**
 * Get student academic year
 */
function getAcademicYear() {
    $currentMonth = date('n');
    $currentYear = date('Y');

    if ($currentMonth >= 6) {
        return $currentYear . '-' . ($currentYear + 1);
    } else {
        return ($currentYear - 1) . '-' . $currentYear;
    }
}

/**
 * Get hostels for dropdown
 */
function getHostels($conn) {
    try {
        $stmt = $conn->query("SELECT id, name FROM hostels WHERE status = 'active' ORDER BY name");
        return $stmt->fetchAll();
    } catch (Exception $e) {
        return [];
    }
}

/**
 * Get active students for dropdown
 */
function getActiveStudents($conn) {
    try {
        $stmt = $conn->query("SELECT s.id, s.roll_number, u.name FROM students s JOIN users u ON s.user_id = u.id WHERE s.status = 'active' ORDER BY u.name");
        return $stmt->fetchAll();
    } catch (Exception $e) {
        return [];
    }
}

/**
 * Get active room allocations for dropdown
 */
function getActiveAllocations($conn) {
    try {
        $stmt = $conn->query("SELECT ra.id, s.roll_number, u.name, r.room_number FROM room_allocations ra JOIN students s ON ra.student_id = s.id JOIN users u ON s.user_id = u.id JOIN rooms r ON ra.room_id = r.id WHERE ra.status = 'active' ORDER BY u.name");
        return $stmt->fetchAll();
    } catch (Exception $e) {
        return [];
    }
}
