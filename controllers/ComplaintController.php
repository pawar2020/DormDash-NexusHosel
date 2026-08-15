<?php
/**
 * Complaint Controller
 * 
 * Handles complaint management
 */

class ComplaintController {
    private $conn;
    
    public function __construct() {
        global $conn;
        $this->conn = $conn;
    }
    
    /**
     * List all complaints
     */
    public function index() {
        requireLogin();
        
        $page = (int)sanitizeInput($_GET['page'] ?? 1);
        $status = sanitizeInput($_GET['status'] ?? '');
        $search = sanitizeInput($_GET['search'] ?? '');
        
        $complaint = new Complaint($this->conn);
        $filters = [];
        
        if ($status) $filters['status'] = $status;
        if ($search) $filters['search'] = $search;
        
        // If student, only show their complaints
        if (hasRole(ROLE_STUDENT)) {
            $student = new Student($this->conn);
            $studentData = $student->getByUserId(getCurrentUserId());
            if ($studentData) {
                $filters['student_id'] = $studentData['id'];
            }
        }
        
        $totalComplaints = $complaint->count($filters);
        $pagination = getPaginationData($totalComplaints, $page, ITEMS_PER_PAGE);
        
        $filters['limit'] = ITEMS_PER_PAGE;
        $filters['offset'] = $pagination['offset'];
        $complaints = $complaint->getAll($filters);
        
        require_once APP_ROOT . '/views/complaints/index.php';
    }
    
    /**
     * Add new complaint
     */
    public function add() {
        requireStudent();
        
        $error = '';
        $success = '';
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
                $error = 'Invalid security token';
            } else {
                $student = new Student($this->conn);
                $studentData = $student->getByUserId(getCurrentUserId());
                
                if (!$studentData) {
                    $error = 'Student record not found';
                } else {
                    $category = sanitizeInput($_POST['category'] ?? '');
                    $title = sanitizeInput($_POST['title'] ?? '');
                    $description = sanitizeInput($_POST['description'] ?? '');
                    $priority = sanitizeInput($_POST['priority'] ?? 'medium');
                    
                    if (empty($category) || empty($title) || empty($description)) {
                        $error = 'Please fill all required fields';
                    } else {
                        $complaint = new Complaint($this->conn);
                        $complaint->student_id = $studentData['id'];
                        $complaint->category = $category;
                        $complaint->title = $title;
                        $complaint->description = $description;
                        $complaint->priority = $priority;
                        $complaint->status = 'open';
                        
                        // Handle file upload
                        if (!empty($_FILES['attachment']['name'])) {
                            $upload = uploadFile($_FILES['attachment'], 'complaints', ALLOWED_DOCUMENT_TYPES);
                            if ($upload['success']) {
                                $complaint->attachment_path = $upload['path'];
                            }
                        }
                        
                        if ($complaint->create()) {
                            logActivity($this->conn, getCurrentUserId(), 'create', 'complaints', 'Created complaint');
                            $success = 'Complaint submitted successfully!';
                            
                            // Redirect after 2 seconds
                            header('Refresh: 2; url=' . APP_URL . '/index.php?action=complaints');
                        } else {
                            $error = 'Error submitting complaint';
                        }
                    }
                }
            }
        }
        
        $csrf_token = generateCsrfToken();
        require_once APP_ROOT . '/views/complaints/add.php';
    }
    
    /**
     * Edit complaint
     */
    public function edit($id) {
        requireStudent();
        
        $complaint = new Complaint($this->conn);
        $complaintData = $complaint->getById($id);
        
        if (!$complaintData) {
            http_response_code(404);
            require_once APP_ROOT . '/views/errors/404.php';
            return;
        }
        
        $student = new Student($this->conn);
        $studentData = $student->getByUserId(getCurrentUserId());
        
        if ($complaintData['student_id'] != $studentData['id']) {
            http_response_code(403);
            require_once APP_ROOT . '/views/errors/403.php';
            return;
        }
        
        $error = '';
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
                $error = 'Invalid security token';
            } else {
                $complaint->id = $id;
                $complaint->category = sanitizeInput($_POST['category'] ?? '');
                $complaint->title = sanitizeInput($_POST['title'] ?? '');
                $complaint->description = sanitizeInput($_POST['description'] ?? '');
                $complaint->priority = sanitizeInput($_POST['priority'] ?? '');
                $complaint->status = $complaintData['status'];
                
                if ($complaint->update()) {
                    logActivity($this->conn, getCurrentUserId(), 'update', 'complaints', 'Updated complaint');
                    header('Location: ' . APP_URL . '/index.php?action=complaints');
                    exit;
                } else {
                    $error = 'Error updating complaint';
                }
            }
        }
        
        $csrf_token = generateCsrfToken();
        require_once APP_ROOT . '/views/complaints/edit.php';
    }
    
    /**
     * View complaint details
     */
    public function view($id) {
        requireLogin();
        
        $complaint = new Complaint($this->conn);
        $complaintData = $complaint->getById($id);
        
        if (!$complaintData) {
            http_response_code(404);
            require_once APP_ROOT . '/views/errors/404.php';
            return;
        }
        
        require_once APP_ROOT . '/views/complaints/view.php';
    }
    
    /**
     * Resolve complaint
     */
    public function resolve($id) {
        requireWarden();
        
        $complaint = new Complaint($this->conn);
        $complaintData = $complaint->getById($id);
        
        if (!$complaintData) {
            setMessage('error', 'Complaint not found');
            header('Location: ' . APP_URL . '/index.php?action=complaints');
            exit;
        }
        
        if ($complaint->resolve($id)) {
            logActivity($this->conn, getCurrentUserId(), 'resolve', 'complaints', 'Resolved complaint');
            setMessage('success', 'Complaint resolved!');
        } else {
            setMessage('error', 'Error resolving complaint');
        }
        
        header('Location: ' . APP_URL . '/index.php?action=complaints');
        exit;
    }
    
    /**
     * Add admin reply
     */
    public function addReply($id) {
        requireWarden();
        
        $complaint = new Complaint($this->conn);
        $complaintData = $complaint->getById($id);
        
        if (!$complaintData) {
            setMessage('error', 'Complaint not found');
            header('Location: ' . APP_URL . '/index.php?action=complaints');
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
                setMessage('error', 'Invalid security token');
            } else {
                $reply = sanitizeInput($_POST['reply'] ?? '');
                
                if (empty($reply)) {
                    setMessage('error', 'Reply cannot be empty');
                } else {
                    if ($complaint->addReply($id, $reply)) {
                        logActivity($this->conn, getCurrentUserId(), 'reply', 'complaints', 'Added reply to complaint');
                        setMessage('success', 'Reply added!');
                    } else {
                        setMessage('error', 'Error adding reply');
                    }
                }
            }
        }
        
        header('Location: ' . APP_URL . '/index.php?action=complaints&subaction=view&id=' . $id);
        exit;
    }
}

?>
