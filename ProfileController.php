<?php
/**
 * Profile Controller
 *
 * Handles user profile viewing and editing
 */

class ProfileController {
    private $conn;

    public function __construct() {
        global $conn;
        $this->conn = $conn;
    }

    /**
     * View user profile
     */
    public function view() {
        requireLogin();
        $view = 'profile';
        $user = new User($this->conn);
        $userData = $user->getById(getCurrentUserId());
        if ($userData) {
            unset($userData['password'], $userData['password_hash'], $userData['reset_token']);
        }
        $record = $userData;
        $csrf = generateCsrfToken();
        require_once APP_ROOT . '/views/profile/index.php';
    }

    /**
     * Upload Profile Photo
     */
    public function uploadPhoto() {
        requireLogin();
        $userId = getCurrentUserId();
        
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && isset($_FILES['profile_photo'])) {
            if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
                setMessage('error', 'Session expired. Please try again.');
                redirect(APP_URL . '/index.php?action=profile');
            }
            
            $file = $_FILES['profile_photo'];
            if ($file['error'] === UPLOAD_ERR_OK) {
                $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                
                if (!in_array($ext, $allowed)) {
                    setMessage('error', 'Only JPG, PNG, GIF, or WEBP images are allowed.');
                    redirect(APP_URL . '/index.php?action=profile');
                }
                
                $targetDir = APP_ROOT . '/uploads/profiles/';
                if (!file_exists($targetDir)) {
                    mkdir($targetDir, 0777, true);
                }
                
                $fileName = 'user_' . $userId . '_' . time() . '.' . $ext;
                $targetPath = $targetDir . $fileName;
                $dbPath = 'uploads/profiles/' . $fileName;
                
                if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                    $stmt = $this->conn->prepare("UPDATE users SET photo_path = ? WHERE id = ?");
                    $stmt->execute([$dbPath, $userId]);
                    
                    $stmtS = $this->conn->prepare("UPDATE students SET profile_photo = ? WHERE user_id = ?");
                    $stmtS->execute([$dbPath, $userId]);
                    
                    $_SESSION['user']['photo_path'] = $dbPath;
                    setMessage('success', 'Profile photo updated successfully!');
                } else {
                    setMessage('error', 'Failed to save profile photo.');
                }
            } else {
                setMessage('error', 'Please select a valid image file.');
            }
        }
        redirect(APP_URL . '/index.php?action=profile');
    }

    /**
     * Edit user profile
     */
    public function edit() {
        requireLogin();
        $view = 'profile/edit';
        $user = new User($this->conn);
        $userData = $user->getById(getCurrentUserId());
        $error = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
                $error = 'Invalid security token';
            } else {
                $name = sanitizeInput($_POST['name'] ?? '');
                $email = sanitizeInput($_POST['email'] ?? '');
                $phone = sanitizeInput($_POST['phone'] ?? '');

                if (!$name || !isValidEmail($email) || ($phone && !isValidPhone($phone))) {
                    $error = 'Enter a valid name, email, and phone number';
                } else {
                    $existing = new User($this->conn);
                    $existing->email = $email;
                    $found = $existing->getByEmail();

                    if ($found && (int)$found['id'] !== (int)getCurrentUserId()) {
                        $error = 'Email already exists';
                    } else {
                        $user->id = getCurrentUserId();
                        $user->name = $name;
                        $user->email = $email;
                        $user->phone = $phone;
                        $user->role = $userData['role'];
                        $user->status = $userData['status'];

                        if ($user->update()) {
                            $_SESSION['user']['name'] = $name;
                            $_SESSION['user']['email'] = $email;
                            setMessage('success', 'Profile updated');
                            redirect(APP_URL . '/index.php?action=profile');
                        }
                        $error = 'Unable to update profile';
                    }
                }
            }
        }

        require APP_ROOT . '/views/app.php';
    }

    /**
     * Change user password
     */
    public function changePassword() {
        requireLogin();
        $view = 'profile/change-password';
        $error = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $user = new User($this->conn);
            $data = $user->getById(getCurrentUserId());
            $current = $_POST['current_password'] ?? '';
            $new = $_POST['new_password'] ?? '';
            $confirm = $_POST['confirm_password'] ?? '';

            if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
                $error = 'Invalid security token';
            } elseif (!verifyPassword($current, $data['password'])) {
                $error = 'Current password is incorrect';
            } elseif (strlen($new) < 8 || $new !== $confirm) {
                $error = 'Use matching passwords of at least 8 characters';
            } elseif ($user->changePassword($data['id'], $new)) {
                setMessage('success', 'Password changed');
                redirect(APP_URL . '/index.php?action=profile');
            } else {
                $error = 'Unable to change password';
            }
        }

        require APP_ROOT . '/views/app.php';
    }
}
