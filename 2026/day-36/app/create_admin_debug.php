<?php
require_once 'config/database.php';

$db = new Database();
$conn = $db->getConnection();

// First, let's check if we can connect to the database
if (!$conn) {
    die("Database connection failed!");
}

// Delete existing admin
$conn->query("DELETE FROM users WHERE email = 'admin@example.com'");

// Create new admin user with debug output
$name = "Admin User";
$email = "admin@example.com";
$password = "admin123";
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

echo "Creating admin with:\n";
echo "Email: " . $email . "\n";
echo "Password (plain): " . $password . "\n";
echo "Password (hashed): " . $hashed_password . "\n";

$stmt = $conn->prepare("
    INSERT INTO users (name, email, password, role, status) 
    VALUES (?, ?, ?, 'admin', 'active')
");

if ($stmt->execute([$name, $email, $hashed_password])) {
    echo "\nAdmin created successfully!\n";
    
    // Verify the admin was created
    $verify = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $verify->execute([$email]);
    $admin = $verify->fetch(PDO::FETCH_ASSOC);
    
    if ($admin) {
        echo "\nVerification:\n";
        echo "User ID: " . $admin['user_id'] . "\n";
        echo "Name: " . $admin['name'] . "\n";
        echo "Email: " . $admin['email'] . "\n";
        echo "Role: " . $admin['role'] . "\n";
        echo "Status: " . $admin['status'] . "\n";
        
        // Test password verification
        echo "\nPassword verification test: ";
        echo (password_verify($password, $admin['password']) ? "PASSED" : "FAILED");
    }
} else {
    echo "Failed to create admin user.\n";
    print_r($stmt->errorInfo());
} 