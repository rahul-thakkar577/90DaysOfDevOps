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

// Fetch current admin info
$admin = $conn->query("
    SELECT * FROM admins 
    WHERE admin_id = " . $_SESSION['admin_id']
)->fetch(PDO::FETCH_ASSOC);

// Fetch system settings if you have any
$settings = [
    'site_name' => 'Library Management System',
    'contact_email' => 'admin@library.com',
    'items_per_page' => 10,
    'maintenance_mode' => false
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="admin-dashboard">
        <?php include 'includes/sidebar.php'; ?>

        <main class="admin-main">
            <header class="admin-header">
                <h1>Settings</h1>
            </header>

            <div class="settings-container">
                <!-- Profile Settings -->
                <div class="settings-section">
                    <h2><i class="fas fa-user-circle"></i> Profile Settings</h2>
                    <form id="profileForm" class="settings-form" onsubmit="updateProfile(event)">
                        <div class="form-group">
                            <label for="name">Name</label>
                            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($admin['name']); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($admin['email']); ?>" required>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn-primary">Save Profile</button>
                        </div>
                    </form>
                </div>

                <!-- Password Change -->
                <div class="settings-section">
                    <h2><i class="fas fa-lock"></i> Change Password</h2>
                    <form id="passwordForm" class="settings-form" onsubmit="updatePassword(event)">
                        <div class="form-group">
                            <label for="currentPassword">Current Password</label>
                            <input type="password" id="currentPassword" name="current_password" required>
                        </div>

                        <div class="form-group">
                            <label for="newPassword">New Password</label>
                            <input type="password" id="newPassword" name="new_password" required>
                        </div>

                        <div class="form-group">
                            <label for="confirmPassword">Confirm New Password</label>
                            <input type="password" id="confirmPassword" name="confirm_password" required>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn-primary">Change Password</button>
                        </div>
                    </form>
                </div>

                <!-- System Settings -->
                <div class="settings-section">
                    <h2><i class="fas fa-cogs"></i> System Settings</h2>
                    <form id="systemForm" class="settings-form" onsubmit="updateSystemSettings(event)">
                        <div class="form-group">
                            <label for="siteName">Site Name</label>
                            <input type="text" id="siteName" name="site_name" value="<?php echo htmlspecialchars($settings['site_name']); ?>">
                        </div>

                        <div class="form-group">
                            <label for="contactEmail">Contact Email</label>
                            <input type="email" id="contactEmail" name="contact_email" value="<?php echo htmlspecialchars($settings['contact_email']); ?>">
                        </div>

                        <div class="form-group">
                            <label for="itemsPerPage">Items Per Page</label>
                            <input type="number" id="itemsPerPage" name="items_per_page" value="<?php echo $settings['items_per_page']; ?>" min="5" max="50">
                        </div>

                        <div class="form-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="maintenance_mode" <?php echo $settings['maintenance_mode'] ? 'checked' : ''; ?>>
                                Enable Maintenance Mode
                            </label>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn-primary">Save Settings</button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <script>
        function updateProfile(event) {
            event.preventDefault();
            const formData = new FormData(event.target);
            
            fetch('api/update_profile.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Profile updated successfully!');
                } else {
                    alert(data.message || 'Failed to update profile');
                }
            });
        }

        function updatePassword(event) {
            event.preventDefault();
            const formData = new FormData(event.target);
            
            if (formData.get('new_password') !== formData.get('confirm_password')) {
                alert('New passwords do not match!');
                return;
            }
            
            fetch('api/update_password.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Password updated successfully!');
                    event.target.reset();
                } else {
                    alert(data.message || 'Failed to update password');
                }
            });
        }

        function updateSystemSettings(event) {
            event.preventDefault();
            const formData = new FormData(event.target);
            
            fetch('api/update_settings.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Settings updated successfully!');
                } else {
                    alert(data.message || 'Failed to update settings');
                }
            });
        }
    </script>
</body>
</html> 