<?php
require_once 'config/database.php';

$db = new Database();
$conn = $db->getConnection();

// Delete existing admin if exists
$conn->query("DELETE FROM users WHERE email = 'admin@example.com'");

// Create new admin user
$name = "Admin User";
$email = "admin@example.com";
$password = "admin123";
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

$stmt = $conn->prepare("
    INSERT INTO users (name, email, password, role, status) 
    VALUES (?, ?, ?, 'admin', 'active')
");

if ($stmt->execute([$name, $email, $hashed_password])) {
    echo "Admin user created successfully!\n";
    echo "Email: admin@example.com\n";
    echo "Password: admin123\n";
} else {
    echo "Failed to create admin user.";
} 