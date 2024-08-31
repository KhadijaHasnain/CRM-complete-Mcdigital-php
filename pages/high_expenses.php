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

// Fetch high domestic expenses
$domesticExpenses = $conn->query("SELECT * FROM Expenses WHERE category='Domestic' AND amount > 150000 ORDER BY date DESC");
if (!$domesticExpenses) {
    die("Query Failed: " . $conn->error);
}

// Fetch high business expenses
$businessExpenses = $conn->query("SELECT * FROM Expenses WHERE category='Business' AND amount > 150000 ORDER BY date DESC");
if (!$businessExpenses) {
    die("Query Failed: " . $conn->error);
}

// Fetch fixed expenses (assuming subcategories 'House Rent' and 'Electricity Bill' as fixed expenses)
$fixedExpenses = $conn->query("SELECT * FROM Expenses WHERE subcategory IN ('House Rent', 'Electricity Bill', 'Gas', 'Netflix', 'TransWorld', 'Teramind', 'Amazon', 'Upwork', 'Blaze', 'ChatGPT membership', 'Call pkg', 'Youtube', 'Freelancer') ORDER BY date DESC");
if (!$fixedExpenses) {
    die("Query Failed: " . $conn->error);
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>High Expenses</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <a class="navbar-brand" href="dashboard.php">Financial Dashboard</a>
    <div class="collapse navbar-collapse">
        <ul class="navbar-nav ml-auto">
            <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
            <li class="nav-item"><a class="nav-link" href="high_expenses.php">High Expenses</a></li>
            <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
        </ul>
    </div>
</nav>

<div class="container mt-4">
    <h2>High Domestic Expenses</h2>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Date</th>
                <th>Subcategory</th>
                <th>Description</th>
                <th>Amount</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $domesticExpenses->fetch_assoc()): ?>
            <tr>
                <td><?php echo $row['date']; ?></td>
                <td><?php echo $row['subcategory']; ?></td>
                <td><?php echo $row['description']; ?></td>
                <td>₨ <?php echo number_format($row['amount'], 2); ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <h2>High Business Expenses</h2>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Date</th>
                <th>Subcategory</th>
                <th>Description</th>
                <th>Amount</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $businessExpenses->fetch_assoc()): ?>
            <tr>
                <td><?php echo $row['date']; ?></td>
                <td><?php echo $row['subcategory']; ?></td>
                <td><?php echo $row['description']; ?></td>
                <td>₨ <?php echo number_format($row['amount'], 2); ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <h2>Fixed Expenses</h2>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Date</th>
                <th>Category</th>
                <th>Subcategory</th>
                <th>Description</th>
                <th>Amount</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $fixedExpenses->fetch_assoc()): ?>
            <tr>
                <td><?php echo $row['date']; ?></td>
                <td><?php echo $row['category']; ?></td>
                <td><?php echo $row['subcategory']; ?></td>
                <td><?php echo $row['description']; ?></td>
                <td>₨ <?php echo number_format($row['amount'], 2); ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
