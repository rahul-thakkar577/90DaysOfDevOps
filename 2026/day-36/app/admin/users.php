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

// Fetch all users except admins
$users = $conn->query("
    SELECT user_id, username, email, status, created_at, role
    FROM users 
    WHERE role = 'user' 
    ORDER BY created_at DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="admin-dashboard">
        <!-- Sidebar -->
        <?php include 'includes/sidebar.php'; ?>

        <main class="admin-main">
            <header class="admin-header">
                <h1>Manage Users</h1>
            </header>

            <div class="users-table">
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th>Joined Date</th>
                            <th>Orders</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td>
                                <span class="status-badge <?php echo htmlspecialchars($user['status']); ?>">
                                    <?php echo ucfirst(htmlspecialchars($user['status'])); ?>
                                </span>
                            </td>
                            <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                            <td>
                                <?php
                                $stmt = $conn->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ?");
                                $stmt->execute([$user['user_id']]);
                                echo $stmt->fetchColumn();
                                ?>
                            </td>
                            <td class="actions">
                                <button class="btn-icon" onclick="viewUserDetails(<?php echo $user['user_id']; ?>)">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="btn-icon" onclick="toggleUserStatus(<?php echo $user['user_id']; ?>, '<?php echo htmlspecialchars($user['status']); ?>')">
                                    <?php if ($user['status'] === 'active'): ?>
                                        <i class="fas fa-ban" title="Suspend User"></i>
                                    <?php else: ?>
                                        <i class="fas fa-check-circle" title="Activate User"></i>
                                    <?php endif; ?>
                                </button>
                                <button class="btn-icon" onclick="resetPassword(<?php echo $user['user_id']; ?>)">
                                    <i class="fas fa-key" title="Reset Password"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- User Details Modal -->
            <div id="userModal" class="modal">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2>User Details</h2>
                        <span class="close" onclick="closeModal()">&times;</span>
                    </div>
                    <div class="modal-body" id="userDetails">
                        <!-- User details will be loaded here -->
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        const modal = document.getElementById('userModal');
        
        function viewUserDetails(userId) {
            fetch('api/get_user_details.php?user_id=' + userId)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('userDetails').innerHTML = `
                            <div class="user-info">
                                <h3>${data.user.username}</h3>
                                <p><strong>Email:</strong> ${data.user.email}</p>
                                <p><strong>Joined:</strong> ${data.user.created_at}</p>
                                <p><strong>Status:</strong> ${data.user.status}</p>
                                <h4>Recent Orders</h4>
                                ${data.orders.length > 0 ? renderOrders(data.orders) : '<p>No orders found</p>'}
                            </div>
                        `;
                        modal.style.display = 'block';
                    } else {
                        alert(data.message);
                    }
                });
        }

        function toggleUserStatus(userId, currentStatus) {
            const newStatus = currentStatus === 'active' ? 'suspended' : 'active';
            const action = currentStatus === 'active' ? 'suspend' : 'activate';
            
            if (confirm(`Are you sure you want to ${action} this user?`)) {
                fetch('api/update_user_status.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `user_id=${userId}&status=${newStatus}`
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

        function resetPassword(userId) {
            if (confirm('Are you sure you want to reset this user\'s password?')) {
                fetch('api/reset_user_password.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `user_id=${userId}`
                })
                .then(response => response.json())
                .then(data => {
                    alert(data.message);
                });
            }
        }

        function renderOrders(orders) {
            return `
                <table class="mini-table">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${orders.map(order => `
                            <tr>
                                <td>#${order.order_id}</td>
                                <td>$${order.total_price}</td>
                                <td>${order.status}</td>
                                <td>${order.created_at}</td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            `;
        }

        function closeModal() {
            modal.style.display = 'none';
        }
    </script>
</body>
</html>