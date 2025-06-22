<?php
session_start();
$host = "localhost";
$user = "root"; // Change if necessary
$password = ""; // Change if necessary
$dbname = "chat";

// Connect to Database
$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// User Registration
if (isset($_POST['register'])) {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
    $stmt->bind_param("ss", $username, $password);
    if ($stmt->execute()) {
        echo "Registered successfully!";
    } else {
        echo "Username already taken!";
    }
    exit;
}

// User Login
if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, password FROM users WHERE username=?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($id, $hashed_password);
    $stmt->fetch();

    if (password_verify($password, $hashed_password)) {
        $_SESSION['user_id'] = $id;
        $_SESSION['username'] = $username;
        echo "success";
    } else {
        echo "Invalid credentials!";
    }
    exit;
}

// Fetch Messages
if (isset($_GET['fetch'])) {
    $result = $conn->query("SELECT users.username, messages.message, messages.created_at 
                            FROM messages 
                            JOIN users ON messages.user_id = users.id 
                            ORDER BY messages.created_at DESC LIMIT 20");
    
    $messages = [];
    while ($row = $result->fetch_assoc()) {
        $messages[] = $row;
    }
    echo json_encode(array_reverse($messages));
    exit;
}

// Save Messages
if (isset($_POST['message'])) {
    $user_id = $_SESSION['user_id'];
    $message = $_POST['message'];

    $stmt = $conn->prepare("INSERT INTO messages (user_id, message) VALUES (?, ?)");
    $stmt->bind_param("is", $user_id, $message);
    $stmt->execute();
    exit;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WebSocket Chat</title>
    <style>
        .container2{
            background-color: #b1b1b1;
  height: auto;
  width: 50%;
  align-content: center;
  left: 50%;
  top: 50%;
  align-self: center;
  margin:15% auto;
  padding:30px 0px 70px 0px;
  border-radius:70px;
  box-shadow: 5px 5px #949494;
   }
   .ip{
    margin: 0 auto;
   }
   .in{
  float: left;
  margin:10px 20%;
  height: 25px;
  width: 60%;
  padding:5px 10px;
}
.int{
  float: left;
  margin:20px 20% 0px 20%;
  height: 30px;
  width: 60%;
  padding:5px 10px;
}
.int2{
    float: left;
  margin:0px 20% 0px 40%;
  height: 25px;
  width: 60%;
  padding:5px 10px;
}
.int3{
  float: left;
  margin:0px 30% 0px 35%;
  height: 25px;
  width: 60%;
  padding:5px 10px;
}
.int1{
  float: left;
  margin:10px 20% 0px 20%;
  height: 30px;
  width: 60%;
  padding:5px 10px;
}
.bt{
  float: left;
  margin:10px 20%;
  height: 40px;
  width: 60.6%;
  padding:10px 10px;
}
    </style>
    <link rel="stylesheet" href="style2.css">
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
<div class="container2">
<h2 class="ip int3">WebSocket Chat</h2>

<?php if (!isset($_SESSION['user_id'])): ?>
    <h3 class="int2">Login / Register</h3>
    <form id="authForm">
        <input class="int" type="text" id="username" placeholder="Username" required>
        <input class="int1" type="password" id="password" placeholder="Password" required>
        <button class="bt" type="button" onclick="login()">Login</button>
        <button class="bt" type="button" onclick="register()">Register</button>
    </form>
<?php else: ?>
    <h3>Welcome, <?= $_SESSION['username'] ?>!</h3>
    <div id="chat-box" style="height: 300px; overflow-y: auto; border: 1px solid #ccc;"></div>
    <input type="text" id="message" placeholder="Type a message">
    <button onclick="sendMessage()">Send</button>
<?php endif; ?>
</div>
<!-- Footer -->
    <footer>
        <div class="container">
            <p>&copy; 2023 SDM SmartDine. All rights reserved.</p>
            <p>Designed with <i class="fas fa-heart" style="color: var(--primary);"></i> for food lovers</p>
        </div>
    </footer>


<script>
let socket = new WebSocket("ws://localhost:8080/chat");

socket.onmessage = function(event) {
    let chatBox = document.getElementById("chat-box");
    chatBox.innerHTML += "<p>" + event.data + "</p>";
    chatBox.scrollTop = chatBox.scrollHeight;
};

function register() {
    let username = document.getElementById("username").value;
    let password = document.getElementById("password").value;
    
    fetch('chat.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `register=1&username=${encodeURIComponent(username)}&password=${encodeURIComponent(password)}`
    }).then(response => response.text()).then(alert);
}

function login() {
    let username = document.getElementById("username").value;
    let password = document.getElementById("password").value;

    fetch('chat.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `login=1&username=${encodeURIComponent(username)}&password=${encodeURIComponent(password)}`
    }).then(response => response.text()).then(response => {
        if (response === "success") location.reload();
        else alert(response);
    });
}

function sendMessage() {
    let message = document.getElementById("message").value;
    socket.send("<?= $_SESSION['username'] ?>: " + message);

    fetch('chat.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `message=${encodeURIComponent(message)}`
    });

    document.getElementById("message").value = "";
}
</script>
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
