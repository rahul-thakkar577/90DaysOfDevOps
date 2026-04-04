<?php
session_start();
require_once '../../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $user_id = $_GET['user_id'] ?? 0;
    
    try {
        $db = new Database();
        $conn = $db->getConnection();
        
        // Get user details
        $stmt = $conn->prepare("
            SELECT user_id, name, email, status, created_at, role, phone, address 
            FROM users 
            WHERE user_id = ? AND role = 'user'
        ");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'User not found']);
            exit();
        }
        
        // Format created_at date
        $user['created_at'] = date('M d, Y', strtotime($user['created_at']));
        
        // Get recent orders with their total price and status
        $stmt = $conn->prepare("
            SELECT o.order_id, o.total_price, o.status, o.created_at
            FROM orders o 
            WHERE o.user_id = ? 
            ORDER BY o.created_at DESC 
            LIMIT 5
        ");
        $stmt->execute([$user_id]);
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Format order dates and prices
        foreach ($orders as &$order) {
            $order['created_at'] = date('M d, Y', strtotime($order['created_at']));
            $order['total_price'] = number_format($order['total_price'], 2);
        }
        
        echo json_encode([
            'success' => true,
            'user' => $user,
            'orders' => $orders
        ]);
    } catch (Exception $e) {
        error_log("Error in get_user_details.php: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'System error']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}