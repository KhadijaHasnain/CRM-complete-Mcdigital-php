<?php

include '../includes/db_connect.php';

$currentYear = date('Y');

// Fetch overall summary data
$totalIncome = $conn->query("SELECT SUM(amount) AS total FROM FreelancingAccounts")->fetch_assoc()['total'];
$totalExpenses = $conn->query("SELECT SUM(amount) AS total FROM Expenses")->fetch_assoc()['total'];
$totalOutsourcing = $conn->query("SELECT SUM(amount) AS total FROM OutsourcingProjects")->fetch_assoc()['total'];
$totalSalary = $conn->query("SELECT SUM(amount + IFNULL(medical, 0) + IFNULL(bonuses, 0) - IFNULL(tax, 0) - IFNULL(deductions, 0)) AS total FROM Payments")->fetch_assoc()['total'];

// Calculate total expenses including salaries and outsourcing
$totalExpensesIncludingSalariesAndOutsourcing = $totalExpenses + $totalOutsourcing + $totalSalary;
$totalSavings = $totalIncome - $totalExpensesIncludingSalariesAndOutsourcing;

// Fetch total domestic expense
$totalDomesticExpenses = $conn->query("SELECT SUM(amount) AS total FROM Expenses WHERE category = 'Domestic'")->fetch_assoc()['total'];

// Fetch total business expense
$totalBusinessExpenses = $conn->query("SELECT SUM(amount) AS total FROM Expenses WHERE category = 'Business'")->fetch_assoc()['total'];

// Fetch data for current year's monthly summary
$monthlyIncomeData = $conn->query("SELECT DATE_FORMAT(date, '%Y-%m') AS month, SUM(amount) AS total FROM FreelancingAccounts WHERE YEAR(date) = $currentYear GROUP BY month ORDER BY month")->fetch_all(MYSQLI_ASSOC);
$monthlyExpenseData = $conn->query("SELECT DATE_FORMAT(date, '%Y-%m') AS month, SUM(amount) AS total FROM Expenses WHERE YEAR(date) = $currentYear GROUP BY month ORDER BY month")->fetch_all(MYSQLI_ASSOC);
$monthlyOutsourcingData = $conn->query("SELECT DATE_FORMAT(date, '%Y-%m') AS month, SUM(amount) AS total FROM OutsourcingProjects WHERE YEAR(date) = $currentYear GROUP BY month ORDER BY month")->fetch_all(MYSQLI_ASSOC);
$monthlySalaryData = $conn->query("SELECT DATE_FORMAT(date, '%Y-%m') AS month, SUM(amount + IFNULL(medical, 0) + IFNULL(bonuses, 0) - IFNULL(tax, 0) - IFNULL(deductions, 0)) AS total FROM Payments WHERE YEAR(date) = $currentYear GROUP BY month ORDER BY month")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Outsourcing Projects Management</title>
  <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet">
  <link href="../css/styles.css" rel="stylesheet">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/js/bootstrap.bundle.min.js"></script>
  <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.3/Chart.bundle.min.js"></script>
  <style>
    .stat-box {
      border: 1px solid #dee2e6;
      padding: 20px;
      margin: 10px;
      text-align: center;
    }
    .stat-title {
      font-weight: bold;
    }
    .stat-value {
      font-size: 1.5em;
      color: green;
    }
    .card-header {
      font-weight: bold;
      font-size: 1.2em;
    }
  </style>
</head>
<body class="sb-nav-fixed">
<?php include '../includes/header.php';?>

<div id="layoutSidenav">
  <div id="layoutSidenav_nav">
    <?php include '../includes/sidebar.php'; ?>
  </div>
  <div id="layoutSidenav_content">
      <main>
<div class="container s mt-4" >
  <div class="row" >
    <div class="col-md-4">
      <div class="card text-white bg-primary mb-3">
        <div class="card-header">Total Income</div>
        <div class="card-body">
          <h5 class="card-title">₨ <?php echo number_format($totalIncome, 2); ?></h5>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card text-white bg-danger mb-3">
        <div class="card-header">Total Expenses</div>
        <div class="card-body">
          <h5 class="card-title">₨ <?php echo number_format($totalExpensesIncludingSalariesAndOutsourcing, 2); ?></h5>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card text-white bg-success mb-3">
        <div class="card-header">Total Savings</div>
        <div class="card-body">
          <h5 class="card-title">₨ <?php echo number_format($totalSavings, 2); ?></h5>
        </div>
      </div>
    </div>
  </div>
  <div class="row">
    <div class="col-md-4">
      <div class="card text-white bg-info mb-3">
        <div class="card-header">Total Outsourcing</div>
        <div class="card-body">
          <h5 class="card-title">₨ <?php echo number_format($totalOutsourcing, 2); ?></h5>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card text-white bg-secondary mb-3">
        <div class="card-header">Total Salary</div>
        <div class="card-body">
          <h5 class="card-title">₨ <?php echo number_format($totalSalary, 2); ?></h5>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card text-white bg-warning mb-3">
        <div class="card-header">Total Domestic Expenses</div>
        <div class="card-body">
          <h5 class="card-title">₨ <?php echo number_format($totalDomesticExpenses, 2); ?></h5>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card text-white bg-dark mb-3">
        <div class="card-header">Total Business Expenses</div>
        <div class="card-body">
          <h5 class="card-title">₨ <?php echo number_format($totalBusinessExpenses, 2); ?></h5>
        </div>
      </div>
    </div>
  </div>
  
  
</div>
<!-- Combined Monthly Summary Chart -->
<div class="row">
    <div class="col-md-12">
      <canvas id="monthlySummaryChart"></canvas>
    </div>
  </div>
<script>
  document.addEventListener('DOMContentLoaded', function () {
    const ctx = document.getElementById('monthlySummaryChart').getContext('2d');
    const monthlySummaryChart = new Chart(ctx, {
      type: 'line',
      data: {
        labels: <?php echo json_encode(array_column($monthlyIncomeData, 'month')); ?>,
        datasets: [
          {
            label: 'Income',
            data: <?php echo json_encode(array_column($monthlyIncomeData, 'total')); ?>,
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
            borderColor: 'rgba(75, 192, 192, 1)',
            borderWidth: 1,
            fill: false
          },
          {
            label: 'Expenses',
            data: <?php echo json_encode(array_column($monthlyExpenseData, 'total')); ?>,
            backgroundColor: 'rgba(255, 99, 132, 0.2)',
            borderColor: 'rgba(255, 99, 132, 1)',
            borderWidth: 1,
            fill: false
          },
          {
            label: 'Outsourcing',
            data: <?php echo json_encode(array_column($monthlyOutsourcingData, 'total')); ?>,
            backgroundColor: 'rgba(54, 162, 235, 0.2)',
            borderColor: 'rgba(54, 162, 235, 1)',
            borderWidth: 1,
            fill: false
          },
          {
            label: 'Salary',
            data: <?php echo json_encode(array_column($monthlySalaryData, 'total')); ?>,
            backgroundColor: 'rgba(153, 102, 255, 0.2)',
            borderColor: 'rgba(153, 102, 255, 1)',
            borderWidth: 1,
            fill: false
          }
        ]
      },
      options: {
        scales: {
          yAxes: [{
            ticks: {
              beginAtZero: true
            }
          }]
        },
        title: {
          display: true,
          text: 'Monthly Summary for the Current Year'
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
