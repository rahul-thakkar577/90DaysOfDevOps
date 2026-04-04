<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

if (!isset($_GET['id'])) {
    header('Location: books.php');
    exit();
}

$db = new Database();
$conn = $db->getConnection();

// Get book details with author and category
$stmt = $conn->prepare("
    SELECT b.*, a.name as author_name, c.name as category_name,
           COALESCE(AVG(r.rating), 0) as avg_rating,
           COUNT(DISTINCT r.review_id) as review_count
    FROM books b
    JOIN authors a ON b.author_id = a.author_id
    JOIN categories c ON b.category_id = c.category_id
    LEFT JOIN reviews r ON b.book_id = r.book_id
    WHERE b.book_id = ?
    GROUP BY b.book_id
");
$stmt->execute([$_GET['id']]);
$book = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$book) {
    header('Location: books.php');
    exit();
}

// Get reviews
$stmt = $conn->prepare("
    SELECT r.*, u.username as user_name
    FROM reviews r
    JOIN users u ON r.user_id = u.user_id
    WHERE r.book_id = ?
    ORDER BY r.created_at DESC
");
$stmt->execute([$_GET['id']]);
$reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($book['title']); ?> - Library Management System</title>
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="../assets/css/books.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <?php include '../includes/sidebar.php'; ?>

        <main class="main-content">
            <header class="page-header">
                <h2><?php echo htmlspecialchars($book['title']); ?></h2>
            </header>

            <div class="book-details-container">
                <div class="book-info">
                    <div class="book-image">
                        <?php if ($book['image_url']): ?>
                            <img src="../<?php echo htmlspecialchars($book['image_url']); ?>" 
                                 alt="<?php echo htmlspecialchars($book['title']); ?>">
                        <?php else: ?>
                            <div class="no-image"><i class="fas fa-book"></i></div>
                        <?php endif; ?>
                    </div>
                    <div class="book-meta">
                        <h3><?php echo htmlspecialchars($book['title']); ?></h3>
                        <p class="author">By <?php echo htmlspecialchars($book['author_name']); ?></p>
                        <p class="category"><?php echo htmlspecialchars($book['category_name']); ?></p>
                        <div class="rating">
                            <?php
                            $rating = round($book['avg_rating']);
                            for ($i = 1; $i <= 5; $i++) {
                                echo '<i class="' . ($i <= $rating ? 'fas' : 'far') . ' fa-star"></i>';
                            }
                            ?>
                            <span>(<?php echo $book['review_count']; ?> reviews)</span>
                        </div>
                        <p class="price">$<?php echo number_format($book['price'], 2); ?></p>
                        <p class="stock">
                            <?php if ($book['available_copies'] > 0): ?>
                                <span class="in-stock">In Stock (<?php echo $book['available_copies']; ?> available)</span>
                            <?php else: ?>
                                <span class="out-of-stock">Out of Stock</span>
                            <?php endif; ?>
                        </p>
                        <?php if ($book['available_copies'] > 0): ?>
                            <button class="btn-primary" onclick="addToCart(<?php echo $book['book_id']; ?>)">
                                <i class="fas fa-cart-plus"></i> Add to Cart
                            </button>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="book-description">
                    <h3>Description</h3>
                    <p><?php echo nl2br(htmlspecialchars($book['description'])); ?></p>
                </div>

                <div class="reviews-section glass-effect">
                    <h3>Customer Reviews</h3>
                    <?php if (empty($reviews)): ?>
                        <p class="no-reviews">No reviews yet</p>
                    <?php else: ?>
                        <div class="reviews-list">
                            <?php foreach ($reviews as $review): ?>
                                <div class="review-card">
                                    <div class="review-header">
                                        <div class="reviewer-info">
                                            <i class="fas fa-user-circle"></i>
                                            <div>
                                                <h4><?php echo htmlspecialchars($review['user_name']); ?></h4>
                                                <p class="review-date">
                                                    <?php echo date('F j, Y', strtotime($review['created_at'])); ?>
                                                </p>
                                            </div>
                                        </div>
                                        <div class="review-rating">
                                            <?php
                                            for ($i = 1; $i <= 5; $i++) {
                                                echo '<i class="' . ($i <= $review['rating'] ? 'fas' : 'far') . ' fa-star"></i>';
                                            }
                                            ?>
                                        </div>
                                    </div>
                                    <p class="review-comment"><?php echo nl2br(htmlspecialchars($review['comment'])); ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <script src="../assets/js/theme.js"></script>
    <script src="../assets/js/cart.js"></script>
</body>
</html> 