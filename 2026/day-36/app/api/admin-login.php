<?php
header('Content-Type: application/json');
require_once '../config/database.php';
require_once '../includes/Auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
    exit();
}

$database = new Database();
$db = $database->getConnection();
$auth = new Auth($db);

$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

if (empty($email) || empty($password)) {
    echo json_encode(["success" => false, "message" => "Email and password are required"]);
    exit();
}

try {
    // First verify if it's an admin account
    $query = "SELECT user_id, name, email, password, role, status FROM users 
              WHERE email = ? AND role = 'admin'";
    $stmt = $db->prepare($query);
    $stmt->execute([$email]);
    
    if ($stmt->rowCount() === 0) {
        echo json_encode(["success" => false, "message" => "Invalid admin credentials"]);
        exit();
    }

    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user['status'] === 'banned') {
        echo json_encode(["success" => false, "message" => "This account has been banned"]);
        exit();
    }
    
    if (password_verify($password, $user['password'])) {
        session_start();
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['name'] = $user['name'];
        $_SESSION['role'] = $user['role'];
        
        echo json_encode([
            "success" => true,
            "message" => "Login successful",
            "role" => $user['role']
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Incorrect password"
        ]);
    }
} catch(PDOException $e) {
    echo json_encode([
        "success" => false,
        "message" => "Login failed: " . $e->getMessage()
    ]);
} 