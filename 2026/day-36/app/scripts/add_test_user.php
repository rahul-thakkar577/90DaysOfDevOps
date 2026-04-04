<?php
require_once __DIR__ . '/../includes/init.php';

try {
    $pdo = getPDO();
    
    // Check if test user already exists
    $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = ?");
    $stmt->execute(['test@example.com']);
    $exists = $stmt->fetch();
    
    if (!$exists) {
        // Add test user (password: test123)
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->execute([
            'testuser',
            'test@example.com',
            '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
            'user'
        ]);
        echo "Test user created successfully!\n";
    } else {
        echo "Test user already exists.\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
