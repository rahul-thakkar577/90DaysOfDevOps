<?php
session_start();
require_once '../includes/init.php';

header('Content-Type: application/json');

try {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    $email = filter_var($input['email'] ?? '', FILTER_SANITIZE_EMAIL);
    $password = $input['password'] ?? '';

    if (empty($email) || empty($password)) {
        throw new Exception('Please enter both email and password');
    }

    $pdo = getPDO();

    // Use the loginUser function from functions.php
    if (loginUser($pdo, $email, $password)) {
        echo json_encode([
            'success' => true,
            'message' => 'Login successful',
            'redirect' => $_SESSION['user_role'] === 'admin' ? 'admin/dashboard.php' : 'user/dashboard.php'
        ]);
    } else {
        throw new Exception('Invalid email or password');
    }

} catch (Exception $e) {
    error_log('Login error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>