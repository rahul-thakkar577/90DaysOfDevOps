<?php
session_start();
require_once '../config/database.php';

// Check if admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    http_response_code(403);
    exit('Unauthorized');
}

if (!isset($_GET['id'])) {
    http_response_code(400);
    exit('User ID is required');
}

$db = new Database();
$conn = $db->getConnection();

$stmt = $conn->prepare("
    SELECT u.*, COUNT(o.order_id) as total_orders, 
    COALESCE(SUM(o.total_price), 0) as total_spent 
    FROM users u 
    LEFT JOIN orders o ON u.user_id = o.user_id 
    WHERE u.user_id = ? AND u.role = 'user' 
    GROUP BY u.user_id
");
$stmt->execute([$_GET['id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    http_response_code(404);
    exit('User not found');
}

// Remove sensitive information
unset($user['password']);

header('Content-Type: application/json');
echo json_encode($user); 