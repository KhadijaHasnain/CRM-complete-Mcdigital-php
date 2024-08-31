<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$conn = new mysqli('localhost', 'root', '', 'freelance_bussiness');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Retrieve and sanitize input data
$team_member_id = $_POST['team_member_id'];
$username = $_POST['username']; // New team member username
$amount = floatval($_POST['amount']);
$medical = floatval($_POST['medical']);
$bonuses = floatval($_POST['bonuses']);
$tax = floatval($_POST['tax']);
$deductions = floatval($_POST['deductions']);
$date = $_POST['date'];

if ($team_member_id == 'new') {
    // Add new team member
    $stmt_add_user = $conn->prepare("INSERT INTO Users (username) VALUES (?)");
    $stmt_add_user->bind_param("s", $username);
    if ($stmt_add_user->execute()) {
        $team_member_id = $stmt_add_user->insert_id;
    } else {
        die("Error: " . $stmt_add_user->error);
    }
    $stmt_add_user->close();
}

// Insert the payment
$stmt = $conn->prepare("INSERT INTO Payments (team_member_id, amount, medical, bonuses, tax, deductions, date) VALUES (?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("iddddss", $team_member_id, $amount, $medical, $bonuses, $tax, $deductions, $date);

if ($stmt->execute()) {
    header("Location: payments.php");
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
