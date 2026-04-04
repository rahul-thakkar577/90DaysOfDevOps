<?php
// Start session and include required files
session_start();
require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../includes/functions.php';

// Check login and get database connection
requireLogin();
$pdo = getPDO();

// Set page title and breadcrumbs
$page_title = 'Dashboard';
$show_breadcrumbs = true;
$breadcrumbs = [];

// Get cart count
$cart_count = getCartCount($pdo, $_SESSION['user_id']);

// Get user's recent orders
$recent_orders = getRecentOrders($pdo, $_SESSION['user_id']);

// Get recently added books
$recent_books = getRecentBooks($pdo);

// Include header after all data is fetched
require_once __DIR__ . '/includes/header.php';
?>

<div class="dashboard-page">
    <div class="welcome-section">
        <h1>Welcome back, <?= htmlspecialchars($_SESSION['username']) ?>!</h1>
        <p>Here's what's happening with your library account</p>
    </div>

    <div class="dashboard-grid">
        <!-- Cart Summary -->
        <div class="dashboard-card">
            <div class="card-icon">
                <i class="fas fa-shopping-cart"></i>
            </div>
            <div class="card-content">
                <h3>Cart Items</h3>
                <p class="card-value"><?= $cart_count ?></p>
                <a href="cart.php" class="card-link">View Cart</a>
            </div>
        </div>

        <!-- Recent Orders -->
        <div class="dashboard-card">
            <div class="card-icon">
                <i class="fas fa-shopping-bag"></i>
            </div>
            <div class="card-content">
                <h3>Recent Orders</h3>
                <p class="card-value"><?= count($recent_orders) ?></p>
                <a href="orders.php" class="card-link">View All Orders</a>
            </div>
        </div>
    </div>

    <!-- Recent Orders List -->
    <?php if (!empty($recent_orders)): ?>
    <div class="section">
        <div class="section-header">
            <h2>Recent Orders</h2>
            <a href="orders.php" class="btn btn-link">View All</a>
        </div>
        <div class="orders-grid">
            <?php foreach ($recent_orders as $order): ?>
            <div class="order-card">
                <div class="order-header">
                    <div class="order-id">Order #<?= $order['order_id'] ?></div>
                    <div class="order-date"><?= formatDate($order['created_at']) ?></div>
                </div>
                <div class="order-details">
                    <div class="order-items"><?= $order['total_items'] ?> items</div>
                    <div class="order-total"><?= formatPrice($order['total_price']) ?></div>
                </div>
                <div class="order-status">
                    <span class="status-badge status-<?= $order['status'] ?>">
                        <?= ucfirst($order['status']) ?>
                    </span>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Recently Added Books -->
    <div class="section">
        <div class="section-header">
            <h2>Recently Added Books</h2>
            <a href="books.php" class="btn btn-link">View All</a>
        </div>
        <div class="books-grid">
            <?php foreach ($recent_books as $book): ?>
            <div class="book-card">
                <div class="book-image">
                    <?php if ($book['image_url']): ?>
                       <img src="/uploads/<?= ltrim($book['image_url'], '/') ?>" 
                            alt="<?= htmlspecialchars($book['title']) ?>">
                    <?php else: ?>
                        <div class="no-image">
                            <i class="fas fa-book"></i>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="book-details">
                    <h3 class="book-title"><?= htmlspecialchars($book['title']) ?></h3>
                    
                    <div class="book-meta">
                        <?php if ($book['avg_rating'] > 0): ?>
                        <div class="rating">
                            <i class="fas fa-star"></i>
                            <span><?= number_format($book['avg_rating'], 1) ?></span>
                            <?php if ($book['review_count'] > 0): ?>
                            <small>(<?= $book['review_count'] ?>)</small>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                        
                        <div class="book-price"><?= formatPrice($book['price']) ?></div>
                    </div>
                    
                    <div class="book-actions">
                        <a href="books.php?id=<?= $book['book_id'] ?>" class="btn btn-primary">
                            <i class="fas fa-info-circle"></i>
                            View Details
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>