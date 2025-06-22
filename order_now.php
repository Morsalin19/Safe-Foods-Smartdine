<?php
// Set timezone to ensure correct time detection
date_default_timezone_set("Asia/Dhaka");

// Database connection
$conn = new mysqli("localhost", "root", "", "sdm_smartdine");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the current hour
$hour = (int) date("H");

// Determine the correct menu based on time and set image directory
if ($hour >= 6 && $hour < 12) {
    $menu_table = "breakfast_menu1";
    $img_dir = "picture3/";
} elseif ($hour >= 12 && $hour < 19) {
    $menu_table = "lunch_menu";
    $img_dir = "picture5/";
} else {
    $menu_table = "dinner_menu";
    $img_dir = "picture4/";
}

// Handle "Order" button click
if (isset($_POST['order'])) {
    $id = (int) $_POST['menu_id'];
    $query = "SELECT * FROM $menu_table WHERE Item_id = $id";
    $result = $conn->query($query);
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $name = $conn->real_escape_string($row['Item_name']);
        $price = $row['Item_price'];
        $image = $conn->real_escape_string($row['Item_img']);
        $conn->query("INSERT INTO orders (menu_id, name, price, image, img_dir) VALUES ($id, '$name', $price, '$image', '$img_dir')");
    }
}

// Handle "Remove" button click
if (isset($_POST['remove'])) {
    $id = (int) $_POST['menu_id'];
    $conn->query("DELETE FROM orders WHERE menu_id = $id");
}

// Handle "Finalize Order" button click
if (isset($_POST['finalize_order'])) {
    $customer_name = $conn->real_escape_string($_POST['customer_name']);
    $table_number = $conn->real_escape_string($_POST['table_number']);
    $ordered_items = $conn->query("SELECT * FROM orders");
    $total_price = 0;
    $order_details = [];

    while ($row = $ordered_items->fetch_assoc()) {
        $total_price += $row['price'];
        $order_details[] = "(" . $row['menu_id'] . ", '" . $row['name'] . "', " . $row['price'] . ", '" . $row['image'] . "', '" . $row['img_dir'] . "', $total_price)";
    }

    if (!empty($order_details)) {
        $table_name = "order_" . preg_replace('/\s+/', '_', strtolower($customer_name)) . "_table_" . $table_number;
        
        $conn->query("CREATE TABLE IF NOT EXISTS $table_name (
            id INT AUTO_INCREMENT PRIMARY KEY,
            menu_id INT,
            name VARCHAR(255),
            price DECIMAL(10,2),
            image VARCHAR(255),
            img_dir VARCHAR(255),
            total_price DECIMAL(10,2)
        )");

        foreach ($order_details as $order) {
            $conn->query("INSERT INTO $table_name (menu_id, name, price, image, img_dir, total_price) VALUES $order");
        }
    }

    $conn->query("DELETE FROM orders");
}

// Fetch menu items
$menu_items = $conn->query("SELECT * FROM $menu_table");
if (!$menu_items) {
    die("Error fetching menu: " . $conn->error);
}

// Fetch ordered items
$ordered_items = $conn->query("SELECT * FROM orders");

// Calculate total price
$total_price = 0;
while ($row = $ordered_items->fetch_assoc()) {
    $total_price += $row['price'];
}
$ordered_items->data_seek(0);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hotel Menu Order</title>
    <style>
        body {
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
            font-family: initial;
        }
        table {
            width: 70%;
            border-collapse: collapse;
            margin: 20px auto;
        }
        table, th, td {
            border: 2px solid #ebebeb;
            text-align: center;
            color: black;
            font-style: italic;
            font-weight: 600;
        }
        th, td {
            padding: 10px;
        }
        button {
            padding: 5px 15px;
            cursor: pointer;
        }
        button:hover {
            background: #b3b3b2;
        }
        .backpage {
            background: #ebebeb;
            float: left;
            margin: 10px 20% 100px 20%;
            height: 25px;
            width: 60.7%;
            padding: 5px 10px;
            text-decoration: none;
            color: #ff0d0d;
            font-weight: bold;
            box-shadow: 2px 2px 2px 2px #b0a2a2;
            font-size: 20px;
            text-align: center;
        }
        .backpage:hover {
            background: #b3b3b2;
        }
        .tables {
            background: white;
            width: 70%;
            margin-top: 10px;
            padding: 10px;
            border-radius: 15px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            margin-bottom: 100px;
            margin-right: auto;
            margin-left: auto;
        }
        #timeDisplay {
            background: #ffef00;
        }
        .in {
            float: left;
            margin: 10px 20%;
            height: 25px;
            width: 60%;
            padding: 5px 10px;
        }
        .int {
            float: left;
            margin: 25px 20% 10px 20%;
            height: 25px;
            width: 60%;
            padding: 5px 10px;
        }
        .int1 {
            float: left;
            margin: 60px 30% 0px 35%;
            height: 25px;
            width: 60%;
            padding: 5px 10px;
        }
        .bt {
            float: left;
            margin: 10px 20%;
            height: 40px;
            width: 62.8%;
            padding: 10px 10px;
        }
        .price {
            background-color: rgb(255, 247, 2);
            padding: 5px 10px;
        }
        .time {
            background-color: rgb(255, 247, 2);
            padding: 5px 10px;
        }
        .menu-item-image {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 5px;
        }
        .ordered-item-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 5px;
        }
    </style>
    <script>
        function updateTime() {
            let now = new Date();
            let timeString = now.toLocaleTimeString();
            document.getElementById("timeDisplay").innerText = timeString;
        }
        setInterval(updateTime, 1000);
    </script>
</head>
<body onload="updateTime()">
    
    <div class="tables">
        <h1 style="text-align: center; margin-bottom: -20px;">Current Time: <span class="time" id="timeDisplay"><?php echo date("h:i:s A"); ?></span></h1>
        <h2 style="text-align: center; margin-bottom: 50px;">Now Ordering from: <?php echo ucfirst(str_replace('_', ' ', $menu_table)); ?></h2>
        <table>
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Menu Item</th>
                    <th>Price</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $menu_items->fetch_assoc()) { ?>
                    <tr>
                        <td>
                            <img 
                                src="<?php echo $img_dir . htmlspecialchars(basename($row['Item_img'])); ?>" 
                                alt="<?php echo htmlspecialchars($row['Item_name']); ?>" 
                                class="menu-item-image"
                                onerror="this.src='<?php echo $img_dir; ?>default.jpg'"
                            >
                        </td>
                        <td><?php echo htmlspecialchars($row['Item_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['Item_price']); ?></td>
                        <td>
                            <form method="POST">
                                <input type="hidden" name="menu_id" value="<?php echo $row['Item_id']; ?>">
                                <button type="submit" name="order">Order</button>
                            </form>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
    
    <div class="tables">
        <h2 style="text-align: center;">Ordered Items</h2>
        <form method="POST">
            <table>
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Menu Item</th>
                        <th>Price</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $ordered_items->fetch_assoc()) { ?>
                        <tr>
                            <td>
                                <img 
                                    src="<?php echo $row['img_dir'] . htmlspecialchars(basename($row['image'])); ?>" 
                                    alt="<?php echo htmlspecialchars($row['name']); ?>" 
                                    class="ordered-item-image"
                                    onerror="this.src='<?php echo $row['img_dir']; ?>default.jpg'"
                                >
                            </td>
                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                            <td><?php echo htmlspecialchars($row['price']); ?></td>
                            <td>
                                <form method="POST">
                                    <input type="hidden" name="menu_id" value="<?php echo $row['menu_id']; ?>">
                                    <button type="submit" name="remove">Remove</button>
                                </form>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
            <h3 style="text-align: center;">Total Price: <span class="price"><?php echo $total_price; ?></span></h3>
           
            <div>
                <h1 class="int1">Ordering Form:</h1>
                <input class="int" type="text" name="customer_name" placeholder="Customer Name" required>
                <input class="in" type="text" name="table_number" placeholder="Table Number" required>
                <button class="bt" type="submit" name="finalize_order">Finalize Order</button>
                <a class="backpage" href="index.html">Back to Home Page</a>
            </div>
        </form>
    </div>
</body>
</html>