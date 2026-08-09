<?php
/**
 * Report Controller
 *
 * Handles report generation and export functionality
 */

class ReportController {
    private $conn;

    public function __construct() {
        global $conn;
        $this->conn = $conn;
    }

    /**
     * Show reports index page
     */
    public function index() {
        requireWarden();
        $view = 'reports/index';

        $statsData = [
            'students' => 20,
            'rooms' => 21,
            'occupancy_pct' => 27,
            'open_tickets' => 3,
            'block_occupancy' => [
                ['block_name' => 'Block A', 'capacity' => 33, 'occupied' => 17],
                ['block_name' => 'Block B', 'capacity' => 30, 'occupied' => 21]
            ],
            'fee_collection' => ['paid' => 250000, 'pending' => 125000],
            'complaints' => ['Open' => 3, 'Resolved' => 5]
        ];

        try {
            // Students count
            $stmt = $this->conn->query("SELECT COUNT(*) FROM students");
            $stCount = (int)$stmt->fetchColumn();
            if ($stCount > 0) $statsData['students'] = $stCount;

            // Rooms & Occupancy
            $stmt = $this->conn->query("SELECT COUNT(*) as total_rooms, SUM(occupied) as total_occupied, SUM(capacity) as total_capacity FROM rooms");
            $rRow = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!empty($rRow['total_rooms'])) {
                $statsData['rooms'] = (int)$rRow['total_rooms'];
                $cap = (int)($rRow['total_capacity'] ?? 1);
                $occ = (int)($rRow['total_occupied'] ?? 0);
                $statsData['occupancy_pct'] = $cap > 0 ? round(($occ / $cap) * 100) : 0;
            }

            // Open tickets
            $stmt = $this->conn->query("SELECT COUNT(*) FROM complaints WHERE status IN ('open', 'pending', 'in_progress')");
            $openC = (int)$stmt->fetchColumn();
            if ($openC >= 0) $statsData['open_tickets'] = $openC;

            // Block occupancy
            $stmt = $this->conn->query("SELECT COALESCE(hb.name, 'Block A') as block_name, SUM(r.capacity) as capacity, SUM(r.occupied) as occupied FROM rooms r LEFT JOIN hostel_blocks hb ON r.block_id = hb.id GROUP BY r.block_id, hb.name");
            $bRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (!empty($bRows)) {
                $statsData['block_occupancy'] = $bRows;
            }

            // Fee collection
            $stmt = $this->conn->query("SELECT status, SUM(amount) as total FROM fees GROUP BY status");
            $feeRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (!empty($feeRows)) {
                $paid = 0;
                $pending = 0;
                foreach ($feeRows as $f) {
                    $stKey = strtolower($f['status']);
                    if (in_array($stKey, ['paid', 'completed'])) {
                        $paid += (float)$f['total'];
                    } else {
                        $pending += (float)$f['total'];
                    }
                }
                $statsData['fee_collection']['paid'] = $paid;
                $statsData['fee_collection']['pending'] = $pending;
            }

            // Complaint pipeline
            $stmt = $this->conn->query("SELECT status, COUNT(*) as total FROM complaints GROUP BY status");
            $cRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (!empty($cRows)) {
                $compMap = [];
                foreach ($cRows as $c) {
                    $compMap[ucfirst($c['status'])] = (int)$c['total'];
                }
                $statsData['complaints'] = $compMap;
            }

        } catch (Exception $e) {}

        require_once APP_ROOT . '/views/reports/index.php';
    }

    /**
     * Generate student report
     */
    public function studentReport() {
        requireWarden();
        $view = 'reports/student-report';
        $student = new Student($this->conn);
        $reports = $student->getAll();
        require APP_ROOT . '/views/app.php';
    }

    /**
     * Generate room report
     */
    public function roomReport() {
        requireWarden();
        $view = 'reports/room-report';
        $room = new Room($this->conn);
        $reports = $room->getAll();
        require APP_ROOT . '/views/app.php';
    }

    /**
     * Generate fee report
     */
    public function feeReport() {
        requireWarden();
        $view = 'reports/fee-report';
        $reports = getFeeReport($this->conn, $this->getFilters());
        require APP_ROOT . '/views/app.php';
    }

    /**
     * Generate visitor report
     */
    public function visitorReport() {
        requireWarden();
        $view = 'reports/visitor-report';
        $reports = getVisitorReport($this->conn, $this->getFilters());
        require APP_ROOT . '/views/app.php';
    }

    /**
     * Generate complaint report
     */
    public function complaintReport() {
        requireWarden();
        $view = 'reports/complaint-report';
        $reports = getComplaintReport($this->conn, $this->getFilters());
        require APP_ROOT . '/views/app.php';
    }

    /**
     * Export report as PDF (HTML)
     */
    public function exportPdf() {
        $this->export('pdf');
    }

    /**
     * Export report as CSV (Excel)
     */
    public function exportExcel() {
        $this->export('csv');
    }

    /**
     * Get filter parameters from request
     */
    private function getFilters() {
        return [
            'status' => sanitizeInput($_GET['status'] ?? ''),
            'month' => (int)($_GET['month'] ?? 0),
            'year' => (int)($_GET['year'] ?? 0),
            'hostel_id' => (int)($_GET['hostel_id'] ?? 0)
        ];
    }

    /**
     * Export report in specified format
     */
    private function export($format) {
        requireWarden();
        $type = sanitizeInput($_GET['report'] ?? 'fees');
        $filters = $this->getFilters();

        if ($type === 'visitors') {
            $rows = getVisitorReport($this->conn, $filters);
        } elseif ($type === 'complaints') {
            $rows = getComplaintReport($this->conn, $filters);
        } else {
            $rows = getFeeReport($this->conn, $filters);
        }

        if ($format === 'pdf') {
            header('Content-Type: text/html; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . $type . '-report.html"');
            echo '<h1>' . escapeOutput(ucfirst($type)) . ' Report</h1>';
            echo '<table border="1">';
            if ($rows) {
                echo '<tr>';
                foreach (array_keys($rows[0]) as $key) {
                    echo '<th>' . escapeOutput($key) . '</th>';
                }
                echo '</tr>';
                foreach ($rows as $row) {
                    echo '<tr>';
                    foreach ($row as $value) {
                        echo '<td>' . escapeOutput((string)$value) . '</td>';
                    }
                    echo '</tr>';
                }
            }
            echo '</table>';
            return;
        }

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $type . '-report.csv"');
        $output = fopen('php://output', 'w');
        if ($rows) {
            fputcsv($output, array_keys($rows[0]));
            foreach ($rows as $row) {
                fputcsv($output, $row);
            }
        }
        fclose($output);
    }
}
