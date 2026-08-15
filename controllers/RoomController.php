<?php
/**
 * Room Controller
 *
 * Handles room management CRUD operations
 */

class RoomController {
    private $conn;

    public function __construct() {
        global $conn;
        $this->conn = $conn;
    }

    /**
     * List all rooms
     */
    public function index() {
        requireWarden();

        $view = 'rooms/index';
        $page = (int)sanitizeInput($_GET['page'] ?? 1);
        $status = sanitizeInput($_GET['status'] ?? '');
        $search = sanitizeInput($_GET['search'] ?? '');

        $room = new Room($this->conn);
        $filters = [];

        if ($status) $filters['status'] = $status;
        if ($search) $filters['search'] = $search;

        $totalRooms = $room->count($filters);
        $pagination = getPaginationData($totalRooms, $page, ITEMS_PER_PAGE);

        $filters['limit'] = 100;
        $filters['offset'] = 0;
        $rooms = $room->getAll($filters);

        // Fetch allocated students grouped by room_id
        $studentsByRoom = [];
        try {
            $stmt = $this->conn->query("SELECT id, full_name, department, course, room_id, student_id FROM students WHERE room_id IS NOT NULL");
            $studentRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($studentRows as $st) {
                $studentsByRoom[$st['room_id']][] = $st;
            }
        } catch (Exception $e) {}

        require_once APP_ROOT . '/views/rooms/index.php';
    }

    /**
     * Add new room
     */
    public function add() {
        requireWarden();

        $view = 'rooms/add';
        $error = '';
        $success = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
                $error = 'Invalid security token';
            } else {
                $hostel_id = sanitizeInput($_POST['hostel_id'] ?? '');
                $room_number = sanitizeInput($_POST['room_number'] ?? '');
                $room_type = sanitizeInput($_POST['room_type'] ?? '');
                $capacity = (int)sanitizeInput($_POST['capacity'] ?? 0);
                $rent = (float)sanitizeInput($_POST['rent_per_month'] ?? 0);

                if (empty($hostel_id) || empty($room_number) || empty($room_type) || $capacity <= 0 || $rent <= 0) {
                    $error = 'Please fill all required fields correctly';
                } else {
                    $room = new Room($this->conn);
                    $room->hostel_id = $hostel_id;
                    $room->room_number = $room_number;
                    $room->room_type = $room_type;
                    $room->capacity = $capacity;
                    $room->beds_available = $capacity;
                    $room->rent_per_month = $rent;
                    $room->description = sanitizeInput($_POST['description'] ?? '');
                    $room->status = 'available';

                    if ($room->create()) {
                        logActivity($this->conn, getCurrentUserId(), 'create', 'rooms', 'Added room: ' . $room_number);
                        header('Location: ' . APP_URL . '/index.php?action=rooms');
                        exit;
                    } else {
                        $error = 'Error adding room';
                    }
                }
            }
        }

        // Get hostels for dropdown
        $hostels = getHostels($this->conn);

        $csrf_token = generateCsrfToken();
        require_once APP_ROOT . '/views/app.php';
    }

    /**
     * Edit room
     */
    public function edit($id) {
        requireWarden();

        $view = 'rooms/edit';
        $room = new Room($this->conn);
        $roomData = $room->getById($id);

        if (!$roomData) {
            http_response_code(404);
            require_once APP_ROOT . '/views/app.php';
            return;
        }

        $error = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
                $error = 'Invalid security token';
            } else {
                $room->id = $id;
                $room->room_number = sanitizeInput($_POST['room_number'] ?? '');
                $room->room_type = sanitizeInput($_POST['room_type'] ?? '');
                $room->capacity = (int)sanitizeInput($_POST['capacity'] ?? 0);
                $room->beds_available = (int)sanitizeInput($_POST['beds_available'] ?? 0);
                $room->rent_per_month = (float)sanitizeInput($_POST['rent_per_month'] ?? 0);
                $room->description = sanitizeInput($_POST['description'] ?? '');
                $room->status = sanitizeInput($_POST['status'] ?? '');

                if ($room->update()) {
                    logActivity($this->conn, getCurrentUserId(), 'update', 'rooms', 'Updated room: ' . $room->room_number);
                    header('Location: ' . APP_URL . '/index.php?action=rooms');
                    exit;
                } else {
                    $error = 'Error updating room';
                }
            }
        }

        // Get hostels for dropdown
        $hostels = getHostels($this->conn);

        $csrf_token = generateCsrfToken();
        require_once APP_ROOT . '/views/app.php';
    }

    /**
     * Delete room
     */
    public function delete($id) {
        requireWarden();

        $room = new Room($this->conn);
        $roomData = $room->getById($id);

        if (!$roomData) {
            http_response_code(404);
            require_once APP_ROOT . '/views/app.php';
            return;
        }

        if ($room->delete($id)) {
            logActivity($this->conn, getCurrentUserId(), 'delete', 'rooms', 'Deleted room: ' . $roomData['room_number']);
            setMessage('success', 'Room deleted successfully!');
        } else {
            setMessage('error', 'Error deleting room');
        }

        header('Location: ' . APP_URL . '/index.php?action=rooms');
        exit;
    }

    /**
     * Allocate room to student
     */
    public function allocate($id) {
        requireWarden();

        $view = 'rooms/allocate';
        $room = new Room($this->conn);
        $roomData = $room->getById($id);

        if (!$roomData) {
            http_response_code(404);
            require_once APP_ROOT . '/views/app.php';
            return;
        }

        $error = '';
        $success = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
                $error = 'Invalid security token';
            } else {
                $student_id = sanitizeInput($_POST['student_id'] ?? '');
                $bed_number = (int)sanitizeInput($_POST['bed_number'] ?? 0);

                if (empty($student_id) || $bed_number <= 0) {
                    $error = 'Please select student and bed';
                } else {
                    // Create allocation
                    $stmt = $this->conn->prepare("
                        INSERT INTO room_allocations (student_id, room_id, hostel_id, allocation_date, bed_number, status)
                        VALUES (?, ?, ?, NOW(), ?, 'active')
                    ");

                    if ($stmt->execute([$student_id, $id, $roomData['hostel_id'], $bed_number])) {
                        // Update room status if needed
                        $stmt = $this->conn->prepare("
                            SELECT COUNT(*) as count FROM room_allocations
                            WHERE room_id = ? AND status = 'active'
                        ");
                        $stmt->execute([$id]);
                        $result = $stmt->fetch();

                        if ($result['count'] >= $roomData['capacity']) {
                            $room->id = $id;
                            $room->status = 'occupied';
                            $room->update();
                        }

                        logActivity($this->conn, getCurrentUserId(), 'allocate', 'rooms', 'Allocated room to student');

                        header('Location: ' . APP_URL . '/index.php?action=rooms');
                        exit;
                    } else {
                        $error = 'Error allocating room';
                    }
                }
            }
        }

        // Get available students
        $students = getActiveStudents($this->conn);

        $csrf_token = generateCsrfToken();
        require_once APP_ROOT . '/views/app.php';
    }

    /**
     * Vacate room
     */
    public function vacate($id) {
        requireWarden();

        $stmt = $this->conn->prepare("
            SELECT * FROM room_allocations WHERE id = ? AND status = 'active' LIMIT 1
        ");
        $stmt->execute([$id]);
        $allocation = $stmt->fetch();

        if (!$allocation) {
            setMessage('error', 'Invalid allocation');
            header('Location: ' . APP_URL . '/index.php?action=rooms');
            exit;
        }

        // Update allocation
        $stmt = $this->conn->prepare("
            UPDATE room_allocations SET vacate_date = NOW(), status = 'vacated' WHERE id = ?
        ");

        if ($stmt->execute([$id])) {
            // Check if room has more allocations
            $stmt = $this->conn->prepare("
                SELECT COUNT(*) as count FROM room_allocations
                WHERE room_id = ? AND status = 'active'
            ");
            $stmt->execute([$allocation['room_id']]);
            $result = $stmt->fetch();

            if ($result['count'] == 0) {
                // Update room status to available
                $room = new Room($this->conn);
                $room->id = $allocation['room_id'];
                $room->status = 'available';
                $room->update();
            }

            logActivity($this->conn, getCurrentUserId(), 'vacate', 'rooms', 'Vacated room');
            setMessage('success', 'Room vacated successfully!');
        } else {
            setMessage('error', 'Error vacating room');
        }

        header('Location: ' . APP_URL . '/index.php?action=rooms');
        exit;
    }
}
