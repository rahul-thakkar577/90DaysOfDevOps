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

// Fetch all books with author and category names
$books = $conn->query("
    SELECT b.*, a.name as author_name, c.name as category_name
    FROM books b
    LEFT JOIN authors a ON b.author_id = a.author_id
    LEFT JOIN categories c ON b.category_id = c.category_id
    ORDER BY b.created_at DESC
")->fetchAll(PDO::FETCH_ASSOC);

// Fetch authors and categories for the add/edit form
$authors = $conn->query("SELECT * FROM authors ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
$categories = $conn->query("SELECT * FROM categories ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Books - Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .book-thumbnail {
            width: 80px;
            height: 120px;
            object-fit: cover;
            border-radius: 4px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        @media (max-width: 768px) {
            .books-table {
                overflow-x: auto;
            }
            
            table {
                min-width: 800px;
            }

            .book-thumbnail {
                width: 60px;
                height: 90px;
            }
        }

        @media (max-width: 480px) {
            .book-thumbnail {
                width: 50px;
                height: 75px;
            }
        }
    </style>
</head>
<body>
    <div class="admin-dashboard">
        <?php include 'includes/sidebar.php'; ?>

        <main class="admin-main">
            <header class="admin-header">
                <h1>Manage Books</h1>
                <div class="admin-actions">
                    <button class="btn-primary" onclick="openAddModal()">
                        <i class="fas fa-plus"></i> Add New Book
                    </button>
                </div>
            </header>

            <div class="books-table">
                <table>
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Title</th>
                            <th>Author</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($books as $book): ?>
                        <tr>
                            <td>
                                <img src="<?php echo !empty($book['image_url']) ? '../' . $book['image_url'] : '../assets/images/default-book.jpg'; ?>" 
                                     alt="<?php echo htmlspecialchars($book['title']); ?>" 
                                     class="book-thumbnail">
                            </td>
                            <td><?php echo htmlspecialchars($book['title']); ?></td>
                            <td><?php echo htmlspecialchars($book['author_name']); ?></td>
                            <td><?php echo htmlspecialchars($book['category_name']); ?></td>
                            <td>₹<?php echo number_format($book['price'], 2); ?></td>
                            <td><?php echo $book['available_copies']; ?></td>
                            <td class="actions">
                                <button class="btn-icon" onclick='editBook(<?php echo json_encode($book); ?>)'>
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn-icon delete" onclick="deleteBook(<?php echo $book['book_id']; ?>)">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Add/Edit Book Modal -->
            <div id="bookModal" class="modal">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 id="modalTitle">Add New Book</h2>
                        <span class="close" onclick="closeModal()">&times;</span>
                    </div>
                    
                    <form id="bookForm" onsubmit="saveBook(event)">
                        <div class="modal-body">
                            <input type="hidden" id="bookId" name="book_id">
                            
                            <div class="form-group">
                                <label for="title">Book Title</label>
                                <input type="text" id="title" name="title" required>
                            </div>

                            <div class="form-group">
                                <label for="author">Author</label>
                                <select id="author" name="author_id" required>
                                    <option value="">Select Author</option>
                                    <?php foreach ($authors as $author): ?>
                                    <option value="<?php echo $author['author_id']; ?>">
                                        <?php echo htmlspecialchars($author['name']); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="category">Category</label>
                                <select id="category" name="category_id" required>
                                    <option value="">Select Category</option>
                                    <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category['category_id']; ?>">
                                        <?php echo htmlspecialchars($category['name']); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="price">Price</label>
                                <input type="number" id="price" name="price" step="0.01" min="0" required>
                            </div>

                            <div class="form-group">
                                <label for="stock">Available Copies</label>
                                <input type="number" id="stock" name="available_copies" min="0" required>
                            </div>

                            <div class="form-group">
                                <label for="description">Description</label>
                                <textarea id="description" name="description" rows="4"></textarea>
                            </div>

                            <div class="form-group">
                                <label for="image">Book Cover Image</label>
                                <input type="file" id="image" name="image" accept="image/*">
                                <div id="currentImage"></div>
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="button" class="btn-secondary" onclick="closeModal()">Cancel</button>
                            <button type="submit" class="btn-primary">Save Book</button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <script>
        const modal = document.getElementById('bookModal');
        const modalTitle = document.getElementById('modalTitle');
        const bookForm = document.getElementById('bookForm');
        const currentImage = document.getElementById('currentImage');

        function openAddModal() {
            modalTitle.textContent = 'Add New Book';
            bookForm.reset();
            document.getElementById('bookId').value = '';
            currentImage.innerHTML = '';
            modal.style.display = 'block';
        }

        function editBook(book) {
            modalTitle.textContent = 'Edit Book';
            document.getElementById('bookId').value = book.book_id;
            document.getElementById('title').value = book.title;
            document.getElementById('author').value = book.author_id;
            document.getElementById('category').value = book.category_id;
            document.getElementById('price').value = book.price;
            document.getElementById('stock').value = book.available_copies;
            document.getElementById('description').value = book.description || '';
            
            if (book.image_url) {
                currentImage.innerHTML = `<img src="../${book.image_url}" alt="Current Image" style="max-width: 100px; margin-top: 10px;">`;
            } else {
                currentImage.innerHTML = '';
            }
            
            modal.style.display = 'block';
        }

        function closeModal() {
            modal.style.display = 'none';
            bookForm.reset();
        }

        bookForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            
            fetch('api/save_book.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'Failed to save book');
                }
            });
        });

        function deleteBook(id) {
            if (confirm('Are you sure you want to delete this book? This action cannot be undone.')) {
                fetch('api/delete_book.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'book_id=' + id
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        location.reload();
                    } else {
                        alert(data.message || 'Failed to delete book');
                    }
                })
                .catch(error => {
                    alert('Error occurred while deleting the book');
                    console.error('Error:', error);
                });
            }
        }
    </script>
</body>
</html>