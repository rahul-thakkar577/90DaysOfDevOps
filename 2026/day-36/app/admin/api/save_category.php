<?php
session_start();
require_once '../../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category_id = $_POST['category_id'] ?? '';
    $name = trim($_POST['name'] ?? '');
    
    if (empty($name)) {
        echo json_encode(['success' => false, 'message' => 'Category name is required']);
        exit();
    }
    
    try {
        $db = new Database();
        $conn = $db->getConnection();
        
        if (empty($category_id)) {
            // Insert new category
            $stmt = $conn->prepare("INSERT INTO categories (name) VALUES (?)");
            $success = $stmt->execute([$name]);
            $message = 'Category added successfully';
        } else {
            // Update existing category
            $stmt = $conn->prepare("UPDATE categories SET name = ? WHERE category_id = ?");
            $success = $stmt->execute([$name, $category_id]);
            $message = 'Category updated successfully';
        }
        
        if ($success) {
            echo json_encode(['success' => true, 'message' => $message]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to save category']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'System error']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
} 