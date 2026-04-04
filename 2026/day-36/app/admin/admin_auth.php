<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

// Clear any existing sessions to prevent conflicts
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

if (empty($email) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Email and password are required']);
    exit();
}

try {
    $db = new Database();
    $conn = $db->getConnection();

    // First check in admins table
    $stmt = $conn->prepare("SELECT admin_id, name, email, password, status FROM admins WHERE email = ? AND status = 'active'");
    $stmt->execute([$email]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($admin) {
        // For admin@admin.com with password admin123
        if ($email === 'admin@admin.com' && $password === 'admin123') {
            $_SESSION['admin_id'] = $admin['admin_id'];
            $_SESSION['admin_name'] = $admin['name'];
            $_SESSION['admin_email'] = $admin['email'];
            echo json_encode(['success' => true]);
            exit();
        }
        // For other admin accounts, verify hashed password
        else if (password_verify($password, $admin['password'])) {
            $_SESSION['admin_id'] = $admin['admin_id'];
            $_SESSION['admin_name'] = $admin['name'];
            $_SESSION['admin_email'] = $admin['email'];
            echo json_encode(['success' => true]);
            exit();
        }
    }

    // If not found in admins table, check users table for admin role
    $stmt = $conn->prepare("SELECT user_id, name, email, password, role FROM users WHERE email = ? AND role = 'admin'");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];
        echo json_encode(['success' => true]);
        exit();
    }

    // If we get here, authentication failed
    echo json_encode(['success' => false, 'message' => 'Invalid email or password']);

} catch (PDOException $e) {
    error_log("Authentication error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error occurred']);
}
?>