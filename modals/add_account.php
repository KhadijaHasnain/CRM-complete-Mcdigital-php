<?php
$conn = new mysqli('localhost', 'root', '', 'freelance_bussiness');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$account_name = $_POST['account_name'];
$amount = $_POST['amount'];

$sql = "INSERT INTO FreelancingAccounts (account_name, amount) VALUES ('$account_name', '$amount')";

if ($conn->query($sql) === TRUE) {
    header("Location: accounts.php");
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}

$conn->close();
?>
