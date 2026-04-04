<?php
session_start();
$isLoggedIn = isset($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Library Management System</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Styles -->
    <link rel="stylesheet" href="assets/css/reset.css">
    <link rel="stylesheet" href="assets/css/main.css">
    <style>
        /* Critical CSS for immediate rendering */
        .navbar {
            opacity: 0;
            animation: fadeIn 0.3s ease forwards;
        }
        @keyframes fadeIn {
            to { opacity: 1; }
        }
        .nav-links a {
            opacity: 0;
            transform: translateY(-10px);
            animation: slideIn 0.3s ease forwards;
            animation-delay: calc(var(--index) * 0.1s);
        }
        @keyframes slideIn {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body class="light-theme">
    <nav class="navbar fixed-top" role="navigation" aria-label="Main navigation">
        <div class="container nav-content">
            <a href="index.php" class="logo" aria-label="Home">
                <i class="fas fa-book-reader"></i>
                <span>Library System</span>
            </a>
            
            <div class="nav-links" role="menubar">
                <?php if ($isLoggedIn): ?>
                    <a href="dashboard.php" role="menuitem" style="--index: 1">
                        <i class="fas fa-home" aria-hidden="true"></i>
                        <span>Dashboard</span>
                    </a>
                    <a href="books.php" role="menuitem" style="--index: 2">
                        <i class="fas fa-book" aria-hidden="true"></i>
                        <span>Books</span>
                    </a>
                    <a href="cart.php" class="cart-link" role="menuitem" style="--index: 3">
                        <i class="fas fa-shopping-cart" aria-hidden="true"></i>
                        <span>Cart</span>
                        <span class="badge" id="cart-count" aria-label="Items in cart">0</span>
                    </a>
                    <a href="orders.php" role="menuitem" style="--index: 4">
                        <i class="fas fa-shopping-bag" aria-hidden="true"></i>
                        <span>Orders</span>
                    </a>
                    <div class="user-menu dropdown" style="--index: 5">
                        <button class="dropdown-toggle" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-user" aria-hidden="true"></i>
                            <span class="user-name">Profile</span>
                        </button>
                        <div class="dropdown-menu" role="menu">
                            <a href="profile.php" role="menuitem">
                                <i class="fas fa-user-circle" aria-hidden="true"></i>
                                Profile
                            </a>
                            <a href="settings.php" role="menuitem">
                                <i class="fas fa-cog" aria-hidden="true"></i>
                                Settings
                            </a>
                            <button id="theme-switch" class="theme-switch" role="menuitem">
                                <i class="fas fa-moon" aria-hidden="true"></i>
                                Theme
                            </button>
                            <a href="logout.php" role="menuitem">
                                <i class="fas fa-sign-out-alt" aria-hidden="true"></i>
                                Logout
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="login.php" role="menuitem" style="--index: 1">
                        <i class="fas fa-sign-in-alt" aria-hidden="true"></i>
                        <span>Login</span>
                    </a>
                    <a href="register.php" role="menuitem" style="--index: 2">
                        <i class="fas fa-user-plus" aria-hidden="true"></i>
                        <span>Register</span>
                    </a>
                    <a href="admin/login.php" role="menuitem" style="--index: 3">
                        <i class="fas fa-user-shield" aria-hidden="true"></i>
                        <span>Admin</span>
                    </a>
                <?php endif; ?>
            </div>

            <?php if ($isLoggedIn): ?>
            <button class="mobile-menu-toggle" aria-label="Toggle navigation menu" aria-expanded="false">
                <i class="fas fa-bars" aria-hidden="true"></i>
            </button>
            <?php endif; ?>
        </div>
    </nav>

    <div class="toast-container" role="alert" aria-live="polite"></div>

    <main>
        <section class="hero" role="banner">
            <div class="container">
                <h1>Welcome to Our Library</h1>
                <p>Discover thousands of books and manage your reading journey</p>
                <?php if (!$isLoggedIn): ?>
                <a href="register.php" class="btn btn-primary">
                    <i class="fas fa-user-plus" aria-hidden="true"></i>
                    <span>Get Started</span>
                </a>
                <?php endif; ?>
            </div>
        </section>

        <section class="features">
            <div class="container">
                <div class="grid">
                    <div class="card" style="--index: 1">
                        <i class="fas fa-search card-icon" aria-hidden="true"></i>
                        <h3>Browse Books</h3>
                        <p>Explore our vast collection of books across various genres</p>
                    </div>
                    <div class="card" style="--index: 2">
                        <i class="fas fa-tasks card-icon" aria-hidden="true"></i>
                        <h3>Easy Management</h3>
                        <p>Keep track of your borrowed books and reading history</p>
                    </div>
                    <div class="card" style="--index: 3">
                        <i class="fas fa-globe card-icon" aria-hidden="true"></i>
                        <h3>Online Access</h3>
                        <p>Access your account and manage books from anywhere</p>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <script src="assets/js/theme.js"></script>
    <script src="assets/js/main.js"></script>
    <script>
        // Initialize animations
        document.addEventListener('DOMContentLoaded', function() {
            // Add animation classes to cards
            document.querySelectorAll('.card').forEach(card => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                
                const observer = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            card.style.animation = `slideIn 0.5s ease forwards ${card.style.getPropertyValue('--index') * 0.1}s`;
                            observer.unobserve(card);
                        }
                    });
                });
                
                observer.observe(card);
            });
        });
    </script>
</body>
</html>
