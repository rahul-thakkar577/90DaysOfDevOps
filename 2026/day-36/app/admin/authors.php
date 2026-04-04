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

// Fetch all authors with book count
$authors = $conn->query("
    SELECT a.*, COUNT(b.book_id) as book_count 
    FROM authors a 
    LEFT JOIN books b ON a.author_id = b.author_id 
    GROUP BY a.author_id 
    ORDER BY a.name ASC
")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Authors - Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="admin-dashboard">
        <?php include 'includes/sidebar.php'; ?>

        <main class="admin-main">
            <header class="admin-header">
                <h1>Manage Authors</h1>
                <div class="admin-actions">
                    <button class="btn-primary" onclick="openAddModal()">
                        <i class="fas fa-plus"></i> Add Author
                    </button>
                </div>
            </header>

            <div class="books-table">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Books</th>
                            <th>Bio</th>
                            <th>Added Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($authors as $author): ?>
                        <tr>
                            <td><?php echo $author['author_id']; ?></td>
                            <td><?php echo htmlspecialchars($author['name']); ?></td>
                            <td><?php echo $author['book_count']; ?></td>
                            <td><?php echo htmlspecialchars(substr($author['bio'] ?? '', 0, 100)) . '...'; ?></td>
                            <td><?php echo date('M d, Y', strtotime($author['created_at'])); ?></td>
                            <td class="actions">
                                <button class="btn-icon" onclick="editAuthor(<?php echo htmlspecialchars(json_encode($author)); ?>)">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn-icon delete" onclick="deleteAuthor(<?php echo $author['author_id']; ?>)">
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

    <!-- Add/Edit Author Modal -->
    <div id="authorModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2 id="modalTitle">Add Author</h2>
            <form id="authorForm" onsubmit="saveAuthor(event)">
                <input type="hidden" id="authorId" name="author_id">
                
                <div class="form-group">
                    <label for="authorName">Author Name</label>
                    <input type="text" id="authorName" name="name" required>
                </div>

                <div class="form-group">
                    <label for="authorBio">Biography</label>
                    <textarea id="authorBio" name="bio" rows="4"></textarea>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn-secondary" onclick="closeModal()">Cancel</button>
                    <button type="submit" class="btn-primary">Save Author</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const modal = document.getElementById('authorModal');
        const modalTitle = document.getElementById('modalTitle');
        const authorForm = document.getElementById('authorForm');
        const authorIdInput = document.getElementById('authorId');
        const authorNameInput = document.getElementById('authorName');
        const authorBioInput = document.getElementById('authorBio');

        function openAddModal() {
            modalTitle.textContent = 'Add Author';
            authorForm.reset();
            authorIdInput.value = '';
            modal.style.display = 'block';
        }

        function editAuthor(author) {
            modalTitle.textContent = 'Edit Author';
            authorIdInput.value = author.author_id;
            authorNameInput.value = author.name;
            authorBioInput.value = author.bio || '';
            modal.style.display = 'block';
        }

        function closeModal() {
            modal.style.display = 'none';
            authorForm.reset();
        }

        function saveAuthor(event) {
            event.preventDefault();
            const formData = new FormData(authorForm);
            
            fetch('api/save_author.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message);
                }
            });
        }

        function deleteAuthor(id) {
            if (confirm('Are you sure you want to delete this author?')) {
                fetch('api/delete_author.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `author_id=${id}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert(data.message);
                    }
                });
            }
        }
    </script>
</body>
</html> 