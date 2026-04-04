<?php
session_start();
require_once '../config/database.php';

// Initialize database connection
$database = new Database();
$pdo = $database->getConnection();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'user') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Please login to add items to cart']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

$book_id = $_POST['book_id'] ?? 0;
$quantity = max(1, min(10, intval($_POST['quantity'] ?? 1)));

if (!$book_id) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid book selection']);
    exit();
}

try {
    // Start transaction
    $pdo->beginTransaction();
    
    // Check if book exists and get its price
    $stmt = $pdo->prepare("SELECT price FROM books WHERE book_id = ?");
    $stmt->execute([$book_id]);
    $book = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$book) {
        throw new Exception('Book not found');
    }
    
    // Check if item already exists in cart
    $stmt = $pdo->prepare("SELECT cart_id, quantity FROM cart WHERE user_id = ? AND book_id = ?");
    $stmt->execute([$_SESSION['user_id'], $book_id]);
    $cart_item = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($cart_item) {
        // Update quantity if total doesn't exceed 10
        $new_quantity = min(10, $cart_item['quantity'] + $quantity);
        $stmt = $pdo->prepare("UPDATE cart SET quantity = ? WHERE cart_id = ?");
        $stmt->execute([$new_quantity, $cart_item['cart_id']]);
        
        $message = $new_quantity === 10 ? 
            'Maximum quantity limit reached (10 items)' : 
            'Cart updated successfully';
    } else {
        // Add new item to cart
        $stmt = $pdo->prepare("
            INSERT INTO cart (user_id, book_id, quantity, created_at) 
            VALUES (?, ?, ?, NOW())
        ");
        $stmt->execute([$_SESSION['user_id'], $book_id, $quantity]);
        
        $message = 'Item added to cart successfully';
    }
    
    // Commit transaction
    $pdo->commit();
    
    // Get updated cart count
    $stmt = $pdo->prepare("SELECT SUM(quantity) as total FROM cart WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => $message,
        'cartCount' => (int)($result['total'] ?? 0)
    ]);

} catch (Exception $e) {
    // Rollback transaction on error
    if ($pdo->inTransaction()) {
        $pdo->rollback();
    }
    
    error_log("Cart Error: " . $e->getMessage());
    
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Failed to add item to cart. Please try again.'
    ]);
}