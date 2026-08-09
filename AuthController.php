<?php
/**
 * Authentication Controller
 * 
 * Handles user authentication, login, register, logout
 */

class AuthController {
    private $conn;
    
    public function __construct() {
        global $conn;
        $this->conn = $conn;
    }
    
    /**
     * Show login page / Process login
     */
    public function login() {
        if (isLoggedIn()) {
            redirect(APP_URL . '/index.php?action=dashboard');
        }
        
        $error = '';
        $selectedRole = sanitizeInput($_GET['role'] ?? $_POST['role'] ?? '');
        $validRoles = [ROLE_ADMIN, ROLE_WARDEN, ROLE_STUDENT];
        if (!in_array($selectedRole, $validRoles, true)) {
            $selectedRole = '';
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = sanitizeInput($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            
            // Validate input
            if (empty($email) || empty($password)) {
                $error = 'Email and password are required';
            } elseif (!isValidEmail($email)) {
                $error = 'Invalid email format';
            } else {
                // Authenticate user
                $user = new User($this->conn);
                $user->email = $email;
                $loginUser = $user->verifyLogin($email, $password);
                
                $userRole = $loginUser ? $loginUser['role'] : '';
                if (in_array($userRole, ['administrator', 'super_admin', 'admin'], true)) {
                    $userRole = ROLE_ADMIN;
                }
                
                $roleMatches = !$selectedRole || $userRole === $selectedRole;
                if ($loginUser && !$roleMatches) {
                    $error = 'This account does not belong to the selected portal.';
                } elseif ($loginUser) {
                    session_regenerate_id(true);
                    // Set session
                    $_SESSION['user_id'] = $loginUser['id'];
                    $_SESSION['user_role'] = $userRole;
                    $_SESSION['user'] = [
                        'id' => $loginUser['id'],
                        'name' => $loginUser['name'],
                        'email' => $loginUser['email'],
                        'role' => $userRole
                    ];
                    $_SESSION['last_activity'] = time();
                    $_SESSION['auth_role'] = $userRole;
                    
                    // Log activity
                    logActivity($this->conn, $loginUser['id'], 'login', 'authentication', 'User logged in');
                    
                    // Redirect to dashboard
                    redirect(APP_URL . '/index.php?action=dashboard');
                } else {
                    $error = 'Invalid email or password';
                }
            }
        }
        
        // Load landing view with login modal open
        $totalRooms = 0; $occupiedRooms = 0; $totalBeds = 0; $occupiedBeds = 0; $occupancyRate = 83.4;
        try {
            if ($this->conn) {
                $totalRooms = (int)$this->conn->query("SELECT COUNT(*) FROM rooms")->fetchColumn();
                $occupiedRooms = (int)$this->conn->query("SELECT COUNT(*) FROM rooms WHERE status = 'occupied'")->fetchColumn();
                $totalBeds = (int)$this->conn->query("SELECT SUM(capacity) FROM rooms")->fetchColumn();
                $occupiedBeds = (int)$this->conn->query("SELECT SUM(occupied) FROM rooms")->fetchColumn();
                if ($totalBeds > 0) { $occupancyRate = round(($occupiedBeds / $totalBeds) * 100, 1); }
            }
        } catch (Exception $e) {}

        require_once APP_ROOT . '/views/landing.php';
    }
    
    /**
     * Show register page
     */
    public function register() {
        if (isLoggedIn()) {
            redirect(APP_URL . '/index.php?action=dashboard');
        }
        
        $error = '';
        $success = '';
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = sanitizeInput($_POST['name'] ?? '');
            $email = sanitizeInput($_POST['email'] ?? '');
            $phone = sanitizeInput($_POST['phone'] ?? '');
            $password = $_POST['password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';
            
            // Validate input
            if (empty($name) || empty($email) || empty($password)) {
                $error = 'Name, email, and password are required';
            } elseif (!isValidEmail($email)) {
                $error = 'Invalid email format';
            } elseif ($password !== $confirmPassword) {
                $error = 'Passwords do not match';
            } elseif (strlen($password) < 6) {
                $error = 'Password must be at least 6 characters';
            } elseif (!empty($phone) && !isValidPhone($phone)) {
                $error = 'Invalid phone number';
            } else {
                // Check if email already exists
                $user = new User($this->conn);
                $user->email = $email;
                $existing = $user->getByEmail();
                
                if ($existing) {
                    $error = 'Email already registered';
                } else {
                    // Create new user
                    $user->name = $name;
                    $user->email = $email;
                    $user->phone = $phone;
                    $user->password = $password;
                    $user->role = ROLE_STUDENT;
                    $user->status = 'active';
                    
                    if ($user->create()) {
                        $success = 'Registration successful! You can now login.';
                    } else {
                        $error = 'Error during registration. Please try again.';
                    }
                }
            }
        }
        
        // Load register view
        require_once APP_ROOT . '/views/auth/register.php';
    }
    
    /**
     * Logout user
     */
    public function logout() {
        if (isLoggedIn()) {
            $userId = getCurrentUserId();
            logActivity($this->conn, $userId, 'logout', 'authentication', 'User logged out');
        }
        
        session_destroy();
        redirect(APP_URL . '/index.php?action=login');
    }
    
    /**
     * Show forgot password page
     */
    public function forgotPassword() {
        if (isLoggedIn()) {
            redirect(APP_URL . '/index.php?action=dashboard');
        }
        
        $error = '';
        $success = '';
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = sanitizeInput($_POST['email'] ?? '');
            
            if (empty($email)) {
                $error = 'Email is required';
            } elseif (!isValidEmail($email)) {
                $error = 'Invalid email format';
            } else {
                // Check if user exists
                $user = new User($this->conn);
                $user->email = $email;
                $userData = $user->getByEmail();
                
                if ($userData) {
                    $resetToken = generateRandomString(32);
                    $success = 'Password reset link sent to your email. Token: ' . $resetToken;
                    $_SESSION['reset_token'] = $resetToken;
                    $_SESSION['reset_email'] = $email;
                } else {
                    $success = 'If email exists, reset link has been sent.';
                }
            }
        }
        
        require_once APP_ROOT . '/views/auth/forgot-password.php';
    }
    
    /**
     * Reset password
     */
    public function resetPassword() {
        if (isLoggedIn()) {
            redirect(APP_URL . '/index.php?action=dashboard');
        }
        
        $token = sanitizeInput($_GET['token'] ?? '');
        $error = '';
        $success = '';
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $newPassword = $_POST['new_password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';
            $email = sanitizeInput($_POST['email'] ?? '');
            
            if (empty($newPassword) || empty($confirmPassword)) {
                $error = 'Password and confirmation are required';
            } elseif ($newPassword !== $confirmPassword) {
                $error = 'Passwords do not match';
            } elseif (strlen($newPassword) < 6) {
                $error = 'Password must be at least 6 characters';
            } else {
                $user = new User($this->conn);
                $user->email = $email;
                $userData = $user->getByEmail();
                
                if ($userData) {
                    if ($user->changePassword($userData['id'], $newPassword)) {
                        $success = 'Password reset successful! You can now login.';
                    } else {
                        $error = 'Error resetting password. Please try again.';
                    }
                } else {
                    $error = 'Invalid request';
                }
            }
        }
        
        require_once APP_ROOT . '/views/auth/reset-password.php';
    }
}
