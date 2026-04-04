<?php
session_start();
require_once '../../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $book_id = $_POST['book_id'] ?? '';
    $title = trim($_POST['title'] ?? '');
    $author_id = $_POST['author_id'] ?? '';
    $category_id = $_POST['category_id'] ?? '';
    $price = $_POST['price'] ?? '';
    $available_copies = $_POST['available_copies'] ?? '';
    $description = trim($_POST['description'] ?? '');
    
    if (empty($title) || empty($author_id) || empty($category_id) || empty($price) || empty($available_copies)) {
        echo json_encode(['success' => false, 'message' => 'All required fields must be filled']);
        exit();
    }
    
    try {
        $db = new Database();
        $conn = $db->getConnection();
        
        // Handle image upload
        $image_url = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = '../../uploads/books/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
            
            if (!in_array($file_extension, $allowed_extensions)) {
                echo json_encode(['success' => false, 'message' => 'Invalid image format']);
                exit();
            }
            
            $filename = uniqid() . '.' . $file_extension;
            $upload_path = $upload_dir . $filename;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                $image_url = 'uploads/books/' . $filename;
            }
        }
        
        if (empty($book_id)) {
            // Insert new book
            $sql = "INSERT INTO books (title, author_id, category_id, price, available_copies, description";
            $sql .= $image_url ? ", image_url" : "";
            $sql .= ") VALUES (?, ?, ?, ?, ?, ?";
            $sql .= $image_url ? ", ?" : "";
            $sql .= ")";
            
            $params = [$title, $author_id, $category_id, $price, $available_copies, $description];
            if ($image_url) {
                $params[] = $image_url;
            }
            
            $stmt = $conn->prepare($sql);
            $success = $stmt->execute($params);
            $message = 'Book added successfully';
        } else {
            // Update existing book
            $sql = "UPDATE books SET title = ?, author_id = ?, category_id = ?, 
                    price = ?, available_copies = ?, description = ?";
            $sql .= $image_url ? ", image_url = ?" : "";
            $sql .= " WHERE book_id = ?";
            
            $params = [$title, $author_id, $category_id, $price, $available_copies, $description];
            if ($image_url) {
                $params[] = $image_url;
            }
            $params[] = $book_id;
            
            $stmt = $conn->prepare($sql);
            $success = $stmt->execute($params);
            $message = 'Book updated successfully';
        }
        
        if ($success) {
            echo json_encode(['success' => true, 'message' => $message]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to save book']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'System error']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
} 