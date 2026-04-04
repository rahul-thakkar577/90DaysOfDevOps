<?php
require_once '../config/database.php';
require_once '../includes/Auth.php';

$database = new Database();
$db = $database->getConnection();
$auth = new Auth($db);

// Create initial admin account
$result = $auth->createAdmin(
    "Admin User",
    "admin@library.com",
    "admin123" // Change this to a secure password
);

echo json_encode($result); 