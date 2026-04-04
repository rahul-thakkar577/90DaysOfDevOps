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

// Handle category actions (add, edit, delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add' || $action === 'edit') {
        $name = $_POST['name'] ?? '';
        
        if (empty($name)) {
            $error = 'Category name is required';
        } else {
            if ($action === 'add') {
                $stmt = $conn->prepare("INSERT INTO categories (name) VALUES (?)");
                if ($stmt->execute([$name])) {
                    $success = 'Category added successfully';
                } else {
                    $error = 'Failed to add category';
                }
            } else {
                $category_id = $_POST['category_id'] ?? '';
                $stmt = $conn->prepare("UPDATE categories SET name = ? WHERE category_id = ?");
                if ($stmt->execute([$name, $category_id])) {
                    $success = 'Category updated successfully';
                } else {
                    $error = 'Failed to update category';
                }
            }
        }
    } elseif ($action === 'delete') {
        $category_id = $_POST['category_id'] ?? '';
        
        // Check if category has any books
        $stmt = $conn->prepare("SELECT COUNT(*) FROM books WHERE category_id = ?");
        $stmt->execute([$category_id]);
        $book_count = $stmt->fetchColumn();
        
        if ($book_count > 0) {
            $error = 'Cannot delete category with existing books';
        } else {
            $stmt = $conn->prepare("DELETE FROM categories WHERE category_id = ?");
            if ($stmt->execute([$category_id])) {
                $success = 'Category deleted successfully';
            } else {
                $error = 'Failed to delete category';
            }
        }
    }
}

// Fetch all categories with book counts
$categories = $conn->query("
    SELECT c.*, COUNT(b.book_id) as book_count 
    FROM categories c 
    LEFT JOIN books b ON c.category_id = b.category_id 
    GROUP BY c.category_id 
    ORDER BY c.name
")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Categories - Library Management System</title>
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
                <h2>Manage Categories</h2>
                <div class="admin-actions">
                    <button class="btn-primary" onclick="showAddCategoryModal()">
                        <i class="fas fa-plus"></i> Add New Category
                    </button>
                </div>
            </header>

            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <div class="categories-table glass-effect">
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Books</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categories as $category): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($category['name']); ?></td>
                                <td><?php echo $category['book_count']; ?></td>
                                <td>
                                    <button class="btn-icon" onclick="editCategory(<?php echo $category['category_id']; ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <?php if ($category['book_count'] == 0): ?>
                                        <button class="btn-icon delete" onclick="deleteCategory(<?php echo $category['category_id']; ?>)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <!-- Add/Edit Category Modal -->
    <div id="categoryModal" class="modal">
        <div class="modal-content glass-effect">
            <span class="close">&times;</span>
            <h2 id="modalTitle">Add New Category</h2>
            <form id="categoryForm" method="POST">
                <input type="hidden" name="action" id="formAction" value="add">
                <input type="hidden" name="category_id" id="categoryId">
                
                <div class="form-group">
                    <label for="name">Category Name</label>
                    <input type="text" id="name" name="name" required>
                </div>
                
                <button type="submit" class="btn-primary">Save Category</button>
            </form>
        </div>
    </div>

    <script src="../assets/js/theme.js"></script>
    <script src="../assets/js/admin.js"></script>
    <script>
        function showAddCategoryModal() {
            document.getElementById('modalTitle').textContent = 'Add New Category';
            document.getElementById('formAction').value = 'add';
            document.getElementById('categoryForm').reset();
            document.getElementById('categoryModal').style.display = 'block';
        }

        function editCategory(categoryId) {
            fetch(`get-category.php?id=${categoryId}`)
                .then(response => response.json())
                .then(category => {
                    document.getElementById('modalTitle').textContent = 'Edit Category';
                    document.getElementById('formAction').value = 'edit';
                    document.getElementById('categoryId').value = category.category_id;
                    document.getElementById('name').value = category.name;
                    document.getElementById('categoryModal').style.display = 'block';
                });
        }

        function deleteCategory(categoryId) {
            if (confirm('Are you sure you want to delete this category?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="category_id" value="${categoryId}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        // Close modal when clicking the close button or outside the modal
        document.querySelector('.close').onclick = function() {
            document.getElementById('categoryModal').style.display = 'none';
        }

        window.onclick = function(event) {
            if (event.target == document.getElementById('categoryModal')) {
                document.getElementById('categoryModal').style.display = 'none';
            }
        }
    </script>
</body>
</html> 