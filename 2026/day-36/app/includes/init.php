<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database class and functions
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/functions.php';

// Initialize database connection
try {
    $db = new Database();
    $pdo = $db->getConnection();
} catch (Exception $e) {
    error_log("Database connection failed: " . $e->getMessage());
    die("Sorry, there was a problem connecting to the database. Please try again later.");
}

// Set default timezone
date_default_timezone_set('Asia/Kolkata');

// Error reporting in development
if ($_SERVER['SERVER_NAME'] === 'localhost' || $_SERVER['SERVER_ADDR'] === '127.0.0.1') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Common variables
define('SITE_NAME', 'Library Management System');
define('SITE_URL', 'http://localhost:8009');

// Image paths configuration
define('IMAGE_PATH', '/'); 
define('ASSETS_PATH', '/assets/');

// Navigation structure
define('NAV_ITEMS', [
    'dashboard' => ['icon' => 'fa-home', 'label' => 'Dashboard'],
    'books' => ['icon' => 'fa-book', 'label' => 'Books'],
    'cart' => ['icon' => 'fa-shopping-cart', 'label' => 'Cart'],
    'orders' => ['icon' => 'fa-shopping-bag', 'label' => 'Orders']
]);

// Database table names
define('TABLE_USERS', 'users');
define('TABLE_BOOKS', 'books');
define('TABLE_CART', 'cart');
define('TABLE_ORDERS', 'orders');
define('TABLE_ORDER_ITEMS', 'order_items');
define('TABLE_REVIEWS', 'reviews');

// Column names
define('COL_IMAGE_URL', 'image_url');
define('COL_CREATED_AT', 'created_at');
define('COL_TOTAL_PRICE', 'total_price');
define('COL_RATING', 'rating');
?>
