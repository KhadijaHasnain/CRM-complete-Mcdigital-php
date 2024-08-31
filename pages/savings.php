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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $total_savings = $_POST['total_savings'];

    $conn->query("TRUNCATE TABLE Savings");
    $stmt = $conn->prepare("INSERT INTO Savings (total_savings) VALUES (?)");
    $stmt->bind_param("d", $total_savings);
    $stmt->execute();
    $stmt->close();

    header("Location: savings.php");
    exit();
}

// Fetch current savings
$savingsResult = $conn->query("SELECT total_savings FROM Savings LIMIT 1");
$savings = $savingsResult->fetch_assoc()['total_savings'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Update Savings</title>
  <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="sb-nav-fixed">
<?php include 'header.php'; ?>

<div id="layoutSidenav">
    <?php include 'sidebar.php'; ?>
    <div id="layoutSidenav_content">
        <main>
            <div class="container-fluid px-4 mt-4">
                <h2>Update Savings</h2>
                <form method="POST" action="savings.php" class="mb-4">
                    <div class="form-group">
                        <label for="total_savings">Total Savings</label>
                        <input type="number" class="form-control" id="total_savings" name="total_savings" step="0.01" value="<?php echo $savings; ?>" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Update Savings</button>
                </form>
            </div>
        </main>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
</body>
</html>
