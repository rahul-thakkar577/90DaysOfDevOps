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

// Handle book actions (add, edit, delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add' || $action === 'edit') {
        $title = $_POST['title'] ?? '';
        $author_id = $_POST['author_id'] ?? '';
        $category_id = $_POST['category_id'] ?? '';
        $price = $_POST['price'] ?? '';
        $available_copies = $_POST['available_copies'] ?? '';
        $description = $_POST['description'] ?? '';
        
        // Handle image upload
        $image_url = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
            $target_dir = "../uploads/books/";
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            
            $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $file_name = uniqid() . '.' . $file_extension;
            $target_file = $target_dir . $file_name;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                $image_url = 'uploads/books/' . $file_name;
            }
        }
        
        if ($action === 'add') {
            $stmt = $conn->prepare("INSERT INTO books (title, author_id, category_id, price, available_copies, description, image_url) VALUES (?, ?, ?, ?, ?, ?, ?)");
            if ($stmt->execute([$title, $author_id, $category_id, $price, $available_copies, $description, $image_url])) {
                $success = 'Book added successfully';
            } else {
                $error = 'Failed to add book';
            }
        } else {
            $book_id = $_POST['book_id'] ?? '';
            $update_query = "UPDATE books SET title = ?, author_id = ?, category_id = ?, price = ?, available_copies = ?, description = ?";
            $params = [$title, $author_id, $category_id, $price, $available_copies, $description];
            
            if ($image_url) {
                $update_query .= ", image_url = ?";
                $params[] = $image_url;
            }
            
            $update_query .= " WHERE book_id = ?";
            $params[] = $book_id;
            
            $stmt = $conn->prepare($update_query);
            if ($stmt->execute($params)) {
                $success = 'Book updated successfully';
            } else {
                $error = 'Failed to update book';
            }
        }
    } elseif ($action === 'delete') {
        $book_id = $_POST['book_id'] ?? '';
        $stmt = $conn->prepare("DELETE FROM books WHERE book_id = ?");
        if ($stmt->execute([$book_id])) {
            $success = 'Book deleted successfully';
        } else {
            $error = 'Failed to delete book';
        }
    }
}

// Fetch all books with author and category names
$books = $conn->query("
    SELECT b.*, a.name as author_name, c.name as category_name 
    FROM books b 
    JOIN authors a ON b.author_id = a.author_id 
    JOIN categories c ON b.category_id = c.category_id 
    ORDER BY b.created_at DESC
")->fetchAll(PDO::FETCH_ASSOC);

// Fetch authors and categories for the form
$authors = $conn->query("SELECT * FROM authors ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
$categories = $conn->query("SELECT * FROM categories ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Books - Library Management System</title>
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
                <h2>Manage Books</h2>
                <div class="admin-actions">
                    <button class="btn-primary" onclick="showAddBookModal()">
                        <i class="fas fa-plus"></i> Add New Book
                    </button>
                </div>
            </header>

            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <div class="books-table glass-effect">
                <table>
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Title</th>
                            <th>Author</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Available</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($books as $book): ?>
                            <tr>
                                <td>
                                    <?php if ($book['image_url']): ?>
                                        <img src="../<?php echo htmlspecialchars($book['image_url']); ?>" alt="Book cover" class="book-thumbnail">
                                    <?php else: ?>
                                        <div class="no-image"><i class="fas fa-book"></i></div>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($book['title']); ?></td>
                                <td><?php echo htmlspecialchars($book['author_name']); ?></td>
                                <td><?php echo htmlspecialchars($book['category_name']); ?></td>
                                <td>$<?php echo number_format($book['price'], 2); ?></td>
                                <td><?php echo $book['available_copies']; ?></td>
                                <td>
                                    <button class="btn-icon" onclick="editBook(<?php echo $book['book_id']; ?>)">
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
        </main>
    </div>

    <!-- Add/Edit Book Modal -->
    <div id="bookModal" class="modal">
        <div class="modal-content glass-effect">
            <span class="close">&times;</span>
            <h2 id="modalTitle">Add New Book</h2>
            <form id="bookForm" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" id="formAction" value="add">
                <input type="hidden" name="book_id" id="bookId">
                
                <div class="form-group">
                    <label for="title">Title</label>
                    <input type="text" id="title" name="title" required>
                </div>
                
                <div class="form-group">
                    <label for="author_id">Author</label>
                    <select id="author_id" name="author_id" required>
                        <?php foreach ($authors as $author): ?>
                            <option value="<?php echo $author['author_id']; ?>">
                                <?php echo htmlspecialchars($author['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="category_id">Category</label>
                    <select id="category_id" name="category_id" required>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['category_id']; ?>">
                                <?php echo htmlspecialchars($category['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="price">Price</label>
                    <input type="number" id="price" name="price" step="0.01" required>
                </div>
                
                <div class="form-group">
                    <label for="available_copies">Available Copies</label>
                    <input type="number" id="available_copies" name="available_copies" required>
                </div>
                
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="4"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="image">Book Cover Image</label>
                    <input type="file" id="image" name="image" accept="image/*">
                </div>
                
                <button type="submit" class="btn-primary">Save Book</button>
            </form>
        </div>
    </div>

    <script src="../assets/js/theme.js"></script>
    <script src="../assets/js/admin.js"></script>
    <script>
        // Book management JavaScript functions
        function showAddBookModal() {
            document.getElementById('modalTitle').textContent = 'Add New Book';
            document.getElementById('formAction').value = 'add';
            document.getElementById('bookForm').reset();
            document.getElementById('bookModal').style.display = 'block';
        }

        function editBook(bookId) {
            // Fetch book details and populate the form
            fetch(`get-book.php?id=${bookId}`)
                .then(response => response.json())
                .then(book => {
                    document.getElementById('modalTitle').textContent = 'Edit Book';
                    document.getElementById('formAction').value = 'edit';
                    document.getElementById('bookId').value = book.book_id;
                    document.getElementById('title').value = book.title;
                    document.getElementById('author_id').value = book.author_id;
                    document.getElementById('category_id').value = book.category_id;
                    document.getElementById('price').value = book.price;
                    document.getElementById('available_copies').value = book.available_copies;
                    document.getElementById('description').value = book.description;
                    document.getElementById('bookModal').style.display = 'block';
                });
        }

        function deleteBook(bookId) {
            if (confirm('Are you sure you want to delete this book?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="book_id" value="${bookId}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        // Close modal when clicking the close button or outside the modal
        document.querySelector('.close').onclick = function() {
            document.getElementById('bookModal').style.display = 'none';
        }

        window.onclick = function(event) {
            if (event.target == document.getElementById('bookModal')) {
                document.getElementById('bookModal').style.display = 'none';
            }
        }
    </script>
</body>
</html> 