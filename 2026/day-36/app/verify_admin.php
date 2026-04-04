<?php
require_once 'config/database.php';

$db = new Database();
$conn = $db->getConnection();

// Check if admin exists
$stmt = $conn->prepare("SELECT * FROM users WHERE role = 'admin'");
$stmt->execute();
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

if ($admin) {
    echo "Admin exists:\n";
    echo "Email: " . $admin['email'] . "\n";
    echo "Role: " . $admin['role'] . "\n";
    echo "Status: " . $admin['status'] . "\n";
} else {
    echo "No admin found in database.\n";
    
    // Create new admin
    $name = "Admin User";
    $email = "admin@example.com";
    $password = "admin123";
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("
        INSERT INTO users (name, email, password, role, status) 
        VALUES (?, ?, ?, 'admin', 'active')
    ");

    if ($stmt->execute([$name, $email, $hashed_password])) {
        echo "\nNew admin created:\n";
        echo "Email: admin@example.com\n";
        echo "Password: admin123\n";
    }
} 