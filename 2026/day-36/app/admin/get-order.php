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
    exit('Order ID is required');
}

$db = new Database();
$conn = $db->getConnection();

$stmt = $conn->prepare("
    SELECT o.*, u.name as user_name, u.email as user_email,
           b.title as book_title, b.price as book_price
    FROM orders o
    JOIN users u ON o.user_id = u.user_id
    JOIN books b ON o.book_id = b.book_id
    WHERE o.order_id = ?
");
$stmt->execute([$_GET['id']]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    http_response_code(404);
    exit('Order not found');
}

header('Content-Type: application/json');
echo json_encode($order); 