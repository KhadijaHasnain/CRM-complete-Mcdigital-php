<?php
$conn = new mysqli('localhost', 'root', '', 'freelance_bussiness');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$category = $_POST['category'];
$subcategory = $_POST['subcategory'] === 'Other' ? $_POST['other_subcategory'] : $_POST['subcategory'];
$description = $_POST['description'];
$amount = $_POST['amount'];
$date = $_POST['date'];

// Prepare the SQL statement
$stmt = $conn->prepare("INSERT INTO Expenses (category, subcategory, description, amount, date) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("sssis", $category, $subcategory, $description, $amount, $date);

// Execute the statement
if ($stmt->execute()) {
    header("Location: expenses.php");
} else {
    echo "Error: " . $stmt->error;
}

// Close the statement and connection
$stmt->close();
$conn->close();
?>
