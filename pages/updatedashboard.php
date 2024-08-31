<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

include '../includes/db_connect.php';

// Fetch data for current month
$currentMonth = date('Y-m');
$incomeQuery = "SELECT SUM(amount) as total FROM FreelancingAccounts WHERE DATE_FORMAT(date, '%Y-%m') = '$currentMonth'";
$expensesQuery = "SELECT SUM(amount) as total FROM Expenses WHERE DATE_FORMAT(date, '%Y-%m') = '$currentMonth'";
$outsourcingQuery = "SELECT SUM(amount) as total FROM OutsourcingProjects WHERE DATE_FORMAT(date, '%Y-%m') = '$currentMonth'";
$salariesQuery = "SELECT SUM(amount + IFNULL(medical, 0) + IFNULL(bonuses, 0) - IFNULL(tax, 0) - IFNULL(deductions, 0)) as total FROM Payments WHERE DATE_FORMAT(date, '%Y-%m') = '$currentMonth'";

$income = $conn->query($incomeQuery)->fetch_assoc()['total'] ?? 0;
$expenses = $conn->query($expensesQuery)->fetch_assoc()['total'] ?? 0;
$outsourcing = $conn->query($outsourcingQuery)->fetch_assoc()['total'] ?? 0;
$salaries = $conn->query($salariesQuery)->fetch_assoc()['total'] ?? 0;

$savings = $income - ($expenses + $outsourcing + $salaries);

// Fetch data for charts (adjusted for the last 6 months)
$monthlyIncomeData = $conn->query("SELECT DATE_FORMAT(date, '%Y-%m') AS month, SUM(amount) AS total FROM FreelancingAccounts GROUP BY month ORDER BY month DESC LIMIT 6")->fetch_all(MYSQLI_ASSOC);
$monthlyExpenseData = $conn->query("SELECT DATE_FORMAT(date, '%Y-%m') AS month, SUM(amount) AS total FROM Expenses GROUP BY month ORDER BY month DESC LIMIT 6")->fetch_all(MYSQLI_ASSOC);
$monthlyOutsourcingData = $conn->query("SELECT DATE_FORMAT(date, '%Y-%m') AS month, SUM(amount) AS total FROM OutsourcingProjects GROUP BY month ORDER BY month DESC LIMIT 6")->fetch_all(MYSQLI_ASSOC);
$monthlySalaryData = $conn->query("SELECT DATE_FORMAT(date, '%Y-%m') AS month, SUM(amount + IFNULL(medical, 0) + IFNULL(bonuses, 0) - IFNULL(tax, 0) - IFNULL(deductions, 0)) AS total FROM Payments GROUP BY month ORDER BY month DESC LIMIT 6")->fetch_all(MYSQLI_ASSOC);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Financial Dashboard</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.min.js"></script>
    <style>
        .card-body canvas {
            max-height: 400px;
        }
        .small-text {
            font-size: 0.75rem;
        }
        .dashboard-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
        }
        .dashboard-container .card {
            flex: 1 1 calc(33.333% - 1em);
            margin: 0.5em;
        }
        @media (max-width: 768px) {
            .dashboard-container .card {
                flex: 1 1 calc(50% - 1em);
            }
        }
        @media (max-width: 576px) {
            .dashboard-container .card {
                flex: 1 1 100%;
            }
        }
    </style>
</head>
<body>

<?php include '../includes/header.php'; ?>
<?php include '../includes/sidebar.php'; ?>

<div class="container mt-4">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-md-3">
            <button class="btn btn-primary btn-block">Add Income</button>
        </div>
        <div class="col-md-3">
            <button class="btn btn-success btn-block">Add Expense</button>
        </div>
        <div class="col-md-3">
            <button class="btn btn-warning btn-block">Add Outsourcing</button>
        </div>
        <div class="col-md-3">
            <button class="btn btn-danger btn-block">Add Salary</button>
        </div>
    </div>

    <!-- Summary and Charts Section -->
    <div class="dashboard-container">
        <div class="card">
            <div class="card-header">
                <h4>Current Month Summary</h4>
            </div>
            <div class="card-body">
                <p>Income: Rs <?php echo number_format($income, 2); ?></p>
                <p>Expenses: Rs <?php echo number_format($expenses, 2); ?></p>
                <p>Outsourcing: Rs <?php echo number_format($outsourcing, 2); ?></p>
                <p>Salaries: Rs <?php echo number_format($salaries, 2); ?></p>
                <p>Savings: Rs <?php echo number_format($savings, 2); ?></p>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                Freelancing Accounts Earning
            </div>
            <div class="card-body">
                <canvas id="freelancingAccountsEarningChart"></canvas>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                Expense Distribution
            </div>
            <div class="card-body">
                <canvas id="expenseDistributionChart"></canvas>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                Monthly Goals for Income and Expense July 2024
            </div>
            <div class="card-body">
                <canvas id="incomeExpenseGoalChart"></canvas>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                Monthly Income vs Monthly Expenses
            </div>
            <div class="card-body small-text">
                <canvas id="monthlyIncomeVsExpensesChart"></canvas>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                Outsourcing Projects Cost
            </div>
            <div class="card-body">
                <canvas id="outsourcingProjectsCostChart"></canvas>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                Income vs Expenses
            </div>
            <div class="card-body">
                <canvas id="incomeVsExpensesChart"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap and Chart.js Scripts -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script>
    // Sample data for the charts
    var incomeExpenseGoalCtx = document.getElementById('incomeExpenseGoalChart').getContext('2d');
    var freelancingAccountsEarningCtx = document.getElementById('freelancingAccountsEarningChart').getContext('2d');
    var expenseDistributionCtx = document.getElementById('expenseDistributionChart').getContext('2d');
    var outsourcingProjectsCostCtx = document.getElementById('outsourcingProjectsCostChart').getContext('2d');
    var incomeVsExpensesCtx = document.getElementById('incomeVsExpensesChart').getContext('2d');
    var monthlyIncomeVsExpensesCtx = document.getElementById('monthlyIncomeVsExpensesChart').getContext('2d');

    // Income vs Expense Goal Chart
    new Chart(incomeExpenseGoalCtx, {
        type: 'bar',
        data: {
            labels: ['Earnings', 'Expenses'],
            datasets: [{
                label: 'Actual',
                data: [<?php echo $income; ?>, <?php echo $expenses; ?>],
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 1
            }, {
                label: 'Goal',
                data: [2000000, 300000], // Example goal values
                backgroundColor: 'rgba(255, 99, 132, 0.2)',
                borderColor: 'rgba(255, 99, 132, 1)',
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Freelancing Accounts Earning Chart
    new Chart(freelancingAccountsEarningCtx, {
        type: 'bar',
        data: {
            labels: ['Upwork', 'Freelancer', 'Fiverr', 'Other'], // Example labels
            datasets: [{
                label: 'Earnings',
                data: [500000, 300000, 200000, 100000], // Example data
                backgroundColor: 'rgba(153, 102, 255, 0.2)',
                borderColor: 'rgba(153, 102, 255, 1)',
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Expense Distribution Chart
    new Chart(expenseDistributionCtx, {
        type: 'pie',
        data: {
            labels: ['Domestic', 'Business'], // Example labels
            datasets: [{
                label: 'Expenses',
                data: [300000, 158000], // Example data
                backgroundColor: ['rgba(255, 206, 86, 0.2)', 'rgba(54, 162, 235, 0.2)'],
                borderColor: ['rgba(255, 206, 86, 1)', 'rgba(54, 162, 235, 1)'],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true
        }
    });

    // Outsourcing Projects Cost Chart
    new Chart(outsourcingProjectsCostCtx, {
        type: 'bar',
        data: {
            labels: ['Upwork', 'Freelancer', 'Fiverr', 'Other'], // Example labels
            datasets: [{
                label: 'Cost',
                data: [100000, 200000, 150000, 50000], // Example data
                backgroundColor: 'rgba(255, 159, 64, 0.2)',
                borderColor: 'rgba(255, 159, 64, 1)',
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Income vs Expenses Chart
    new Chart(incomeVsExpensesCtx, {
        type: 'bar',
        data: {
            labels: ['Income', 'Expenses'],
            datasets: [{
                label: 'Amount',
                data: [<?php echo $income; ?>, <?php echo $expenses; ?>],
                backgroundColor: ['rgba(75, 192, 192, 0.2)', 'rgba(255, 99, 132, 0.2)'],
                borderColor: ['rgba(75, 192, 192, 1)', 'rgba(255, 99, 132, 1)'],
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Monthly Income vs Monthly Expenses Chart
    new Chart(monthlyIncomeVsExpensesCtx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode(array_column(array_slice($monthlyIncomeData, 0, 6), 'month')); ?>,
            datasets: [{
                label: 'Income',
                data: <?php echo json_encode(array_column(array_slice($monthlyIncomeData, 0, 6), 'total')); ?>,
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 1,
                fill: false
            }, {
                label: 'Expenses',
                data: <?php echo json_encode(array_column(array_slice($monthlyExpenseData, 0, 6), 'total')); ?>,
                backgroundColor: 'rgba(255, 99, 132, 0.2)',
                borderColor: 'rgba(255, 99, 132, 1)',
                borderWidth: 1,
                fill: false
            }, {
                label: 'Outsourcing',
                data: <?php echo json_encode(array_column(array_slice($monthlyOutsourcingData, 0, 6), 'total')); ?>,
                backgroundColor: 'rgba(153, 102, 255, 0.2)',
                borderColor: 'rgba(153, 102, 255, 1)',
                borderWidth: 1,
                fill: false
            }, {
                label: 'Salaries',
                data: <?php echo json_encode(array_column(array_slice($monthlySalaryData, 0, 6), 'total')); ?>,
                backgroundColor: 'rgba(255, 159, 64, 0.2)',
                borderColor: 'rgba(255, 159, 64, 1)',
                borderWidth: 1,
                fill: false
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            },
            elements: {
                line: {
                    tension: 0.1
                }
            }
        }
    });
</script>
</body>
</html>
