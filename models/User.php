<?php
/**
 * User Model
 *
 * Handles user-related database operations
 */

class User {
    private $conn;
    private $table = 'users';

    public $id;
    public $name;
    public $email;
    public $phone;
    public $password;
    public $role;
    public $status;
    public $created_at;
    public $updated_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Create new user
     */
    public function create() {
        try {
            $query = "INSERT INTO " . $this->table . "
                      (name, email, password_hash, role, status)
                      VALUES (?, ?, ?, ?, ?)";

            $stmt = $this->conn->prepare($query);
            $hashedPassword = hashPassword($this->password);

            $success = $stmt->execute([
                $this->name,
                $this->email,
                $hashedPassword,
                $this->role ?? ROLE_STUDENT,
                $this->status ?? 'active'
            ]);

            if ($success) {
                $newUserId = (int)$this->conn->lastInsertId();
                $this->id = $newUserId;

                if (($this->role ?? ROLE_STUDENT) === ROLE_STUDENT) {
                    $maxStmt = $this->conn->query("SELECT MAX(id) as max_id FROM students");
                    $nextId = ((int)$maxStmt->fetchColumn()) + 1;
                    $stuId = 'STU' . sprintf('%03d', $nextId);

                    $sStmt = $this->conn->prepare("INSERT INTO students (user_id, student_id, full_name, email, phone, department, course, room_id, status) VALUES (?, ?, ?, ?, ?, 'Computer Science', 'B.Tech CS', 1, 'active')");
                    $sStmt->execute([
                        $newUserId,
                        $stuId,
                        $this->name,
                        $this->email,
                        $this->phone ?? '',
                    ]);
                }
                return true;
            }
            return false;
        } catch (Exception $e) {
            error_log('Error creating user: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get user by email
     */
    public function getByEmail() {
        try {
            $query = "SELECT * FROM " . $this->table . " WHERE email = ? LIMIT 1";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$this->email]);
            return $stmt->fetch();
        } catch (Exception $e) {
            error_log('Error fetching user: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get user by ID
     */
    public function getById($id) {
        try {
            $query = "SELECT * FROM " . $this->table . " WHERE id = ? LIMIT 1";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch (Exception $e) {
            error_log('Error fetching user: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get all users with filters
     */
    public function getAll($filters = []) {
        try {
            $query = "SELECT * FROM " . $this->table . " WHERE 1=1";
            $params = [];

            if (!empty($filters['role'])) {
                $query .= " AND role = ?";
                $params[] = $filters['role'];
            }

            if (!empty($filters['status'])) {
                $query .= " AND status = ?";
                $params[] = $filters['status'];
            }

            if (!empty($filters['search'])) {
                $query .= " AND (name LIKE ? OR email LIKE ?)";
                $search = '%' . $filters['search'] . '%';
                $params[] = $search;
                $params[] = $search;
            }

            $query .= " ORDER BY created_at DESC";

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
            error_log('Error fetching users: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Update user
     */
    public function update() {
        try {
            $query = "UPDATE " . $this->table . " SET
                      name = ?,
                      email = ?,
                      role = ?,
                      status = ?,
                      updated_at = NOW()
                      WHERE id = ?";

            $stmt = $this->conn->prepare($query);

            $res = $stmt->execute([
                $this->name,
                $this->email,
                $this->role,
                $this->status,
                $this->id
            ]);

            if ($res && !empty($this->phone)) {
                $sStmt = $this->conn->prepare("UPDATE students SET phone = ?, full_name = ? WHERE user_id = ?");
                $sStmt->execute([$this->phone, $this->name, $this->id]);
            }
            return $res;
        } catch (Exception $e) {
            error_log('Error updating user: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Change password
     */
    public function changePassword($id, $newPassword) {
        try {
            $query = "UPDATE " . $this->table . " SET
                      password_hash = ?,
                      updated_at = NOW()
                      WHERE id = ?";

            $stmt = $this->conn->prepare($query);
            $hashedPassword = hashPassword($newPassword);

            return $stmt->execute([$hashedPassword, $id]);
        } catch (Exception $e) {
            error_log('Error changing password: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete user
     */
    public function delete($id) {
        try {
            $query = "DELETE FROM " . $this->table . " WHERE id = ?";
            $stmt = $this->conn->prepare($query);
            return $stmt->execute([$id]);
        } catch (Exception $e) {
            error_log('Error deleting user: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Count total users
     */
    public function count($filters = []) {
        try {
            $query = "SELECT COUNT(*) as count FROM " . $this->table . " WHERE 1=1";
            $params = [];

            if (!empty($filters['role'])) {
                $query .= " AND role = ?";
                $params[] = $filters['role'];
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
            error_log('Error counting users: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Verify login credentials for an active account.
     */
    public function verifyLogin($email, $password) {
        $this->email = $email;
        $user = $this->getByEmail();

        if (!$user || $user['status'] !== 'active') {
            return null;
        }

        $passHash = $user['password_hash'] ?? $user['password'] ?? '';
        return verifyPassword($password, $passHash) ? $user : null;
    }
}
