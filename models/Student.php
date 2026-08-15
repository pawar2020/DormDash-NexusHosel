<?php
/**
 * Student Model
 * 
 * Handles student-related database operations
 */

class Student {
    private $conn;
    private $table = 'students';
    
    public $id;
    public $user_id;
    public $roll_number;
    public $registration_number;
    public $course;
    public $year;
    public $semester;
    public $date_of_birth;
    public $gender;
    public $father_name;
    public $mother_name;
    public $parent_phone;
    public $permanent_address;
    public $current_address;
    public $admission_date;
    public $photo_path;
    public $status;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    public function create() {
        try {
            $query = "INSERT INTO " . $this->table . " 
                      (user_id, student_id, full_name, email, phone, course, department, 
                       year_level, address, guardian_name, guardian_phone, admission_date, status) 
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $this->conn->prepare($query);
            
            return $stmt->execute([
                $this->user_id,
                $this->student_id ?? $this->roll_number ?? ('STU' . time()),
                $this->full_name ?? $this->name ?? 'Student',
                $this->email,
                $this->phone ?? '',
                $this->course ?? 'B.Tech',
                $this->department ?? 'Computer Science',
                $this->year_level ?? $this->year ?? '1',
                $this->address ?? $this->permanent_address ?? '',
                $this->guardian_name ?? $this->father_name ?? '',
                $this->guardian_phone ?? $this->parent_phone ?? '',
                $this->admission_date ?? date('Y-m-d'),
                $this->status ?? 'active'
            ]);
        } catch (Exception $e) {
            error_log('Error creating student: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get student by ID
     */
    public function getById($id) {
        try {
            $query = "SELECT s.*, u.name, u.email 
                      FROM " . $this->table . " s
                      JOIN users u ON s.user_id = u.id
                      WHERE s.id = ? LIMIT 1";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch (Exception $e) {
            error_log('Error fetching student: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get student by user ID
     */
    public function getByUserId($userId) {
        try {
            $query = "SELECT s.*, u.name, u.email 
                      FROM " . $this->table . " s
                      JOIN users u ON s.user_id = u.id
                      WHERE s.user_id = ? LIMIT 1";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$userId]);
            $res = $stmt->fetch();
            if ($res) return $res;

            // Fallback: If user_id is unlinked, match by email or create profile
            $uStmt = $this->conn->prepare("SELECT email, name FROM users WHERE id = ? LIMIT 1");
            $uStmt->execute([$userId]);
            $uRow = $uStmt->fetch();
            if ($uRow) {
                $email = $uRow['email'];
                $sStmt = $this->conn->prepare("SELECT * FROM " . $this->table . " WHERE email = ? OR full_name = ? LIMIT 1");
                $sStmt->execute([$email, $uRow['name']]);
                $sRow = $sStmt->fetch();
                if ($sRow) {
                    $this->conn->prepare("UPDATE " . $this->table . " SET user_id = ? WHERE id = ?")->execute([$userId, $sRow['id']]);
                    return array_merge($sRow, ['name' => $uRow['name'], 'email' => $email]);
                }

                $maxStmt = $this->conn->query("SELECT MAX(id) as max_id FROM " . $this->table);
                $nextId = ((int)$maxStmt->fetchColumn()) + 1;
                $stuId = 'STU' . sprintf('%03d', $nextId);
                $ins = $this->conn->prepare("INSERT INTO " . $this->table . " (user_id, student_id, full_name, email, department, course, room_id, status) VALUES (?, ?, ?, ?, 'Computer Science', 'B.Tech CS', 1, 'active')");
                $ins->execute([$userId, $stuId, $uRow['name'], $email]);
                $newId = $this->conn->lastInsertId();
                return [
                    'id' => $newId,
                    'user_id' => $userId,
                    'student_id' => $stuId,
                    'full_name' => $uRow['name'],
                    'email' => $email,
                    'department' => 'Computer Science',
                    'course' => 'B.Tech CS',
                    'room_id' => 1,
                    'status' => 'active'
                ];
            }
            return null;
        } catch (Exception $e) {
            error_log('Error fetching student: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get student by roll number
     */
    public function getByRollNumber($rollNumber) {
        try {
            $query = "SELECT s.*, COALESCE(u.name, s.full_name) as name, COALESCE(u.email, s.email) as email 
                      FROM " . $this->table . " s
                      LEFT JOIN users u ON s.user_id = u.id
                      WHERE s.student_id = ? LIMIT 1";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$rollNumber]);
            return $stmt->fetch();
        } catch (Exception $e) {
            error_log('Error fetching student: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get all students with filters
     */
    public function getAll($filters = []) {
        try {
            $query = "SELECT s.*, COALESCE(u.name, s.full_name) as name, COALESCE(u.email, s.email) as email 
                      FROM " . $this->table . " s
                      LEFT JOIN users u ON s.user_id = u.id
                      WHERE 1=1";
            $params = [];
            
            if (!empty($filters['status'])) {
                $query .= " AND s.status = ?";
                $params[] = $filters['status'];
            }
            
            if (!empty($filters['course'])) {
                $query .= " AND s.course = ?";
                $params[] = $filters['course'];
            }
            
            if (!empty($filters['year'])) {
                $query .= " AND s.year_level = ?";
                $params[] = $filters['year'];
            }
            
            if (!empty($filters['search'])) {
                $query .= " AND (s.student_id LIKE ? OR s.full_name LIKE ? OR s.email LIKE ? OR u.name LIKE ?)";
                $search = '%' . $filters['search'] . '%';
                $params[] = $search;
                $params[] = $search;
                $params[] = $search;
                $params[] = $search;
            }
            
            $query .= " ORDER BY s.id DESC";
            
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
            error_log('Error fetching students: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Update student
     */
    public function update() {
        try {
            $query = "UPDATE " . $this->table . " SET 
                      roll_number = ?,
                      registration_number = ?,
                      course = ?,
                      year = ?,
                      semester = ?,
                      date_of_birth = ?,
                      gender = ?,
                      father_name = ?,
                      mother_name = ?,
                      parent_phone = ?,
                      permanent_address = ?,
                      current_address = ?,
                      admission_date = ?,
                      photo_path = ?,
                      status = ?
                      WHERE id = ?";
            
            $stmt = $this->conn->prepare($query);
            
            return $stmt->execute([
                $this->roll_number,
                $this->registration_number,
                $this->course,
                $this->year,
                $this->semester,
                $this->date_of_birth,
                $this->gender,
                $this->father_name,
                $this->mother_name,
                $this->parent_phone,
                $this->permanent_address,
                $this->current_address,
                $this->admission_date,
                $this->photo_path,
                $this->status,
                $this->id
            ]);
        } catch (Exception $e) {
            error_log('Error updating student: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Delete student
     */
    public function delete($id) {
        try {
            $query = "DELETE FROM " . $this->table . " WHERE id = ?";
            $stmt = $this->conn->prepare($query);
            return $stmt->execute([$id]);
        } catch (Exception $e) {
            error_log('Error deleting student: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Count total students
     */
    public function count($filters = []) {
        try {
            $query = "SELECT COUNT(*) as count FROM " . $this->table . " WHERE 1=1";
            $params = [];
            
            if (!empty($filters['status'])) {
                $query .= " AND status = ?";
                $params[] = $filters['status'];
            }
            
            if (!empty($filters['course'])) {
                $query .= " AND course = ?";
                $params[] = $filters['course'];
            }
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
            $result = $stmt->fetch();
            return $result['count'] ?? 0;
        } catch (Exception $e) {
            error_log('Error counting students: ' . $e->getMessage());
            return 0;
        }
    }
}

?>
