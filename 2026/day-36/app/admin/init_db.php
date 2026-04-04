<?php
session_start();
require_once __DIR__ . '/../includes/init.php';

// Only allow on localhost for security
if (!in_array($_SERVER['SERVER_NAME'], ['localhost', '127.0.0.1'])) {
    die('This script can only be run on localhost');
}

$messages = [];
$errors = [];

try {
    $pdo = getPDO();
    
    // Drop existing tables in reverse order to handle foreign key constraints
    $tables = ['reviews', 'order_items', 'orders', 'cart', 'books', 'users'];
    foreach ($tables as $table) {
        $pdo->exec("DROP TABLE IF EXISTS $table");
        $messages[] = "Dropped table: $table";
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
    $messages[] = "Database schema created successfully";
    
    // Add test user if not exists
    $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = ?");
    $stmt->execute(['test@example.com']);
    if (!$stmt->fetch()) {
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->execute([
            'testuser',
            'test@example.com',
            '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
            'user'
        ]);
        $messages[] = "Test user created successfully";
    }
    
    $messages[] = "Database initialization completed!";
    
} catch (Exception $e) {
    $errors[] = "Error: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Initialization</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            line-height: 1.6;
            padding: 2rem;
            max-width: 800px;
            margin: 0 auto;
            background-color: #f8fafc;
            color: #334155;
        }
        
        .container {
            background-color: white;
            padding: 2rem;
            border-radius: 0.5rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        
        h1 {
            margin-bottom: 1rem;
            color: #1e293b;
        }
        
        .message {
            padding: 1rem;
            border-radius: 0.375rem;
            margin-bottom: 1rem;
        }
        
        .success {
            background-color: #f0fdf4;
            color: #166534;
            border: 1px solid #dcfce7;
        }
        
        .error {
            background-color: #fef2f2;
            color: #991b1b;
            border: 1px solid #fee2e2;
        }
        
        .info-box {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            padding: 1rem;
            border-radius: 0.375rem;
            margin-top: 2rem;
        }
        
        .info-box h2 {
            font-size: 1.25rem;
            margin-bottom: 1rem;
            color: #1e293b;
        }
        
        .credentials {
            font-family: monospace;
            background-color: #f1f5f9;
            padding: 0.5rem;
            border-radius: 0.25rem;
            margin: 0.5rem 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Database Initialization</h1>
        
        <?php if (!empty($errors)): ?>
            <?php foreach ($errors as $error): ?>
                <div class="message error">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
        
        <?php if (!empty($messages)): ?>
            <?php foreach ($messages as $message): ?>
                <div class="message success">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endforeach; ?>
            
            <div class="info-box">
                <h2>Login Credentials</h2>
                <p><strong>Admin User:</strong></p>
                <div class="credentials">
                    Email: admin@example.com<br>
                    Password: admin123
                </div>
                
                <p><strong>Test User:</strong></p>
                <div class="credentials">
                    Email: test@example.com<br>
                    Password: test123
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
