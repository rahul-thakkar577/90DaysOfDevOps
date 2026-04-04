<?php
session_start();
require_once '../../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    
    if (empty($name) || empty($email)) {
        echo json_encode(['success' => false, 'message' => 'All fields are required']);
        exit();
    }
    
    try {
        $db = new Database();
        $conn = $db->getConnection();
        
        // Check if email is already used by another admin
        $stmt = $conn->prepare("SELECT admin_id FROM admins WHERE email = ? AND admin_id != ?");
        $stmt->execute([$email, $_SESSION['admin_id']]);
        if ($stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Email is already in use']);
            exit();
        }
        
        $stmt = $conn->prepare("UPDATE admins SET name = ?, email = ? WHERE admin_id = ?");
        if ($stmt->execute([$name, $email, $_SESSION['admin_id']])) {
            $_SESSION['admin_name'] = $name;
            $_SESSION['admin_email'] = $email;
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update profile']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'System error']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
} 