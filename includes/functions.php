<?php
/**
 * Business Logic Functions
 * 
 * Core application functions
 */

/**
 * Get dashboard statistics
 */
function getDashboardStats($conn) {
    $stats = [];
    
    try {
        // Total Students
        $stmt = $conn->query("SELECT COUNT(*) as count FROM students WHERE status = 'active'");
        $stats['total_students'] = $stmt->fetch()['count'];
        
        // Total Rooms
        $stmt = $conn->query("SELECT COUNT(*) as count FROM rooms");
        $stats['total_rooms'] = $stmt->fetch()['count'];
        
        // Available Rooms
        $stmt = $conn->query("SELECT COUNT(*) as count FROM rooms WHERE status = 'available'");
        $stats['available_rooms'] = $stmt->fetch()['count'];
        
        // Occupied Rooms
        $stmt = $conn->query("SELECT COUNT(*) as count FROM rooms WHERE status = 'occupied'");
        $stats['occupied_rooms'] = $stmt->fetch()['count'];
        
        // Pending Fees
        $stmt = $conn->query("SELECT COUNT(*) as count FROM fees WHERE status IN ('pending', 'overdue')");
        $stats['pending_fees'] = $stmt->fetch()['count'];
        
        // Open Complaints
        $stmt = $conn->query("SELECT COUNT(*) as count FROM complaints WHERE status IN ('open', 'in_progress')");
        $stats['open_complaints'] = $stmt->fetch()['count'];
        
        // Total Hostels
        $stmt = $conn->query("SELECT COUNT(*) as count FROM hostels WHERE status = 'active'");
        $stats['total_hostels'] = $stmt->fetch()['count'];
        
        return $stats;
    } catch (Exception $e) {
        return [];
    }
}

/**
 * Get recent activities
 */
function getRecentActivities($conn, $limit = 10) {
    try {
        $stmt = $conn->prepare("
            SELECT al.*, u.name, u.email
            FROM activity_logs al
            JOIN users u ON al.user_id = u.id
            ORDER BY al.created_at DESC
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    } catch (Exception $e) {
        return [];
    }
}

/**
 * Log activity
 */
function logActivity($conn, $userId, $action, $module, $description = '', $ipAddress = '') {
    try {
        $ipAddress = $ipAddress ?: ($_SERVER['REMOTE_ADDR'] ?? '');
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        $stmt = $conn->prepare("
            INSERT INTO activity_logs (user_id, action, module, description, ip_address, user_agent)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        return $stmt->execute([$userId, $action, $module, $description, $ipAddress, $userAgent]);
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Get pending fees for student
 */
function getPendingFees($conn, $studentId) {
    try {
        $stmt = $conn->prepare("
            SELECT SUM(amount) as total
            FROM fees
            WHERE student_id = ? AND status IN ('pending', 'overdue', 'partial')
        ");
        $stmt->execute([$studentId]);
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    } catch (Exception $e) {
        return 0;
    }
}

/**
 * Get student current room allocation
 */
function getStudentCurrentRoom($conn, $studentId) {
    try {
        $stmt = $conn->prepare("
            SELECT ra.*, r.room_number, h.name as hostel_name
            FROM room_allocations ra
            JOIN rooms r ON ra.room_id = r.id
            JOIN hostels h ON ra.hostel_id = h.id
            WHERE ra.student_id = ? AND ra.status = 'active'
            LIMIT 1
        ");
        $stmt->execute([$studentId]);
        return $stmt->fetch();
    } catch (Exception $e) {
        return null;
    }
}

/**
 * Get available beds in room
 */
function getAvailableBeds($conn, $roomId) {
    try {
        $stmt = $conn->prepare("
            SELECT COUNT(*) as available
            FROM (
                SELECT 1 FROM rooms r
                WHERE r.id = ? AND r.capacity > (
                    SELECT COUNT(*) FROM room_allocations
                    WHERE room_id = ? AND status = 'active'
                )
            ) as available_count
        ");
        $stmt->execute([$roomId, $roomId]);
        $result = $stmt->fetch();
        return $result['available'] ?? 0;
    } catch (Exception $e) {
        return 0;
    }
}

/**
 * Calculate fee report
 */
function getFeeReport($conn, $filters = []) {
    try {
        $query = "
            SELECT 
                f.*,
                s.roll_number,
                u.name as student_name,
                h.name as hostel_name,
                r.room_number
            FROM fees f
            JOIN students s ON f.student_id = s.id
            JOIN users u ON s.user_id = u.id
            JOIN room_allocations ra ON f.allocation_id = ra.id
            JOIN rooms r ON ra.room_id = r.id
            JOIN hostels h ON ra.hostel_id = h.id
            WHERE 1=1
        ";
        
        $params = [];
        
        if (!empty($filters['status'])) {
            $query .= " AND f.status = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['month'])) {
            $query .= " AND MONTH(f.created_at) = ?";
            $params[] = $filters['month'];
        }
        
        if (!empty($filters['year'])) {
            $query .= " AND YEAR(f.created_at) = ?";
            $params[] = $filters['year'];
        }
        
        if (!empty($filters['hostel_id'])) {
            $query .= " AND ra.hostel_id = ?";
            $params[] = $filters['hostel_id'];
        }
        
        $query .= " ORDER BY f.created_at DESC";
        
        $stmt = $conn->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll();
    } catch (Exception $e) {
        return [];
    }
}

/**
 * Get visitor report
 */
function getVisitorReport($conn, $filters = []) {
    try {
        $query = "
            SELECT 
                v.*,
                s.roll_number,
                u.name as student_name,
                h.name as hostel_name
            FROM visitors v
            JOIN students s ON v.student_id = s.id
            JOIN users u ON s.user_id = u.id
            JOIN room_allocations ra ON v.student_id = ra.student_id AND ra.status = 'active'
            JOIN hostels h ON ra.hostel_id = h.id
            WHERE 1=1
        ";
        
        $params = [];
        
        if (!empty($filters['status'])) {
            $query .= " AND v.status = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['month'])) {
            $query .= " AND MONTH(v.entry_date) = ?";
            $params[] = $filters['month'];
        }
        
        if (!empty($filters['year'])) {
            $query .= " AND YEAR(v.entry_date) = ?";
            $params[] = $filters['year'];
        }
        
        $query .= " ORDER BY v.entry_date DESC";
        
        $stmt = $conn->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll();
    } catch (Exception $e) {
        return [];
    }
}

/**
 * Get complaint report
 */
function getComplaintReport($conn, $filters = []) {
    try {
        $query = "
            SELECT 
                c.*,
                s.roll_number,
                u.name as student_name,
                h.name as hostel_name
            FROM complaints c
            JOIN students s ON c.student_id = s.id
            JOIN users u ON s.user_id = u.id
            JOIN room_allocations ra ON c.student_id = ra.student_id AND ra.status = 'active'
            JOIN hostels h ON ra.hostel_id = h.id
            WHERE 1=1
        ";
        
        $params = [];
        
        if (!empty($filters['status'])) {
            $query .= " AND c.status = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['priority'])) {
            $query .= " AND c.priority = ?";
            $params[] = $filters['priority'];
        }
        
        if (!empty($filters['category'])) {
            $query .= " AND c.category = ?";
            $params[] = $filters['category'];
        }
        
        $query .= " ORDER BY c.priority DESC, c.created_at DESC";
        
        $stmt = $conn->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll();
    } catch (Exception $e) {
        return [];
    }
}

?>
