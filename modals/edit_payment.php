<?php
session_start();

$conn = new mysqli('localhost', 'root', '', 'freelance_bussiness');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$id = $_POST['id'];
$team_member_id = $_POST['team_member_id'];
$amount = $_POST['amount'];
$medical = isset($_POST['medical']) ? $_POST['medical'] : 0;
$bonuses = isset($_POST['bonuses']) ? $_POST['bonuses'] : 0;
$tax = isset($_POST['tax']) ? $_POST['tax'] : 0;
$deductions = isset($_POST['deductions']) ? $_POST['deductions'] : 0;
$date = $_POST['date'];

$sql = "UPDATE Payments SET team_member_id = ?, amount = ?, medical = ?, bonuses = ?, tax = ?, deductions = ?, date = ? WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("idddddsi", $team_member_id, $amount, $medical, $bonuses, $tax, $deductions, $date, $id);

if ($stmt->execute()) {
    $_SESSION['success'] = "Payment updated successfully.";
} else {
    $_SESSION['error'] = "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();

header("Location: payments.php");
exit();
?>
