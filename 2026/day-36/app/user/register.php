<?php
session_start();
require_once __DIR__ . '/../classes/Auth.php';

// Add debug code
if (!class_exists('Auth')) {
    die('Auth class not found');    
}

$auth = new Auth();
if (!method_exists($auth, 'register')) {
    die('register method not found in Auth class');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = 'Please fill in all fields';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } else {
        $result = $auth->register($name, $email, $password);
        
        if (isset($result['error'])) {
            $error = $result['error'];
        } else {
            $success = $result['success'];
            header('refresh:2;url=login.php');
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Library Management System</title>
    <link rel="stylesheet" href="../assets/css/style.css">
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

    <div class="container">
        <div class="login-container glass-effect">
            <h2>Register</h2>
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            <form method="POST" class="login-form">
                <div class="form-group">
                    <input type="text" name="name" placeholder="Full Name" required>
                </div>
                <div class="form-group">
                    <input type="email" name="email" placeholder="Email" required>
                </div>
                <div class="form-group">
                    <input type="password" name="password" placeholder="Password" required>
                </div>
                <div class="form-group">
                    <input type="password" name="confirm_password" placeholder="Confirm Password" required>
                </div>
                <button type="submit" class="btn-primary">Register</button>
            </form>
            <div class="form-footer">
                <p>Already have an account? <a href="login.php">Login</a></p>
                <p><a href="../index.php">Back to Home</a></p>
            </div>
        </div>
    </div>

    <script src="../assets/js/theme.js"></script>
</body>
</html> 