<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Get the latest order details
$db = new Database();
$conn = $db->getConnection();

$stmt = $conn->prepare("
    SELECT o.*, oi.quantity, b.title, b.image_url
    FROM orders o
    JOIN order_items oi ON o.order_id = oi.order_id
    JOIN books b ON oi.book_id = b.book_id
    WHERE o.user_id = ?
    ORDER BY o.created_at DESC
    LIMIT 1
");
$stmt->execute([$_SESSION['user_id']]);
$order_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($order_items)) {
    header('Location: dashboard.php');
    exit();
}

// Get the first item for order details (they'll all have the same order info)
$order = $order_items[0];

$pageTitle = 'Order Confirmation';
require_once 'includes/header.php';
?>

<div class="confirmation-page">
    <div class="confirmation-container glass-effect">
        <div class="success-icon">
            <i class="fas fa-check-circle"></i>
        </div>
        <h2>Order Confirmed!</h2>
        <p>Thank you for your purchase. Your order has been successfully placed.</p>
        
        <div class="order-info">
            <h3>Order Details</h3>
            <p><strong>Order ID:</strong> #<?php echo $order['order_id']; ?></p>
            <p><strong>Date:</strong> <?php echo date('F j, Y, g:i a', strtotime($order['created_at'])); ?></p>
            <p><strong>Total Amount:</strong> ₹<?php echo number_format($order['total_price'], 2); ?></p>
        </div>

        <div class="ordered-items">
            <h3>Ordered Items</h3>
            <?php foreach ($order_items as $item): ?>
                <div class="book-preview">
                    <div class="book-image">
                        <?php if ($item['image_url']): ?>
                            <img src="../<?php echo htmlspecialchars($item['image_url']); ?>" 
                                 alt="<?php echo htmlspecialchars($item['title']); ?>">
                        <?php else: ?>
                            <div class="no-image"><i class="fas fa-book"></i></div>
                        <?php endif; ?>
                    </div>
                    <div class="book-details">
                        <h4><?php echo htmlspecialchars($item['title']); ?></h4>
                        <p>Quantity: <?php echo $item['quantity']; ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="action-buttons">
            <a href="orders.php" class="btn-primary">View All Orders</a>
            <a href="books.php" class="btn-secondary">Continue Shopping</a>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>