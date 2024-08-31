<?php
$conn = new mysqli('localhost', 'root', '', 'freelance_bussiness');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$id = $_POST['id'];

$sql = "DELETE FROM OutsourcingProjects WHERE id='$id'";

if ($conn->query($sql) === TRUE) {
    header("Location: outsourcing.php");
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}

$conn->close();
?>
