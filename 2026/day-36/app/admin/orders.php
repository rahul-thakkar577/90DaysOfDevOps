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

// Fetch all orders with user and book details
$orders = $conn->query("SELECT o.order_id, u.username as user_name, oi.quantity, oi.price, o.total_price, o.status, o.created_at, b.title as book_title 
FROM orders o 
JOIN users u ON o.user_id = u.user_id 
JOIN order_items oi ON o.order_id = oi.order_id
JOIN books b ON oi.book_id = b.book_id 
ORDER BY o.created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Orders - Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="admin-dashboard">
        <?php include 'includes/sidebar.php'; ?>

        <main class="admin-main">
            <header class="admin-header">
                <h1>Manage Orders</h1>
            </header>

            <div class="books-table">
                <table>
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>User</th>
                            <th>Book</th>
                            <th>Quantity</th>
                            <th>Total Price</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                        <tr>
                            <td>#<?php echo $order['order_id']; ?></td>
                            <td><?php echo htmlspecialchars($order['user_name']); ?></td>
                            <td><?php echo htmlspecialchars($order['book_title']); ?></td>
                            <td><?php echo $order['quantity']; ?></td>
                            <td>₹<?php echo number_format($order['total_price'], 2); ?></td>
                            <td>
                                <span class="status-badge <?php echo $order['status']; ?>">
                                    <?php echo ucfirst($order['status']); ?>
                                </span>
                            </td>
                            <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                            <td class="actions">
                                <?php if ($order['status'] !== 'delivered' && $order['status'] !== 'cancelled'): ?>
                                    <?php if ($order['status'] === 'pending'): ?>
                                        <button class="btn-icon" onclick="updateOrderStatus(<?php echo $order['order_id']; ?>, 'processing')" title="Mark as Processing">
                                            <i class="fas fa-cog"></i>
                                        </button>
                                    <?php endif; ?>
                                    
                                    <?php if ($order['status'] === 'pending' || $order['status'] === 'processing'): ?>
                                        <button class="btn-icon" onclick="updateOrderStatus(<?php echo $order['order_id']; ?>, 'delivered')" title="Mark as Delivered">
                                            <i class="fas fa-truck"></i>
                                        </button>
                                    <?php endif; ?>
                                    
                                    <button class="btn-icon delete" onclick="updateOrderStatus(<?php echo $order['order_id']; ?>, 'cancelled')" title="Cancel Order">
                                        <i class="fas fa-times"></i>
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <script>
        function updateOrderStatus(orderId, status) {
            if (confirm(`Are you sure you want to mark this order as ${status}?`)) {
                fetch('api/update_order_status.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `order_id=${orderId}&status=${status}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert(data.message);
                    }
                });
            }
        }
    </script>
</body>
</html>