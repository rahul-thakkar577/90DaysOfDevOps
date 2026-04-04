<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    // Create connection
    $conn = new PDO(
        "mysql:host=localhost;dbname=library_db",
        "root",
        ""
    );
    
    // Set error mode
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Test queries using the correct column names
    $tests = [
        "Books Table" => "SELECT book_id, title, image_url, created_at FROM books LIMIT 1",
        "Orders Table" => "SELECT order_id, total_price, created_at FROM orders LIMIT 1",
        "Reviews Table" => "SELECT review_id, book_id, user_id, rating FROM reviews LIMIT 1",
        "Cart Table" => "SELECT cart_id, book_id, user_id, quantity FROM cart LIMIT 1",
        "Users Table" => "SELECT user_id, email, role FROM users LIMIT 1"
    ];
    
    echo "<h2>Database Connection Test Results:</h2>";
    echo "<pre>";
    
    // Connection status
    echo "✅ Database connection successful!\n";
    echo "Server Info: " . $conn->getAttribute(PDO::ATTR_SERVER_VERSION) . "\n\n";
    
    // Test each table
    foreach ($tests as $table => $query) {
        try {
            $stmt = $conn->query($query);
            echo "✅ {$table}: Successfully queried\n";
            if ($table === "Users Table") {
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($user) {
                    echo "   Found user with email: " . $user['email'] . "\n";
                }
            }
        } catch (PDOException $e) {
            echo "❌ {$table}: " . $e->getMessage() . "\n";
        }
    }
    
    echo "</pre>";
    
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>
