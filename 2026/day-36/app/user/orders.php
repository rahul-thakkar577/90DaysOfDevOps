<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'user') {
    header('Location: ../login.php');
    exit();
}

$db = new Database();
$conn = $db->getConnection();

// Fetch orders with proper column names as per MEMORY
$stmt = $conn->prepare("
    SELECT o.order_id, o.total_price, o.status, o.created_at,
           oi.quantity, b.price as item_price, b.book_id,
           b.title, b.image_url,
           (SELECT IFNULL(AVG(rating), 0) FROM reviews r WHERE r.book_id = b.book_id) as avg_rating
    FROM orders o
    JOIN order_items oi ON o.order_id = oi.order_id
    JOIN books b ON oi.book_id = b.book_id
    WHERE o.user_id = ?
    ORDER BY o.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Group orders by order_id
$grouped_orders = [];
foreach ($orders as $order) {
    if (!isset($grouped_orders[$order['order_id']])) {
        $grouped_orders[$order['order_id']] = [
            'order_id' => $order['order_id'],
            'total_price' => $order['total_price'],
            'status' => $order['status'],
            'created_at' => $order['created_at'],
            'items' => []
        ];
    }
    $grouped_orders[$order['order_id']]['items'][] = [
        'book_id' => $order['book_id'],
        'title' => $order['title'],
        'image_url' => $order['image_url'],
        'quantity' => $order['quantity'],
        'price' => $order['item_price'],
        'avg_rating' => $order['avg_rating']
    ];
}

require_once 'includes/header.php';
?>

<div class="content-header">
    <h2 class="page-title">My Orders</h2>
</div>

<?php if (empty($grouped_orders)): ?>
    <div class="empty-state">
        <i class="fas fa-shopping-bag"></i>
        <h3>No orders yet</h3>
        <p>You haven't placed any orders yet. Start shopping to see your orders here.</p>
        <a href="books.php" class="btn btn-primary">Browse Books</a>
    </div>
<?php else: ?>
    <div class="orders-container">
        <?php foreach ($grouped_orders as $order): ?>
            <div class="order-card">
                <div class="order-header">
                    <div class="order-info">
                        <h3>Order #<?php echo $order['order_id']; ?></h3>
                        <span class="order-date">
                            <?php echo date('F j, Y g:i A', strtotime($order['created_at'])); ?>
                        </span>
                    </div>
                    <div class="order-status">
                        <span class="badge badge-<?php echo strtolower($order['status']); ?>">
                            <?php echo ucfirst($order['status']); ?>
                        </span>
                    </div>
                </div>

                <div class="order-items">
                    <?php foreach ($order['items'] as $item): ?>
                        <div class="order-item">
                            <div class="item-image">
                                <?php if ($item['image_url']): ?>
                                    <img src="../<?php echo htmlspecialchars($item['image_url']); ?>" 
                                         alt="<?php echo htmlspecialchars($item['title']); ?>">
                                <?php else: ?>
                                    <div class="no-image">
                                        <i class="fas fa-book"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="item-details">
                                <h4><?php echo htmlspecialchars($item['title']); ?></h4>
                                <div class="item-meta">
                                    <div class="rating">
                                        <i class="fas fa-star"></i>
                                        <span><?php echo number_format($item['avg_rating'], 1); ?></span>
                                    </div>
                                    <span class="quantity">Qty: <?php echo $item['quantity']; ?></span>
                                </div>
                                <div class="item-price">₹<?php echo number_format($item['price'], 2); ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="order-footer">
                    <div class="order-total">
                        <span>Total:</span>
                        <span class="total-amount">₹<?php echo number_format($order['total_price'], 2); ?></span>
                    </div>
                    <div class="order-actions">
                        <a href="invoice.php?order_id=<?php echo $order['order_id']; ?>"
                           class="btn btn-outline" target="_blank">
                            <i class="fas fa-file-invoice"></i>
                            Download Invoice
                        </a>
                        <?php if ($order['status'] === 'delivered'): ?>
                            <a href="write-review.php?order_id=<?php echo $order['order_id']; ?>"
                               class="btn btn-outline">
                                <i class="fas fa-star"></i>
                                Write Review
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<style>
/* Orders Page Styles */
.orders-container {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.order-card {
    background: var(--card-bg);
    border-radius: 1rem;
    box-shadow: var(--shadow-sm);
    overflow: hidden;
}

.order-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.5rem;
    border-bottom: 1px solid var(--border-color);
}

.order-info h3 {
    font-size: 1.25rem;
    font-weight: 600;
    color: var(--text-primary);
    margin: 0 0 0.5rem 0;
}

.order-date {
    font-size: 0.875rem;
    color: var(--text-secondary);
}

.order-items {
    padding: 1.5rem;
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.order-item {
    display: grid;
    grid-template-columns: 100px 1fr;
    gap: 1.5rem;
}

.item-image {
    width: 100px;
    height: 150px;
    border-radius: 0.5rem;
    overflow: hidden;
}

.item-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.no-image {
    width: 100%;
    height: 100%;
    background: var(--background-alt);
    display: flex;
    align-items: center;
    justify-content: center;
}

.no-image i {
    font-size: 2rem;
    color: var(--text-muted);
}

.item-details h4 {
    font-size: 1rem;
    font-weight: 500;
    color: var(--text-primary);
    margin: 0 0 0.5rem 0;
}

.item-meta {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 0.5rem;
}

.quantity {
    font-size: 0.875rem;
    color: var(--text-secondary);
}

.order-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.5rem;
    background: var(--background-alt);
    border-top: 1px solid var(--border-color);
}

.order-actions {
    display: flex;
    gap: 1rem;
}

.order-actions .btn {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.order-actions .btn i {
    font-size: 0.875rem;
}

.order-total {
    font-size: 1.25rem;
    font-weight: 600;
    color: var(--text-primary);
}

.total-amount {
    color: var(--primary-color);
    margin-left: 0.5rem;
}

/* Status badges */
.badge-pending {
    background: rgba(245, 158, 11, 0.1);
    color: var(--warning-color);
}

.badge-processing {
    background: rgba(99, 102, 241, 0.1);
    color: var(--primary-color);
}

.badge-shipped {
    background: rgba(16, 185, 129, 0.1);
    color: var(--secondary-color);
}

.badge-delivered {
    background: rgba(34, 197, 94, 0.1);
    color: var(--success-color);
}

.badge-cancelled {
    background: rgba(239, 68, 68, 0.1);
    color: var(--error-color);
}

/* Empty state */
.empty-state {
    text-align: center;
    padding: 3rem;
    background: var(--card-bg);
    border-radius: 1rem;
    box-shadow: var(--shadow-sm);
}

.empty-state i {
    font-size: 4rem;
    color: var(--text-muted);
    margin-bottom: 1.5rem;
}

.empty-state h3 {
    font-size: 1.5rem;
    color: var(--text-primary);
    margin: 0 0 1rem 0;
}

.empty-state p {
    color: var(--text-secondary);
    margin: 0 0 1.5rem 0;
}

/* Responsive styles */
@media (max-width: 768px) {
    .order-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
    }
    
    .order-item {
        grid-template-columns: 80px 1fr;
    }
    
    .item-image {
        width: 80px;
        height: 120px;
    }
    
    .order-footer {
        flex-direction: column;
        gap: 1rem;
        text-align: center;
    }

    .order-actions {
        flex-direction: column;
        width: 100%;
    }

    .order-actions .btn {
        width: 100%;
        justify-content: center;
    }
}

@media (max-width: 480px) {
    .order-item {
        grid-template-columns: 1fr;
        text-align: center;
    }
    
    .item-image {
        width: 120px;
        height: 180px;
        margin: 0 auto;
    }
    
    .item-meta {
        justify-content: center;
    }
}
</style>

<?php require_once 'includes/footer.php'; ?>