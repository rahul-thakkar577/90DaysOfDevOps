<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    http_response_code(403);
    exit('Unauthorized');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed');
}

$book_id = $_POST['book_id'] ?? null;
if (!$book_id) {
    http_response_code(400);
    exit('Book ID is required');
}

// Validate file upload
if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    exit('Invalid file upload');
}

$file = $_FILES['image'];
$allowed_types = ['image/jpeg', 'image/png', 'image/webp'];
$max_size = 5 * 1024 * 1024; // 5MB

// Validate file type and size
if (!in_array($file['type'], $allowed_types)) {
    http_response_code(400);
    exit('Invalid file type. Only JPG, PNG and WebP are allowed.');
}

if ($file['size'] > $max_size) {
    http_response_code(400);
    exit('File too large. Maximum size is 5MB.');
}

// Generate unique filename
$extension = pathinfo($file['name'], PATHINFO_EXTENSION);
$filename = uniqid('book_') . '.' . $extension;
$upload_path = '../uploads/books/' . $filename;

// Create directory if it doesn't exist
if (!is_dir('../uploads/books')) {
    mkdir('../uploads/books', 0777, true);
}

// Move uploaded file
if (!move_uploaded_file($file['tmp_name'], $upload_path)) {
    http_response_code(500);
    exit('Failed to upload file');
}

// Update database
$db = new Database();
$conn = $db->getConnection();

$stmt = $conn->prepare("UPDATE books SET image_url = ? WHERE book_id = ?");
if (!$stmt->execute(['uploads/books/' . $filename, $book_id])) {
    unlink($upload_path);
    http_response_code(500);
    exit('Failed to update database');
}

echo json_encode([
    'success' => true,
    'image_url' => 'uploads/books/' . $filename
]); 