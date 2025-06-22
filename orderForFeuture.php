<?php
// Set timezone to ensure correct time detection
date_default_timezone_set("Asia/Dhaka");

// Database connection
$conn = new mysqli("localhost", "root", "", "sdm_smartdine");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Ensure finalized_orders table exists
$conn->query("CREATE TABLE IF NOT EXISTS finalized_orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_name VARCHAR(255) NOT NULL,
    table_number VARCHAR(50) NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// Get the current hour
$hour = (int) date("H");

// Determine the current menu based on time
if ($hour >= 6 && $hour < 12) {
    $time_based_tables = ["breakfast_menu1", "lunch_menu", "dinner_menu"];
} elseif ($hour >= 12 && $hour < 19) {
    $time_based_tables = ["lunch_menu", "dinner_menu", "breakfast_menu1"];
} else {
    $time_based_tables = ["dinner_menu", "breakfast_menu1", "lunch_menu"];
}

// Additional always-available menus
$additional_tables = ["special_menu", "colde_drinks", "fruits_menu"];

// Combine all menu tables to display
$all_menu_tables = array_merge($time_based_tables, $additional_tables);

// Handle "Order" button click
if (isset($_POST['order'])) {
    $id = (int) $_POST['menu_id'];
    $menu_table = $_POST['menu_table'];
    $query = "SELECT * FROM $menu_table WHERE Item_id = $id";
    $result = $conn->query($query);
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $name = $conn->real_escape_string($row['Item_name'] ?? $row['drinks_name'] ?? '');
        $price = $row['Item_price'] ?? $row['price'] ?? 0;
        $image = $conn->real_escape_string($row['Item_img'] ?? $row['item_img'] ?? '');
        
        // Determine image directory based on menu table
        if ($menu_table == 'dinner_menu') {
            $img_dir = 'picture4/';
        } elseif ($menu_table == 'breakfast_menu1') {
            $img_dir = 'picture3/';
        } elseif ($menu_table == 'lunch_menu') {
            $img_dir = 'picture5/';
        } elseif ($menu_table == 'special_menu') {
            $img_dir = 'picture2/';
        } elseif ($menu_table == 'colde_drinks') {
            $img_dir = 'picture1/';
        } elseif ($menu_table == 'fruits_menu') {
            $img_dir = 'picture/';
        } else {
            $img_dir = '';
        }
        
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
    
    while ($row = $ordered_items->fetch_assoc()) {
        $total_price += $row['price'];
    }
    
    if ($total_price > 0) {
        $conn->query("INSERT INTO finalized_orders (customer_name, table_number, total_price) VALUES ('$customer_name', '$table_number', $total_price)");
        $conn->query("DELETE FROM orders");
    }
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
            background:#b3b3b2;
        }
        .backpage {
            background: #ebebeb;
            float: left;
            margin:10px 20% 100px 20%;
            height: 25px;
            width: 60%;
            padding:5px 10px;
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
            padding:10px;
            border-radius:15px;
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
            margin:10px 20%;
            height: 25px;
            width: 60%;
            padding:5px 10px;
        }
        .int {
            float: left;
            margin:25px 20% 10px 20%;
            height: 25px;
            width: 60%;
            padding:5px 10px;
        }
        .int1 {
            float: left;
            margin: 60px 30% 0px 35%;
            height: 25px;
            width: 60%;
            padding:5px 10px;
        }
        .bt {
            float: left;
            margin:10px 20%;
            height: 40px;
            width: 62.3%;
            padding:10px 10px;
        }
        .price {
            background-color:rgb(255, 247, 2);
            padding: 5px 10px;
        }
        .time {
            background-color:rgb(255, 247, 2);
            padding: 5px 10px;
        }
        .menu-item-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 5px;
        }
        .ordered-item-image {
            width: 60px;
            height: 60px;
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
    <h1 style="text-align: center; margin-bottom: -20px;">Current Time: <span class="time" id="timeDisplay"><?php echo date("h:i:s A"); ?></span></h1>
    <h2 style="text-align: center; margin-bottom: 50px;">Now Available Items:</h2>

    <?php foreach ($all_menu_tables as $menu_table): ?>
        <div class="tables">
            <h2 style="border-radius: 5px;text-align: center;background:red;width: 20%;margin:10px auto;height: 33px; padding:5px;"><?php echo ucfirst(str_replace('_', ' ', $menu_table)); ?>:</h2>
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
                    <?php 
                    $menu_items = $conn->query("SELECT * FROM $menu_table");
                    while ($row = $menu_items->fetch_assoc()): 
                        $item_name = $row['Item_name'] ?? $row['drinks_name'] ?? '';
                        $item_price = $row['Item_price'] ?? $row['price'] ?? 0;
                        $item_img = $row['Item_img'] ?? $row['item_img'] ?? '';
                        $item_id = $row['Item_id'] ?? $row['id'] ?? 0;
                        
                        // Determine correct image path based on menu table
                        if ($menu_table == 'dinner_menu') {
                            $img_path = 'picture4/' . basename($item_img);
                        } elseif ($menu_table == 'breakfast_menu1') {
                            $img_path = 'picture3/' . basename($item_img);
                        } elseif ($menu_table == 'lunch_menu') {
                            $img_path = 'picture5/' . basename($item_img);
                        } elseif ($menu_table == 'special_menu') {
                            $img_path = 'picture2/' . basename($item_img);
                        } elseif ($menu_table == 'colde_drinks') {
                            $img_path = 'picture1/' . basename($item_img);
                        } elseif ($menu_table == 'fruits_menu') {
                            $img_path = 'picture/' . basename($item_img);
                        } else {
                            $img_path = $item_img;
                        }
                    ?>
                        <tr>
                            <td>
                                <?php if (!empty($item_img)): ?>
                                    <img 
                                        src="<?php echo htmlspecialchars($img_path); ?>" 
                                        alt="<?php echo htmlspecialchars($item_name); ?>" 
                                        class="menu-item-image"
                                        onerror="this.src='images/default.jpg'"
                                    >
                                <?php else: ?>
                                    <img src="images/default.jpg" class="menu-item-image">
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($item_name); ?></td>
                            <td><?php echo htmlspecialchars($item_price); ?></td>
                            <td>
                                <form method="POST">
                                    <input type="hidden" name="menu_id" value="<?php echo $item_id; ?>">
                                    <input type="hidden" name="menu_table" value="<?php echo $menu_table; ?>">
                                    <button type="submit" name="order">Order</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php endforeach; ?>

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
                    <?php while ($row = $ordered_items->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <?php if (!empty($row['image'])): ?>
                                    <img 
                                        src="<?php echo htmlspecialchars($row['img_dir'] . htmlspecialchars(basename($row['image']))); ?>" 
                                        alt="<?php echo htmlspecialchars($row['name']); ?>" 
                                        class="ordered-item-image"
                                        onerror="this.src='images/default.jpg'"
                                    >
                                <?php else: ?>
                                    <img src="images/default.jpg" class="ordered-item-image">
                                <?php endif; ?>
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
                    <?php endwhile; ?>
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