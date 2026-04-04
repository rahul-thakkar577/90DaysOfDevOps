<?php
session_start();
require_once '../config/database.php';

// Check if admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

$db = new Database();
$conn = $db->getConnection();

$error = '';
$success = '';

// Handle order status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'update_status') {
        $order_id = $_POST['order_id'] ?? '';
        $status = $_POST['status'] ?? '';
        
        $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE order_id = ?");
        if ($stmt->execute([$status, $order_id])) {
            $success = 'Order status updated successfully';
        } else {
            $error = 'Failed to update order status';
        }
    }
}

// Fetch all orders with user and book details
$orders = $conn->query("
    SELECT o.*, u.name as user_name, u.email as user_email,
           b.title as book_title, b.price as book_price
    FROM orders o
    JOIN users u ON o.user_id = u.user_id
    JOIN books b ON o.book_id = b.book_id
    ORDER BY o.created_at DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Orders - Library Management System</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="light-theme">
    <div class="theme-switch">
        <input type="checkbox" id="theme-toggle">
        <label for="theme-toggle" class="theme-toggle-label">
            <i class="fas fa-sun"></i>
            <i class="fas fa-moon"></i>
        </label>
    </div>

    <div class="dashboard-container">
        <?php include 'includes/sidebar.php'; ?>

        <main class="content">
            <header class="content-header glass-effect">
                <h2>Manage Orders</h2>
                <div class="order-filters">
                    <select id="statusFilter" onchange="filterOrders()">
                        <option value="">All Status</option>
                        <option value="pending">Pending</option>
                        <option value="completed">Completed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
            </header>

            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <div class="orders-table glass-effect">
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
                            <tr data-status="<?php echo $order['status']; ?>">
                                <td>#<?php echo $order['order_id']; ?></td>
                                <td>
                                    <?php echo htmlspecialchars($order['user_name']); ?>
                                    <br>
                                    <small><?php echo htmlspecialchars($order['user_email']); ?></small>
                                </td>
                                <td><?php echo htmlspecialchars($order['book_title']); ?></td>
                                <td><?php echo $order['quantity']; ?></td>
                                <td>$<?php echo number_format($order['total_price'], 2); ?></td>
                                <td>
                                    <select class="status-select" onchange="updateOrderStatus(<?php echo $order['order_id']; ?>, this.value)">
                                        <option value="pending" <?php echo $order['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                        <option value="completed" <?php echo $order['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                        <option value="cancelled" <?php echo $order['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                    </select>
                                </td>
                                <td><?php echo date('M d, Y H:i', strtotime($order['created_at'])); ?></td>
                                <td>
                                    <button class="btn-icon" onclick="viewOrderDetails(<?php echo $order['order_id']; ?>)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <!-- Order Details Modal -->
    <div id="orderModal" class="modal">
        <div class="modal-content glass-effect">
            <span class="close">&times;</span>
            <h2>Order Details</h2>
            <div id="orderDetails"></div>
        </div>
    </div>

    <script src="../assets/js/theme.js"></script>
    <script src="../assets/js/admin.js"></script>
    <script>
        function updateOrderStatus(orderId, status) {
            if (confirm('Are you sure you want to update this order\'s status?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="update_status">
                    <input type="hidden" name="order_id" value="${orderId}">
                    <input type="hidden" name="status" value="${status}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        function viewOrderDetails(orderId) {
            fetch(`get-order.php?id=${orderId}`)
                .then(response => response.json())
                .then(order => {
                    const details = `
                        <div class="order-details">
                            <p><strong>Order ID:</strong> #${order.order_id}</p>
                            <p><strong>Customer:</strong> ${order.user_name}</p>
                            <p><strong>Email:</strong> ${order.user_email}</p>
                            <p><strong>Book:</strong> ${order.book_title}</p>
                            <p><strong>Quantity:</strong> ${order.quantity}</p>
                            <p><strong>Total Price:</strong> $${parseFloat(order.total_price).toFixed(2)}</p>
                            <p><strong>Status:</strong> ${order.status}</p>
                            <p><strong>Order Date:</strong> ${new Date(order.created_at).toLocaleString()}</p>
                        </div>
                    `;
                    document.getElementById('orderDetails').innerHTML = details;
                    document.getElementById('orderModal').style.display = 'block';
                });
        }

        function filterOrders() {
            const status = document.getElementById('statusFilter').value;
            const rows = document.querySelectorAll('.orders-table tbody tr');
            
            rows.forEach(row => {
                if (!status || row.dataset.status === status) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        // Close modal when clicking the close button or outside the modal
        document.querySelector('.close').onclick = function() {
            document.getElementById('orderModal').style.display = 'none';
        }

        window.onclick = function(event) {
            if (event.target == document.getElementById('orderModal')) {
                document.getElementById('orderModal').style.display = 'none';
            }
        }
    </script>
</body>
</html> 