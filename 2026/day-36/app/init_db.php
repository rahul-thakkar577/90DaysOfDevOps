<?php
// Initialize error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    // Include database class
    require_once __DIR__ . '/config/database.php';
    
    // Create database instance
    $db = new Database();
    $pdo = $db->getConnection();
    
    echo "Database connection successful!\n";
    
    // Check if tables exist
    $tables = ['users', 'authors', 'categories', 'books', 'cart', 'orders', 'order_items', 'reviews'];
    $existing_tables = [];
    
    foreach ($tables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($stmt->fetch()) {
            $existing_tables[] = $table;
        }
    }
    
    if (count($existing_tables) === count($tables)) {
        echo "All required tables exist:\n";
        foreach ($existing_tables as $table) {
            echo "- $table\n";
        }
    } else {
        echo "Missing tables:\n";
        $missing = array_diff($tables, $existing_tables);
        foreach ($missing as $table) {
            echo "- $table\n";
        }
    }
    
    // Check sample data
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM books");
    $book_count = $stmt->fetch()['count'];
    echo "\nNumber of books in database: $book_count\n";
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM categories");
    $category_count = $stmt->fetch()['count'];
    echo "Number of categories in database: $category_count\n";
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM authors");
    $author_count = $stmt->fetch()['count'];
    echo "Number of authors in database: $author_count\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
