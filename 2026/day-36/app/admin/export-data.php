<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

$db = new Database();
$conn = $db->getConnection();

$type = $_GET['type'] ?? '';
$format = $_GET['format'] ?? 'csv';

switch ($type) {
    case 'orders':
        $data = exportOrders($conn);
        $filename = 'orders_export_' . date('Y-m-d');
        break;
    case 'users':
        $data = exportUsers($conn);
        $filename = 'users_export_' . date('Y-m-d');
        break;
    case 'books':
        $data = exportBooks($conn);
        $filename = 'books_export_' . date('Y-m-d');
        break;
    default:
        header('Location: dashboard.php');
        exit();
}

if ($format === 'csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '.csv"');
    outputCSV($data);
} else {
    header('Content-Type: application/json');
    header('Content-Disposition: attachment; filename="' . $filename . '.json"');
    echo json_encode($data, JSON_PRETTY_PRINT);
}

function exportOrders($conn) {
    $stmt = $conn->query("
        SELECT o.order_id, u.name as user_name, u.email, 
               b.title as book_title, o.quantity, o.total_price,
               o.status, o.created_at
        FROM orders o
        JOIN users u ON o.user_id = u.user_id
        JOIN books b ON o.book_id = b.book_id
        ORDER BY o.created_at DESC
    ");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function exportUsers($conn) {
    $stmt = $conn->query("
        SELECT user_id, name, email, role, status, created_at,
               (SELECT COUNT(*) FROM orders WHERE user_id = users.user_id) as total_orders,
               (SELECT COALESCE(SUM(total_price), 0) FROM orders WHERE user_id = users.user_id) as total_spent
        FROM users
        WHERE role = 'user'
        ORDER BY created_at DESC
    ");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function exportBooks($conn) {
    $stmt = $conn->query("
        SELECT b.*, a.name as author_name, c.name as category_name,
               (SELECT COUNT(*) FROM orders WHERE book_id = b.book_id) as times_ordered,
               COALESCE(AVG(r.rating), 0) as avg_rating
        FROM books b
        JOIN authors a ON b.author_id = a.author_id
        JOIN categories c ON b.category_id = c.category_id
        LEFT JOIN reviews r ON b.book_id = r.book_id
        GROUP BY b.book_id
        ORDER BY times_ordered DESC
    ");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function outputCSV($data) {
    if (empty($data)) return;
    
    $output = fopen('php://output', 'w');
    fputcsv($output, array_keys($data[0]));
    
    foreach ($data as $row) {
        fputcsv($output, $row);
    }
    
    fclose($output);
} 