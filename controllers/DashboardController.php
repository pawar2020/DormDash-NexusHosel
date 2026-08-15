<?php
/**
 * Dashboard Controller
 * 
 * Handles dashboard display for different user roles
 */

class DashboardController {
    private $conn;
    
    public function __construct() {
        global $conn;
        $this->conn = $conn;
    }
    
    /**
     * Show dashboard based on user role
     */
    public function index() {
        requireLogin();
        
        $userRole = getCurrentUserRole();
        $userId = getCurrentUserId();
        
        if ($userRole === ROLE_ADMIN) {
            $stats = [
                'total students' => $this->count('students', "status = 'active'"),
                'occupied rooms' => $this->count('rooms', "status = 'occupied'"),
                'available rooms' => $this->count('rooms', "status = 'available'"),
                'wardens' => $this->count('users', "role = 'warden' AND status = 'active'"),
                'pending complaints' => $this->count('complaints', "status IN ('open', 'in_progress')"),
                'pending leaves' => $this->count('leave_requests', "status = 'pending'"),
                'today visitors' => $this->count('visitors', 'DATE(entry_date) = CURDATE()'),
            ];
            $chartData = $this->adminCharts();
            require_once APP_ROOT . '/views/dashboard/admin.php';
        } elseif ($userRole === ROLE_WARDEN) {
            $stats = [
                'active students' => $this->count('students', "status = 'active'"),
                'open complaints' => $this->count('complaints', "status IN ('open', 'in_progress')"),
                'visitor requests' => $this->count('visitors', "status = 'pending'"),
                'leave requests' => $this->count('leave_requests', "status = 'pending'"),
            ];
            $chartData = $this->wardenCharts();
            require_once APP_ROOT . '/views/dashboard/warden.php';
        } else {
            $student = new Student($this->conn);
            $studentData = $student->getByUserId($userId);
            $stats = [];
            if ($studentData) {
                $currentRoom = getStudentCurrentRoom($this->conn, $studentData['id']);
                $pendingFees = getPendingFees($this->conn, $studentData['id']);
                $stats = [
                    'room' => $currentRoom ? $currentRoom['room_number'] : 'Not allocated',
                    'pending fees' => formatCurrency($pendingFees),
                    'complaints' => $this->count('complaints', 'student_id = ' . (int)$studentData['id']),
                    'leave requests' => $this->count('leave_requests', 'student_id = ' . (int)$studentData['id']),
                ];
            }
            
            require_once APP_ROOT . '/views/dashboard/student.php';
        }
    }

    private function count($table, $where = '1=1') {
        try {
            return (int)$this->conn->query("SELECT COUNT(*) FROM {$table} WHERE {$where}")->fetchColumn();
        } catch (Exception $e) {
            return 0;
        }
    }

    private function adminCharts() {
        return [
            'complaints' => $this->statusCounts('complaints'),
            'leaves' => $this->statusCounts('leaves'),
            'rooms' => $this->statusCounts('rooms'),
        ];
    }

    private function wardenCharts() {
        return [
            'complaints' => $this->statusCounts('complaints'),
            'rooms' => $this->statusCounts('rooms'),
        ];
    }

    private function statusCounts($table) {
        try {
            $rows = $this->conn->query("SELECT COALESCE(NULLIF(status, ''), 'open') AS label, COUNT(*) AS total FROM {$table} GROUP BY status")->fetchAll(PDO::FETCH_ASSOC);
            foreach ($rows as &$r) {
                $raw = strtolower($r['label'] ?? 'open');
                $r['label'] = ucwords(str_replace('_', ' ', $raw));
            }
            return $rows;
        } catch (Exception $e) {
            return [];
        }
    }
}

?>
