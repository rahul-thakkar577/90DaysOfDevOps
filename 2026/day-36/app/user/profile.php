<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'user') {
    header('Location: ../login.php');
    exit();
}

$db = new Database();
$conn = $db->getConnection();

$success_message = '';
$error_message = '';

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    
    if (empty($name) || empty($email)) {
        $error_message = 'Name and email are required fields.';
    } else {
        try {
            // Check if email is already taken by another user
            $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ? AND user_id != ?");
            $stmt->execute([$email, $_SESSION['user_id']]);
            if ($stmt->fetch()) {
                $error_message = 'Email is already taken by another user.';
            } else {
                // Update profile
                $stmt = $conn->prepare("
                    UPDATE users 
                    SET name = ?, email = ?, phone = ?, address = ? 
                    WHERE user_id = ?
                ");
                $stmt->execute([$name, $email, $phone, $address, $_SESSION['user_id']]);
                
                // Update session data
                $_SESSION['user_name'] = $name;
                $_SESSION['user_email'] = $email;
                
                $success_message = 'Profile updated successfully!';
            }
        } catch (PDOException $e) {
            $error_message = 'An error occurred while updating your profile.';
        }
    }
}

// Fetch current user data
$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

require_once 'includes/header.php';
?>

<body class="user-layout">
    <!-- Theme Switch -->
    <div class="theme-switch">
        <input type="checkbox" id="theme-toggle" onchange="toggleTheme()">
        <label for="theme-toggle" class="switch">
            <span class="slider"></span>
        </label>
    </div>

    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-logo">
            <img src="../assets/images/logo.png" alt="Library Logo">
            <h1>My Library</h1>
        </div>
        
        <div class="user-info">
            <i class="fas fa-user-circle"></i>
            <h3><?php echo htmlspecialchars($_SESSION['user_name']); ?></h3>
        </div>

        <nav class="nav-menu">
            <a href="dashboard.php" class="nav-link">
                <i class="fas fa-home"></i>
                <span>Dashboard</span>
            </a>
            <a href="books.php" class="nav-link">
                <i class="fas fa-book"></i>
                <span>Browse Books</span>
            </a>
            <a href="cart.php" class="nav-link">
                <i class="fas fa-shopping-cart"></i>
                <span>Cart</span>
            </a>
            <a href="orders.php" class="nav-link">
                <i class="fas fa-shopping-bag"></i>
                <span>My Orders</span>
            </a>
            <a href="profile.php" class="nav-link active">
                <i class="fas fa-user"></i>
                <span>Profile</span>
            </a>
            <a href="logout.php" class="nav-link">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </nav>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <div class="content-header">
            <h2 class="page-title">My Profile</h2>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Profile Information</h3>
            </div>
            
            <?php if ($success_message): ?>
                <div class="alert alert-success">
                    <?php echo htmlspecialchars($success_message); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($error_message): ?>
                <div class="alert alert-error">
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" class="profile-form">
                <div class="form-group">
                    <label class="form-label" for="name">Full Name</label>
                    <input type="text" id="name" name="name" class="form-control" 
                           value="<?php echo htmlspecialchars($user['name']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="email">Email Address</label>
                    <input type="email" id="email" name="email" class="form-control" 
                           value="<?php echo htmlspecialchars($user['email']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="phone">Phone Number</label>
                    <input type="tel" id="phone" name="phone" class="form-control" 
                           value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" 
                           pattern="[0-9]{10}" title="Please enter a valid 10-digit phone number">
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="address">Delivery Address</label>
                    <textarea id="address" name="address" class="form-control" rows="3"
                              ><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i>
                        Save Changes
                    </button>
                </div>
            </form>
        </div>

        <div class="card mt-4">
            <div class="card-header">
                <h3 class="card-title">Change Password</h3>
            </div>
            
            <form method="POST" action="change-password.php" class="password-form" id="passwordForm">
                <div class="form-group">
                    <label class="form-label" for="current_password">Current Password</label>
                    <input type="password" id="current_password" name="current_password" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="new_password">New Password</label>
                    <input type="password" id="new_password" name="new_password" class="form-control" 
                           pattern="^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$" 
                           title="Password must be at least 8 characters long and include both letters and numbers" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="confirm_password">Confirm New Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-key"></i>
                        Change Password
                    </button>
                </div>
            </form>
        </div>

        <script>
        document.getElementById('passwordForm').addEventListener('submit', function(e) {
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (newPassword !== confirmPassword) {
                e.preventDefault();
                alert('New password and confirmation password do not match.');
            }
        });
        </script>
    </main>
</body>
</html>