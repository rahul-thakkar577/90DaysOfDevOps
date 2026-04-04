<?php
session_start();

// Clear all session variables
$_SESSION = array();

// Destroy the session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time()-3600, '/');
}

// Destroy the session
session_destroy();
?>

<!DOCTYPE html>
<html lang="en" data-theme="<?php echo isset($_COOKIE['theme']) ? $_COOKIE['theme'] : 'light'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logged Out - Library Management System</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .logout-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            padding: 20px;
            font-family: 'Poppins', sans-serif;
        }

        .logout-card {
            background: var(--card-bg, #fff);
            width: 100%;
            max-width: 400px;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            animation: slideUp 0.5s ease-out;
        }

        .logout-icon {
            background: var(--primary-color);
            padding: 30px;
            text-align: center;
        }

        .logout-icon i {
            font-size: 50px;
            color: #fff;
            animation: scaleUp 0.5s ease-out;
        }

        .logout-content {
            padding: 30px;
            text-align: center;
        }

        .logout-content h2 {
            color: var(--text-primary);
            font-size: 24px;
            margin-bottom: 15px;
            font-weight: 600;
        }

        .logout-content p {
            color: var(--text-secondary);
            font-size: 15px;
            margin-bottom: 25px;
            line-height: 1.6;
        }

        .back-to-login {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: var(--primary-color);
            color: #fff;
            padding: 12px 25px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .back-to-login:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes scaleUp {
            from {
                transform: scale(0.5);
                opacity: 0;
            }
            to {
                transform: scale(1);
                opacity: 1;
            }
        }

        /* Dark theme adjustments */
        [data-theme="dark"] .logout-card {
            background: var(--card-bg);
        }

        [data-theme="dark"] .logout-content h2 {
            color: #fff;
        }

        [data-theme="dark"] .logout-content p {
            color: #adb5bd;
        }

        /* Responsive */
        @media (max-width: 480px) {
            .logout-card {
                margin: 15px;
            }
            
            .logout-icon {
                padding: 25px;
            }
            
            .logout-content {
                padding: 25px 20px;
            }
            
            .logout-content h2 {
                font-size: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="logout-container">
        <div class="logout-card">
            <div class="logout-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="logout-content">
                <h2>Successfully Logged Out</h2>
                <p>Thank you for using our library system.<br>Have a great day!</p>
                <a href="../login.php" class="back-to-login">
                    <i class="fas fa-sign-in-alt"></i>
                    <span>Back to Login</span>
                </a>
            </div>
        </div>
    </div>

    <script src="../assets/js/theme.js"></script>
    <script>
        // Auto redirect after 3 seconds
        setTimeout(() => {
            window.location.href = '../login.php';
        }, 3000);
    </script>
</body>
</html>
