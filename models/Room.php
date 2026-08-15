<?php
/**
 * Room Model
 * 
 * Handles room-related database operations
 */

class Room {
    private $conn;
    private $table = 'rooms';
    
    public $id;
    public $block_id;
    public $hostel_id;
    public $room_number;
    public $room_type;
    public $capacity;
    public $occupied;
    public $beds_available;
    public $rent_per_month;
    public $monthly_fee;
    public $description;
    public $status;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    /**
     * Create new room
     */
    public function create() {
        try {
            $query = "INSERT INTO " . $this->table . " 
                      (block_id, room_number, room_type, capacity, occupied, 
                       monthly_fee, status) 
                      VALUES (?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $this->conn->prepare($query);
            
            return $stmt->execute([
                $this->block_id ?? 1,
                $this->room_number,
                $this->room_type ?? 'single',
                $this->capacity ?? 1,
                $this->occupied ?? 0,
                $this->rent_per_month ?? $this->monthly_fee ?? 0.00,
                $this->status ?? 'available'
            ]);
        } catch (Exception $e) {
            error_log('Error creating room: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get room by ID
     */
    public function getById($id) {
        try {
            $query = "SELECT r.*, COALESCE(h.name, 'Main Hostel') as hostel_name, COALESCE(hb.name, 'Block A') as block_name 
                      FROM " . $this->table . " r
                      LEFT JOIN hostel_blocks hb ON r.block_id = hb.id
                      LEFT JOIN hostels h ON hb.hostel_id = h.id
                      WHERE r.id = ? LIMIT 1";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch (Exception $e) {
            error_log('Error fetching room: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get all rooms with filters
     */
    public function getAll($filters = []) {
        try {
            $query = "SELECT r.*, COALESCE(h.name, 'Main Hostel') as hostel_name, COALESCE(hb.name, 'Block A') as block_name,
                      (r.capacity - COALESCE(r.occupied, 0)) as beds_available
                      FROM " . $this->table . " r
                      LEFT JOIN hostel_blocks hb ON r.block_id = hb.id
                      LEFT JOIN hostels h ON hb.hostel_id = h.id
                      WHERE 1=1";
            $params = [];
            
            if (!empty($filters['hostel_id'])) {
                $query .= " AND hb.hostel_id = ?";
                $params[] = $filters['hostel_id'];
            }
            
            if (!empty($filters['status'])) {
                $query .= " AND r.status = ?";
                $params[] = $filters['status'];
            }
            
            if (!empty($filters['room_type'])) {
                $query .= " AND r.room_type = ?";
                $params[] = $filters['room_type'];
            }
            
            if (!empty($filters['search'])) {
                $query .= " AND r.room_number LIKE ?";
                $params[] = '%' . $filters['search'] . '%';
            }
            
            $query .= " ORDER BY r.room_number ASC";
            
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
            error_log('Error fetching rooms: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get available rooms
     */
    public function getAvailable($hostelId = null) {
        try {
            $query = "SELECT r.*, COALESCE(h.name, 'Main Hostel') as hostel_name, COALESCE(hb.name, 'Block A') as block_name,
                      (r.capacity - COALESCE(r.occupied, 0)) as available_beds
                      FROM " . $this->table . " r
                      LEFT JOIN hostel_blocks hb ON r.block_id = hb.id
                      LEFT JOIN hostels h ON hb.hostel_id = h.id
                      WHERE r.status = 'available'";
            
            $params = [];
            
            if ($hostelId) {
                $query .= " AND hb.hostel_id = ?";
                $params[] = $hostelId;
            }
            
            $query .= " HAVING available_beds > 0 ORDER BY r.room_number ASC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log('Error fetching available rooms: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Update room
     */
    public function update() {
        try {
            $query = "UPDATE " . $this->table . " SET 
                      room_number = ?,
                      room_type = ?,
                      capacity = ?,
                      occupied = ?,
                      monthly_fee = ?,
                      status = ?
                      WHERE id = ?";
            
            $stmt = $this->conn->prepare($query);
            
            return $stmt->execute([
                $this->room_number,
                $this->room_type,
                $this->capacity,
                $this->occupied ?? 0,
                $this->rent_per_month ?? $this->monthly_fee ?? 0.00,
                $this->status,
                $this->id
            ]);
        } catch (Exception $e) {
            error_log('Error updating room: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Delete room
     */
    public function delete($id) {
        try {
            $query = "DELETE FROM " . $this->table . " WHERE id = ?";
            $stmt = $this->conn->prepare($query);
            return $stmt->execute([$id]);
        } catch (Exception $e) {
            error_log('Error deleting room: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Count total rooms
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
            error_log('Error counting rooms: ' . $e->getMessage());
            return 0;
        }
    }
}
