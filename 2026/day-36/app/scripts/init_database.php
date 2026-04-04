<?php
require_once __DIR__ . '/../includes/init.php';

try {
    $pdo = getPDO();
    
    // Drop existing tables in reverse order to handle foreign key constraints
    $tables = ['reviews', 'order_items', 'orders', 'cart', 'books', 'users'];
    foreach ($tables as $table) {
        $pdo->exec("DROP TABLE IF EXISTS $table");
    }
    
    // Read and execute the database schema
    $sql = file_get_contents(__DIR__ . '/../database.sql');
    
    // Split SQL file into individual statements
    $statements = array_filter(
        array_map(
            'trim',
            explode(';', $sql)
        )
    );
    
    // Execute each statement
    foreach ($statements as $statement) {
        if (!empty($statement)) {
            $pdo->exec($statement);
        }
    }
    
    // Add test user (password: test123)
    $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
    $stmt->execute([
        'testuser',
        'test@example.com',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        'user'
    ]);
    
    echo "Database initialized successfully!\n";
    echo "You can now login with:\n";
    echo "Admin - Email: admin@example.com, Password: admin123\n";
    echo "Test User - Email: test@example.com, Password: test123\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
