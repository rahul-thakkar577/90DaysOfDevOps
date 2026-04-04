<?php
require_once 'config/database.php';

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    // Create a proper password hash
    $password = 'admin123';
    $hash = password_hash($password, PASSWORD_DEFAULT);
    
    // Update the admin password
    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
    $result = $stmt->execute([$hash, 'admin@example.com']);
    
    if ($result) {
        echo "✅ Admin password updated successfully\n";
        
        // Verify the update
        $stmt = $conn->prepare("SELECT user_id, email, role FROM users WHERE email = ?");
        $stmt->execute(['admin@example.com']);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo "Admin user details:\n";
        echo "User ID: " . $user['user_id'] . "\n";
        echo "Email: " . $user['email'] . "\n";
        echo "Role: " . $user['role'] . "\n";
    } else {
        echo "❌ Failed to update admin password\n";
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
