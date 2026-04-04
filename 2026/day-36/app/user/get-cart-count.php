<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['count' => 0]);
    exit();
}

$db = new Database();
$conn = $db->getConnection();

// Get cart count using proper column names as per MEMORY
$stmt = $conn->prepare("
    SELECT SUM(quantity) as total_items 
    FROM cart 
    WHERE user_id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$result = $stmt->fetch(PDO::FETCH_ASSOC);

header('Content-Type: application/json');
echo json_encode(['count' => (int)($result['total_items'] ?? 0)]);