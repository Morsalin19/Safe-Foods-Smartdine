<?php
$firstName = $_POST['firstName'];
$lastName = $_POST['lastName'];
$gender = $_POST['gender'];
$phone = $_POST['phone'];
$email = $_POST['email'];
$password = $_POST['password'];
$dob = $_POST['dob'];

$conn = new mysqli('localhost', 'root', '', 'sdm_smartdine'); // Correct password
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
} else {
    $stmt = $conn->prepare("INSERT INTO register_info (firstName, lastName, gender, phone, email, password, dob)
                            VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssss", $firstName, $lastName, $gender, $phone, $email, $password, $dob);
    $stmt->execute();
    echo "Registration Successful!";
    $stmt->close();
    $conn->close();
}
?>
