    </div> <!-- Close content-container -->
</main> <!-- Close main-content -->
</div> <!-- .user-layout -->

<!-- Footer -->
<footer class="footer">
    <div class="footer-content">
        <div class="footer-section">
            <h3>About Us</h3>
            <p>Your trusted online library for purchasing and reading books. We provide a vast collection of digital and physical books to enhance your reading experience.</p>
            <div class="social-links">
                <a href="#" class="social-link"><i class="fab fa-facebook"></i></a>
                <a href="#" class="social-link"><i class="fab fa-twitter"></i></a>
                <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
            </div>
        </div>
        <div class="footer-section">
            <h3>Quick Links</h3>
            <ul class="footer-links">
                <li><a href="dashboard.php"><i class="fas fa-home"></i> Home</a></li>
                <li><a href="books.php"><i class="fas fa-book"></i> Books</a></li>
                <li><a href="cart.php"><i class="fas fa-shopping-cart"></i> Cart</a></li>
                <li><a href="orders.php"><i class="fas fa-shopping-bag"></i> Orders</a></li>
                <li><a href="profile.php"><i class="fas fa-user"></i> Profile</a></li>
            </ul>
        </div>
        <div class="footer-section">
            <h3>Contact Info</h3>
            <ul class="contact-info">
                <li><i class="fas fa-envelope"></i> info@library.com</li>
                <li><i class="fas fa-phone"></i> +1 (234) 567-8900</li>
                <li><i class="fas fa-map-marker-alt"></i> 123 Library Street, Book City</li>
            </ul>
        </div>
    </div>
    <div class="footer-bottom">
        <p>&copy; <?php echo date('Y'); ?> Online Library. All rights reserved.</p>
    </div>
</footer>

<div class="toast-container"></div>

<script>
    // Toast notifications
    function showToast(title, message, type = 'info') {
        const toastContainer = document.querySelector('.toast-container');
        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        toast.innerHTML = `
            <div class="toast-header">
                <strong>${title}</strong>
                <button type="button" class="toast-close">&times;</button>
            </div>
            <div class="toast-body">${message}</div>
        `;
        
        toastContainer.appendChild(toast);
        
        // Auto remove after 3 seconds
        setTimeout(() => {
            toast.classList.add('removing');
            setTimeout(() => {
                toast.remove();
            }, 300);
        }, 3000);
        
        // Close button
        toast.querySelector('.toast-close').addEventListener('click', () => {
            toast.classList.add('removing');
            setTimeout(() => {
                toast.remove();
            }, 300);
        });
    }

    // Show session messages if they exist
    <?php if (isset($_SESSION['success_message'])): ?>
        showToast('Success', '<?php echo htmlspecialchars($_SESSION['success_message']); ?>', 'success');
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_message'])): ?>
        showToast('Error', '<?php echo htmlspecialchars($_SESSION['error_message']); ?>', 'error');
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>
</script>
</body>
</html>