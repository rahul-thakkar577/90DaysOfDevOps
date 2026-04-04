<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'user') {
    header('Location: ../login.php');
    exit();
}

$db = new Database();
$conn = $db->getConnection();

$order_id = $_GET['order_id'] ?? 0;

// Validate order and get books
try {
    $stmt = $conn->prepare("
        SELECT od.book_id, b.title, b.image_url,
               (SELECT COUNT(*) FROM reviews r WHERE r.book_id = b.book_id AND r.user_id = ?) as has_review
        FROM order_items od
        JOIN books b ON od.book_id = b.book_id
        JOIN orders o ON od.order_id = o.order_id
        WHERE o.order_id = ? AND o.user_id = ? AND o.status = 'delivered'
    ");
    $stmt->execute([$_SESSION['user_id'], $order_id, $_SESSION['user_id']]);
    $books = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($books)) {
        $_SESSION['error_message'] = 'Invalid order or you cannot review these books yet.';
        header('Location: orders.php');
        exit();
    }
} catch (Exception $e) {
    error_log("Review Error: " . $e->getMessage());
    $_SESSION['error_message'] = 'An error occurred. Please try again later.';
    header('Location: orders.php');
    exit();
}

// Handle review submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $book_id = $_POST['book_id'] ?? 0;
    $rating = min(5, max(1, intval($_POST['rating'] ?? 0)));
    $comment = trim($_POST['comment'] ?? '');
    
    try {
        // Check if book exists in the order
        $book_exists = false;
        foreach ($books as $book) {
            if ($book['book_id'] == $book_id) {
                $book_exists = true;
                break;
            }
        }
        
        if (!$book_exists) {
            throw new Exception('Invalid book selection');
        }
        
        // Check if already reviewed
        $stmt = $conn->prepare("SELECT review_id FROM reviews WHERE user_id = ? AND book_id = ?");
        $stmt->execute([$_SESSION['user_id'], $book_id]);
        if ($stmt->fetch()) {
            throw new Exception('You have already reviewed this book');
        }
        
        // Add review using proper column names as per MEMORY
        $stmt = $conn->prepare("
            INSERT INTO reviews (user_id, book_id, rating, comment, created_at) 
            VALUES (?, ?, ?, ?, NOW())
        ");
        $stmt->execute([$_SESSION['user_id'], $book_id, $rating, $comment]);
        
        $_SESSION['success_message'] = 'Review submitted successfully!';
        header('Location: orders.php');
        exit();
        
    } catch (Exception $e) {
        $_SESSION['error_message'] = $e->getMessage();
    }
}

require_once 'includes/header.php';
?>

<div class="content-header">
    <h2 class="page-title">Write Review</h2>
</div>

<div class="reviews-container">
    <?php foreach ($books as $book): ?>
        <?php if (!$book['has_review']): ?>
            <div class="review-card">
                <div class="book-info">
                    <div class="book-image">
                        <?php if ($book['image_url']): ?>
                            <img src="../<?php echo htmlspecialchars($book['image_url']); ?>" 
                                 alt="<?php echo htmlspecialchars($book['title']); ?>">
                        <?php else: ?>
                            <div class="no-image">
                                <i class="fas fa-book"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="book-details">
                        <h3><?php echo htmlspecialchars($book['title']); ?></h3>
                    </div>
                </div>

                <form method="POST" class="review-form">
                    <input type="hidden" name="book_id" value="<?php echo $book['book_id']; ?>">
                    
                    <div class="rating-input">
                        <label>Your Rating</label>
                        <div class="stars">
                            <?php for ($i = 5; $i >= 1; $i--): ?>
                                <input type="radio" id="star<?php echo $i; ?>_<?php echo $book['book_id']; ?>" 
                                       name="rating" value="<?php echo $i; ?>" required>
                                <label for="star<?php echo $i; ?>_<?php echo $book['book_id']; ?>">
                                    <i class="fas fa-star"></i>
                                </label>
                            <?php endfor; ?>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="comment_<?php echo $book['book_id']; ?>">Your Review</label>
                        <textarea id="comment_<?php echo $book['book_id']; ?>" name="comment" 
                                  class="form-control" rows="4" required
                                  placeholder="Share your thoughts about this book..."></textarea>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane"></i>
                            Submit Review
                        </button>
                    </div>
                </form>
            </div>
        <?php endif; ?>
    <?php endforeach; ?>
</div>

<style>
/* Review Page Styles */
.reviews-container {
    display: flex;
    flex-direction: column;
    gap: 2rem;
    max-width: 800px;
    margin: 0 auto;
}

.review-card {
    background: var(--card-bg);
    border-radius: 1rem;
    padding: 1.5rem;
    box-shadow: var(--shadow-sm);
}

.book-info {
    display: flex;
    gap: 1.5rem;
    margin-bottom: 1.5rem;
    padding-bottom: 1.5rem;
    border-bottom: 1px solid var(--border-color);
}

.book-image {
    width: 120px;
    height: 180px;
    border-radius: 0.5rem;
    overflow: hidden;
}

.book-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.book-details h3 {
    font-size: 1.25rem;
    font-weight: 600;
    color: var(--text-primary);
    margin: 0;
}

/* Star Rating */
.rating-input {
    margin-bottom: 1.5rem;
}

.rating-input label {
    display: block;
    font-size: 0.875rem;
    font-weight: 500;
    color: var(--text-secondary);
    margin-bottom: 0.5rem;
}

.stars {
    display: flex;
    flex-direction: row-reverse;
    gap: 0.25rem;
}

.stars input {
    display: none;
}

.stars label {
    cursor: pointer;
    color: var(--text-muted);
    font-size: 1.5rem;
    transition: color var(--transition-fast);
}

.stars label:hover,
.stars label:hover ~ label,
.stars input:checked ~ label {
    color: var(--warning-color);
}

/* Form Styling */
.review-form textarea {
    resize: vertical;
}

.form-actions {
    margin-top: 1.5rem;
    display: flex;
    justify-content: flex-end;
}

/* Responsive */
@media (max-width: 768px) {
    .book-info {
        flex-direction: column;
        align-items: center;
        text-align: center;
    }
    
    .book-image {
        width: 150px;
        height: 225px;
    }
}

@media (max-width: 480px) {
    .review-card {
        padding: 1rem;
    }
    
    .stars {
        justify-content: center;
    }
    
    .form-actions {
        flex-direction: column;
    }
    
    .form-actions .btn {
        width: 100%;
    }
}
</style>

<?php require_once 'includes/footer.php'; ?>