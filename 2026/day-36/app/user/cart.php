<?php
$pageTitle = 'Shopping Cart';
require_once 'includes/header.php';
require_once '../config/database.php';

// Initialize database connection
try {
    $database = new Database();
    $pdo = $database->getConnection();
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json; charset=utf-8');
    $action = $_POST['action'] ?? '';
    $response = ['success' => false, 'message' => '', 'data' => null];

    try {
        if ($action === 'update') {
            if (empty($_POST['cart_id'])) {
                throw new Exception('Invalid cart item selected');
            }
            
            $cart_id = filter_var($_POST['cart_id'], FILTER_VALIDATE_INT);
            if (!$cart_id) {
                throw new Exception('Invalid cart item ID');
            }
            
            $quantity = filter_var($_POST['quantity'] ?? 1, FILTER_VALIDATE_INT);
            if (!$quantity || $quantity < 1 || $quantity > 10) {
                throw new Exception('Invalid quantity. Must be between 1 and 10');
            }
            
            // Verify cart item belongs to user
            $stmt = $pdo->prepare("SELECT c.cart_id, b.price, b.title FROM cart c JOIN books b ON b.book_id = c.book_id WHERE c.cart_id = ? AND c.user_id = ?");
            if (!$stmt->execute([$cart_id, $_SESSION['user_id']])) {
                throw new Exception('Failed to verify cart item');
            }
            $item = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$item) {
                throw new Exception('Cart item not found');
            }
            
            $pdo->beginTransaction();
            try {
                $stmt = $pdo->prepare("UPDATE cart SET quantity = ? WHERE cart_id = ? AND user_id = ?");
                if (!$stmt->execute([$quantity, $cart_id, $_SESSION['user_id']])) {
                    throw new Exception('Failed to update quantity');
                }
                
                // Get updated cart total
                $stmt = $pdo->prepare("
                    SELECT COALESCE(SUM(b.price * c.quantity), 0) as total,
                           COUNT(*) as count
                    FROM cart c 
                    JOIN books b ON b.book_id = c.book_id 
                    WHERE c.user_id = ?
                ");
                if (!$stmt->execute([$_SESSION['user_id']])) {
                    throw new Exception('Failed to get cart total');
                }
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                $total = $result['total'] ?? 0;
                $count = $result['count'] ?? 0;
                
                $shipping = $total > 0 ? 50 : 0;
                
                $pdo->commit();
                
                $response = [
                    'success' => true,
                    'message' => 'Cart updated successfully',
                    'data' => [
                        'subtotal' => number_format($total, 2),
                        'shipping' => number_format($shipping, 2),
                        'total' => number_format($total + $shipping, 2),
                        'item_total' => number_format($item['price'] * $quantity, 2),
                        'cart_count' => $count
                    ]
                ];
            } catch (Exception $e) {
                $pdo->rollBack();
                throw $e;
            }
        } elseif ($action === 'remove') {
            if (empty($_POST['cart_id'])) {
                throw new Exception('Invalid cart item selected');
            }
            
            $cart_id = filter_var($_POST['cart_id'], FILTER_VALIDATE_INT);
            if (!$cart_id) {
                throw new Exception('Invalid cart item ID');
            }
            
            // Verify cart item belongs to user
            $stmt = $pdo->prepare("SELECT cart_id FROM cart WHERE cart_id = ? AND user_id = ?");
            if (!$stmt->execute([$cart_id, $_SESSION['user_id']])) {
                throw new Exception('Failed to verify cart item');
            }
            if (!$stmt->fetch()) {
                throw new Exception('Cart item not found');
            }
            
            $pdo->beginTransaction();
            try {
                $stmt = $pdo->prepare("DELETE FROM cart WHERE cart_id = ? AND user_id = ?");
                if (!$stmt->execute([$cart_id, $_SESSION['user_id']])) {
                    throw new Exception('Failed to remove item');
                }
                
                // Get updated cart total and count
                $stmt = $pdo->prepare("
                    SELECT COALESCE(SUM(b.price * c.quantity), 0) as total,
                           COUNT(*) as count
                    FROM cart c 
                    JOIN books b ON b.book_id = c.book_id 
                    WHERE c.user_id = ?
                ");
                if (!$stmt->execute([$_SESSION['user_id']])) {
                    throw new Exception('Failed to get cart total');
                }
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                $total = $result['total'] ?? 0;
                $count = $result['count'] ?? 0;
                
                $shipping = $total > 0 ? 50 : 0;
                
                $pdo->commit();
                
                $response = [
                    'success' => true,
                    'message' => 'Item removed from cart',
                    'data' => [
                        'subtotal' => number_format($total, 2),
                        'shipping' => number_format($shipping, 2),
                        'total' => number_format($total + $shipping, 2),
                        'cart_count' => $count
                    ]
                ];
            } catch (Exception $e) {
                $pdo->rollBack();
                throw $e;
            }
        } else {
            throw new Exception('Invalid action');
        }
    } catch (Exception $e) {
        error_log("Cart Error: " . $e->getMessage());
        $response['message'] = $e->getMessage();
    }
    
    echo json_encode($response, JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE);
    exit();
}

// Get cart items
$stmt = $pdo->prepare("
    SELECT c.cart_id, c.quantity, b.*, 
           COALESCE((SELECT AVG(rating) FROM reviews r WHERE r.book_id = b.book_id), 0) as rating,
           (SELECT COUNT(*) FROM reviews r WHERE r.book_id = b.book_id) as review_count
    FROM cart c 
    JOIN books b ON b.book_id = c.book_id 
    WHERE c.user_id = ? 
    ORDER BY c.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate totals
$subtotal = 0;
foreach ($cart_items as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}

$shipping = $subtotal > 0 ? 50 : 0;
$total = $subtotal + $shipping;
?>

<div class="cart-page">
    <div class="cart-container">
        <div class="cart-items">
            <?php if (empty($cart_items)): ?>
            <div class="empty-state">
                <i class="fas fa-shopping-cart"></i>
                <h3>Your cart is empty</h3>
                <p>Browse our collection and add some books to your cart</p>
                <a href="books.php" class="btn btn-primary">Browse Books</a>
            </div>
            <?php else: ?>
                <?php foreach ($cart_items as $item): ?>
                <div class="cart-item" data-cart-id="<?= htmlspecialchars($item['cart_id']) ?>">
                    <div class="item-image">
                        <?php if (!empty($item['image_url'])): ?>
                            <img src="<?= htmlspecialchars('../' . $item['image_url']) ?>" 
                                 alt="<?= htmlspecialchars($item['title']) ?>"
                                 loading="lazy">
                        <?php else: ?>
                            <div class="no-image">
                                <i class="fas fa-book"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="item-details">
                        <h3 class="item-title"><?= htmlspecialchars($item['title']) ?></h3>
                        <div class="item-meta">
                            <?php if ($item['rating'] > 0): ?>
                            <div class="rating" title="<?= number_format($item['rating'], 1) ?> out of 5">
                                <?php
                                $rating = round($item['rating'] * 2) / 2;
                                for ($i = 1; $i <= 5; $i++) {
                                    if ($rating >= $i) {
                                        echo '<i class="fas fa-star"></i>';
                                    } elseif ($rating >= $i - 0.5) {
                                        echo '<i class="fas fa-star-half-alt"></i>';
                                    } else {
                                        echo '<i class="far fa-star"></i>';
                                    }
                                }
                                ?>
                                <span>(<?= $item['review_count'] ?>)</span>
                            </div>
                            <?php endif; ?>
                            <div class="item-price">₹<?= number_format($item['price'], 2) ?></div>
                        </div>
                        <div class="item-actions">
                            <div class="quantity-controls">
                                <button type="button" class="qty-btn minus" <?= $item['quantity'] <= 1 ? 'disabled' : '' ?>>
                                    <i class="fas fa-minus"></i>
                                </button>
                                <input type="number" class="quantity-input" value="<?= $item['quantity'] ?>" min="1" max="10" readonly>
                                <button type="button" class="qty-btn plus" <?= $item['quantity'] >= 10 ? 'disabled' : '' ?>>
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                            <button type="button" class="remove-btn">
                                <i class="fas fa-trash-alt"></i>
                                Remove
                            </button>
                        </div>
                    </div>
                    
                    <div class="item-total">
                        ₹<?= number_format($item['price'] * $item['quantity'], 2) ?>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <?php if (!empty($cart_items)): ?>
        <div class="cart-summary">
            <h3>Order Summary</h3>
            <div class="summary-row">
                <span>Subtotal</span>
                <span class="subtotal">₹<?= number_format($subtotal, 2) ?></span>
            </div>
            <div class="summary-row">
                <span>Shipping</span>
                <span class="shipping">₹<?= number_format($shipping, 2) ?></span>
            </div>
            <div class="summary-row total">
                <span>Total</span>
                <span class="total">₹<?= number_format($total, 2) ?></span>
            </div>
            <button type="button" class="checkout-btn">
                Proceed to Checkout
            </button>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const cartItems = document.querySelector('.cart-items');
    if (!cartItems) return;
    
    // Handle quantity changes
    cartItems.addEventListener('click', async function(e) {
        const target = e.target;
        const qtyBtn = target.closest('.qty-btn');
        const removeBtn = target.closest('.remove-btn');
        
        if (qtyBtn) {
            e.preventDefault();
            
            if (qtyBtn.disabled) return;
            
            const cartItem = qtyBtn.closest('.cart-item');
            const input = cartItem.querySelector('.quantity-input');
            const cartId = cartItem.dataset.cartId;
            let quantity = parseInt(input.value);
            
            if (qtyBtn.classList.contains('minus')) {
                quantity = Math.max(1, quantity - 1);
            } else {
                quantity = Math.min(10, quantity + 1);
            }
            
            try {
                const formData = new FormData();
                formData.append('action', 'update');
                formData.append('cart_id', cartId);
                formData.append('quantity', quantity);
                
                const response = await fetch('cart.php', {
                    method: 'POST',
                    body: formData
                });
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const result = await response.json();
                
                if (result.success) {
                    // Update quantity input and button states
                    input.value = quantity;
                    cartItem.querySelector('.minus').disabled = quantity <= 1;
                    cartItem.querySelector('.plus').disabled = quantity >= 10;
                    
                    // Update item total
                    cartItem.querySelector('.item-total').textContent = `₹${result.data.item_total}`;
                    
                    // Update cart summary
                    document.querySelector('.subtotal').textContent = `₹${result.data.subtotal}`;
                    document.querySelector('.shipping').textContent = `₹${result.data.shipping}`;
                    document.querySelector('.total').textContent = `₹${result.data.total}`;
                    
                    // Update cart count in header
                    const cartCount = document.getElementById('cart-count');
                    if (cartCount) {
                        cartCount.textContent = result.data.cart_count;
                        cartCount.style.display = result.data.cart_count > 0 ? 'flex' : 'none';
                    }
                    
                    createToast('success', result.message);
                } else {
                    throw new Error(result.message || 'Failed to update cart');
                }
            } catch (error) {
                console.error('Cart Error:', error);
                createToast('error', error.message);
                
                // Revert quantity
                input.value = quantity;
                cartItem.querySelector('.minus').disabled = quantity <= 1;
                cartItem.querySelector('.plus').disabled = quantity >= 10;
            }
        } else if (removeBtn) {
            e.preventDefault();
            
            if (!confirm('Are you sure you want to remove this item?')) {
                return;
            }
            
            const cartItem = removeBtn.closest('.cart-item');
            const cartId = cartItem.dataset.cartId;
            
            try {
                const formData = new FormData();
                formData.append('action', 'remove');
                formData.append('cart_id', cartId);
                
                const response = await fetch('cart.php', {
                    method: 'POST',
                    body: formData
                });
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const result = await response.json();
                
                if (result.success) {
                    // Remove item from DOM
                    cartItem.remove();
                    
                    // Update cart summary
                    document.querySelector('.subtotal').textContent = `₹${result.data.subtotal}`;
                    document.querySelector('.shipping').textContent = `₹${result.data.shipping}`;
                    document.querySelector('.total').textContent = `₹${result.data.total}`;
                    
                    // Update cart count in header
                    const cartCount = document.getElementById('cart-count');
                    if (cartCount) {
                        cartCount.textContent = result.data.cart_count;
                        cartCount.style.display = result.data.cart_count > 0 ? 'flex' : 'none';
                    }
                    
                    // Show empty state if no items left
                    if (result.data.cart_count === 0) {
                        location.reload();
                    }
                    
                    createToast('success', result.message);
                } else {
                    throw new Error(result.message || 'Failed to remove item');
                }
            } catch (error) {
                console.error('Cart Error:', error);
                createToast('error', error.message);
            }
        }
    });

    // Handle checkout button click
    const checkoutBtn = document.querySelector('.checkout-btn');
    if (checkoutBtn) {
        checkoutBtn.addEventListener('click', function() {
            window.location.href = 'checkout.php';
        });
    }
});
</script>

<style>
.cart-page {
    padding: 2rem;
}

.cart-container {
    display: grid;
    grid-template-columns: 1fr 300px;
    gap: 2rem;
    max-width: 1200px;
    margin: 0 auto;
}

@media (max-width: 768px) {
    .cart-container {
        grid-template-columns: 1fr;
    }
}

.cart-items {
    background: white;
    border-radius: 1rem;
    padding: 1.5rem;
    box-shadow: var(--shadow-sm);
}

.cart-item {
    display: grid;
    grid-template-columns: 120px 1fr auto;
    gap: 1.5rem;
    padding: 1.5rem;
    border-bottom: 1px solid var(--border-color);
}

.cart-item:last-child {
    border-bottom: none;
}

.item-image {
    aspect-ratio: 2/3;
    background: #f5f5f5;
    border-radius: 0.5rem;
    overflow: hidden;
}

.item-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.no-image {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--text-muted);
    font-size: 2rem;
}

.item-details {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.item-title {
    margin: 0;
    font-size: 1.125rem;
    font-weight: 600;
    color: var(--text-primary);
}

.item-meta {
    display: flex;
    align-items: center;
    gap: 1rem;
    color: var(--text-muted);
    font-size: 0.875rem;
}

.rating {
    color: #ffd700;
}

.rating span {
    color: var(--text-muted);
    margin-left: 0.25rem;
}

.item-price {
    font-weight: 600;
    color: var(--primary-color);
}

.item-actions {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-top: auto;
}

.quantity-controls {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.qty-btn {
    padding: 0.5rem;
    border: 1px solid var(--border-color);
    background: white;
    border-radius: 0.25rem;
    cursor: pointer;
    transition: all 0.2s;
}

.qty-btn:not(:disabled):hover {
    background: var(--primary-color);
    border-color: var(--primary-color);
    color: white;
}

.qty-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.quantity-input {
    width: 3rem;
    padding: 0.5rem;
    text-align: center;
    border: 1px solid var(--border-color);
    border-radius: 0.25rem;
    font-size: 0.875rem;
}

.remove-btn {
    padding: 0.5rem 1rem;
    border: none;
    background: none;
    color: var(--danger-color);
    cursor: pointer;
    font-size: 0.875rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    transition: opacity 0.2s;
}

.remove-btn:hover {
    opacity: 0.7;
}

.item-total {
    font-size: 1.125rem;
    font-weight: 600;
    color: var(--text-primary);
}

.cart-summary {
    background: white;
    border-radius: 1rem;
    padding: 1.5rem;
    box-shadow: var(--shadow-sm);
    height: fit-content;
    position: sticky;
    top: 2rem;
}

.cart-summary h3 {
    margin: 0 0 1.5rem;
    font-size: 1.25rem;
    color: var(--text-primary);
}

.summary-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 1rem;
    color: var(--text-muted);
    font-size: 0.875rem;
}

.summary-row.total {
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 1px solid var(--border-color);
    color: var(--text-primary);
    font-size: 1.125rem;
    font-weight: 600;
}

.checkout-btn {
    width: 100%;
    padding: 1rem;
    background: var(--primary-color);
    color: white;
    border: none;
    border-radius: 0.5rem;
    font-size: 1rem;
    font-weight: 500;
    cursor: pointer;
    transition: background-color 0.2s;
    margin-top: 1.5rem;
}

.checkout-btn:hover {
    background: var(--primary-dark);
}

.empty-state {
    text-align: center;
    padding: 3rem;
    color: var(--text-muted);
}

.empty-state i {
    font-size: 3rem;
    margin-bottom: 1rem;
}

.empty-state h3 {
    margin: 0 0 0.5rem;
    color: var(--text-primary);
}

.empty-state .btn {
    display: inline-block;
    padding: 0.75rem 1.5rem;
    background: var(--primary-color);
    color: white;
    text-decoration: none;
    border-radius: 0.5rem;
    font-weight: 500;
    margin-top: 1.5rem;
    transition: background-color 0.2s;
}

.empty-state .btn:hover {
    background: var(--primary-dark);
}

@media (max-width: 768px) {
    .cart-page {
        padding: 1rem;
    }
    
    .cart-item {
        grid-template-columns: 80px 1fr;
        gap: 1rem;
        padding: 1rem;
    }
    
    .item-total {
        grid-column: 1 / -1;
        text-align: right;
        margin-top: 1rem;
    }
    
    .item-actions {
        flex-direction: column;
        align-items: flex-start;
    }
}
</style>

<?php require_once 'includes/footer.php'; ?>