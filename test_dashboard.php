<?php
/**
 * Test script — renders the admin dashboard with mock data
 * No database required. Verifies PHP view renders correctly.
 */
require_once 'config/config.php';
require_once 'includes/auth.php';
require_once 'includes/helpers.php';

// Mock session
$_SESSION['user_id'] = 1;
$_SESSION['user_role'] = 'admin';
$_SESSION['user'] = ['username' => 'admin', 'full_name' => 'Admin'];

// Mock data matching DashboardController output
$stats = [
    'total students' => 156,
    'occupied rooms' => 42,
    'available rooms' => 18,
    'wardens' => 5,
    'pending complaints' => 8,
    'pending leaves' => 3,
    'today visitors' => 12,
];

$chartData = [
    'complaints' => [
        ['label' => 'open', 'total' => 5],
        ['label' => 'in_progress', 'total' => 3],
        ['label' => 'resolved', 'total' => 12],
    ],
    'rooms' => [
        ['label' => 'occupied', 'total' => 42],
        ['label' => 'available', 'total' => 18],
        ['label' => 'maintenance', 'total' => 2],
    ],
    'leaves' => [
        ['label' => 'pending', 'total' => 3],
        ['label' => 'approved', 'total' => 8],
        ['label' => 'rejected', 'total' => 2],
    ],
];

// Render the admin dashboard view
