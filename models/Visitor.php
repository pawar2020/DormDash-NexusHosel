<?php
/**
 * Visitor Model
 * 
 * Handles visitor-related database operations
 */

class Visitor {
    private $conn;
    private $table = 'visitors';
    
    public $id;
    public $student_id;
    public $visitor_name;
    public $visitor_phone;
    public $relationship;
    public $id_proof_type;
    public $id_proof_number;
    public $entry_date;
    public $exit_date;
    public $entry_time;
    public $exit_time;
    public $purpose;
    public $notes;
    public $status;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    /**
     * Create new visitor entry
     */
    public function create() {
        try {
            $query = "INSERT INTO " . $this->table . " 
                      (student_id, visitor_name, phone, purpose, status) 
                      VALUES (?, ?, ?, ?, ?)";
            
            $stmt = $this->conn->prepare($query);
            
            return $stmt->execute([
                $this->student_id,
                $this->visitor_name,
                $this->visitor_phone ?? $this->phone ?? '',
                $this->purpose ?? '',
                $this->status ?? 'pending'
            ]);
        } catch (Exception $e) {
            error_log('Error creating visitor entry: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get visitor by ID
     */
    public function getById($id) {
        try {
            $query = "SELECT v.*, COALESCE(s.student_id, 'STU001') as roll_number, COALESCE(u.name, s.full_name) as student_name, 
                      COALESCE(h.name, 'Main Hostel') as hostel_name
                      FROM " . $this->table . " v
                      LEFT JOIN students s ON v.student_id = s.id
                      LEFT JOIN users u ON s.user_id = u.id
                      LEFT JOIN rooms r ON s.room_id = r.id
                      LEFT JOIN hostel_blocks hb ON r.block_id = hb.id
                      LEFT JOIN hostels h ON hb.hostel_id = h.id
                      WHERE v.id = ? LIMIT 1";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch (Exception $e) {
            error_log('Error fetching visitor: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get all visitors with filters
     */
    public function getAll($filters = []) {
        try {
            $query = "SELECT v.*, COALESCE(s.student_id, 'STU001') as roll_number, COALESCE(u.name, s.full_name) as student_name, 
                      COALESCE(h.name, 'Main Hostel') as hostel_name
                      FROM " . $this->table . " v
                      LEFT JOIN students s ON v.student_id = s.id
                      LEFT JOIN users u ON s.user_id = u.id
                      LEFT JOIN rooms r ON s.room_id = r.id
                      LEFT JOIN hostel_blocks hb ON r.block_id = hb.id
                      LEFT JOIN hostels h ON hb.hostel_id = h.id
                      WHERE 1=1";
            $params = [];
            
            if (!empty($filters['student_id'])) {
                $query .= " AND v.student_id = ?";
                $params[] = $filters['student_id'];
            }
            
            if (!empty($filters['status'])) {
                $query .= " AND v.status = ?";
                $params[] = $filters['status'];
            }
            
            if (!empty($filters['search'])) {
                $query .= " AND (v.visitor_name LIKE ? OR s.student_id LIKE ?)";
                $search = '%' . $filters['search'] . '%';
                $params[] = $search;
                $params[] = $search;
            }
            
            $query .= " ORDER BY v.id DESC";
            
            if (!empty($filters['limit'])) {
                $query .= " LIMIT " . (int)$filters['limit'];
                if (!empty($filters['offset'])) {
                    $query .= " OFFSET " . (int)$filters['offset'];
                }
            }
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log('Error fetching visitors: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Record visitor exit
     */
    public function recordExit($id) {
        try {
            $query = "UPDATE " . $this->table . " SET 
                      exit_time = NOW(),
                      status = 'checked_out'
                      WHERE id = ?";
            
            $stmt = $this->conn->prepare($query);
            return $stmt->execute([$id]);
        } catch (Exception $e) {
            error_log('Error recording visitor exit: ' . $e->getMessage());
            return false;
        }
    }

    public function approve($id) {
        try {
            $stmt = $this->conn->prepare("UPDATE visitors SET status = 'approved' WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->rowCount() === 1;
        } catch (Exception $e) {
            error_log('Error approving visitor request: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Count total visitors
     */
    public function count($filters = []) {
        try {
            $query = "SELECT COUNT(*) as count FROM " . $this->table . " WHERE 1=1";
            $params = [];
            
            if (!empty($filters['status'])) {
                $query .= " AND status = ?";
                $params[] = $filters['status'];
            }
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
            $result = $stmt->fetch();
            return $result['count'] ?? 0;
        } catch (Exception $e) {
            error_log('Error counting visitors: ' . $e->getMessage());
            return 0;
        }
    }
}
