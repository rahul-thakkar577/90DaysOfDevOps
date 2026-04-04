<?php
require_once 'config/database.php';

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    // First, delete existing admin
    $stmt = $conn->prepare("DELETE FROM users WHERE email = ?");
    $stmt->execute(['admin@example.com']);
    
    // Create new admin with proper password hash
    $password = 'admin123';
    $hash = password_hash($password, PASSWORD_DEFAULT);
    
    $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
    $result = $stmt->execute(['Admin', 'admin@example.com', $hash, 'admin']);
    
    if ($result) {
        // Verify the admin account
        $stmt = $conn->prepare("SELECT user_id, email, role, password FROM users WHERE email = ?");
        $stmt->execute(['admin@example.com']);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo "✅ Admin account reset successfully\n";
        echo "\nAdmin Details:\n";
        echo "Email: " . $user['email'] . "\n";
        echo "Role: " . $user['role'] . "\n";
        echo "Password Hash Length: " . strlen($user['password']) . "\n";
        
        // Verify password verification works
        if (password_verify('admin123', $user['password'])) {
            echo "\n✅ Password verification successful\n";
        } else {
            echo "\n❌ Password verification failed\n";
        }
    } else {
        echo "❌ Failed to reset admin account\n";
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
