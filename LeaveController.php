<?php

class LeaveController {
    private $conn;

    public function __construct() {
        global $conn;
        $this->conn = $conn;
    }

    public function index() {
        $leave = new LeaveRequest($this->conn);
        $studentData = null;
        if (hasRole(ROLE_STUDENT)) {
            $studentData = (new Student($this->conn))->getByUserId(getCurrentUserId());
            $leaves = $studentData ? $leave->getAll($studentData['id']) : [];
        } else {
            $leaves = $leave->getAll();
        }
        require APP_ROOT . '/views/leaves/index.php';
    }

    public function add() {
        requireStudent();
        $error = '';
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
                $error = 'Your session expired. Please submit the form again.';
            } else {
                $from = trim($_POST['from_date'] ?? '');
                $to = trim($_POST['to_date'] ?? '');
                $reason = trim($_POST['reason'] ?? '');
                $student = (new Student($this->conn))->getByUserId(getCurrentUserId());
                
                if (!$student) {
                    $error = 'Student profile record not found. Please contact administration.';
                } elseif (empty($from) || empty($to)) {
                    $error = 'Please select both From date and To date.';
                } elseif (empty($reason)) {
                    $error = 'Please provide a reason for your leave request.';
                } elseif ($to < $from) {
                    $error = 'End date (To date) cannot be earlier than Start date (From date).';
                } elseif ((new LeaveRequest($this->conn))->create($student['id'], $from, $to, $reason)) {
                    setMessage('success', 'Leave request submitted for approval.');
                    redirect(APP_URL . '/index.php?action=leaves');
                } else {
                    $error = 'Unable to submit the leave request.';
                }
            }
        }
        require APP_ROOT . '/views/leaves/add.php';
    }

    public function decision($id) {
        requireWarden();
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST' || !verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            setMessage('error', 'Invalid leave approval request.');
        } else {
            $status = $_POST['status'] ?? '';
            $remarks = trim($_POST['remarks'] ?? '');
            if (!in_array($status, ['approved', 'rejected'], true)) {
                setMessage('error', 'Choose an approval decision.');
            } elseif ((new LeaveRequest($this->conn))->decide((int)$id, $status, getCurrentUserId(), $remarks)) {
                setMessage('success', 'Leave request ' . $status . '.');
            } else {
                setMessage('error', 'This leave request is no longer pending.');
            }
        }
        redirect(APP_URL . '/index.php?action=leaves');
    }
}
