<?php

class LeaveRequest {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create($studentId, $fromDate, $toDate, $reason) {
        $stmt = $this->conn->prepare(
            'INSERT INTO leave_requests (student_id, from_date, to_date, reason) VALUES (?, ?, ?, ?)'
        );
        return $stmt->execute([$studentId, $fromDate, $toDate, $reason]);
    }

    public function getAll($studentId = null) {
        $sql = 'SELECT lr.*, u.name AS student_name, s.student_id AS roll_number, reviewer.name AS reviewer_name
                FROM leave_requests lr
                JOIN students s ON s.id = lr.student_id
                JOIN users u ON u.id = s.user_id
                LEFT JOIN users reviewer ON reviewer.id = lr.reviewed_by';
        $params = [];
        if ($studentId !== null) {
            $sql .= ' WHERE lr.student_id = ?';
            $params[] = $studentId;
        }
        $sql .= ' ORDER BY lr.created_at DESC';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function decide($id, $status, $reviewerId, $remarks) {
        $stmt = $this->conn->prepare(
            "UPDATE leave_requests SET status = ?, reviewed_by = ?, reviewed_at = NOW(), remarks = ?
             WHERE id = ? AND status = 'pending'"
        );
        $stmt->execute([$status, $reviewerId, $remarks, $id]);
        return $stmt->rowCount() === 1;
    }
}
