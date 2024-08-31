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

// Fetch fixed monthly expenses
$expenses = $conn->query("SELECT category, SUM(amount) as total FROM FixedMonthlyExpenses GROUP BY category")->fetch_all(MYSQLI_ASSOC);

// Fetch current savings from dashboard
$savingsResult = $conn->query("SELECT total_savings FROM Savings LIMIT 1");
$savings = $savingsResult->fetch_assoc()['total_savings'];

$totalExpenses = 0;
$totalSalary = 0;
$totalDomestic = 0;
$totalBusiness = 0;
foreach ($expenses as $expense) {
    $totalExpenses += $expense['total'];
    if ($expense['category'] == 'Salary') {
        $totalSalary += $expense['total'];
    } elseif ($expense['category'] == 'Domestic') {
        $totalDomestic += $expense['total'];
    } elseif ($expense['category'] == 'Business') {
        $totalBusiness += $expense['total'];
    }
}

// Calculate predictions
$months = [4, 6, 12];
$predictions = [];
foreach ($months as $month) {
    $required = $totalExpenses * $month;
    $remainingSavings = $savings - $required;
    $predictions[$month] = [
        'required' => $required,
        'savings' => $remainingSavings >= 0 ? "No issue" : "Need ₨ " . number_format(abs($remainingSavings), 2)
    ];
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Expense Predictions</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .table-summary th {
            background-color: rgb(33, 37, 41);
            color: white;
        }
        .table-summary th, .table-summary td {
            text-align: center;
            font-weight: bold;
            border: 1px solid rgb(33, 37, 41);
        }
        .table-summary .total-row {
            font-weight: bold;
            background-color: #f8f9fa;
        }
        .table-summary .total-row th, .table-summary .total-row td {
            border-top: 2px solid #dee2e6;
        }
    </style>
</head>
<body class="sb-nav-fixed">
<?php include 'header.php'; ?>

<div id="layoutSidenav">
    <?php include 'sidebar.php'; ?>
    <div id="layoutSidenav_content">
        <main>
            <div class="container-fluid px-4 mt-4">
                <h2>Expense Predictions</h2>
                <div class="row">
                    <div class="col-lg-6">
                        <table class="table table-bordered table-summary">
                            <thead>
                                <tr>
                                    <th>Category</th>
                                    <th>Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Salary</td>
                                    <td>₨ <?php echo number_format($totalSalary, 2); ?></td>
                                </tr>
                                <tr>
                                    <td>Domestic</td>
                                    <td>₨ <?php echo number_format($totalDomestic, 2); ?></td>
                                </tr>
                                <tr>
                                    <td>Business</td>
                                    <td>₨ <?php echo number_format($totalBusiness, 2); ?></td>
                                </tr>
                                <tr class="total-row">
                                    <th>Total Monthly Expenses</th>
                                    <td>₨ <?php echo number_format($totalExpenses, 2); ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="col-lg-6">
                        <?php include 'goal.php'; ?>
                    </div>
                </div>

                <h2>Savings and Predictions</h2>
                <table class="table table-bordered table-summary">
                    <thead>
                        <tr>
                            <th>Period</th>
                            <th>Required Amount</th>
                            <th>Remaining Savings</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($predictions as $month => $prediction): ?>
                        <tr>
                            <td><?php echo $month; ?> Months</td>
                            <td>₨ <?php echo number_format($prediction['required'], 2); ?></td>
                            <td><?php echo $prediction['savings']; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
