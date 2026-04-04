<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$db = new Database();
$conn = $db->getConnection();

// Get all categories and authors for filters
$categories = $conn->query("SELECT * FROM categories ORDER BY name")->fetchAll();
$authors = $conn->query("SELECT * FROM authors ORDER BY name")->fetchAll();

// Build search query
$where_conditions = [];
$params = [];

if ($_GET) {
    if (!empty($_GET['title'])) {
        $where_conditions[] = "b.title LIKE ?";
        $params[] = "%" . $_GET['title'] . "%";
    }
    
    if (!empty($_GET['author'])) {
        $where_conditions[] = "b.author_id = ?";
        $params[] = $_GET['author'];
    }
    
    if (!empty($_GET['category'])) {
        $where_conditions[] = "b.category_id = ?";
        $params[] = $_GET['category'];
    }
    
    if (!empty($_GET['price_min'])) {
        $where_conditions[] = "b.price >= ?";
        $params[] = $_GET['price_min'];
    }
    
    if (!empty($_GET['price_max'])) {
        $where_conditions[] = "b.price <= ?";
        $params[] = $_GET['price_max'];
    }
    
    if (isset($_GET['in_stock']) && $_GET['in_stock'] === '1') {
        $where_conditions[] = "b.available_copies > 0";
    }
    
    if (!empty($_GET['rating'])) {
        $where_conditions[] = "COALESCE(AVG(r.rating), 0) >= ?";
        $params[] = $_GET['rating'];
    }
}

$where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

$query = "
    SELECT b.*, a.name as author_name, c.name as category_name,
           COALESCE(AVG(r.rating), 0) as avg_rating,
           COUNT(DISTINCT r.review_id) as review_count
    FROM books b
    JOIN authors a ON b.author_id = a.author_id
    JOIN categories c ON b.category_id = c.category_id
    LEFT JOIN reviews r ON b.book_id = r.book_id
    $where_clause
    GROUP BY b.book_id
    ORDER BY " . ($_GET['sort'] ?? "b.title ASC");

$stmt = $conn->prepare($query);
$stmt->execute($params);
$books = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<!-- HTML content similar to books.php but with advanced filters --> 