<?php
require_once 'config/database.php';

try {
    $db = new Database();
    $conn = $db->getConnection();

    // Reset users table with correct column names
    $conn->exec("DROP TABLE IF EXISTS users");
    $sql = "CREATE TABLE users (
        user_id INT PRIMARY KEY AUTO_INCREMENT,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        phone VARCHAR(20),
        address TEXT,
        role ENUM('user', 'admin') DEFAULT 'user',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $conn->exec($sql);
    echo "✅ Users table reset successfully\n";

    // Create test user account
    $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
    $stmt->execute([
        'Test User',
        'user@example.com',
        password_hash('user123', PASSWORD_DEFAULT),
        'user'
    ]);
    echo "✅ Test user account created\n";
    echo "   Email: user@example.com\n";
    echo "   Password: user123\n";

    // Create admin user account
    $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
    $stmt->execute([
        'Admin User',
        'admin@example.com',
        password_hash('admin123', PASSWORD_DEFAULT),
        'admin'
    ]);
    echo "✅ Admin user account created\n";
    echo "   Email: admin@example.com\n";
    echo "   Password: admin123\n";

    // Reset admins table
    $conn->exec("DROP TABLE IF EXISTS admins");
    $sql = "CREATE TABLE admins (
        admin_id INT PRIMARY KEY AUTO_INCREMENT,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        status ENUM('active', 'inactive') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $conn->exec($sql);
    echo "✅ Admins table reset successfully\n";

    // Create super admin account
    $stmt = $conn->prepare("INSERT INTO admins (name, email, password, status) VALUES (?, ?, ?, ?)");
    $stmt->execute([
        'Super Admin',
        'admin@admin.com',
        password_hash('admin123', PASSWORD_DEFAULT),
        'active'
    ]);
    echo "✅ Super admin account created\n";
    echo "   Email: admin@admin.com\n";
    echo "   Password: admin123\n";

} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
