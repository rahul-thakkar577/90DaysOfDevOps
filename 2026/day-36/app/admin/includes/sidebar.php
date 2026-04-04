<nav class="admin-sidebar">
    <div class="admin-profile">
        <i class="fas fa-user-circle"></i>
        <h3><?php echo htmlspecialchars($_SESSION['admin_name']); ?></h3>
        <p>Administrator</p>
    </div>
    
    <ul class="admin-menu">
        <li class="<?php echo basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : ''; ?>">
            <a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
        </li>
        <li class="<?php echo basename($_SERVER['PHP_SELF']) === 'books.php' ? 'active' : ''; ?>">
            <a href="books.php"><i class="fas fa-book"></i> Manage Books</a>
        </li>
        <li class="<?php echo basename($_SERVER['PHP_SELF']) === 'users.php' ? 'active' : ''; ?>">
            <a href="users.php"><i class="fas fa-users"></i> Manage Users</a>
        </li>
        <li class="<?php echo basename($_SERVER['PHP_SELF']) === 'orders.php' ? 'active' : ''; ?>">
            <a href="orders.php"><i class="fas fa-shopping-cart"></i> Orders</a>
        </li>
        <li class="<?php echo basename($_SERVER['PHP_SELF']) === 'categories.php' ? 'active' : ''; ?>">
            <a href="categories.php"><i class="fas fa-tags"></i> Categories</a>
        </li>
        <li class="<?php echo basename($_SERVER['PHP_SELF']) === 'authors.php' ? 'active' : ''; ?>">
            <a href="authors.php"><i class="fas fa-pen"></i> Authors</a>
        </li>
        <li class="<?php echo basename($_SERVER['PHP_SELF']) === 'reports.php' ? 'active' : ''; ?>">
            <a href="reports.php"><i class="fas fa-chart-bar"></i> Reports</a>
        </li>
        <li class="<?php echo basename($_SERVER['PHP_SELF']) === 'settings.php' ? 'active' : ''; ?>">
            <a href="settings.php"><i class="fas fa-cog"></i> Settings</a>
        </li>
        <li>
            <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </li>
    </ul>
</nav> 