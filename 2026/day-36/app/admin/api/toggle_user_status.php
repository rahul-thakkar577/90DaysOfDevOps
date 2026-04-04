<?php
session_start();
require_once '../../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'] ?? 0;
    
    try {
        $db = new Database();
        $conn = $db->getConnection();
        
        // Get current status
        $stmt = $conn->prepare("SELECT status FROM users WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            $new_status = $user['status'] === 'active' ? 'suspended' : 'active';
            
            $stmt = $conn->prepare("UPDATE users SET status = ? WHERE user_id = ?");
            if ($stmt->execute([$new_status, $user_id])) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to update status']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'User not found']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'System error']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
} 