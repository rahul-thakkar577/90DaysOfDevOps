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

// Fetch all reviews with user and book details
$reviews = $conn->query("
    SELECT r.*, u.name as user_name, b.title as book_title 
    FROM reviews r
    JOIN users u ON r.user_id = u.user_id 
    JOIN books b ON r.book_id = b.book_id 
    ORDER BY r.created_at DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Reviews - Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="admin-dashboard">
        <?php include 'includes/sidebar.php'; ?>

        <main class="admin-main">
            <header class="admin-header">
                <h1>Manage Reviews</h1>
            </header>

            <div class="reviews-table">
                <table>
                    <thead>
                        <tr>
                            <th>Book</th>
                            <th>User</th>
                            <th>Rating</th>
                            <th>Review</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reviews as $review): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($review['book_title']); ?></td>
                            <td><?php echo htmlspecialchars($review['user_name']); ?></td>
                            <td>
                                <div class="rating-stars">
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
                            </td>
                            <td>
                                <div class="review-text">
                                    <?php echo htmlspecialchars($review['comment']); ?>
                                </div>
                            </td>
                            <td><?php echo date('M d, Y', strtotime($review['created_at'])); ?></td>
                            <td class="actions">
                                <button class="btn-icon" onclick="viewReview(<?php echo htmlspecialchars(json_encode($review)); ?>)">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="btn-icon delete" onclick="deleteReview(<?php echo $review['review_id']; ?>)">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Review Details Modal -->
            <div id="reviewModal" class="modal">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2>Review Details</h2>
                        <span class="close" onclick="closeModal()">&times;</span>
                    </div>
                    <div class="modal-body" id="reviewDetails">
                        <!-- Review details will be loaded here -->
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        const modal = document.getElementById('reviewModal');
        
        function viewReview(review) {
            document.getElementById('reviewDetails').innerHTML = `
                <div class="review-details">
                    <h3>${review.book_title}</h3>
                    <p class="review-meta">
                        <strong>By:</strong> ${review.user_name}<br>
                        <strong>Date:</strong> ${new Date(review.created_at).toLocaleDateString()}
                    </p>
                    <div class="rating-display">
                        <strong>Rating:</strong>
                        <div class="stars">
                            ${getStarRating(review.rating)}
                        </div>
                    </div>
                    <div class="review-content">
                        <strong>Review:</strong>
                        <p>${review.comment}</p>
                    </div>
                </div>
            `;
            modal.style.display = 'block';
        }

        function getStarRating(rating) {
            let stars = '';
            for (let i = 1; i <= 5; i++) {
                if (i <= rating) {
                    stars += '<i class="fas fa-star"></i>';
                } else {
                    stars += '<i class="far fa-star"></i>';
                }
            }
            return stars;
        }

        function deleteReview(reviewId) {
            if (confirm('Are you sure you want to delete this review?')) {
                fetch('api/delete_review.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `review_id=${reviewId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert(data.message || 'Failed to delete review');
                    }
                });
            }
        }

        function closeModal() {
            modal.style.display = 'none';
        }
    </script>
</body>
</html> 