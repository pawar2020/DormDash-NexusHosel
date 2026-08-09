<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/models/User.php';

$hash = password_hash('password', PASSWORD_BCRYPT, ['cost' => 10]);
$stmt = $conn->prepare("UPDATE users SET password = ?");
$stmt->execute([$hash]);
echo "Updated " . $stmt->rowCount() . " password hashes to 'password'\n";

// Verify
$user = new User($conn);
$loginUser = $user->verifyLogin('admin@hostel.com', 'password');
echo "Login test: " . ($loginUser ? "PASS (user: " . $loginUser['name'] . ")" : "FAIL") . "\n";
