<?php
/**
 * Landing Page Controller
 */

class LandingController {
    private $conn;

    public function __construct() {
        global $conn;
        $this->conn = $conn;
    }

    public function index() {
        $totalRooms = 0;
        $occupiedRooms = 0;
        $totalBeds = 0;
        $occupiedBeds = 0;
        $occupancyRate = 83.4; // Default fallback highlight
        $totalStudents = 0;
        $totalWardens = 0;

        if ($this->conn) {
            try {
                $totalRooms = (int)$this->conn->query("SELECT COUNT(*) FROM rooms")->fetchColumn();
                $occupiedRooms = (int)$this->conn->query("SELECT COUNT(*) FROM rooms WHERE status = 'occupied'")->fetchColumn();
                $totalBeds = (int)$this->conn->query("SELECT SUM(capacity) FROM rooms")->fetchColumn();
                $occupiedBeds = (int)$this->conn->query("SELECT SUM(occupied) FROM rooms")->fetchColumn();
                $totalStudents = (int)$this->conn->query("SELECT COUNT(*) FROM students")->fetchColumn();
                $totalWardens = (int)$this->conn->query("SELECT COUNT(*) FROM users WHERE role = 'warden'")->fetchColumn();

                if ($totalBeds > 0) {
                    $occupancyRate = round(($occupiedBeds / $totalBeds) * 100, 1);
                } elseif ($totalRooms > 0) {
                    $occupancyRate = round(($occupiedRooms / $totalRooms) * 100, 1);
                }
            } catch (Exception $e) {
                error_log("LandingController error: " . $e->getMessage());
            }
        }

        require_once APP_ROOT . '/views/landing.php';
    }
}
