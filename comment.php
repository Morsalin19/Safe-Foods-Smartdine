<?php
// Database connection
$conn = new mysqli("localhost", "root", "", "sdm_smartdine");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create table if it doesn't exist
$sql_create_table = "CREATE TABLE IF NOT EXISTS sdm_smartdine (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    table_number INT NOT NULL,
    comment TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
$conn->query($sql_create_table);

// Insert comment when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_comment'])) {
    $name = trim($_POST['name']);
    $table_number = trim($_POST['table_number']);
    $comment = trim($_POST['comment']);

    if (!empty($name) && !empty($table_number) && !empty($comment)) {
        $stmt = $conn->prepare("INSERT INTO sdm_smartdine (name, table_number, comment) VALUES (?, ?, ?)");
        $stmt->bind_param("sis", $name, $table_number, $comment);
        
        if ($stmt->execute()) {
            echo "<script>alert('Comment Submitted Successfully!');</script>";
        } else {
            echo "<script>alert('Error submitting comment. Try again.');</script>";
        }
        $stmt->close();
    } else {
        echo "<script>alert('All fields are required!');</script>";
    }
}

// Fetch previous comments
$comments = $conn->query("SELECT * FROM sdm_smartdine ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="style2.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Feedback</title>
    <style>
        .backpage {
            background: #ebebeb;
            margin: auto 15px;
            padding: 10px 10px;
            text-decoration: none;
            color: #ff0d0d;
            font-weight: bold;
            box-shadow: 2px 2px 2px 2px #b0a2a2;
            font-size: 20px;
        }
        .backpage:hover {
            background: #c9c9c6;
        }
        body { font-family: Arial, sans-serif; text-align: center; padding: 20px; }
        .form-container { 
  width: 50%;
  width: 50%;
  display: none;
  background-color: #9a9a95;
  margin:50px auto;
  align-content: center;
  border-radius:15px;
  box-shadow: 3px 3px #808080;
}
        table { width: 80%; margin: auto; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 10px; border: 1px solid black; text-align: center; }
        th { background-color: #f2f2f2; }
        input, textarea {
 width: 58%;
  padding: 10px;
  margin: 10px 4% 0px 1%;
 }
        .button2 { padding: 13px 20px; cursor: pointer; }
        .int{
            float: left;
  margin:25px 20% 10px 20%;
  height: 40px;
  width: 57%;
  padding:5px 10px;
}
.int1{
  float: left;
  margin:5px 20% 0px 20%;
  height: 40px;
  width: 57%;
  padding:5px 10px;
}
.bt{
  float: left;
  margin:0px 20% 26px;
  height: 40px;
  width: 57.6%;
  padding:10px 10px;
}
.bt1:hover{
    border-radius: 0%;
    box-shadow: 2px 2px #808080;
}
.head-text{
background: #e1e1e1;
  top: 50px;
  margin-top: 50px;
  margin-left: 38%;
  margin-right: 38%;
  padding: 10px;
  color: blue;
  border-radius: 7px;
  box-shadow: 3px 3px #ced5dc;
}
    </style>
    <script>
        function showCommentForm() {
            document.getElementById('commentForm').style.display = "block";
        }
    </script>
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
            <li><a href="payment.php">Payment</a></li>
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

    <!-- Comment Form -->
    <div id="commentForm" class="form-container">
        <form method="POST">
            <input class="int" type="text" name="name" placeholder="Your Name" required><br><br>
            <input class="int1" type="number" name="table_number" placeholder="Table Number" required><br><br>
            <textarea name="comment" placeholder="Write your comment..." required></textarea><br><br>
            <button class="bt" type="submit" name="submit_comment">Submit Your Comment</button>
        </form>
    </div>

    <!-- Display Previous Comments -->
    <h2 class="head-text">Customer's Feedback</h2>
    <table>
        <tr style = " background:rgb(246, 243, 243); color: #ff0d0d; font-size: 20px;">
            <th>Name</th>
            <th>Table Number</th>
            <th>Comment</th>
            <th>Time</th>
        </tr>
        <?php while ($row = $comments->fetch_assoc()) { ?>
            <tr>
                <td><?php echo htmlspecialchars($row['name']); ?></td>
                <td><?php echo htmlspecialchars($row['table_number']); ?></td>
                <td><?php echo htmlspecialchars($row['comment']); ?></td>
                <td><?php echo $row['created_at']; ?></td>
            </tr>
        <?php } ?>
    </table>
    
<!-- Footer -->
    <footer style="margin-top: 50px">
        <!-- Write Comment Button -->
        <div>      
            <button class="bt1 button2" onclick="showCommentForm()">Write Your Comment</button>
            <a class="backpage" href="index.html">Back to Home Page</a>
        </div>
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
</body>
</html>
