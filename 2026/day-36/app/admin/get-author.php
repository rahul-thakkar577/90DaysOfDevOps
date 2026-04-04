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
    exit('Author ID is required');
}

$db = new Database();
$conn = $db->getConnection();

$stmt = $conn->prepare("SELECT * FROM authors WHERE author_id = ?");
$stmt->execute([$_GET['id']]);
$author = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$author) {
    http_response_code(404);
    exit('Author not found');
}

header('Content-Type: application/json');
echo json_encode($author); 