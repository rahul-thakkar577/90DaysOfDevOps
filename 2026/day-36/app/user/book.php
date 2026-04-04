<?php
session_start();
require_once '../config/database.php';

if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit();
}

$book_id = $_GET['id'];
$db = new Database();
$conn = $db->getConnection();

// Fetch book details with author name
$stmt = $conn->prepare("
    SELECT b.*, a.name as author_name, c.name as category_name,
           COALESCE(AVG(r.rating), 0) as average_rating,
           COUNT(r.review_id) as review_count
    FROM books b
    LEFT JOIN authors a ON b.author_id = a.author_id
    LEFT JOIN categories c ON b.category_id = c.category_id
    LEFT JOIN reviews r ON b.book_id = r.book_id
    WHERE b.book_id = ?
    GROUP BY b.book_id
");
$stmt->execute([$book_id]);
$book = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$book) {
    header('Location: index.php');
    exit();
}

// Fetch reviews for this book
$stmt = $conn->prepare("
    SELECT r.*, u.name as user_name
    FROM reviews r
    JOIN users u ON r.user_id = u.user_id
    WHERE r.book_id = ?
    ORDER BY r.created_at DESC
");
$stmt->execute([$book_id]);
$reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($book['title']); ?> - Library System</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/review.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container">
        <div class="book-details">
            <?php if (isset($_GET['review_success'])): ?>
                <div class="review-success">
                    <div class="review-success-content">
                        <i class="fas fa-check-circle"></i>
                        <p>Your review has been submitted successfully!</p>
                    </div>
                    <div class="review-success-actions">
                        <a href="../index.php" class="success-btn">
                            <i class="fas fa-home"></i> Home
                        </a>
                        <a href="orders.php" class="success-btn">
                            <i class="fas fa-shopping-bag"></i> My Orders
                        </a>
                    </div>
                </div>
                <script>
                    // Auto dismiss the success message after 5 seconds
                    setTimeout(() => {
                        const successMsg = document.querySelector('.review-success');
                        successMsg.style.animation = 'fadeOut 0.5s forwards';
                        setTimeout(() => successMsg.remove(), 500);
                    }, 5000);
                </script>
            <?php endif; ?>

            <div class="book-header">
                <div class="book-image">
                    <img src="<?php echo !empty($book['image_url']) ? '../' . $book['image_url'] : '../assets/images/default-book.jpg'; ?>" 
                         alt="<?php echo htmlspecialchars($book['title']); ?>">
                </div>
                <div class="book-info">
                    <h1><?php echo htmlspecialchars($book['title']); ?></h1>
                    <p class="author">by <?php echo htmlspecialchars($book['author_name']); ?></p>
                    <p class="category"><?php echo htmlspecialchars($book['category_name']); ?></p>
                    
                    <div class="rating-summary">
                        <div class="stars">
                            <?php
                            $rating = round($book['average_rating']);
                            for ($i = 1; $i <= 5; $i++) {
                                if ($i <= $rating) {
                                    echo '<i class="fas fa-star"></i>';
                                } else {
                                    echo '<i class="far fa-star"></i>';
                                }
                            }
                            ?>
                            <span>(<?php echo $book['review_count']; ?> reviews)</span>
                        </div>
                    </div>

                    <div class="price">₹<?php echo number_format($book['price'], 2); ?></div>
                    
                    <div class="stock-status">
                        <?php if ($book['available_copies'] > 0): ?>
                            <span class="in-stock">In Stock (<?php echo $book['available_copies']; ?> copies available)</span>
                        <?php else: ?>
                            <span class="out-of-stock">Out of Stock</span>
                        <?php endif; ?>
                    </div>

                    <div class="book-actions">
                        <?php if ($book['available_copies'] > 0): ?>
                            <form action="cart.php" method="POST">
                                <input type="hidden" name="book_id" value="<?php echo $book_id; ?>">
                                <button type="submit" class="btn-primary">
                                    <i class="fas fa-shopping-cart"></i> Add to Cart
                                </button>
                            </form>
                        <?php endif; ?>
                        
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <a href="write-review.php?book_id=<?php echo $book_id; ?>" class="btn-secondary">
                                <i class="fas fa-star"></i> Write a Review
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="book-description">
                <h2>Description</h2>
                <p><?php echo nl2br(htmlspecialchars($book['description'])); ?></p>
            </div>

            <div class="book-reviews">
                <h2>Customer Reviews</h2>
                <?php if (empty($reviews)): ?>
                    <p class="no-reviews">No reviews yet. Be the first to review this book!</p>
                <?php else: ?>
                    <?php foreach ($reviews as $review): ?>
                        <div class="review-card">
                            <div class="review-header">
                                <div class="reviewer-info">
                                    <span class="reviewer-name"><?php echo htmlspecialchars($review['user_name']); ?></span>
                                    <span class="review-date"><?php echo date('M d, Y', strtotime($review['created_at'])); ?></span>
                                </div>
                                <div class="review-rating">
                                    <?php
                                    for ($i = 1; $i <= 5; $i++) {
                                        if ($i <= $review['rating']) {
                                            echo '<i class="fas fa-star"></i>';
                                        } else {
                                            echo '<i class="far fa-star"></i>';
                                        }
                                    }
                                    ?>
                                </div>
                            </div>
                            <div class="review-content">
                                <?php echo nl2br(htmlspecialchars($review['comment'])); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html> 