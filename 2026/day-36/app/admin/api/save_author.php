<?php
session_start();
require_once '../../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $author_id = $_POST['author_id'] ?? '';
    $name = trim($_POST['name'] ?? '');
    $bio = trim($_POST['bio'] ?? '');
    
    if (empty($name)) {
        echo json_encode(['success' => false, 'message' => 'Author name is required']);
        exit();
    }
    
    try {
        $db = new Database();
        $conn = $db->getConnection();
        
        if (empty($author_id)) {
            // Insert new author
            $stmt = $conn->prepare("INSERT INTO authors (name, bio) VALUES (?, ?)");
            $success = $stmt->execute([$name, $bio]);
            $message = 'Author added successfully';
        } else {
            // Update existing author
            $stmt = $conn->prepare("UPDATE authors SET name = ?, bio = ? WHERE author_id = ?");
            $success = $stmt->execute([$name, $bio, $author_id]);
            $message = 'Author updated successfully';
        }
        
        if ($success) {
            echo json_encode(['success' => true, 'message' => $message]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to save author']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'System error']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
} 