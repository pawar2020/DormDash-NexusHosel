<?php
/**
 * Complaint Model
 * 
 * Handles complaint-related database operations
 */

class Complaint {
    private $conn;
    private $table = 'complaints';
    
    public $id;
    public $student_id;
    public $category;
    public $title;
    public $description;
    public $priority;
    public $status;
    public $assigned_to;
    public $admin_reply;
    public $replied_date;
    public $resolved_date;
    public $attachment_path;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    /**
     * Create new complaint
     */
    public function create() {
        try {
            $query = "INSERT INTO " . $this->table . " 
                      (student_id, category, title, description, priority, status) 
                      VALUES (?, ?, ?, ?, ?, ?)";
            
            $stmt = $this->conn->prepare($query);
            
            return $stmt->execute([
                $this->student_id,
                $this->category ?? 'other',
                $this->title,
                $this->description,
                $this->priority ?? 'low',
                $this->status ?? 'submitted'
            ]);
        } catch (Exception $e) {
            error_log('Error creating complaint: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get complaint by ID
     */
    public function getById($id) {
        try {
            $query = "SELECT c.*, COALESCE(s.student_id, 'STU001') as roll_number, COALESCE(u.name, s.full_name) as student_name, 
                      w.name as assigned_to_name, COALESCE(h.name, 'Main Hostel') as hostel_name
                      FROM " . $this->table . " c
                      LEFT JOIN students s ON c.student_id = s.id
                      LEFT JOIN users u ON s.user_id = u.id
                      LEFT JOIN users w ON c.assigned_to = w.id
                      LEFT JOIN rooms r ON s.room_id = r.id
                      LEFT JOIN hostel_blocks hb ON r.block_id = hb.id
                      LEFT JOIN hostels h ON hb.hostel_id = h.id
                      WHERE c.id = ? LIMIT 1";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch (Exception $e) {
            error_log('Error fetching complaint: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get all complaints with filters
     */
    public function getAll($filters = []) {
        try {
            $query = "SELECT c.*, COALESCE(s.student_id, 'STU001') as roll_number, COALESCE(u.name, s.full_name) as student_name, 
                      w.name as assigned_to_name, COALESCE(h.name, 'Main Hostel') as hostel_name
                      FROM " . $this->table . " c
                      LEFT JOIN students s ON c.student_id = s.id
                      LEFT JOIN users u ON s.user_id = u.id
                      LEFT JOIN users w ON c.assigned_to = w.id
                      LEFT JOIN rooms r ON s.room_id = r.id
                      LEFT JOIN hostel_blocks hb ON r.block_id = hb.id
                      LEFT JOIN hostels h ON hb.hostel_id = h.id
                      WHERE 1=1";
            $params = [];
            
            if (!empty($filters['student_id'])) {
                $query .= " AND c.student_id = ?";
                $params[] = $filters['student_id'];
            }
            
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
            
            if (!empty($filters['search'])) {
                $query .= " AND (c.title LIKE ? OR c.description LIKE ? OR s.student_id LIKE ?)";
                $search = '%' . $filters['search'] . '%';
                $params[] = $search;
                $params[] = $search;
                $params[] = $search;
            }
            
            $query .= " ORDER BY c.created_at DESC";
            
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
            error_log('Error fetching complaints: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Update complaint status
     */
    public function updateStatus($id, $status, $assignedTo = null) {
        try {
            $query = "UPDATE " . $this->table . " SET status = ?";
            $params = [$status];
            
            if ($assignedTo !== null) {
                $query .= ", assigned_to = ?";
                $params[] = $assignedTo;
            }
            
            if ($status === 'resolved') {
                $query .= ", updated_at = NOW()";
            }
            
            $query .= " WHERE id = ?";
            $params[] = $id;
            
            $stmt = $this->conn->prepare($query);
            return $stmt->execute($params);
        } catch (Exception $e) {
            error_log('Error updating complaint status: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Resolve complaint by ID
     */
    public function resolve($id) {
        return $this->updateStatus($id, 'resolved');
    }
    
    /**
     * Count total complaints
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
            error_log('Error counting complaints: ' . $e->getMessage());
            return 0;
        }
    }
}
