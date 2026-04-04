<?php
session_start();
require_once '../../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category_id = $_POST['category_id'] ?? 0;
    
    try {
        $db = new Database();
        $conn = $db->getConnection();
        
        // Check if category is being used by any books
        $stmt = $conn->prepare("SELECT COUNT(*) FROM books WHERE category_id = ?");
        $stmt->execute([$category_id]);
        $count = $stmt->fetchColumn();
        
        if ($count > 0) {
            echo json_encode(['success' => false, 'message' => 'Cannot delete category: it is being used by books']);
            exit();
        }
        
        $stmt = $conn->prepare("DELETE FROM categories WHERE category_id = ?");
        if ($stmt->execute([$category_id])) {
            echo json_encode(['success' => true, 'message' => 'Category deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete category']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'System error']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
} 