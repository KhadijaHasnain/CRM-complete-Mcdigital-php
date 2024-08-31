<?php
$conn = new mysqli('localhost', 'root', '', 'freelance_bussiness');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$id = $_POST['id'];
$account_name = $_POST['account_name'];
$amount = $_POST['amount'];
$date = $_POST['date'];

$sql = "UPDATE FreelancingAccounts SET account_name='$account_name', amount='$amount', date='$date' WHERE id='$id'";

if ($conn->query($sql) === TRUE) {
    header("Location: accounts.php");
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}

$conn->close();
?>
