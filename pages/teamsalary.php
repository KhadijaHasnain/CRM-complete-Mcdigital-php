<?php

// Database connection
include '../includes/db_connect.php';
// Fetch data for each specified team member
$team_members = ["Ilyas", "Usman", "Mehrab", "Naqeeb", "Ammar", "Zulqarnain", "Momina", "Hafsa", "Ami", "Mahnoor", "Waqas"];
$sql = "SELECT Users.username, 
        SUM(Payments.amount + IFNULL(Payments.medical, 0) + IFNULL(Payments.bonuses, 0)) AS total_amount 
        FROM Payments 
        JOIN Users ON Payments.team_member_id = Users.id 
        WHERE Users.username IN ('" . implode("','", $team_members) . "')
        GROUP BY Users.username";
$result = $conn->query($sql);

$labels = [];
$amounts = [];

$total_team_amount = 0;

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $labels[] = $row['username'];
        $amounts[] = $row['total_amount'];
        $total_team_amount += $row['total_amount'];
    }
}

// Fetch total amount for all employees
$sql_all = "SELECT Users.username, 
        SUM(Payments.amount + IFNULL(Payments.medical, 0) + IFNULL(Payments.bonuses, 0)) AS total_amount 
        FROM Payments 
        JOIN Users ON Payments.team_member_id = Users.id 
        GROUP BY Users.username";
$result_all = $conn->query($sql_all);

$labels_all = [];
$amounts_all = [];

$total_all_employees_amount = 0;

if ($result_all->num_rows > 0) {
    while ($row = $result_all->fetch_assoc()) {
        $labels_all[] = $row['username'];
        $amounts_all[] = $row['total_amount'];
        $total_all_employees_amount += $row['total_amount'];
    }
}

// Fetch monthly income data for line chart
$monthlyIncomeData = $conn->query("SELECT DATE_FORMAT(date, '%Y-%m') AS month, SUM(amount) AS total FROM FreelancingAccounts GROUP BY month ORDER BY month")->fetch_all(MYSQLI_ASSOC);

$months = [];
$monthly_incomes = [];

if (!empty($monthlyIncomeData)) {
    foreach ($monthlyIncomeData as $data) {
        $months[] = $data['month'];
        $monthly_incomes[] = $data['total'];
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Team Payments Overview</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
  <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet">
  <link href="../css/styles.css" rel="stylesheet">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/js/bootstrap.min.js"></script>
  <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.3/Chart.bundle.min.js"></script>
</head>
<body class="sb-nav-fixed">
<?php include '../includes/header.php';?>

<div id="layoutSidenav">
  <div id="layoutSidenav_nav">
    <?php include '../includes/sidebar.php'; ?>
  </div>
  <div id="layoutSidenav_content">
      <main>
    <div class="container mt-5">
        <h2 class="text-center">Team Payments Overview</h2>
        <canvas id="paymentsChart" width="400" height="200"></canvas>

        <h2 class="text-center mt-5">Monthly Income Trend</h2>
        <canvas id="incomeTrendChart" width="400" height="200"></canvas>

        <h2 class="text-center mt-5">Total Amount for All Employees</h2>
        <canvas id="allEmployeesChart" width="400" height="200"></canvas>

        <h2 class="text-center mt-5">Total Team Amount</h2>
        <div class="text-center">
            <h3>₨ <?php echo number_format($total_team_amount, 2); ?></h3>
        </div>

        <h2 class="text-center mt-5">Total Amount for All Employees</h2>
        <div class="text-center">
            <h3>₨ <?php echo number_format($total_all_employees_amount, 2); ?></h3>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
<script src="assets/demo/chart-area-demo.js"></script>
  <script src="assets/demo/chart-bar-demo.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js" crossorigin="anonymous"></script>
  <script src="../js/datatables-simple-demo.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            var ctx = document.getElementById('paymentsChart').getContext('2d');
            var paymentsChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: <?php echo json_encode($labels); ?>,
                    datasets: [{
                        label: 'Total Amount',
                        data: <?php echo json_encode($amounts); ?>,
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
                    }
                }
            });

            var incomeCtx = document.getElementById('incomeTrendChart').getContext('2d');
            var incomeTrendChart = new Chart(incomeCtx, {
                type: 'line',
                data: {
                    labels: <?php echo json_encode($months); ?>,
                    datasets: [{
                        label: 'Monthly Income',
                        data: <?php echo json_encode($monthly_incomes); ?>,
                        backgroundColor: 'rgba(54, 162, 235, 0.2)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1,
                        fill: false
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

            var allEmployeesCtx = document.getElementById('allEmployeesChart').getContext('2d');
            var allEmployeesChart = new Chart(allEmployeesCtx, {
                type: 'bar',
                data: {
                    labels: <?php echo json_encode($labels_all); ?>,
                    datasets: [{
                        label: 'Total Amount',
                        data: <?php echo json_encode($amounts_all); ?>,
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
        });


        
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
