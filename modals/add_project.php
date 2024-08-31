<?php
$conn = new mysqli('localhost', 'root', '', 'freelance_bussiness');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$platform = $_POST['platform'];
$project_name = $_POST['project_name'];
$amount = $_POST['amount'];
$date = $_POST['date'];
$project_link = $_POST['project_link'];

$sql = "INSERT INTO OutsourcingProjects (platform, project_name, amount, date, project_link) VALUES ('$platform', '$project_name', '$amount', '$date', '$project_link')";

if ($conn->query($sql) === TRUE) {
    header("Location: outsourcing.php");
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}

$conn->close();
?>
