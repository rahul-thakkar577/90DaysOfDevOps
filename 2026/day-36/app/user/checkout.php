<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$db = new Database();
$conn = $db->getConnection();

// Fetch cart items and total
$stmt = $conn->prepare("
    SELECT c.*, b.title, b.price, b.available_copies
    FROM cart c
    JOIN books b ON c.book_id = b.book_id
    WHERE c.user_id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($cart_items)) {
    header('Location: cart.php');
    exit();
}

$subtotal = 0;
foreach ($cart_items as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}
$tax = $subtotal * 0.1;
$total = $subtotal + $tax;

// Handle checkout process
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $conn->beginTransaction();
        
        // Create orders for each cart item
        foreach ($cart_items as $item) {
            // Check if book is still available
            $stmt = $conn->prepare("SELECT available_copies FROM books WHERE book_id = ? FOR UPDATE");
            $stmt->execute([$item['book_id']]);
            $book = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($book['available_copies'] < $item['quantity']) {
                throw new Exception("Sorry, '{$item['title']}' is no longer available in the requested quantity.");
            }
            
            // Create order if not already created
            if (!isset($order_id)) {
                $stmt = $conn->prepare("
                    INSERT INTO orders (user_id, total_price, status)
                    VALUES (?, ?, 'pending')
                ");
                $stmt->execute([$_SESSION['user_id'], $total]);
                $order_id = $conn->lastInsertId();
            }
            
            // Add item to order_items
            $stmt = $conn->prepare("
                INSERT INTO order_items (order_id, book_id, quantity, price)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([$order_id, $item['book_id'], $item['quantity'], $item['price']]);
            
            // Update book inventory
            $stmt = $conn->prepare("
                UPDATE books 
                SET available_copies = available_copies - ?
                WHERE book_id = ?
            ");
            $stmt->execute([$item['quantity'], $item['book_id']]);
            
            // Remove items from cart after purchase
            $stmt = $conn->prepare("
                DELETE FROM cart 
                WHERE cart_id = ?
            ");
            $stmt->execute([$item['cart_id']]);
        }
        
        $conn->commit();
        header('Location: order-confirmation.php');
        exit();
        
    } catch (Exception $e) {
        $conn->rollBack();
        $error = $e->getMessage();
    }
}

$pageTitle = 'Checkout';
require_once 'includes/header.php';
?>

<div class="checkout-page">
    <?php if (isset($error)): ?>
        <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>

    <div class="checkout-container">
        <div class="order-details glass-effect">
            <h3>Order Details</h3>
            <?php foreach ($cart_items as $item): ?>
                <div class="order-item">
                    <div class="item-info">
                        <h4><?php echo htmlspecialchars($item['title']); ?></h4>
                        <p>Quantity: <?php echo $item['quantity']; ?></p>
                    </div>
                    <div class="item-price">
                        ₹<?php echo number_format($item['price'] * $item['quantity'], 2); ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="payment-section glass-effect">
            <h3>Payment Details</h3>
            <form id="payment-form" method="POST">
                <div class="form-group">
                    <label for="card-number">Card Number</label>
                    <input type="text" id="card-number" placeholder="1234 5678 9012 3456" required>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="expiry">Expiry Date</label>
                        <input type="text" id="expiry" placeholder="MM/YY" required>
                    </div>
                    <div class="form-group">
                        <label for="cvv">CVV</label>
                        <input type="text" id="cvv" placeholder="123" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="name">Cardholder Name</label>
                    <input type="text" id="name" placeholder="John Doe" required>
                </div>

                <div class="order-summary">
                    <div class="summary-item">
                        <span>Subtotal</span>
                        <span>₹<?php echo number_format($subtotal, 2); ?></span>
                    </div>
                    <div class="summary-item">
                        <span>Tax (10%)</span>
                        <span>₹<?php echo number_format($tax, 2); ?></span>
                    </div>
                    <div class="summary-total">
                        <span>Total</span>
                        <span>₹<?php echo number_format($total, 2); ?></span>
                    </div>
                </div>

                <button type="submit" class="btn-primary">Complete Purchase</button>
            </form>
        </div>
    </div>
</div>

<script>
    // Simple card number formatting
    document.getElementById('card-number').addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        value = value.replace(/(.{4})/g, '$1 ').trim();
        e.target.value = value;
    });

    // Expiry date formatting
    document.getElementById('expiry').addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        if (value.length >= 2) {
            value = value.slice(0,2) + '/' + value.slice(2,4);
        }
        e.target.value = value;
    });

    // CVV limit
    document.getElementById('cvv').addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        e.target.value = value.slice(0,3);
    });
</script>

<?php require_once 'includes/footer.php'; ?>