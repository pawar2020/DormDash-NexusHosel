<?php
/**
 * Fee Model
 * 
 * Handles fee-related database operations
 */

class Fee {
    private $conn;
    private $table = 'fees';
    
    public $id;
    public $student_id;
    public $allocation_id;
    public $fee_type;
    public $amount;
    public $due_date;
    public $paid_date;
    public $payment_method;
    public $transaction_id;
    public $status;
    public $notes;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    /**
     * Create new fee
     */
    public function create() {
        try {
            $query = "INSERT INTO " . $this->table . " 
                      (student_id, allocation_id, fee_type, amount, due_date, 
                       payment_method, transaction_id, status, notes) 
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $this->conn->prepare($query);
            
            return $stmt->execute([
                $this->student_id,
                $this->allocation_id,
                $this->fee_type,
                $this->amount,
                $this->due_date,
                $this->payment_method,
                $this->transaction_id,
                $this->status ?? 'pending',
                $this->notes
            ]);
        } catch (Exception $e) {
            error_log('Error creating fee: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get fee by ID
     */
    public function getById($id) {
        try {
            $query = "SELECT f.*, s.roll_number, u.name as student_name, h.name as hostel_name,
                      r.room_number
                      FROM " . $this->table . " f
                      JOIN students s ON f.student_id = s.id
                      JOIN users u ON s.user_id = u.id
                      JOIN room_allocations ra ON f.allocation_id = ra.id
                      JOIN rooms r ON ra.room_id = r.id
                      JOIN hostels h ON ra.hostel_id = h.id
                      WHERE f.id = ? LIMIT 1";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch (Exception $e) {
            error_log('Error fetching fee: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get all fees with filters
     */
    public function getAll($filters = []) {
        try {
            $query = "SELECT f.*, COALESCE(s.student_id, 'STU001') as roll_number, COALESCE(u.name, s.full_name) as student_name, 
                      COALESCE(h.name, 'Main Hostel') as hostel_name, COALESCE(r.room_number, 'N/A') as room_number
                      FROM " . $this->table . " f
                      LEFT JOIN students s ON f.student_id = s.id
                      LEFT JOIN users u ON s.user_id = u.id
                      LEFT JOIN rooms r ON s.room_id = r.id
                      LEFT JOIN hostel_blocks hb ON r.block_id = hb.id
                      LEFT JOIN hostels h ON hb.hostel_id = h.id
                      WHERE 1=1";
            $params = [];
            
            if (!empty($filters['student_id'])) {
                $query .= " AND f.student_id = ?";
                $params[] = $filters['student_id'];
            }
            
            if (!empty($filters['status'])) {
                $query .= " AND f.status = ?";
                $params[] = $filters['status'];
            }
            
            if (!empty($filters['fee_type'])) {
                $query .= " AND f.fee_type = ?";
                $params[] = $filters['fee_type'];
            }
            
            if (!empty($filters['search'])) {
                $query .= " AND (s.roll_number LIKE ? OR u.name LIKE ?)";
                $search = '%' . $filters['search'] . '%';
                $params[] = $search;
                $params[] = $search;
            }
            
            $query .= " ORDER BY f.created_at DESC";
            
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
            error_log('Error fetching fees: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get pending fees for student
     */
    public function getStudentPendingFees($studentId) {
        try {
            $query = "SELECT f.* 
                      FROM " . $this->table . " f
                      WHERE f.student_id = ? AND f.status IN ('pending', 'overdue', 'partial')
                      ORDER BY f.due_date ASC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$studentId]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log('Error fetching pending fees: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Update fee
     */
    public function update() {
        try {
            $query = "UPDATE " . $this->table . " SET 
                      amount = ?,
                      due_date = ?,
                      paid_date = ?,
                      payment_method = ?,
                      transaction_id = ?,
                      status = ?,
                      notes = ?
                      WHERE id = ?";
            
            $stmt = $this->conn->prepare($query);
            
            return $stmt->execute([
                $this->amount,
                $this->due_date,
                $this->paid_date,
                $this->payment_method,
                $this->transaction_id,
                $this->status,
                $this->notes,
                $this->id
            ]);
        } catch (Exception $e) {
            error_log('Error updating fee: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Mark fee as paid
     */
    public function markAsPaid($id, $paymentMethod = 'cash', $transactionId = '') {
        try {
            $query = "UPDATE " . $this->table . " SET 
                      status = 'paid',
                      paid_date = NOW(),
                      payment_method = ?,
                      transaction_id = ?
                      WHERE id = ?";
            
            $stmt = $this->conn->prepare($query);
            return $stmt->execute([$paymentMethod, $transactionId, $id]);
        } catch (Exception $e) {
            error_log('Error marking fee as paid: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Delete fee
     */
    public function delete($id) {
        try {
            $query = "DELETE FROM " . $this->table . " WHERE id = ?";
            $stmt = $this->conn->prepare($query);
            return $stmt->execute([$id]);
        } catch (Exception $e) {
            error_log('Error deleting fee: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Count total fees
     */
    public function count($filters = []) {
        try {
            $query = "SELECT COUNT(*) as count FROM " . $this->table . " WHERE 1=1";
            $params = [];
            
            if (!empty($filters['student_id'])) {
                $query .= " AND student_id = ?";
                $params[] = $filters['student_id'];
            }
            
            if (!empty($filters['status'])) {
                $query .= " AND status = ?";
                $params[] = $filters['status'];
            }
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
            $result = $stmt->fetch();
            return $result['count'] ?? 0;
        } catch (Exception $e) {
            error_log('Error counting fees: ' . $e->getMessage());
            return 0;
        }
    }
}

?>
