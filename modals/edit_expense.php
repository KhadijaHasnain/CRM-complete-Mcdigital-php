<?php
$conn = new mysqli('localhost', 'root', '', 'freelance_bussiness');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$id = $_POST['id'];
$category = $_POST['category'];
$subcategory = $_POST['subcategory'] === 'Other' ? $_POST['other_subcategory'] : $_POST['subcategory'];
$description = $_POST['description'];
$amount = $_POST['amount'];
$date = $_POST['date'];

$sql = "UPDATE Expenses SET category='$category', subcategory='$subcategory', description='$description', amount='$amount', date='$date' WHERE id='$id'";

if ($conn->query($sql) === TRUE) {
    header("Location: expenses.php");
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}

$conn->close();
?>
