<!-- Individual item price -->
<td>₹<?php echo number_format($item['price'], 2); ?></td>

<!-- Subtotal -->
<td>₹<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>

<!-- Cart total -->
<div class="cart-total">
    <strong>Total:</strong> ₹<?php echo number_format($total, 2); ?>
</div> 