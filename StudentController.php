<?php
/**
 * Student Controller
 *
 * Handles student management CRUD operations
 */

class StudentController {
    private $conn;

    public function __construct() {
        global $conn;
        $this->conn = $conn;
    }

    /**
     * List all students
     */
    public function index() {
        requireWarden();

        $view = 'students/index';
        $page = (int)sanitizeInput($_GET['page'] ?? 1);
        $status = sanitizeInput($_GET['status'] ?? '');
        $search = sanitizeInput($_GET['search'] ?? '');

        $student = new Student($this->conn);
        $filters = [];

        if ($status) $filters['status'] = $status;
        if ($search) $filters['search'] = $search;

        $totalStudents = $student->count($filters);
        $pagination = getPaginationData($totalStudents, $page, ITEMS_PER_PAGE);

        $filters['limit'] = ITEMS_PER_PAGE;
        $filters['offset'] = $pagination['offset'];
        $students = $student->getAll($filters);

        require_once APP_ROOT . '/views/app.php';
    }

    /**
     * Show add student form
     */
    public function add() {
        requireWarden();

        $view = 'students/add';
        $error = '';
        $success = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validate CSRF token
            if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
                $error = 'Invalid security token';
            } else {
                // Get form data
                $name = sanitizeInput($_POST['name'] ?? '');
                $email = sanitizeInput($_POST['email'] ?? '');
                $phone = sanitizeInput($_POST['phone'] ?? '');
                $password = $_POST['password'] ?? '';
                $roll_number = sanitizeInput($_POST['roll_number'] ?? '');
                $registration_number = sanitizeInput($_POST['registration_number'] ?? '');
                $course = sanitizeInput($_POST['course'] ?? '');
                $year = sanitizeInput($_POST['year'] ?? '');

                // Validate
                if (empty($name) || empty($email) || empty($password) || empty($roll_number)) {
                    $error = 'Please fill all required fields';
                } elseif (!isValidEmail($email)) {
                    $error = 'Invalid email format';
                } elseif ($phone && !isValidPhone($phone)) {
                    $error = 'Invalid phone number';
                } else {
                    // Create user first
                    $user = new User($this->conn);
                    $user->email = $email;

                    if ($user->getByEmail()) {
                        $error = 'Email already exists';
                    } else {
                        $user->name = $name;
                        $user->phone = $phone;
                        $user->password = $password;
                        $user->role = ROLE_STUDENT;
                        $user->status = 'active';

                        if ($user->create()) {
                            // Get inserted user ID
                            $userData = $user->getByEmail();

                            if ($userData) {
                                // Create student record
                                $student = new Student($this->conn);
                                $student->user_id = $userData['id'];
                                $student->roll_number = $roll_number;
                                $student->registration_number = $registration_number;
                                $student->course = $course;
                                $student->year = $year;
                                $student->admission_date = date('Y-m-d');
                                $student->status = 'active';

                                if ($student->create()) {
                                    logActivity($this->conn, getCurrentUserId(), 'create', 'students', 'Added new student: ' . $name);
                                    $success = 'Student added successfully!';

                                    // Redirect to student list
                                    header('Location: ' . APP_URL . '/index.php?action=students');
                                    exit;
                                } else {
                                    $error = 'Error creating student record';
                                    $user->delete($userData['id']);
                                }
                            }
                        } else {
                            $error = 'Error creating user account';
                        }
                    }
                }
            }
        }

        $csrf_token = generateCsrfToken();
        require_once APP_ROOT . '/views/app.php';
    }

    /**
     * Show edit student form
     */
    public function edit($id) {
        requireWarden();

        $view = 'students/edit';
        $student = new Student($this->conn);
        $studentData = $student->getById($id);

        if (!$studentData) {
            http_response_code(404);
            require_once APP_ROOT . '/views/app.php';
            return;
        }

        $error = '';
        $success = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validate CSRF token
            if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
                $error = 'Invalid security token';
            } else {
                // Get form data
                $name = sanitizeInput($_POST['name'] ?? '');
                $email = sanitizeInput($_POST['email'] ?? '');
                $phone = sanitizeInput($_POST['phone'] ?? '');
                $course = sanitizeInput($_POST['course'] ?? '');
                $year = sanitizeInput($_POST['year'] ?? '');

                if (empty($name) || empty($email) || empty($course)) {
                    $error = 'Please fill all required fields';
                } elseif (!isValidEmail($email)) {
                    $error = 'Invalid email format';
                } else {
                    // Update student
                    $student->id = $id;
                    $student->name = $name;
                    $student->course = $course;
                    $student->year = $year;

                    if ($student->update()) {
                        // Update user
                        $user = new User($this->conn);
                        $user->id = $studentData['user_id'];
                        $user->name = $name;
                        $user->email = $email;
                        $user->phone = $phone;
                        $user->role = ROLE_STUDENT;
                        $user->status = 'active';
                        $user->update();

                        logActivity($this->conn, getCurrentUserId(), 'update', 'students', 'Updated student: ' . $name);
                        $success = 'Student updated successfully!';

                        // Redirect to student list
                        header('Location: ' . APP_URL . '/index.php?action=students');
                        exit;
                    } else {
                        $error = 'Error updating student';
                    }
                }
            }
        }

        $csrf_token = generateCsrfToken();
        require_once APP_ROOT . '/views/app.php';
    }

    /**
     * Delete student
     */
    public function delete($id) {
        requireWarden();

        $student = new Student($this->conn);
        $studentData = $student->getById($id);

        if (!$studentData) {
            http_response_code(404);
            require_once APP_ROOT . '/views/app.php';
            return;
        }

        if ($student->delete($id)) {
            // Delete user
            $user = new User($this->conn);
            $user->delete($studentData['user_id']);

            logActivity($this->conn, getCurrentUserId(), 'delete', 'students', 'Deleted student: ' . $studentData['name']);

            setMessage('success', 'Student deleted successfully!');
        } else {
            setMessage('error', 'Error deleting student');
        }

        header('Location: ' . APP_URL . '/index.php?action=students');
        exit;
    }

    /**
     * View student profile
     */
    public function view($id) {
        requireLogin();

        $view = 'students/view';
        $student = new Student($this->conn);
        $studentData = $student->getById($id);

        if (!$studentData) {
            http_response_code(404);
            require_once APP_ROOT . '/views/app.php';
            return;
        }

        $currentRoom = getStudentCurrentRoom($this->conn, $id);
        $pendingFees = getPendingFees($this->conn, $id);

        require_once APP_ROOT . '/views/app.php';
    }

    /**
     * Search students
     */
    public function search() {
        requireWarden();

        $view = 'students/search';
        $search = sanitizeInput($_GET['q'] ?? '');
        $page = (int)sanitizeInput($_GET['page'] ?? 1);

        $student = new Student($this->conn);
        $filters = ['search' => $search];

        $totalStudents = $student->count($filters);
        $pagination = getPaginationData($totalStudents, $page, ITEMS_PER_PAGE);

        $filters['limit'] = ITEMS_PER_PAGE;
        $filters['offset'] = $pagination['offset'];
        $students = $student->getAll($filters);

        require_once APP_ROOT . '/views/app.php';
    }
}
