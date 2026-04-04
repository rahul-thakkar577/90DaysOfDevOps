function addToCart(bookId) {
    fetch('add-to-cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            book_id: bookId,
            quantity: 1
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateCartCount();
            showNotification('Book added to cart successfully!', 'success');
        } else {
            showNotification(data.message || 'Failed to add book to cart', 'error');
        }
    })
    .catch(error => {
        showNotification('An error occurred', 'error');
    });
}

function updateCartCount() {
    fetch('get-cart-count.php')
        .then(response => response.json())
        .then(data => {
            document.getElementById('cartCount').textContent = data.count;
        });
}

function showNotification(message, type) {
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 3000);
}

// Update cart count on page load
document.addEventListener('DOMContentLoaded', updateCartCount); 