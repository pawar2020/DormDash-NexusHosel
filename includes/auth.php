<?php
/**
 * Session and Authentication Helper
 * 
 * Manages session handling and authentication
 */

session_name(SESSION_NAME);
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['user_role']);
}

/**
 * Check if user has specific role
 */
function hasRole($role) {
    return isLoggedIn() && $_SESSION['user_role'] === $role;
}

/**
 * Check if user has one of multiple roles
 */
function hasAnyRole($roles = []) {
    if (!isLoggedIn()) return false;
    return in_array($_SESSION['user_role'], $roles);
}

/**
 * Get current logged in user ID
 */
function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Get current logged in user role
 */
function getCurrentUserRole() {
    return $_SESSION['user_role'] ?? null;
}

/**
 * Get current logged in user data
 */
function getCurrentUser() {
    return isset($_SESSION['user']) ? $_SESSION['user'] : null;
}

/**
 * Logout user
 */
function logout() {
    session_destroy();
    header('Location: ' . APP_URL . '/index.php?action=login');
    exit;
}

/**
 * Redirect to login if not authenticated
 */
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ' . APP_URL . '/index.php?action=login');
        exit;
    }
}

/**
 * Redirect if not admin
 */
function requireAdmin() {
    requireLogin();
    if (!hasRole(ROLE_ADMIN)) {
        http_response_code(403);
        header('Location: ' . APP_URL . '/index.php?action=error&code=403');
        exit;
    }
}

/**
 * Redirect if not warden
 */
function requireWarden() {
    requireLogin();
    if (!hasAnyRole([ROLE_ADMIN, ROLE_WARDEN])) {
        http_response_code(403);
        header('Location: ' . APP_URL . '/index.php?action=error&code=403');
        exit;
    }
}

/**
 * Redirect if not student
 */
function requireStudent() {
    requireLogin();
    if (!hasRole(ROLE_STUDENT)) {
        http_response_code(403);
        header('Location: ' . APP_URL . '/index.php?action=error&code=403');
        exit;
    }
}

/**
 * Set session message
 */
function setMessage($type, $message) {
    $_SESSION['message'] = [
        'type' => $type, // 'success', 'error', 'warning', 'info'
        'text' => $message
    ];
}

/**
 * Get and clear session message
 */
function getMessage() {
    if (isset($_SESSION['message'])) {
        $message = $_SESSION['message'];
        unset($_SESSION['message']);
        return $message;
    }
    return null;
}

/**
 * Check CSRF token
 */
function verifyCsrfToken($token) {
    if (!isset($_SESSION['csrf_token'])) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Generate CSRF token
 */
function generateCsrfToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Check session timeout
 */
function checkSessionTimeout() {
    if (isLoggedIn()) {
        if (isset($_SESSION['last_activity'])) {
            if ((time() - $_SESSION['last_activity']) > SESSION_TIMEOUT) {
                logout();
            }
        }
        $_SESSION['last_activity'] = time();
    }
}

?>
