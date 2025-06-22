<?php
session_start();

// Check if payment was successful
if (!isset($_SESSION['payment_details'])) {
    header('Location: index.php');
    exit;
}

$payment = $_SESSION['payment_details'];
unset($_SESSION['payment_details']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Successful</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <div class="success-message">
            <h1>Payment Successful!</h1>
            <div class="success-icon">✓</div>
            
            <div class="payment-receipt">
                <h2>Payment Details</h2>
                <p><strong>Amount:</strong> ৳<?php echo number_format($payment['amount'], 2); ?></p>
                <p><strong>Payment Method:</strong> 
                    <?php 
                    switch ($payment['method']) {
                        case 'mobile_banking':
                            echo ucfirst($payment['mobile_bank']) . ' (' . $payment['mobile_number'] . ')';
                            break;
                        case 'card':
                            echo 'Card ending with ' . $payment['card_last4'];
                            break;
                        case 'cash':
                            echo 'Cash on Delivery';
                            break;
                    }
                    ?>
                </p>
                <p><strong>Transaction Date:</strong> <?php echo $payment['date']; ?></p>
                
                <?php if ($payment['method'] === 'mobile_banking'): ?>
                <p><strong>Transaction ID:</strong> <?php echo $payment['transaction_id']; ?></p>
                <?php endif; ?>
            </div>
            
            <p>Your order has been placed successfully. Thank you for your payment!</p>
            <a href="businessINFO.html" class="back-button">Return to Menu</a>
        </div>
    </div>
</body>
</html>