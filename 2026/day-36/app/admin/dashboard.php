<?php
session_start();
require_once '../config/database.php';

// Check if admin is not logged in
if (!isset($_SESSION['admin_id']) && !(isset($_SESSION['user_id']) && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin')) {
    header("Location: login.php");
    exit();
}

$db = new Database();
$conn = $db->getConnection();

// Fetch statistics
$stats = [
    'total_books' => $conn->query("SELECT COUNT(*) FROM books")->fetchColumn(),
    'total_users' => $conn->query("SELECT COUNT(*) FROM users WHERE role = 'user'")->fetchColumn(),
    'total_orders' => $conn->query("SELECT COUNT(*) FROM orders")->fetchColumn(),
    'total_revenue' => $conn->query("SELECT COALESCE(SUM(total_price), 0) FROM orders WHERE status = 'completed'")->fetchColumn()
];

// Fetch recent orders with book details from cart
$recent_orders = $conn->query("
    SELECT o.order_id, o.user_id, o.total_price, o.status, o.created_at,
           u.username as user_name,
           GROUP_CONCAT(DISTINCT b.title SEPARATOR ', ') as book_titles
    FROM orders o 
    JOIN users u ON o.user_id = u.user_id 
    JOIN cart c ON c.user_id = o.user_id 
    JOIN books b ON c.book_id = b.book_id 
    WHERE c.created_at <= o.created_at
    GROUP BY o.order_id, o.user_id, o.total_price, o.status, o.created_at, u.username
    ORDER BY o.created_at DESC 
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

// Fetch low stock books (available_copies <= 5)
$low_stock_books = $conn->query("
    SELECT b.*, a.name as author_name 
    FROM books b
    LEFT JOIN authors a ON b.author_id = a.author_id
    WHERE b.available_copies <= 5
    ORDER BY b.available_copies ASC 
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

// Get admin name from session
$admin_name = isset($_SESSION['admin_name']) ? $_SESSION['admin_name'] : $_SESSION['user_name'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Library Management System</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .welcome-message {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .welcome-message h2 {
            color: #2c3e50;
            margin: 0;
            font-size: 1.5em;
        }
        .welcome-message p {
            color: #666;
            margin: 10px 0 0;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .stat-card i {
            font-size: 2em;
            color: #4a90e2;
        }
        .stat-info h3 {
            margin: 0;
            font-size: 0.9em;
            color: #666;
        }
        .stat-info p {
            margin: 5px 0 0;
            font-size: 1.5em;
            color: #2c3e50;
            font-weight: 600;
        }
        .recent-orders, .books-table {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .recent-orders h2, .books-table h2 {
            color: #2c3e50;
            margin: 0 0 20px;
            font-size: 1.3em;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        th {
            font-weight: 600;
            color: #2c3e50;
            background: #f8f9fa;
        }
        td {
            color: #666;
        }
        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85em;
            font-weight: 500;
        }
        .status-badge.pending {
            background: #fff3cd;
            color: #856404;
        }
        .status-badge.completed {
            background: #d4edda;
            color: #155724;
        }
        .status-badge.cancelled {
            background: #f8d7da;
            color: #721c24;
        }
        .stock-warning {
            color: #dc3545;
            font-weight: 600;
        }
        .btn-icon {
            color: #4a90e2;
            text-decoration: none;
            font-size: 1.1em;
            transition: color 0.3s;
        }
        .btn-icon:hover {
            color: #357abd;
        }
    </style>
</head>
<body>
    <div class="admin-dashboard">
        <?php include 'includes/sidebar.php'; ?>

        <main class="admin-main">
            <div class="welcome-message">
                <h2>Welcome, <?php echo htmlspecialchars($admin_name); ?>!</h2>
                <p>Here's an overview of your library management system</p>
            </div>

            <!-- Statistics Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <i class="fas fa-book"></i>
                    <div class="stat-info">
                        <h3>Total Books</h3>
                        <p><?php echo number_format($stats['total_books']); ?></p>
                    </div>
                </div>
                <div class="stat-card">
                    <i class="fas fa-users"></i>
                    <div class="stat-info">
                        <h3>Total Users</h3>
                        <p><?php echo number_format($stats['total_users']); ?></p>
                    </div>
                </div>
                <div class="stat-card">
                    <i class="fas fa-shopping-cart"></i>
                    <div class="stat-info">
                        <h3>Total Orders</h3>
                        <p><?php echo number_format($stats['total_orders']); ?></p>
                    </div>
                </div>
                <div class="stat-card">
                    <i class="fas fa-dollar-sign"></i>
                    <div class="stat-info">
                        <h3>Total Revenue</h3>
                        <p>₹<?php echo number_format($stats['total_revenue'], 2); ?></p>
                    </div>
                </div>
            </div>

            <!-- Recent Orders -->
            <div class="recent-orders">
                <h2>Recent Orders</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>User</th>
                            <th>Books</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_orders as $order): ?>
                        <tr>
                            <td>#<?php echo $order['order_id']; ?></td>
                            <td><?php echo htmlspecialchars($order['user_name']); ?></td>
                            <td><?php echo htmlspecialchars($order['book_titles']); ?></td>
                            <td>₹<?php echo number_format($order['total_price'], 2); ?></td>
                            <td>
                                <span class="status-badge <?php echo $order['status']; ?>">
                                    <?php echo ucfirst($order['status']); ?>
                                </span>
                            </td>
                            <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Low Stock Books -->
            <div class="books-table">
                <h2>Low Stock Alert</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Book Title</th>
                            <th>Author</th>
                            <th>Available Copies</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($low_stock_books as $book): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($book['title']); ?></td>
                            <td><?php echo htmlspecialchars($book['author_name']); ?></td>
                            <td>
                                <span class="stock-warning"><?php echo $book['available_copies']; ?></span>
                            </td>
                            <td>
                                <a href="books.php?id=<?php echo $book['book_id']; ?>" class="btn-icon">
                                    <i class="fas fa-edit"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>
</html>