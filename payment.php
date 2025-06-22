<?php
session_start();

// Database connection
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'sdm_smartdine';

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize variables
$show_order = false;
$error = '';
$cart = [];
$total = 0;
$customer_name = '';
$table_number = '';

// Reset the form if requested
if (isset($_GET['reset'])) {
    unset($_SESSION['customer_name']);
    unset($_SESSION['table_number']);
    unset($_SESSION['cart']);
}

// Handle form submission for customer details
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['customer_name'])) {
    $customer_name = trim($_POST['customer_name']);
    $table_number = trim($_POST['table_number']);
    
    if (empty($customer_name) || empty($table_number)) {
        $error = "Please enter both customer name and table number";
    } else {
        // Generate the order table name
        $sanitized_name = strtolower(str_replace(' ', '_', $customer_name));
        $order_table_name = "order_{$sanitized_name}_table_{$table_number}";
        
        // Check if the order table exists
        $check_table = $conn->query("SHOW TABLES LIKE '$order_table_name'");
        if ($check_table->num_rows == 0) {
            $error = "No order found for this customer and table number";
        } else {
            // Fetch items from the order table
            $order_items = $conn->query("SELECT * FROM `$order_table_name`");
            if ($order_items->num_rows > 0) {
                $_SESSION['customer_name'] = $customer_name;
                $_SESSION['table_number'] = $table_number;
                $_SESSION['cart'] = [];
                
                while ($item = $order_items->fetch_assoc()) {
                    $_SESSION['cart'][] = [
                        'id' => $item['menu_id'] ?? 0,
                        'name' => $item['name'] ?? 'Unknown Item',
                        'price' => $item['price'] ?? 0
                    ];
                }
                
                // Calculate total
                foreach ($_SESSION['cart'] as $item) {
                    $total += $item['price']/2;                   
                }
                
                $show_order = true;
            } else {
                $error = "No items found in this order";
            }
        }
    }
}

// If we have session data, show the order
if (isset($_SESSION['customer_name']) && isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    $customer_name = $_SESSION['customer_name'];
    $table_number = $_SESSION['table_number'];
    $cart = $_SESSION['cart'];
    
    // Calculate total
    foreach ($cart as $item) {
        $total += $item['price']/2;
    }
    
    $show_order = true;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SDM_SmartDine - Payment</title>
    <link rel="stylesheet" href="style2.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container2 {
            max-width: 800px;
            margin: 30px auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .order-header {
            text-align: center;
            margin-bottom: 20px;
        }
        .customer-details {
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .customer-details h2 {
            margin-top: 0;
        }
        .customer-details input {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }
        .submit-details {
            background-color: #3498db;
            color: white;
            border: none;
            padding: 12px 20px;
            font-size: 16px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .submit-details:hover {
            background-color: #2980b9;
        }
        .order-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .order-info p {
            margin: 5px 0;
        }
        .error-message {
            color: #e74c3c;
            background: #fdecea;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
            border: 1px solid #f5c6cb;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f2f2f2;
        }
        .payment-methods {
            margin-top: 30px;
        }
        .payment-option {
            margin-bottom: 15px;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .payment-details {
            margin-top: 10px;
            padding-left: 20px;
        }
        .payment-details input, 
        .payment-details select {
            width: 100%;
            padding: 8px;
            margin: 5px 0;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .card-info {
            display: flex;
            gap: 10px;
        }
        .card-info input {
            flex: 1;
        }
        .pay-button {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 12px 20px;
            font-size: 16px;
            border-radius: 5px;
            cursor: pointer;
            width: 100%;
            margin-top: 20px;
        }
        .pay-button:hover {
            background-color: #45a049;
        }
        .reset-button {
            background-color: #f39c12;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 20px;
        }
        .reset-button:hover {
            background-color: #e67e22;
        }
    </style>
</head>
<body>
    <!-- Header with Hero Image -->
     <header>
        <div class="container">
            <marquee class="logo" behavior="" direction="">Safe Foods SmartDine</marquee>
            <p class="tagline2">@Providing Safe Foods is our Goal!</p>
            <p class="tagline">To Makeing Helthy Genaration!</p>
        </div>
    </header>
    
    <!-- Navigation -->
    <nav>
        <ul>
            <li><a href="about.html">About</a></li>
            <li><button id="menuButton">Menu</button></li>
            <li><a href="galary.html">Gallery</a></li>
            <li><button id="contactButton">Contact</button></li>
            <li><button id="ordertButton">Resurvation</button></li>
            <li><a href="index.html">Home</a></li>
        </ul>
    </nav>
    <!--menu ber-->
    <div class="menu_div" id="menu-div">
        <ul>
            <li><a href="breakfast.php">Breakfast Menu</a></li>
            <li><a href="lunch.php">Lunch Menu</a></li>
            <li><a href="dinner.php">Dinner Menu</a></li>
            <li><a href="fruits.php">Fruits Menu</a></li>
            <li><a href="cold_drinks.php">Cold Drinks</a></li>
            <li><a href="order_now.php">Available Menu</a></li>
            <li><a href="special.php">Special Menu</a></li>
        </ul>
    </div>
    <!--contact ber-->
    <div class="contact-div" id="contact-div">
        <ul>
            <li><a href="chat.php">Instant Chat</a></li>
            <li><a href="comment.php">Review Comment</a></li>
            <li><a href="contact.html">Another Contact</a></li>
        </ul>
    </div>
    <!--resurve ber-->
    <div class="order-div" id="order-div">
        <ul>
            <li><a href="order_now.php">Order Now</a></li>
            <li><a href="orderForFeuture.php">Feutur's Order</a></li>
            <li><a href="">Home Delivary</a></li>
        </ul>
    </div>
    <div class="container2">
        <div class="order-header">
            <h1>SDM_SmartDine</h1>
            <h2>Order Payment</h2>
        </div>
        
        <?php if (!empty($error)): ?>
            <div class="error-message">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <?php if (!$show_order): ?>
        <!-- Customer Details Form - Always shown first -->
        <div class="customer-details">
            <h2>Enter Your Details</h2>
            <form method="post">
                <input type="text" name="customer_name" placeholder="Your Name" required 
                       value="<?php echo htmlspecialchars($customer_name); ?>">
                <input type="text" name="table_number" placeholder="Table Number" required
                       value="<?php echo htmlspecialchars($table_number); ?>">
                <button type="submit" class="submit-details">Find My Order</button>
            </form>
        </div>
        <?php else: ?>
        <!-- Order and Payment Information - Only shown after valid submission -->
        <div class="order-info">
            <p><strong>Customer Name:</strong> <?php echo htmlspecialchars($customer_name); ?></p>
            <p><strong>Table Number:</strong> <?php echo htmlspecialchars($table_number); ?></p>
            <a href="?reset=1" class="reset-button">Search Another Order</a>
        </div>
        
        <div class="order-summary">
            <h2>Order Summary</h2>
            <table>
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Price</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cart as $item): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['name']); ?></td>
                        <td>৳<?php echo number_format($item['price'], 2); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th>Total</th>
                        <th>৳<?php echo number_format($total, 2); ?></th>
                    </tr>
                </tfoot>
            </table>
        </div>
       

        <div class="payment-methods">
            <h2>Select Payment Method</h2>
            
            <form action="process-payment.php" method="post" id="payment-form">
                <input type="hidden" name="amount" value="<?php echo $total; ?>">
                <input type="hidden" name="customer_name" value="<?php echo htmlspecialchars($customer_name); ?>">
                <input type="hidden" name="table_number" value="<?php echo htmlspecialchars($table_number); ?>">
                
                <!-- Mobile Banking Option -->
                <div class="payment-option">
                    <input type="radio" name="payment_method" id="mobile-banking" value="mobile_banking" checked>
                    <label for="mobile-banking">Mobile Banking</label>
                    
                    <div class="payment-details" id="mobile-banking-details">
                        <select name="mobile_bank" required>
                            <option value="">Select Mobile Bank</option>
                            <option value="bkash">bKash</option>
                            <option value="nagad">Nagad</option>
                            <option value="rocket">Rocket</option>
                        </select>
                        <input type="text" name="mobile_number" placeholder="Mobile Number" required>
                        <input type="text" name="transaction_id" placeholder="Transaction ID" required>
                        <small>Please make the payment first and then enter the transaction ID</small>
                    </div>
                </div>
                
                <!-- Card Payment Option -->
                <div class="payment-option">
                    <input type="radio" name="payment_method" id="card" value="card">
                    <label for="card">Credit/Debit Card</label>
                    
                    <div class="payment-details" id="card-details">
                        <input type="text" name="card_number" placeholder="Card Number" disabled required>
                        <input type="text" name="card_name" placeholder="Cardholder Name" disabled required>
                        <div class="card-info">
                            <input type="text" name="expiry_date" placeholder="MM/YY" disabled required>
                            <input type="text" name="cvv" placeholder="CVV" disabled required>
                        </div>
                    </div>
                </div>
                
                <!-- Cash Payment Option -->
                <div class="payment-option">
                    <input type="radio" name="payment_method" id="cash" value="cash">
                    <label for="cash">Cash Payment</label>
                    
                    <div class="payment-details" id="cash-details">
                        <p>You will pay with cash when your order is served.</p>
                    </div>
                </div>
                
                <button type="submit" class="pay-button">Complete Payment</button>
            </form>
        </div>
        <?php endif; ?>
    </div>
 <!-- Footer -->
    <footer>
        <div class="container">
            <p>&copy; 2023 SDM SmartDine. All rights reserved.</p>
            <p>Designed with <i class="fas fa-heart" style="color: var(--primary);"></i> for food lovers</p>
        </div>
    </footer>

    <!--script code part-->
<!--menu div-->
<script>
    var p = document.getElementById("menuButton")
    var q = document.getElementById("menu-div")
    p.addEventListener('click', () => {
          // Toggle visibility of the button list
          if (q.style.display === 'none' || q.style.display === '') {
            q.style.display = 'block';
          } else {
            q.style.display = 'none';
          }
        });
</script>
<!--contact div-->
<script>
    var x = document.getElementById("contactButton")
    var y = document.getElementById("contact-div")
    x.addEventListener('click', () => {
          // Toggle visibility of the button list
          if (y.style.display === 'none' || y.style.display === '') {
            y.style.display = 'block';
          } else {
            y.style.display = 'none';
          }
        });
</script>
<!--Resurvation div-->
<script>
    var a = document.getElementById("ordertButton")
    var b = document.getElementById("order-div")
    a.addEventListener('click', () => {
          // Toggle visibility of the button list
          if (b.style.display === 'none' || b.style.display === '') {
            b.style.display = 'block';
          } else {
            b.style.display = 'none';
          }
        });
</script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const paymentMethods = document.querySelectorAll('input[name="payment_method"]');
            
            paymentMethods.forEach(method => {
                method.addEventListener('change', function() {
                    // Disable all payment details first
                    document.querySelectorAll('.payment-details input').forEach(input => {
                        input.disabled = true;
                        input.required = false;
                    });
                    
                    // Enable the selected payment method's inputs
                    const detailsId = this.id + '-details';
                    const detailsDiv = document.getElementById(detailsId);
                    if (detailsDiv) {
                        detailsDiv.querySelectorAll('input').forEach(input => {
                            input.disabled = false;
                            input.required = true;
                        });
                    }
                });
            });
            
            // Trigger change event for the initially checked method if exists
            const checkedMethod = document.querySelector('input[name="payment_method"]:checked');
            if (checkedMethod) {
                checkedMethod.dispatchEvent(new Event('change'));
            }
        });
    </script>
</body>
</html>
<?php
$conn->close();
?>