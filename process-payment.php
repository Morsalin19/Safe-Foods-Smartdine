<?php
session_start();

// Validate the payment
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

// Basic validation
$errors = [];
$amount = floatval($_POST['amount'] ?? 0);
$paymentMethod = $_POST['payment_method'] ?? '';

if ($amount <= 0) {
    $errors[] = "Invalid payment amount";
}

if (!in_array($paymentMethod, ['mobile_banking', 'card', 'cash'])) {
    $errors[] = "Invalid payment method";
}

// Payment method specific validation
switch ($paymentMethod) {
    case 'mobile_banking':
        $mobileBank = $_POST['mobile_bank'] ?? '';
        $mobileNumber = $_POST['mobile_number'] ?? '';
        $transactionId = $_POST['transaction_id'] ?? '';
        
        if (!in_array($mobileBank, ['bkash', 'nagad', 'rocket'])) {
            $errors[] = "Please select a valid mobile banking service";
        }
        
        if (empty($mobileNumber) || !preg_match('/^01[3-9]\d{8}$/', $mobileNumber)) {
            $errors[] = "Please enter a valid Bangladeshi mobile number";
        }
        
        if (empty($transactionId)) {
            $errors[] = "Transaction ID is required";
        }
        break;
        
    case 'card':
        $cardNumber = $_POST['card_number'] ?? '';
        $cardName = $_POST['card_name'] ?? '';
        $expiryDate = $_POST['expiry_date'] ?? '';
        $cvv = $_POST['cvv'] ?? '';
        
        if (empty($cardNumber) || !preg_match('/^\d{16}$/', str_replace(' ', '', $cardNumber))) {
            $errors[] = "Please enter a valid 16-digit card number";
        }
        
        if (empty($cardName)) {
            $errors[] = "Cardholder name is required";
        }
        
        if (empty($expiryDate) || !preg_match('/^(0[1-9]|1[0-2])\/?([0-9]{2})$/', $expiryDate)) {
            $errors[] = "Please enter a valid expiry date (MM/YY)";
        }
        
        if (empty($cvv) || !preg_match('/^\d{3,4}$/', $cvv)) {
            $errors[] = "Please enter a valid CVV (3 or 4 digits)";
        }
        break;
}

// If there are errors, redirect back with errors
if (!empty($errors)) {
    $_SESSION['payment_errors'] = $errors;
    header('Location: index.php');
    exit;
}

// Process payment (in a real app, you would integrate with payment gateways here)
$paymentDetails = [
    'amount' => $amount,
    'method' => $paymentMethod,
    'date' => date('Y-m-d H:i:s'),
    'status' => 'completed' // In real app, this would depend on gateway response
];

// For mobile banking
if ($paymentMethod === 'mobile_banking') {
    $paymentDetails['mobile_bank'] = $_POST['mobile_bank'];
    $paymentDetails['mobile_number'] = $_POST['mobile_number'];
    $paymentDetails['transaction_id'] = $_POST['transaction_id'];
}

// For card payments
if ($paymentMethod === 'card') {
    $paymentDetails['card_last4'] = substr(str_replace(' ', '', $_POST['card_number']), -4);
}

// Save payment details to session (in real app, save to database)
$_SESSION['payment_details'] = $paymentDetails;

// Clear cart after successful payment
unset($_SESSION['cart']);

// Redirect to success page
header('Location: success.php');
exit;
?>