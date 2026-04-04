<?php
require_once 'config/database.php';

try {
    $db = new Database();
    $conn = $db->getConnection();

    // Create users table with correct column names from MEMORY
    $sql = "CREATE TABLE IF NOT EXISTS users (
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
    echo "✅ Users table created successfully\n";

    // Check if default user exists
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute(['admin@example.com']);
    $user = $stmt->fetch();

    if (!$user) {
        // Create default admin user
        $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
        $result = $stmt->execute([
            'Admin',
            'admin@example.com',
            password_hash('admin123', PASSWORD_DEFAULT),
            'admin'
        ]);

        if ($result) {
            echo "✅ Default admin user created\n";
            echo "Email: admin@example.com\n";
            echo "Password: admin123\n";
        }
    } else {
        // Update existing admin password
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
        $result = $stmt->execute([
            password_hash('admin123', PASSWORD_DEFAULT),
            'admin@example.com'
        ]);
        echo "✅ Admin password updated\n";
    }

    // Create books table with correct column names from MEMORY
    $sql = "CREATE TABLE IF NOT EXISTS books (
        book_id INT PRIMARY KEY AUTO_INCREMENT,
        title VARCHAR(255) NOT NULL,
        author_id INT,
        category_id INT,
        description TEXT,
        price DECIMAL(10,2),
        image_url VARCHAR(255),
        available_copies INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    $conn->exec($sql);
    echo "✅ Books table created successfully\n";

    // Create cart table with correct column names from MEMORY
    $sql = "CREATE TABLE IF NOT EXISTS cart (
        cart_id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT NOT NULL,
        book_id INT NOT NULL,
        quantity INT NOT NULL DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(user_id),
        FOREIGN KEY (book_id) REFERENCES books(book_id)
    )";
    
    $conn->exec($sql);
    echo "✅ Cart table created successfully\n";

    // Create orders table with correct column names from MEMORY
    $sql = "CREATE TABLE IF NOT EXISTS orders (
        order_id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT NOT NULL,
        total_price DECIMAL(10,2) NOT NULL,
        status ENUM('pending', 'completed', 'cancelled') DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(user_id)
    )";
    
    $conn->exec($sql);
    echo "✅ Orders table created successfully\n";

    // Create reviews table with correct column names from MEMORY
    $sql = "CREATE TABLE IF NOT EXISTS reviews (
        review_id INT PRIMARY KEY AUTO_INCREMENT,
        book_id INT NOT NULL,
        user_id INT NOT NULL,
        rating INT NOT NULL CHECK (rating BETWEEN 1 AND 5),
        comment TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (book_id) REFERENCES books(book_id),
        FOREIGN KEY (user_id) REFERENCES users(user_id)
    )";
    
    $conn->exec($sql);
    echo "✅ Reviews table created successfully\n";

} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
