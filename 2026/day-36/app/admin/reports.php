<?php
session_start();
require_once '../config/database.php';

// Check if admin is not logged in
if(!isset($_SESSION['admin_id']) || !isset($_SESSION['admin_email'])) {
    header("Location: login.php");
    exit();
}

$db = new Database();
$conn = $db->getConnection();

// Get date range
$start_date = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
$end_date = $_GET['end_date'] ?? date('Y-m-d');

// Fetch statistics
$stats = [
    'total_revenue' => $conn->query("SELECT COALESCE(SUM(total_price), 0) FROM orders WHERE status = 'completed' AND DATE(created_at) BETWEEN '$start_date' AND '$end_date'")->fetchColumn(),
    'total_orders' => $conn->query("SELECT COUNT(*) FROM orders WHERE DATE(created_at) BETWEEN '$start_date' AND '$end_date'")->fetchColumn(),
    'total_users' => $conn->query("SELECT COUNT(*) FROM users WHERE role = 'user' AND DATE(created_at) BETWEEN '$start_date' AND '$end_date'")->fetchColumn(),
    'total_books' => $conn->query("SELECT COUNT(*) FROM books")->fetchColumn()
];

// Top selling books
$top_books = $conn->query("SELECT b.title, COUNT(oi.order_item_id) as order_count, SUM(oi.quantity) as total_quantity
FROM books b
JOIN order_items oi ON b.book_id = oi.book_id
JOIN orders o ON oi.order_id = o.order_id
WHERE o.status = 'completed'
AND DATE(o.created_at) BETWEEN '$start_date' AND '$end_date'
GROUP BY b.book_id
ORDER BY total_quantity DESC
LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);

// Category distribution
$category_stats = $conn->query("SELECT c.name, COUNT(b.book_id) as book_count
FROM categories c
LEFT JOIN books b ON c.category_id = b.category_id
GROUP BY c.category_id
ORDER BY book_count DESC")->fetchAll(PDO::FETCH_ASSOC);

// Monthly revenue
$monthly_revenue = $conn->query("SELECT DATE_FORMAT(created_at, '%Y-%m') as month, SUM(total_price) as revenue
FROM orders
WHERE status = 'completed'
AND DATE(created_at) BETWEEN '$start_date' AND '$end_date'
GROUP BY month
ORDER BY month ASC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="admin-dashboard">
        <?php include 'includes/sidebar.php'; ?>

        <main class="admin-main">
            <header class="admin-header">
                <h1>Reports & Analytics</h1>
                <div class="admin-actions">
                    <form id="dateRangeForm" class="date-range-form">
                        <input type="date" name="start_date" value="<?php echo $start_date; ?>">
                        <input type="date" name="end_date" value="<?php echo $end_date; ?>">
                        <button type="submit" class="btn-primary">Apply Filter</button>
                    </form>
                </div>
            </header>

            <!-- Statistics Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <i class="fas fa-rupee-sign"></i>
                    <div class="stat-info">
                        <h3>Total Revenue</h3>
                        <p>₹<?php echo number_format($stats['total_revenue'], 2); ?></p>
                    </div>
                </div>
                <div class="stat-card">
                    <i class="fas fa-shopping-cart"></i>
                    <div class="stat-info">
                        <h3>Total Orders</h3>
                        <p><?php echo $stats['total_orders']; ?></p>
                    </div>
                </div>
                <div class="stat-card">
                    <i class="fas fa-users"></i>
                    <div class="stat-info">
                        <h3>New Users</h3>
                        <p><?php echo $stats['total_users']; ?></p>
                    </div>
                </div>
                <div class="stat-card">
                    <i class="fas fa-book"></i>
                    <div class="stat-info">
                        <h3>Total Books</h3>
                        <p><?php echo $stats['total_books']; ?></p>
                    </div>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="charts-grid">
                <!-- Revenue Chart -->
                <div class="chart-container">
                    <h2>Monthly Revenue</h2>
                    <canvas id="revenueChart"></canvas>
                </div>

                <!-- Category Distribution -->
                <div class="chart-container">
                    <h2>Books by Category</h2>
                    <canvas id="categoryChart"></canvas>
                </div>
            </div>

            <!-- Top Selling Books Table -->
            <div class="report-section">
                <h2>Top Selling Books</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Book Title</th>
                            <th>Orders</th>
                            <th>Total Quantity</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($top_books as $book): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($book['title']); ?></td>
                            <td><?php echo $book['order_count']; ?></td>
                            <td><?php echo $book['total_quantity']; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <script>
        // Monthly Revenue Chart
        const revenueData = <?php echo json_encode($monthly_revenue); ?>;
        new Chart(document.getElementById('revenueChart'), {
            type: 'line',
            data: {
                labels: revenueData.map(item => item.month),
                datasets: [{
                    label: 'Revenue',
                    data: revenueData.map(item => item.revenue),
                    borderColor: '#4a90e2',
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: value => '₹' + value
                        }
                    }
                }
            }
        });

        // Category Distribution Chart
        const categoryData = <?php echo json_encode($category_stats); ?>;
        new Chart(document.getElementById('categoryChart'), {
            type: 'bar',
            data: {
                labels: categoryData.map(item => item.name),
                datasets: [{
                    label: 'Books',
                    data: categoryData.map(item => item.book_count),
                    backgroundColor: '#4a90e2'
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
</body>
</html>