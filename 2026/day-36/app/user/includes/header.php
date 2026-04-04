<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../includes/init.php';

// Get cart count if user is logged in
$cart_count = 0;
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM cart WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $cart_count = $stmt->fetch()['count'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? $page_title . ' - ' : '' ?><?= SITE_NAME ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/user.css">
    <style>
        .navbar {
            backdrop-filter: blur(10px);
            background-color: rgba(255, 255, 255, 0.1);
        }
        .nav-links {
            backdrop-filter: blur(10px);
            background-color: rgba(255, 255, 255, 0.1);
        }
        .profile-menu {
            backdrop-filter: blur(10px);
            background-color: rgba(255, 255, 255, 0.1);
        }
    </style>
</head>
<body class="<?= isset($_COOKIE['theme']) && $_COOKIE['theme'] === 'dark' ? 'dark-theme' : '' ?>">
    <nav class="navbar">
        <div class="nav-brand">
            <a href="dashboard.php">
                <i class="fas fa-book-reader"></i>
                <span><?= SITE_NAME ?></span>
            </a>
        </div>
        
        <div class="nav-links">
            <a href="dashboard.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : '' ?>">
                <i class="fas fa-home"></i>
                <span>Dashboard</span>
            </a>
            
            <a href="books.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'books.php' ? 'active' : '' ?>">
                <i class="fas fa-book"></i>
                <span>Books</span>
            </a>
            
            <a href="cart.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'cart.php' ? 'active' : '' ?>">
                <i class="fas fa-shopping-cart"></i>
                <span>Cart</span>
                <?php if ($cart_count > 0): ?>
                <span class="badge" id="cartCount"><?= $cart_count ?></span>
                <?php endif; ?>
            </a>
            
            <a href="orders.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'orders.php' ? 'active' : '' ?>">
                <i class="fas fa-shopping-bag"></i>
                <span>Orders</span>
            </a>
        </div>

        <!-- User Menu -->
        <div class="nav-profile">
            <button class="profile-button" onclick="toggleProfileMenu()">
                <i class="fas fa-user-circle"></i>
            </button>
            <div class="profile-menu" id="profileMenu">
                <div class="profile-header">
                    <i class="fas fa-user-circle"></i>
                    <div class="profile-info">
                        <strong><?= htmlspecialchars($_SESSION['username'] ?? '') ?></strong>
                        <small><?= htmlspecialchars($_SESSION['email'] ?? '') ?></small>
                    </div>
                </div>
                <a href="profile.php" class="menu-item">
                    <i class="fas fa-user-cog"></i>
                    <span>Profile Settings</span>
                </a>
                <button type="button" class="menu-item" onclick="toggleTheme()">
                    <i class="fas fa-moon" id="themeIcon"></i>
                    <span id="themeText">Dark Mode</span>
                </button>
                <a href="logout.php" class="menu-item text-danger">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </div>
        </div>

        <!-- Mobile menu button -->
        <button class="mobile-menu-button" onclick="toggleMobileMenu()">
            <i class="fas fa-bars"></i>
        </button>
    </nav>

    <!-- Toast notifications -->
    <div class="toast-container" id="toastContainer"></div>

    <!-- Main content -->
    <main class="main-content">
        <?php if (isset($show_breadcrumbs) && $show_breadcrumbs): ?>
        <div class="breadcrumbs">
            <a href="dashboard.php">Home</a>
            <?php if (isset($breadcrumbs)): ?>
                <?php foreach ($breadcrumbs as $label => $url): ?>
                    <i class="fas fa-chevron-right"></i>
                    <?php if ($url): ?>
                        <a href="<?= $url ?>"><?= $label ?></a>
                    <?php else: ?>
                        <span><?= $label ?></span>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <?php endif; ?>

<script>
function toggleProfileMenu() {
    const menu = document.getElementById('profileMenu');
    menu.classList.toggle('show');
    
    // Close menu when clicking outside
    if (menu.classList.contains('show')) {
        document.addEventListener('click', closeProfileMenu);
    }
}

function closeProfileMenu(e) {
    const menu = document.getElementById('profileMenu');
    const button = document.querySelector('.profile-button');
    
    if (!menu.contains(e.target) && !button.contains(e.target)) {
        menu.classList.remove('show');
        document.removeEventListener('click', closeProfileMenu);
    }
}

function toggleMobileMenu() {
    const navLinks = document.querySelector('.nav-links');
    navLinks.classList.toggle('show');
    document.body.classList.toggle('menu-open');
}

function toggleTheme() {
    const body = document.body;
    const isDark = body.classList.toggle('dark-theme');
    const icon = document.getElementById('themeIcon');
    const text = document.getElementById('themeText');
    
    // Update icon and text
    if (isDark) {
        icon.classList.remove('fa-moon');
        icon.classList.add('fa-sun');
        text.textContent = 'Light Mode';
    } else {
        icon.classList.remove('fa-sun');
        icon.classList.add('fa-moon');
        text.textContent = 'Dark Mode';
    }
    
    // Save preference
    document.cookie = `theme=${isDark ? 'dark' : 'light'}; path=/; max-age=31536000`;
}

function updateCartCount(change = 0) {
    const cartCount = document.getElementById('cartCount');
    const cartLink = document.querySelector('a[href="cart.php"]');
    
    if (cartCount) {
        const newCount = parseInt(cartCount.textContent) + change;
        if (newCount <= 0) {
            cartCount.remove();
        } else {
            cartCount.textContent = newCount;
        }
    } else if (change > 0) {
        const badge = document.createElement('span');
        badge.className = 'badge';
        badge.id = 'cartCount';
        badge.textContent = change;
        cartLink.appendChild(badge);
    }
}

function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
        <span>${message}</span>
    `;
    
    const container = document.getElementById('toastContainer');
    container.appendChild(toast);
    
    setTimeout(() => {
        toast.classList.add('fade-out');
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}
</script>