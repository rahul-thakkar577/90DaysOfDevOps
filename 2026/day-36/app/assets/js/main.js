document.addEventListener('DOMContentLoaded', function() {
    // Cart count functionality
    const cartCount = document.getElementById('cart-count');
    const cartButtons = document.querySelectorAll('.add-to-cart');
    
    function updateCartCount(count) {
        if (cartCount) {
            cartCount.textContent = count;
            cartCount.style.display = count > 0 ? 'flex' : 'none';
            
            // Animate the badge
            cartCount.animate([
                { transform: 'scale(1.2)', opacity: '0.8' },
                { transform: 'scale(1)', opacity: '1' }
            ], {
                duration: 300,
                easing: 'ease-out'
            });
        }
    }

    // Toast notification system
    const toastContainer = document.querySelector('.toast-container');
    const toastTypes = {
        success: { icon: 'fas fa-check-circle', color: 'var(--success-color)' },
        error: { icon: 'fas fa-times-circle', color: 'var(--danger-color)' },
        info: { icon: 'fas fa-info-circle', color: 'var(--info-color)' },
        warning: { icon: 'fas fa-exclamation-circle', color: 'var(--warning-color)' }
    };

    function showToast(message, type = 'info', duration = 3000) {
        if (!toastContainer) return;

        const toast = document.createElement('div');
        toast.className = 'toast';
        toast.style.backgroundColor = 'var(--light-color)';
        toast.style.color = 'var(--text-color)';
        toast.style.border = '1px solid var(--border-color)';
        toast.style.borderRadius = '0.75rem';
        toast.style.padding = '1rem 1.5rem';
        toast.style.marginBottom = '0.5rem';
        toast.style.display = 'flex';
        toast.style.alignItems = 'center';
        toast.style.gap = '1rem';
        toast.style.boxShadow = 'var(--card-shadow)';
        toast.style.animation = 'fadeIn 0.3s ease';
        toast.style.position = 'relative';
        toast.style.maxWidth = '400px';
        toast.style.width = '100%';
        toast.style.backdropFilter = 'blur(8px)';
        toast.style.webkitBackdropFilter = 'blur(8px)';

        const icon = document.createElement('i');
        icon.className = toastTypes[type].icon;
        icon.style.color = toastTypes[type].color;
        icon.style.fontSize = '1.25rem';

        const messageText = document.createElement('span');
        messageText.textContent = message;
        messageText.style.flex = '1';

        const closeButton = document.createElement('button');
        closeButton.innerHTML = '<i class="fas fa-times"></i>';
        closeButton.style.background = 'none';
        closeButton.style.border = 'none';
        closeButton.style.color = 'var(--text-muted)';
        closeButton.style.cursor = 'pointer';
        closeButton.style.padding = '0.25rem';
        closeButton.style.fontSize = '1rem';
        closeButton.style.opacity = '0.7';
        closeButton.style.transition = 'var(--transition)';

        closeButton.addEventListener('mouseover', () => {
            closeButton.style.opacity = '1';
            closeButton.style.transform = 'scale(1.1)';
        });

        closeButton.addEventListener('mouseout', () => {
            closeButton.style.opacity = '0.7';
            closeButton.style.transform = 'scale(1)';
        });

        closeButton.addEventListener('click', () => {
            toast.style.animation = 'fadeOut 0.3s ease forwards';
            setTimeout(() => toast.remove(), 300);
        });

        toast.appendChild(icon);
        toast.appendChild(messageText);
        toast.appendChild(closeButton);
        toastContainer.appendChild(toast);

        // Auto remove after duration
        setTimeout(() => {
            if (toast.parentElement) {
                toast.style.animation = 'fadeOut 0.3s ease forwards';
                setTimeout(() => toast.remove(), 300);
            }
        }, duration);
    }

    // Example usage of cart and toast (for testing)
    if (cartButtons) {
        cartButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const currentCount = parseInt(cartCount.textContent || '0');
                updateCartCount(currentCount + 1);
                showToast('Book added to cart successfully!', 'success');
            });
        });
    }

    // Add to window for external access
    window.showToast = showToast;
    window.updateCartCount = updateCartCount;
});
