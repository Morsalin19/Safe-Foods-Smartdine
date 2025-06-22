<?php
// Database configuration
$host = "localhost";
$username = "root";
$password = "";
$dbname = "sdm_smartdine";

// Create a connection
$conn = new mysqli($host, $username, $password, $dbname);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Query to fetch data
$sql = "SELECT id, drinks_name, price, Item_img FROM colde_drinks";
$result = $conn->query($sql);

// Display data in a table
if ($result->num_rows > 0) {
    echo"<h2 style = 'background: #3f4bff;
  font-size: 29px;
  color: red;
  margin: 20px 440px 0 440px;
  padding: 10px 170px;
  border-radius: 7px;'>Cold Drinks<h2>";
    echo "<table border='1' style='border-collapse: collapse;
  width: 70%;
  margin: auto;
  border: 2px solid #ffb9b9;'>";
    echo "<tr style='background: #a3a30e;; font-size: 29px; color: red;'>
            <th>ID</th>
            <th>Drink Name</th>
            <th>Price</th>
            <th>Item Picture</th>
          </tr>";
    
    while ($row = $result->fetch_assoc()) {
        echo "<tr style = 'color: green;
  font-size: 25;
  background: #f1f1f0;
  text-align: center;'>
                <td>" . htmlspecialchars($row["id"]) . "</td>
                <td>" . htmlspecialchars($row["drinks_name"]) . "</td>
                <td>" . htmlspecialchars($row["price"]) . "</td>
                <td><img src='" . htmlspecialchars($row["Item_img"]) . "' alt='" . htmlspecialchars($row["drinks_name"]) . "' style='width: 150px;height: 100px; object-fit: cover;border-radius: 5px;'></td>
              </tr>";
    }
    echo "</table>";
} else {
    echo "No data found";
}

// Close the connection
$conn->close();
?>