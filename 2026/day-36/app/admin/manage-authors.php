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

// Handle author actions (add, edit, delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add' || $action === 'edit') {
        $name = $_POST['name'] ?? '';
        $bio = $_POST['bio'] ?? '';
        
        if (empty($name)) {
            $error = 'Author name is required';
        } else {
            if ($action === 'add') {
                $stmt = $conn->prepare("INSERT INTO authors (name, bio) VALUES (?, ?)");
                if ($stmt->execute([$name, $bio])) {
                    $success = 'Author added successfully';
                } else {
                    $error = 'Failed to add author';
                }
            } else {
                $author_id = $_POST['author_id'] ?? '';
                $stmt = $conn->prepare("UPDATE authors SET name = ?, bio = ? WHERE author_id = ?");
                if ($stmt->execute([$name, $bio, $author_id])) {
                    $success = 'Author updated successfully';
                } else {
                    $error = 'Failed to update author';
                }
            }
        }
    } elseif ($action === 'delete') {
        $author_id = $_POST['author_id'] ?? '';
        
        // Check if author has any books
        $stmt = $conn->prepare("SELECT COUNT(*) FROM books WHERE author_id = ?");
        $stmt->execute([$author_id]);
        $book_count = $stmt->fetchColumn();
        
        if ($book_count > 0) {
            $error = 'Cannot delete author with existing books';
        } else {
            $stmt = $conn->prepare("DELETE FROM authors WHERE author_id = ?");
            if ($stmt->execute([$author_id])) {
                $success = 'Author deleted successfully';
            } else {
                $error = 'Failed to delete author';
            }
        }
    }
}

// Fetch all authors with book counts
$authors = $conn->query("
    SELECT a.*, COUNT(b.book_id) as book_count 
    FROM authors a 
    LEFT JOIN books b ON a.author_id = b.author_id 
    GROUP BY a.author_id 
    ORDER BY a.name
")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Authors - Library Management System</title>
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
                <h2>Manage Authors</h2>
                <div class="admin-actions">
                    <button class="btn-primary" onclick="showAddAuthorModal()">
                        <i class="fas fa-plus"></i> Add New Author
                    </button>
                </div>
            </header>

            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <div class="authors-table glass-effect">
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Bio</th>
                            <th>Books</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($authors as $author): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($author['name']); ?></td>
                                <td><?php echo htmlspecialchars($author['bio']); ?></td>
                                <td><?php echo $author['book_count']; ?></td>
                                <td>
                                    <button class="btn-icon" onclick="editAuthor(<?php echo $author['author_id']; ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <?php if ($author['book_count'] == 0): ?>
                                        <button class="btn-icon delete" onclick="deleteAuthor(<?php echo $author['author_id']; ?>)">
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

    <!-- Add/Edit Author Modal -->
    <div id="authorModal" class="modal">
        <div class="modal-content glass-effect">
            <span class="close">&times;</span>
            <h2 id="modalTitle">Add New Author</h2>
            <form id="authorForm" method="POST">
                <input type="hidden" name="action" id="formAction" value="add">
                <input type="hidden" name="author_id" id="authorId">
                
                <div class="form-group">
                    <label for="name">Author Name</label>
                    <input type="text" id="name" name="name" required>
                </div>
                
                <div class="form-group">
                    <label for="bio">Biography</label>
                    <textarea id="bio" name="bio" rows="4"></textarea>
                </div>
                
                <button type="submit" class="btn-primary">Save Author</button>
            </form>
        </div>
    </div>

    <script src="../assets/js/theme.js"></script>
    <script src="../assets/js/admin.js"></script>
    <script>
        function showAddAuthorModal() {
            document.getElementById('modalTitle').textContent = 'Add New Author';
            document.getElementById('formAction').value = 'add';
            document.getElementById('authorForm').reset();
            document.getElementById('authorModal').style.display = 'block';
        }

        function editAuthor(authorId) {
            fetch(`get-author.php?id=${authorId}`)
                .then(response => response.json())
                .then(author => {
                    document.getElementById('modalTitle').textContent = 'Edit Author';
                    document.getElementById('formAction').value = 'edit';
                    document.getElementById('authorId').value = author.author_id;
                    document.getElementById('name').value = author.name;
                    document.getElementById('bio').value = author.bio;
                    document.getElementById('authorModal').style.display = 'block';
                });
        }

        function deleteAuthor(authorId) {
            if (confirm('Are you sure you want to delete this author?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="author_id" value="${authorId}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        // Close modal when clicking the close button or outside the modal
        document.querySelector('.close').onclick = function() {
            document.getElementById('authorModal').style.display = 'none';
        }

        window.onclick = function(event) {
            if (event.target == document.getElementById('authorModal')) {
                document.getElementById('authorModal').style.display = 'none';
            }
        }
    </script>
</body>
</html> 