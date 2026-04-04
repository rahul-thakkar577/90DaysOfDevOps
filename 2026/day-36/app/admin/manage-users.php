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

// Handle user actions (edit status, delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $user_id = $_POST['user_id'] ?? '';
    
    if ($action === 'status') {
        $status = $_POST['status'] ?? '';
        $stmt = $conn->prepare("UPDATE users SET status = ? WHERE user_id = ? AND role = 'user'");
        if ($stmt->execute([$status, $user_id])) {
            $success = 'User status updated successfully';
        } else {
            $error = 'Failed to update user status';
        }
    } elseif ($action === 'delete') {
        // Check if user has any orders
        $stmt = $conn->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $order_count = $stmt->fetchColumn();
        
        if ($order_count > 0) {
            $error = 'Cannot delete user with existing orders';
        } else {
            $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ? AND role = 'user'");
            if ($stmt->execute([$user_id])) {
                $success = 'User deleted successfully';
            } else {
                $error = 'Failed to delete user';
            }
        }
    }
}

// Fetch all users with order counts
$users = $conn->query("
    SELECT u.*, COUNT(o.order_id) as order_count 
    FROM users u 
    LEFT JOIN orders o ON u.user_id = o.user_id 
    WHERE u.role = 'user'
    GROUP BY u.user_id 
    ORDER BY u.created_at DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Library Management System</title>
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
                <h2>Manage Users</h2>
            </header>

            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <div class="users-table glass-effect">
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th>Orders</th>
                            <th>Joined Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($user['name']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td>
                                    <select class="status-select" onchange="updateUserStatus(<?php echo $user['user_id']; ?>, this.value)">
                                        <option value="active" <?php echo $user['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                                        <option value="suspended" <?php echo $user['status'] === 'suspended' ? 'selected' : ''; ?>>Suspended</option>
                                    </select>
                                </td>
                                <td><?php echo $user['order_count']; ?></td>
                                <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                <td>
                                    <button class="btn-icon" onclick="viewUserDetails(<?php echo $user['user_id']; ?>)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <?php if ($user['order_count'] == 0): ?>
                                        <button class="btn-icon delete" onclick="deleteUser(<?php echo $user['user_id']; ?>)">
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

    <!-- User Details Modal -->
    <div id="userModal" class="modal">
        <div class="modal-content glass-effect">
            <span class="close">&times;</span>
            <h2>User Details</h2>
            <div id="userDetails"></div>
        </div>
    </div>

    <script src="../assets/js/theme.js"></script>
    <script src="../assets/js/admin.js"></script>
    <script>
        function updateUserStatus(userId, status) {
            if (confirm('Are you sure you want to update this user\'s status?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="status">
                    <input type="hidden" name="user_id" value="${userId}">
                    <input type="hidden" name="status" value="${status}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        function viewUserDetails(userId) {
            fetch(`get-user.php?id=${userId}`)
                .then(response => response.json())
                .then(user => {
                    const details = `
                        <div class="user-details">
                            <p><strong>Name:</strong> ${user.name}</p>
                            <p><strong>Email:</strong> ${user.email}</p>
                            <p><strong>Status:</strong> ${user.status}</p>
                            <p><strong>Joined:</strong> ${new Date(user.created_at).toLocaleDateString()}</p>
                        </div>
                    `;
                    document.getElementById('userDetails').innerHTML = details;
                    document.getElementById('userModal').style.display = 'block';
                });
        }

        function deleteUser(userId) {
            if (confirm('Are you sure you want to delete this user?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="user_id" value="${userId}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        // Close modal when clicking the close button or outside the modal
        document.querySelector('.close').onclick = function() {
            document.getElementById('userModal').style.display = 'none';
        }

        window.onclick = function(event) {
            if (event.target == document.getElementById('userModal')) {
                document.getElementById('userModal').style.display = 'none';
            }
        }
    </script>
</body>
</html> 