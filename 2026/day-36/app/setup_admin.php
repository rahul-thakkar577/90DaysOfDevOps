<?php
require_once 'config/database.php';

try {
    $db = new Database();
    $conn = $db->getConnection();

    // Create admins table
    $sql = "CREATE TABLE IF NOT EXISTS admins (
        admin_id INT PRIMARY KEY AUTO_INCREMENT,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        status ENUM('active', 'inactive') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    $conn->exec($sql);
    echo "✅ Admins table created successfully\n";

    // Check if admin exists
    $stmt = $conn->prepare("SELECT * FROM admins WHERE email = ?");
    $stmt->execute(['admin@admin.com']);
    $admin = $stmt->fetch();

    if (!$admin) {
        // Create default admin account
        $stmt = $conn->prepare("INSERT INTO admins (name, email, password, status) VALUES (?, ?, ?, ?)");
        $result = $stmt->execute([
            'Admin',
            'admin@admin.com',
            password_hash('admin123', PASSWORD_DEFAULT),
            'active'
        ]);

        if ($result) {
            echo "✅ Default admin account created\n";
            echo "Email: admin@admin.com\n";
            echo "Password: admin123\n";
        }
    } else {
        echo "ℹ️ Admin account already exists\n";
    }

} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>