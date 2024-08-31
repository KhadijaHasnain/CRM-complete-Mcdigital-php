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

// Fetch profit margin data
$profitMarginsData = $conn->query("
    SELECT 
        DATE_FORMAT(date, '%Y-%m') AS month, 
        SUM(amount) AS total_income, 
        (
            SELECT SUM(amount + IFNULL(medical, 0) + IFNULL(bonuses, 0) - IFNULL(tax, 0) - IFNULL(deductions, 0)) 
            FROM Payments 
            WHERE DATE_FORMAT(date, '%Y-%m') = DATE_FORMAT(FreelancingAccounts.date, '%Y-%m')
        ) AS total_salary,
        (
            SELECT SUM(amount) 
            FROM Expenses 
            WHERE DATE_FORMAT(date, '%Y-%m') = DATE_FORMAT(FreelancingAccounts.date, '%Y-%m')
        ) AS total_expenses,
        (
            SELECT SUM(amount) 
            FROM OutsourcingProjects 
            WHERE DATE_FORMAT(date, '%Y-%m') = DATE_FORMAT(FreelancingAccounts.date, '%Y-%m')
        ) AS total_outsourcing
    FROM FreelancingAccounts
    GROUP BY month
    ORDER BY month DESC
")->fetch_all(MYSQLI_ASSOC);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profit Margins</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <a class="navbar-brand" href="dashboard.php">Financial Dashboard</a>
    <div class="collapse navbar-collapse">
        <ul class="navbar-nav ml-auto">
            <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
            <li class="nav-item"><a class="nav-link" href="profit_margins.php">Profit Margins</a></li>
            <li class="nav-item"><a class="nav-link" href="top_performing.php">Top Performing Accounts and Projects</a></li>
            <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
        </ul>
    </div>
</nav>

<div class="container mt-4">
    <h2>Profit Margins</h2>
    <canvas id="profitMarginChart" width="400" height="200"></canvas>
    <table class="table table-bordered table-striped mt-4">
        <thead>
            <tr>
                <th>Month</th>
                <th>Total Income</th>
                <th>Total Expenses</th>
                <th>Total Salary</th>
                <th>Total Outsourcing</th>
                <th>Profit Margin</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $months = [];
            $profitMargins = [];
            foreach ($profitMarginsData as $data): 
                $totalExpenses = $data['total_expenses'] + $data['total_salary'] + $data['total_outsourcing'];
                $profitMargin = $data['total_income'] - $totalExpenses;
                $months[] = $data['month'];
                $profitMargins[] = $profitMargin;
            ?>
            <tr>
                <td><?php echo $data['month']; ?></td>
                <td>₨ <?php echo number_format($data['total_income'], 2); ?></td>
                <td>₨ <?php echo number_format($data['total_expenses'], 2); ?></td>
                <td>₨ <?php echo number_format($data['total_salary'], 2); ?></td>
                <td>₨ <?php echo number_format($data['total_outsourcing'], 2); ?></td>
                <td>₨ <?php echo number_format($profitMargin, 2); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var ctx = document.getElementById('profitMarginChart').getContext('2d');
    var profitMarginChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($months); ?>,
            datasets: [{
                label: 'Profit Margin',
                data: <?php echo json_encode($profitMargins); ?>,
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            },
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    text: 'Monthly Profit Margins'
                }
            }
        }
    });
});
</script>

<script>
    window.addEventListener('DOMContentLoaded', event => {
        // Toggle the side navigation
        const sidebarToggle = document.body.querySelector('#sidebarToggle');
        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', event => {
                event.preventDefault();
                document.body.classList.toggle('sb-sidenav-toggled');
            });
        }
    });
</script>

</body>
</html>
