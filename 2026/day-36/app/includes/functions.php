<?php
// Database functions
function getPDO() {
    global $pdo;
    if (!isset($pdo)) {
        $db = new Database();
        $pdo = $db->getConnection();
    }
    return $pdo;
}

// Authentication functions
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ../login.php');
        exit();
    }
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function loginUser($pdo, $email, $password) {
    try {
        $stmt = $pdo->prepare("SELECT user_id, username, email, password, role FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];
            return true;
        }
        return false;
    } catch (PDOException $e) {
        error_log("Login error: " . $e->getMessage());
        return false;
    }
}

// Cart functions
function getCartCount($pdo, $user_id) {
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM cart WHERE user_id = ?");
    $stmt->execute([$user_id]);
    return $stmt->fetch()['count'];
}

function addToCart($pdo, $user_id, $book_id, $quantity = 1) {
    // Check if book already in cart
    $stmt = $pdo->prepare("SELECT cart_id, quantity FROM cart WHERE user_id = ? AND book_id = ?");
    $stmt->execute([$user_id, $book_id]);
    $existing = $stmt->fetch();
    
    if ($existing) {
        // Update quantity
        $new_quantity = min(10, $existing['quantity'] + $quantity);
        $stmt = $pdo->prepare("UPDATE cart SET quantity = ? WHERE cart_id = ?");
        return $stmt->execute([$new_quantity, $existing['cart_id']]);
    } else {
        // Add new item
        $stmt = $pdo->prepare("INSERT INTO cart (user_id, book_id, quantity) VALUES (?, ?, ?)");
        return $stmt->execute([$user_id, $book_id, $quantity]);
    }
}

function removeFromCart($pdo, $cart_id, $user_id) {
    $stmt = $pdo->prepare("DELETE FROM cart WHERE cart_id = ? AND user_id = ?");
    return $stmt->execute([$cart_id, $user_id]);
}

function updateCartQuantity($pdo, $cart_id, $user_id, $quantity) {
    $quantity = max(1, min(10, $quantity));
    $stmt = $pdo->prepare("UPDATE cart SET quantity = ? WHERE cart_id = ? AND user_id = ?");
    return $stmt->execute([$quantity, $cart_id, $user_id]);
}

// Book functions
function getBookDetails($pdo, $book_id) {
    $stmt = $pdo->prepare("
        SELECT b.*, 
               IFNULL(AVG(r.rating), 0) as avg_rating,
               COUNT(DISTINCT r.review_id) as review_count
        FROM books b
        LEFT JOIN reviews r ON b.book_id = r.book_id
        WHERE b.book_id = ?
        GROUP BY b.book_id
    ");
    $stmt->execute([$book_id]);
    return $stmt->fetch();
}

function getRecentBooks($pdo, $limit = 6) {
    $stmt = $pdo->prepare("
        SELECT b.*, 
               IFNULL(AVG(r.rating), 0) as avg_rating,
               COUNT(DISTINCT r.review_id) as review_count
        FROM books b
        LEFT JOIN reviews r ON b.book_id = r.book_id
        GROUP BY b.book_id
        ORDER BY b.created_at DESC
        LIMIT ?
    ");
    $stmt->execute([$limit]);
    return $stmt->fetchAll();
}

// Order functions
function getRecentOrders($pdo, $user_id, $limit = 5) {
    $stmt = $pdo->prepare("
        SELECT o.*, COUNT(oi.order_item_id) as total_items 
        FROM orders o 
        LEFT JOIN order_items oi ON o.order_id = oi.order_id 
        WHERE o.user_id = ? 
        GROUP BY o.order_id 
        ORDER BY o.created_at DESC 
        LIMIT ?
    ");
    $stmt->execute([$user_id, $limit]);
    return $stmt->fetchAll();
}

// Utility functions
function formatPrice($price) {
    return '₹' . number_format($price, 2);
}

function formatDate($date) {
    return date('M j, Y', strtotime($date));
}

function getImageUrl($image_url) {
    return $image_url ? '../' . $image_url : null;
}

// Error handling functions
function handleError($e) {
    error_log($e->getMessage());
    if (isDevEnvironment()) {
        throw $e;
    }
    return false;
}

function isDevEnvironment() {
    return $_SERVER['SERVER_NAME'] === 'localhost' || $_SERVER['SERVER_ADDR'] === '127.0.0.1';
}
?>
