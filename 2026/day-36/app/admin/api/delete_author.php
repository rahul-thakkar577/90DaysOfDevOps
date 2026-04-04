<?php
session_start();
require_once '../../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $author_id = $_POST['author_id'] ?? 0;
    
    try {
        $db = new Database();
        $conn = $db->getConnection();
        
        // Check if author has any books
        $stmt = $conn->prepare("SELECT COUNT(*) FROM books WHERE author_id = ?");
        $stmt->execute([$author_id]);
        $count = $stmt->fetchColumn();
        
        if ($count > 0) {
            echo json_encode(['success' => false, 'message' => 'Cannot delete author: they have books in the system']);
            exit();
        }
        
        $stmt = $conn->prepare("DELETE FROM authors WHERE author_id = ?");
        if ($stmt->execute([$author_id])) {
            echo json_encode(['success' => true, 'message' => 'Author deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete author']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'System error']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
} 