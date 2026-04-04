<?php
session_start();
require_once '../../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $book_id = $_POST['book_id'] ?? 0;
    
    try {
        $db = new Database();
        $conn = $db->getConnection();
        
        // Check if book has any orders
        $stmt = $conn->prepare("SELECT COUNT(*) FROM order_items WHERE book_id = ?");
        $stmt->execute([$book_id]);
        $orderCount = $stmt->fetchColumn();
        
        if ($orderCount > 0) {
            echo json_encode(['success' => false, 'message' => 'Cannot delete book: it has existing orders']);
            exit();
        }
        
        // Get the image URL before deleting
        $stmt = $conn->prepare("SELECT image_url FROM books WHERE book_id = ?");
        $stmt->execute([$book_id]);
        $image_url = $stmt->fetchColumn();
        
        // Delete the book
        $stmt = $conn->prepare("DELETE FROM books WHERE book_id = ?");
        if ($stmt->execute([$book_id])) {
            // Delete the image file if it exists
            if ($image_url && file_exists('../../' . $image_url)) {
                unlink('../../' . $image_url);
            }
            echo json_encode(['success' => true, 'message' => 'Book deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete book']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'System error']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
} 