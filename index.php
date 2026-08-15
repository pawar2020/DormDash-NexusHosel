<?php
/**
 * Hostel Management System
 * Main Application Router
 * 
 * This is the central entry point for all requests
 */

// Load configuration
require_once 'config/config.php';
require_once 'config/database.php';

// Load includes
require_once 'includes/helpers.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

// Load domain models before controllers instantiate them.
foreach (['User', 'Student', 'Room', 'Fee', 'Visitor', 'Complaint', 'LeaveRequest'] as $model) {
    require_once APP_ROOT . '/models/' . $model . '.php';
}

// Set error reporting after APP_ENV is defined.
error_reporting(E_ALL);
ini_set('display_errors', APP_ENV === 'development' ? '1' : '0');
ini_set('log_errors', '1');

// Check session timeout
checkSessionTimeout();

// Get the action parameter (default: home if not logged in, dashboard if logged in)
$action = sanitizeInput($_GET['action'] ?? '');
$subaction = sanitizeInput($_GET['subaction'] ?? '');
$id = sanitizeInput($_GET['id'] ?? '');

// Redirect based on login status
if (empty($action)) {
    if (isLoggedIn()) {
        $action = 'dashboard';
    } else {
        $action = 'home';
    }
}

// Route the request
try {
    switch ($action) {
        // Landing / Hero page
        case 'home':
        case 'landing':
            require_once 'controllers/LandingController.php';
            $controller = new LandingController();
            $controller->index();
            break;

        // Authentication routes
        case 'login':
            require_once 'controllers/AuthController.php';
            $controller = new AuthController();
            $controller->login();
            break;

        case 'register':
            require_once 'controllers/AuthController.php';
            $controller = new AuthController();
            $controller->register();
            break;

        case 'logout':
            require_once 'controllers/AuthController.php';
            $controller = new AuthController();
            $controller->logout();
            break;

        case 'forgot-password':
            require_once 'controllers/AuthController.php';
            $controller = new AuthController();
            $controller->forgotPassword();
            break;

        case 'reset-password':
            require_once 'controllers/AuthController.php';
            $controller = new AuthController();
            $controller->resetPassword();
            break;
            
        // Dashboard routes
        case 'dashboard':
            requireLogin();
            require_once 'controllers/DashboardController.php';
            $controller = new DashboardController();
            $controller->index();
            break;
            
        // Student management routes
        case 'students':
            requireWarden();
            require_once 'controllers/StudentController.php';
            $controller = new StudentController();
            
            switch ($subaction) {
                case 'add':
                    $controller->add();
                    break;
                case 'edit':
                    $controller->edit($id);
                    break;
                case 'delete':
                    $controller->delete($id);
                    break;
                case 'view':
                    $controller->view($id);
                    break;
                case 'search':
                    $controller->search();
                    break;
                default:
                    $controller->index();
            }
            break;
            
        // Room management routes
        case 'rooms':
            requireWarden();
            require_once 'controllers/RoomController.php';
            $controller = new RoomController();
            
            switch ($subaction) {
                case 'add':
                    $controller->add();
                    break;
                case 'edit':
                    $controller->edit($id);
                    break;
                case 'delete':
                    $controller->delete($id);
                    break;
                case 'allocate':
                    $controller->allocate($id);
                    break;
                case 'vacate':
                    $controller->vacate($id);
                    break;
                default:
                    $controller->index();
            }
            break;
            
        // Fee management routes
        case 'fees':
            requireWarden();
            require_once 'controllers/FeeController.php';
            $controller = new FeeController();
            
            switch ($subaction) {
                case 'add':
                    $controller->add();
                    break;
                case 'edit':
                    $controller->edit($id);
                    break;
                case 'delete':
                    $controller->delete($id);
                    break;
                case 'mark-paid':
                    $controller->markPaid($id);
                    break;
                case 'receipt':
                    $controller->generateReceipt($id);
                    break;
                default:
                    $controller->index();
            }
            break;
            
        // Visitor management routes
        case 'visitors':
            requireLogin();
            require_once 'controllers/VisitorController.php';
            $controller = new VisitorController();
            
            switch ($subaction) {
                case 'entry':
                    if (hasRole(ROLE_STUDENT)) {
                        $controller->entry();
                    } else {
                        $controller->recordEntry();
                    }
                    break;
                case 'exit':
                    $controller->recordExit($id);
                    break;
                case 'approve':
                    $controller->approve($id);
                    break;
                default:
                    $controller->index();
            }
            break;
            
        // Complaint management routes
        case 'complaints':
            requireLogin();
            require_once 'controllers/ComplaintController.php';
            $controller = new ComplaintController();
            
            switch ($subaction) {
                case 'add':
                    $controller->add();
                    break;
                case 'edit':
                    $controller->edit($id);
                    break;
                case 'view':
                    $controller->view($id);
                    break;
                case 'resolve':
                    $controller->resolve($id);
                    break;
                case 'reply':
                    $controller->addReply($id);
                    break;
                default:
                    $controller->index();
            }
            break;

        // Leave request routes
        case 'leaves':
            requireLogin();
            require_once 'controllers/LeaveController.php';
            $controller = new LeaveController();
            switch ($subaction) {
                case 'add':
                    $controller->add();
                    break;
                case 'decision':
                    $controller->decision($id);
                    break;
                default:
                    $controller->index();
            }
            break;
            
        // Reports routes
        case 'reports':
            requireWarden();
            require_once 'controllers/ReportController.php';
            $controller = new ReportController();
            
            switch ($subaction) {
                case 'students':
                    $controller->studentReport();
                    break;
                case 'rooms':
                    $controller->roomReport();
                    break;
                case 'fees':
                    $controller->feeReport();
                    break;
                case 'visitors':
                    $controller->visitorReport();
                    break;
                case 'complaints':
                    $controller->complaintReport();
                    break;
                case 'export-pdf':
                    $controller->exportPdf();
                    break;
                case 'export-excel':
                    $controller->exportExcel();
                    break;
                default:
                    $controller->index();
            }
            break;
            
        // Error pages
        case 'error':
            $code = sanitizeInput($_GET['code'] ?? '500');
            $codeFile = 'views/errors/' . $code . '.php';
            
            if (file_exists($codeFile)) {
                http_response_code($code);
                require_once $codeFile;
            } else {
                http_response_code(500);
                require_once 'views/errors/500.php';
            }
            break;
            
        // Legal pages
        case 'privacy':
            $view = 'legal/privacy';
            require_once APP_ROOT . '/views/legal/privacy.php';
            break;

        case 'terms':
            $view = 'legal/terms';
            require_once APP_ROOT . '/views/legal/terms.php';
            break;

        // Profile and settings
        case 'profile':
            requireLogin();
            require_once 'controllers/ProfileController.php';
            $controller = new ProfileController();
            
            switch ($subaction) {
                case 'upload-photo':
                    $controller->uploadPhoto();
                    break;
                case 'edit':
                    $controller->edit();
                    break;
                case 'change-password':
                    $controller->changePassword();
                    break;
                default:
                    $controller->view();
            }
            break;
            
        // Default 404
        default:
            http_response_code(404);
            require_once 'views/errors/404.php';
    }
} catch (Exception $e) {
    error_log('Application Error: ' . $e->getMessage());
    http_response_code(500);
    require_once 'views/errors/500.php';
}

?>
