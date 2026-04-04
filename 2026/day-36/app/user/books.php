<?php
session_start();
require_once '../config/database.php';

$db = new Database();
$conn = $db->getConnection();

// Fetch all active books with author and category names
$books = $conn->query("
    SELECT b.*, a.name as author_name, c.name as category_name
    FROM books b
    LEFT JOIN authors a ON b.author_id = a.author_id
    LEFT JOIN categories c ON b.category_id = c.category_id
    ORDER BY b.created_at DESC
")->fetchAll(PDO::FETCH_ASSOC);

// Get categories for filter
$categories = $conn->query("SELECT * FROM categories ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Books - Online Library</title>
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="../assets/css/books.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="container">
        <section class="books-header">
            <h1>Available Books</h1>
            <div class="filters">
                <select id="categoryFilter" onchange="filterBooks()">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $category): ?>
                    <option value="<?php echo htmlspecialchars($category['name']); ?>">
                        <?php echo htmlspecialchars($category['name']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                <input type="text" id="searchInput" placeholder="Search books..." onkeyup="filterBooks()">
            </div>
        </section>

        <section class="books-grid">
            <?php foreach ($books as $book): ?>
            <div class="book-card" data-category="<?php echo htmlspecialchars($book['category_name']); ?>">
                <div class="book-image">
                    <img src="<?php echo !empty($book['image_url']) ? '../' . $book['image_url'] : '../assets/images/default-book.jpg'; ?>" 
                         alt="<?php echo htmlspecialchars($book['title']); ?>">
                </div>
                <div class="book-info">
                    <h3><?php echo htmlspecialchars($book['title']); ?></h3>
                    <p class="author">by <?php echo htmlspecialchars($book['author_name']); ?></p>
                    <p class="category"><?php echo htmlspecialchars($book['category_name']); ?></p>
                    <p class="price">₹<?php echo number_format($book['price'], 2); ?></p>
                    <div class="book-actions">
                        <a href="book-details.php?id=<?php echo $book['book_id']; ?>" class="btn-primary">View Details</a>
                        <?php if ($book['available_copies'] > 0): ?>
                        <button onclick="addToCart(<?php echo $book['book_id']; ?>)" class="btn-secondary">
                            <i class="fas fa-cart-plus"></i> Add to Cart
                        </button>
                        <?php else: ?>
                        <button class="btn-secondary disabled" disabled>Out of Stock</button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </section>
    </main>

    <?php include 'includes/footer.php'; ?>

    <script>
        function filterBooks() {
            const category = document.getElementById('categoryFilter').value.toLowerCase();
            const search = document.getElementById('searchInput').value.toLowerCase();
            const books = document.querySelectorAll('.book-card');

            books.forEach(book => {
                const bookCategory = book.dataset.category.toLowerCase();
                const bookTitle = book.querySelector('h3').textContent.toLowerCase();
                const bookAuthor = book.querySelector('.author').textContent.toLowerCase();

                const matchesCategory = !category || bookCategory === category;
                const matchesSearch = !search || 
                    bookTitle.includes(search) || 
                    bookAuthor.includes(search);

                book.style.display = (matchesCategory && matchesSearch) ? 'block' : 'none';
            });
        }

        function addToCart(bookId) {
            fetch('add-to-cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'book_id=' + bookId
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Book added to cart successfully!');
                    // Update cart count in header if it exists
                    const cartCount = document.getElementById('cartCount');
                    if (cartCount) {
                        fetch('get-cart-count.php')
                            .then(response => response.json())
                            .then(data => {
                                cartCount.textContent = data.count;
                            });
                    }
                } else {
                    alert(data.message || 'Failed to add book to cart');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while adding the book to cart');
            });
        }
    </script>
</body>
</html>