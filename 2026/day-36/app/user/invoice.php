<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['order_id'])) {
    header('Location: orders.php');
    exit();
}

$db = new Database();
$conn = $db->getConnection();

// Get order details
$stmt = $conn->prepare("
    SELECT o.*, u.username, u.email
    FROM orders o
    JOIN users u ON o.user_id = u.user_id
    WHERE o.order_id = ? AND o.user_id = ?
");
$stmt->execute([$_GET['order_id'], $_SESSION['user_id']]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    header('Location: orders.php');
    exit();
}

// Get order items
$stmt = $conn->prepare("
    SELECT oi.*, b.title, b.price as unit_price
    FROM order_items oi
    JOIN books b ON oi.book_id = b.book_id
    WHERE oi.order_id = ?
");
$stmt->execute([$order['order_id']]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate totals
$subtotal = 0;
foreach ($items as $item) {
    $subtotal += $item['quantity'] * $item['unit_price'];
}
$tax = $subtotal * 0.1; // 10% tax
$total = $subtotal + $tax;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #<?php echo $order['order_id']; ?></title>
    <style>
        @media print {
            body {
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }
            .no-print {
                display: none;
            }
        }
        
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        
        .invoice-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #ddd;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #333;
        }
        
        .company-info {
            margin-bottom: 30px;
        }
        
        .invoice-details {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        
        .customer-info {
            flex: 1;
        }
        
        .invoice-info {
            text-align: right;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        th {
            background-color: #f5f5f5;
        }
        
        .totals {
            text-align: right;
            margin-bottom: 30px;
        }
        
        .total-row {
            margin: 5px 0;
        }
        
        .grand-total {
            font-size: 1.2em;
            font-weight: bold;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 2px solid #333;
        }
        
        .footer {
            text-align: center;
            margin-top: 50px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
        }
        
        .print-button {
            background: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        
        .print-button:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>
    <div class="no-print" style="text-align: center; margin-bottom: 20px;">
        <button onclick="window.print()" class="print-button">Print Invoice</button>
    </div>
    
    <div class="invoice-container">
        <div class="header">
            <h1>INVOICE</h1>
        </div>
        
        <div class="company-info">
            <h2>Library Management System</h2>
            <p>123 Library Street<br>
            City, State, ZIP<br>
            Phone: (123) 456-7890<br>
            Email: contact@library.com</p>
        </div>
        
        <div class="invoice-details">
            <div class="customer-info">
                <h3>Bill To:</h3>
                <p><?php echo htmlspecialchars($order['username']); ?><br>
                <?php echo htmlspecialchars($order['email']); ?></p>
            </div>
            
            <div class="invoice-info">
                <h3>Invoice Details:</h3>
                <p>Invoice #: <?php echo $order['order_id']; ?><br>
                Date: <?php echo date('F j, Y', strtotime($order['created_at'])); ?><br>
                Status: <?php echo ucfirst($order['status']); ?></p>
            </div>
        </div>
        
        <table>
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Quantity</th>
                    <th>Unit Price</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['title']); ?></td>
                    <td><?php echo $item['quantity']; ?></td>
                    <td>₹<?php echo number_format($item['unit_price'], 2); ?></td>
                    <td>₹<?php echo number_format($item['quantity'] * $item['unit_price'], 2); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <div class="totals">
            <div class="total-row">
                <strong>Subtotal:</strong> 
                <span>₹<?php echo number_format($subtotal, 2); ?></span>
            </div>
            <div class="total-row">
                <strong>Tax (10%):</strong> 
                <span>₹<?php echo number_format($tax, 2); ?></span>
            </div>
            <div class="total-row grand-total">
                <strong>Total:</strong> 
                <span>₹<?php echo number_format($total, 2); ?></span>
            </div>
        </div>
        
        <div class="footer">
            <p>Thank you for your business!</p>
        </div>
    </div>
</body>
</html>