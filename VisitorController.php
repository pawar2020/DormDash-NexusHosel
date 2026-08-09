<?php
/**
 * Visitor Controller
 * 
 * Handles visitor entry and exit management
 */

class VisitorController {
    private $conn;
    
    public function __construct() {
        global $conn;
        $this->conn = $conn;
    }
    
    /**
     * List all visitors
     */
    public function index() {
        requireLogin();
        
        $page = (int)sanitizeInput($_GET['page'] ?? 1);
        $status = sanitizeInput($_GET['status'] ?? '');
        $search = sanitizeInput($_GET['search'] ?? '');
        
        $visitor = new Visitor($this->conn);
        $filters = [];
        
        if ($status) $filters['status'] = $status;
        if ($search) $filters['search'] = $search;
        
        // If student, only show their visitors
        if (hasRole(ROLE_STUDENT)) {
            $student = new Student($this->conn);
            $studentData = $student->getByUserId(getCurrentUserId());
            if ($studentData) {
                $filters['student_id'] = $studentData['id'];
            }
        }
        
        $totalVisitors = $visitor->count($filters);
        $pagination = getPaginationData($totalVisitors, $page, ITEMS_PER_PAGE);
        
        $filters['limit'] = ITEMS_PER_PAGE;
        $filters['offset'] = $pagination['offset'];
        $visitors = $visitor->getAll($filters);
        
        require_once APP_ROOT . '/views/visitors/index.php';
    }
    
    /**
     * Record visitor entry (for students)
     */
    public function entry() {
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
                    $visitor_name = sanitizeInput($_POST['visitor_name'] ?? '');
                    $relationship = sanitizeInput($_POST['relationship'] ?? '');
                    $id_proof_type = sanitizeInput($_POST['id_proof_type'] ?? '');
                    $id_proof_number = sanitizeInput($_POST['id_proof_number'] ?? '');
                    $purpose = sanitizeInput($_POST['purpose'] ?? '');
                    
                    if (empty($visitor_name) || empty($relationship)) {
                        $error = 'Please fill all required fields';
                    } else {
                        $visitor = new Visitor($this->conn);
                        $visitor->student_id = $studentData['id'];
                        $visitor->visitor_name = $visitor_name;
                        $visitor->relationship = $relationship;
                        $visitor->id_proof_type = $id_proof_type;
                        $visitor->id_proof_number = $id_proof_number;
                        $visitor->entry_date = date('Y-m-d H:i:s');
                        $visitor->entry_time = null;
                        $visitor->purpose = $purpose;
                        $visitor->status = 'pending';
                        
                        if ($visitor->create()) {
                            logActivity($this->conn, getCurrentUserId(), 'create', 'visitors', 'Submitted visitor request');
                            $success = 'Visitor request submitted for warden approval.';
                        } else {
                            $error = 'Error recording visitor entry';
                        }
                    }
                }
            }
        }
        
        $csrf_token = generateCsrfToken();
        require_once APP_ROOT . '/views/visitors/entry.php';
    }
    
    /**
     * Record visitor entry (for wardens)
     */
    public function recordEntry() {
        requireWarden();
        
        $error = '';
        $success = '';
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
                $error = 'Invalid security token';
            } else {
                $student_id = sanitizeInput($_POST['student_id'] ?? '');
                $visitor_name = sanitizeInput($_POST['visitor_name'] ?? '');
                $visitor_phone = sanitizeInput($_POST['visitor_phone'] ?? '');
                $relationship = sanitizeInput($_POST['relationship'] ?? '');
                $purpose = sanitizeInput($_POST['purpose'] ?? '');
                
                if (empty($student_id) || empty($visitor_name)) {
                    $error = 'Please fill all required fields';
                } else {
                    $visitor = new Visitor($this->conn);
                    $visitor->student_id = $student_id;
                    $visitor->visitor_name = $visitor_name;
                    $visitor->visitor_phone = $visitor_phone;
                    $visitor->relationship = $relationship;
                    $visitor->entry_date = date('Y-m-d');
                    $visitor->entry_time = date('H:i:s');
                    $visitor->purpose = $purpose;
                    $visitor->status = 'entered';
                    
                    if ($visitor->create()) {
                        logActivity($this->conn, getCurrentUserId(), 'create', 'visitors', 'Recorded visitor entry');
                        setMessage('success', 'Visitor entry recorded!');
                        header('Location: ' . APP_URL . '/index.php?action=visitors');
                        exit;
                    } else {
                        $error = 'Error recording visitor entry';
                    }
                }
            }
        }
        
        $student = new Student($this->conn);
        $students = $student->getAll(['status' => 'active']);
        
        $csrf_token = generateCsrfToken();
        require_once APP_ROOT . '/views/visitors/record-entry.php';
    }
    
    /**
     * Record visitor exit
     */
    public function recordExit($id) {
        requireLogin();
        
        $visitor = new Visitor($this->conn);
        $visitorData = $visitor->getById($id);
        
        if (!$visitorData || $visitorData['status'] !== 'entered') {
            setMessage('error', 'Invalid visitor record');
            header('Location: ' . APP_URL . '/index.php?action=visitors');
            exit;
        }
        
        if ($visitor->recordExit($id)) {
            logActivity($this->conn, getCurrentUserId(), 'record_exit', 'visitors', 'Recorded visitor exit');
            setMessage('success', 'Visitor exit recorded!');
        } else {
            setMessage('error', 'Error recording visitor exit');
        }
        
        header('Location: ' . APP_URL . '/index.php?action=visitors');
        exit;
    }

    public function approve($id) {
        requireWarden();
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST' || !verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            setMessage('error', 'Invalid visitor approval request.');
        } elseif ((new Visitor($this->conn))->approve((int)$id)) {
            logActivity($this->conn, getCurrentUserId(), 'approve', 'visitors', 'Approved visitor request');
            setMessage('success', 'Visitor request approved.');
        } else {
            setMessage('error', 'This visitor request is no longer pending.');
        }
        redirect(APP_URL . '/index.php?action=visitors');
    }
}

?>
