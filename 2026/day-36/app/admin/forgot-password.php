<?php
session_start();
require_once '../config/database.php';
require_once '../config/mail.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    
    $db = new Database();
    $conn = $db->getConnection();
    
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        // Generate reset token
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        $stmt = $conn->prepare("
            INSERT INTO password_resets (user_id, token, expires_at)
            VALUES (?, ?, ?)
        ");
        
        if ($stmt->execute([$user['user_id'], $token, $expires])) {
            // Send reset email
            $reset_link = "http://yourdomain.com/reset-password.php?token=" . $token;
            $mail = new Mail();
            $mail->sendPasswordReset($email, $reset_link);
            
            $success = 'Password reset instructions have been sent to your email';
        } else {
            $error = 'Failed to process password reset request';
        }
    } else {
        $error = 'No account found with that email address';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Library Management System</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/auth.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="light-theme">
    <div class="auth-container">
        <div class="auth-card glass-effect">
            <h2>Forgot Password</h2>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php else: ?>
                <form method="POST" class="auth-form">
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    
                    <button type="submit" class="btn-primary">Send Reset Link</button>
                </form>
            <?php endif; ?>
            
            <div class="auth-links">
                <a href="login.php">Back to Login</a>
            </div>
        </div>
    </div>
</body>
</html> 