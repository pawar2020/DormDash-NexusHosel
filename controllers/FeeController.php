<?php
/**
 * Fee Controller
 *
 * Handles fee management
 */

class FeeController {
    private $conn;

    public function __construct() {
        global $conn;
        $this->conn = $conn;
    }

    /**
     * List all fees
     */
    public function index() {
        requireWarden();

        $view = 'fees/index';
        $page = (int)sanitizeInput($_GET['page'] ?? 1);
        $status = sanitizeInput($_GET['status'] ?? '');
        $search = sanitizeInput($_GET['search'] ?? '');

        $fee = new Fee($this->conn);
        $filters = [];

        if ($status) $filters['status'] = $status;
        if ($search) $filters['search'] = $search;

        $totalFees = $fee->count($filters);
        $pagination = getPaginationData($totalFees, $page, ITEMS_PER_PAGE);

        $filters['limit'] = ITEMS_PER_PAGE;
        $filters['offset'] = $pagination['offset'];
        $fees = $fee->getAll($filters);

        require_once APP_ROOT . '/views/app.php';
    }

    /**
     * Add new fee
     */
    public function add() {
        requireWarden();

        $view = 'fees/add';
        $error = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
                $error = 'Invalid security token';
            } else {
                $student_id = sanitizeInput($_POST['student_id'] ?? '');
                $allocation_id = sanitizeInput($_POST['allocation_id'] ?? '');
                $fee_type = sanitizeInput($_POST['fee_type'] ?? '');
                $amount = (float)sanitizeInput($_POST['amount'] ?? 0);
                $due_date = sanitizeInput($_POST['due_date'] ?? '');

                if (empty($student_id) || empty($allocation_id) || empty($fee_type) || $amount <= 0 || empty($due_date)) {
                    $error = 'Please fill all required fields';
                } else {
                    $fee = new Fee($this->conn);
                    $fee->student_id = $student_id;
                    $fee->allocation_id = $allocation_id;
                    $fee->fee_type = $fee_type;
                    $fee->amount = $amount;
                    $fee->due_date = $due_date;
                    $fee->status = 'pending';

                    if ($fee->create()) {
                        logActivity($this->conn, getCurrentUserId(), 'create', 'fees', 'Added fee');
                        header('Location: ' . APP_URL . '/index.php?action=fees');
                        exit;
                    } else {
                        $error = 'Error adding fee';
                    }
                }
            }
        }

        // Get students and allocations for dropdowns
        $students = getActiveStudents($this->conn);
        $allocations = getActiveAllocations($this->conn);

        $csrf_token = generateCsrfToken();
        require_once APP_ROOT . '/views/app.php';
    }

    /**
     * Edit fee
     */
    public function edit($id) {
        requireWarden();

        $view = 'fees/edit';
        $fee = new Fee($this->conn);
        $feeData = $fee->getById($id);

        if (!$feeData) {
            http_response_code(404);
            require_once APP_ROOT . '/views/app.php';
            return;
        }

        $error = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
                $error = 'Invalid security token';
            } else {
                $fee->id = $id;
                $fee->amount = (float)sanitizeInput($_POST['amount'] ?? 0);
                $fee->due_date = sanitizeInput($_POST['due_date'] ?? '');
                $fee->fee_type = sanitizeInput($_POST['fee_type'] ?? '');
                $fee->status = sanitizeInput($_POST['status'] ?? '');
                $fee->notes = sanitizeInput($_POST['notes'] ?? '');

                if ($fee->update()) {
                    logActivity($this->conn, getCurrentUserId(), 'update', 'fees', 'Updated fee');
                    header('Location: ' . APP_URL . '/index.php?action=fees');
                    exit;
                } else {
                    $error = 'Error updating fee';
                }
            }
        }

        $csrf_token = generateCsrfToken();
        require_once APP_ROOT . '/views/app.php';
    }

    /**
     * Delete fee
     */
    public function delete($id) {
        requireWarden();

        $fee = new Fee($this->conn);
        $feeData = $fee->getById($id);

        if (!$feeData) {
            http_response_code(404);
            require_once APP_ROOT . '/views/app.php';
            return;
        }

        if ($fee->delete($id)) {
            logActivity($this->conn, getCurrentUserId(), 'delete', 'fees', 'Deleted fee');
            setMessage('success', 'Fee deleted successfully!');
        } else {
            setMessage('error', 'Error deleting fee');
        }

        header('Location: ' . APP_URL . '/index.php?action=fees');
        exit;
    }

    /**
     * Mark fee as paid
     */
    public function markPaid($id) {
        requireWarden();

        $fee = new Fee($this->conn);
        $feeData = $fee->getById($id);

        if (!$feeData) {
            setMessage('error', 'Fee not found');
            header('Location: ' . APP_URL . '/index.php?action=fees');
            exit;
        }

        $paymentMethod = sanitizeInput($_POST['payment_method'] ?? 'cash');
        $transactionId = sanitizeInput($_POST['transaction_id'] ?? '');

        if ($fee->markAsPaid($id, $paymentMethod, $transactionId)) {
            logActivity($this->conn, getCurrentUserId(), 'mark_paid', 'fees', 'Marked fee as paid');
            setMessage('success', 'Fee marked as paid!');
        } else {
            setMessage('error', 'Error marking fee as paid');
        }

        header('Location: ' . APP_URL . '/index.php?action=fees&subaction=view&id=' . $id);
        exit;
    }

    /**
     * Generate receipt
     */
    public function generateReceipt($id) {
        requireWarden();

        $fee = new Fee($this->conn);
        $feeData = $fee->getById($id);

        if (!$feeData || $feeData['status'] !== 'paid') {
            setMessage('error', 'Receipt can only be generated for paid fees');
            header('Location: ' . APP_URL . '/index.php?action=fees');
            exit;
        }

        header('Content-Type: text/html; charset=utf-8');
        header('Content-Disposition: attachment; filename="receipt_' . $feeData['id'] . '.html"');
        require_once APP_ROOT . '/views/fees/receipt.php';
    }
}
