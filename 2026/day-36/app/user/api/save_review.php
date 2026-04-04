<?php
session_start();
require_once '../../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login to write a review']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $book_id = $_POST['book_id'] ?? 0;
    $rating = $_POST['rating'] ?? 0;
    $comment = trim($_POST['comment'] ?? '');
    
    if (empty($book_id) || empty($rating) || empty($comment)) {
        echo json_encode(['success' => false, 'message' => 'All fields are required']);
        exit();
    }
    
    if ($rating < 1 || $rating > 5) {
        echo json_encode(['success' => false, 'message' => 'Invalid rating']);
        exit();
    }
    
    try {
        $db = new Database();
        $conn = $db->getConnection();
        
        // Check if user has purchased the book
        $stmt = $conn->prepare("
            SELECT COUNT(*) FROM orders 
            JOIN order_items od ON orders.order_id = od.order_id
            WHERE orders.user_id = ? AND od.book_id = ? AND orders.status = 'delivered'
        ");
        $stmt->execute([$user_id, $book_id]);
        if ($stmt->fetchColumn() == 0) {
            echo json_encode(['success' => false, 'message' => 'You must purchase the book before reviewing']);
            exit();
        }
        
        // Check if reviews table exists
        $stmt = $conn->query("SHOW TABLES LIKE 'reviews'");
        if ($stmt->rowCount() == 0) {
            // Create reviews table if it doesn't exist
            $conn->exec("
                CREATE TABLE reviews (
                    review_id INT PRIMARY KEY AUTO_INCREMENT,
                    user_id INT NOT NULL,
                    book_id INT NOT NULL,
                    rating INT NOT NULL,
                    comment TEXT NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    FOREIGN KEY (user_id) REFERENCES users(user_id),
                    FOREIGN KEY (book_id) REFERENCES books(book_id),
                    UNIQUE KEY unique_review (user_id, book_id)
                )
            ");
        }
        
        // Check for existing review
        $stmt = $conn->prepare("SELECT review_id FROM reviews WHERE user_id = ? AND book_id = ?");
        $stmt->execute([$user_id, $book_id]);
        $existingReview = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($existingReview) {
            // Update existing review
            $stmt = $conn->prepare("
                UPDATE reviews 
                SET rating = ?, comment = ?
                WHERE review_id = ?
            ");
            $success = $stmt->execute([$rating, $comment, $existingReview['review_id']]);
            $message = 'Review updated successfully';
        } else {
            // Insert new review
            $stmt = $conn->prepare("
                INSERT INTO reviews (user_id, book_id, rating, comment) 
                VALUES (?, ?, ?, ?)
            ");
            $success = $stmt->execute([$user_id, $book_id, $rating, $comment]);
            $message = 'Review submitted successfully';
        }
        
        if ($success) {
            echo json_encode(['success' => true, 'message' => $message]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to save review']);
        }
    } catch (PDOException $e) {
        error_log("Review submission error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
} 