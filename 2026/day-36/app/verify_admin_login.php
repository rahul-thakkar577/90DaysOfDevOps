<?php
require_once 'config/database.php';

$db = new Database();
$conn = $db->getConnection();

// Check if admins table exists
$tableCheck = $conn->query("SHOW TABLES LIKE 'admins'");
if ($tableCheck->rowCount() == 0) {
    echo "Admins table does not exist!\n";
    exit;
}

// Check admin credentials
$email = 'admin@admin.com';
$password = 'admin123';

$stmt = $conn->prepare("SELECT * FROM admins WHERE email = ?");
$stmt->execute([$email]);
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

if ($admin) {
    echo "Admin found in database:\n";
    echo "ID: " . $admin['admin_id'] . "\n";
    echo "Name: " . $admin['name'] . "\n";
    echo "Email: " . $admin['email'] . "\n";
    echo "Status: " . $admin['status'] . "\n";
    
    // Test password
    echo "\nTesting password 'admin123':\n";
    if (password_verify($password, $admin['password'])) {
        echo "Password verification: SUCCESS\n";
    } else {
        echo "Password verification: FAILED\n";
        
        // Create new password hash for reference
        echo "\nNew password hash for 'admin123': " . password_hash($password, PASSWORD_DEFAULT) . "\n";
    }
} else {
    echo "No admin found with email: admin@admin.com\n";
} 