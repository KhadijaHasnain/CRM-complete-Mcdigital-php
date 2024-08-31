<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}
?>  

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Financial Dashboard</title>
  <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
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
<body>
  <?php
  // Database connection
  $conn = new mysqli('localhost', 'root', '', 'freelance_bussiness');

  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  // Fetch summary data
  $totalIncome = $conn->query("SELECT SUM(amount) AS total FROM FreelancingAccounts")->fetch_assoc()['total'];
  $totalExpenses = $conn->query("SELECT SUM(amount) AS total FROM Expenses")->fetch_assoc()['total'];
  
  // Fetch total outsourcing amount
  $totalOutsourcing = $conn->query("SELECT SUM(amount) AS total FROM OutsourcingProjects")->fetch_assoc()['total'];

  // Calculate total savings
  $totalSavings = $totalIncome - $totalExpenses - $totalOutsourcing;

  // Fetch data for charts
  $expensesData = $conn->query("SELECT category, SUM(amount) AS total FROM Expenses GROUP BY category")->fetch_all(MYSQLI_ASSOC);
  $monthlyIncomeData = $conn->query("SELECT DATE_FORMAT(date, '%Y-%m') AS month, SUM(amount) AS total FROM FreelancingAccounts GROUP BY month ORDER BY month")->fetch_all(MYSQLI_ASSOC);
  $monthlyExpenseData = $conn->query("SELECT DATE_FORMAT(date, '%Y-%m') AS month, SUM(amount) AS total FROM Expenses GROUP BY month ORDER BY month")->fetch_all(MYSQLI_ASSOC);

  // Fetch total domestic expense
  $domesticExpenseResult = $conn->query("SELECT SUM(amount) AS total FROM Expenses WHERE category = 'Domestic'");
  $domesticExpense = $domesticExpenseResult->fetch_assoc()['total'];

  // Fetch total business expense
  $businessExpenseResult = $conn->query("SELECT SUM(amount) AS total FROM Expenses WHERE category = 'Business'");
  $businessExpense = $businessExpenseResult->fetch_assoc()['total'];

  // Fetch total salary
  $salaryResult = $conn->query("SELECT SUM(amount + medical + bonuses - tax - deductions) AS total FROM Payments");
  $totalSalary = $salaryResult->fetch_assoc()['total'];

  // Fetch total outsourcing for each platform
  $outsourcingResults = $conn->query("SELECT platform, SUM(amount) AS total FROM OutsourcingProjects GROUP BY platform");

  // Fetch total amounts for each account
  $accountResults = $conn->query("SELECT account_name, SUM(amount) AS total FROM FreelancingAccounts GROUP BY account_name");

  // Fetch data for outsourcing chart
  $outsourcingData = $conn->query("SELECT platform, SUM(amount) AS total FROM OutsourcingProjects GROUP BY platform")->fetch_all(MYSQLI_ASSOC);

  // Fetch data for freelancing accounts chart
  $freelancingAccountsData = $conn->query("SELECT account_name, SUM(amount) AS total FROM FreelancingAccounts GROUP BY account_name")->fetch_all(MYSQLI_ASSOC);

  // Fetch data for team member salary chart
  $teamMemberSalaryData = $conn->query("SELECT Users.username, DATE_FORMAT(Payments.date, '%Y-%m') AS month, SUM(Payments.amount + Payments.medical + Payments.bonuses - Payments.tax - Payments.deductions) AS total FROM Payments JOIN Users ON Payments.team_member_id = Users.id GROUP BY Users.username, month ORDER BY month, Users.username")->fetch_all(MYSQLI_ASSOC);

  // Fetch data for monthly outsourcing projects
  $monthlyOutsourcingData = $conn->query("SELECT DATE_FORMAT(date, '%Y-%m') AS month, SUM(amount) AS total FROM OutsourcingProjects GROUP BY month ORDER BY month")->fetch_all(MYSQLI_ASSOC);

  // Fetch totals for each subcategory in Domestic
  $domesticSubcategories = $conn->query("SELECT subcategory, SUM(amount) AS total FROM Expenses WHERE category = 'Domestic' GROUP BY subcategory")->fetch_all(MYSQLI_ASSOC);

  // Fetch totals for each subcategory in Business
  $businessSubcategories = $conn->query("SELECT subcategory, SUM(amount) AS total FROM Expenses WHERE category = 'Business' GROUP BY subcategory")->fetch_all(MYSQLI_ASSOC);
  ?>

  <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <a class="navbar-brand" href="#">Financial Dashboard</a>
    <div class="collapse navbar-collapse">
      <ul class="navbar-nav ml-auto">
        <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
        <li class="nav-item"><a class="nav-link" href="accounts.php">Freelancing Accounts</a></li>
        <li class="nav-item"><a class="nav-link" href="payments.php">Payments</a></li>
        <li class="nav-item"><a class="nav-link" href="outsourcing.php">Outsourcing Projects</a></li>
        <li class="nav-item"><a class="nav-link" href="expenses.php">Expenses</a></li>
        <li class="nav-item"><a class="nav-link" href="reports.php">Reports</a></li>
        <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
      </ul>
    </div>
  </nav>

  <div class="container mt-4">
 

    <div class="row">
      <div class="col-md-4">
        <div class="card text-white bg-primary mb-3">
          <div class="card-header">Total Income</div>
          <div class="card-body">
            <h5 class="card-title">₨ <?php echo number_format($totalIncome, ); ?></h5>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card text-white bg-danger mb-3">
          <div class="card-header">Total Expenses</div>
          <div class="card-body">
            <h5 class="card-title">₨ <?php echo number_format($totalExpenses, ); ?></h5>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card text-white bg-success mb-3">
          <div class="card-header">Total Savings</div>
          <div class="card-body">
            <h5 class="card-title">₨ <?php echo number_format($totalSavings, ); ?></h5>
          </div>
        </div>
      </div>
    </div>

    


    <div class="row">
      <div class="col-md-6">
        <div class="card mb-3">
          <div class="card-header">Expense Distribution</div>
          <div class="card-body">
            <canvas id="expenseChart"></canvas>
          </div>
        </div>
      </div>
      <div class="col-md-6">
        <div class="card mb-3">
          <div class="card-header">Income vs Expenses</div>
          <div class="card-body">
            <canvas id="incomeExpenseChart"></canvas>
          </div>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-md-6">
        <div class="card mb-3">
          <div class="card-header">Monthly Income</div>
          <div class="card-body">
            <canvas id="monthlyIncomeChart"></canvas>
          </div>
        </div>
      </div>
      <div class="col-md-6">
        <div class="card mb-3">
          <div class="card-header">Monthly Expenses</div>
          <div class="card-body">
            <canvas id="monthlyExpenseChart"></canvas>
          </div>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-md-6">
        <div class="card mb-3">
          <div class="card-header">Freelancing Accounts Earning</div>
          <div class="card-body">
            <canvas id="freelancingAccountsChart"></canvas>
          </div>
        </div>
      </div>
      <div class="col-md-6">
        <div class="card mb-3">
          <div class="card-header">Outsourcing Projects Cost</div>
          <div class="card-body">
            <canvas id="outsourcingChart"></canvas>
          </div>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-md-6">
        <div class="card mb-3">
          <div class="card-header">Monthly Income vs Monthly Expenses</div>
          <div class="card-body">
            <canvas id="monthlyIncomeExpenseChart"></canvas>
          </div>
        </div>
      </div>
      <div class="col-md-6">
        <div class="card mb-3">
          <div class="card-header">Team Member Salary by Month</div>
          <div class="card-body">
            <canvas id="teamMemberSalaryChart"></canvas>
          </div>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-md-12">
        <div class="card mb-3">
          <div class="card-header">Monthly Outsourcing Projects</div>
          <div class="card-body">
            <canvas id="monthlyOutsourcingChart"></canvas>
          </div>
        </div>
      </div>  
    </div>

    <div class="container">
  <div class="row">
    <div class="col-md-6">
      <div class="card mb-3">
        <div class="card-header">Domestic Expenses</div>
        <div class="card-body">
          <table class="table">
            <thead>
              <tr>
                <th>Subcategory</th>
                <th>Total</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($domesticSubcategories as $subcategory): ?>
              <tr>
                <td><?php echo $subcategory['subcategory']; ?></td>
                <td>₨ <?php echo number_format($subcategory['total'], ); ?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
    <div class="col-md-6">
      <div class="card mb-3">
        <div class="card-header">Business Expenses</div>
        <div class="card-body">
          <table class="table">
            <thead>
              <tr>
                <th>Subcategory</th>
                <th>Total</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($businessSubcategories as $subcategory): ?>
              <tr>
                <td><?php echo $subcategory['subcategory']; ?></td>
                <td>₨ <?php echo number_format($subcategory['total'], ); ?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>


  <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
  <script>
    var ctx1 = document.getElementById('expenseChart').getContext('2d');
    var expenseChart = new Chart(ctx1, {
      type: 'pie',
      data: {
        labels: <?php echo json_encode(array_column($expensesData, 'category')); ?>,
        datasets: [{
          data: <?php echo json_encode(array_column($expensesData, 'total')); ?>,
          backgroundColor: ['#007bff', '#dc3545', '#ffc107', '#28a745', '#17a2b8', '#6c757d']
        }]
      }
    });

    var ctx2 = document.getElementById('incomeExpenseChart').getContext('2d');
    var incomeExpenseChart = new Chart(ctx2, {
      type: 'bar',
      data: {
        labels: ['Total Income', 'Total Expenses'],
        datasets: [{
          label: 'Amount',
          data: [<?php echo $totalIncome; ?>, <?php echo $totalExpenses; ?>],
          backgroundColor: ['#28a745', '#dc3545']
        }]
      },
      options: {
        scales: {
          yAxes: [{
            ticks: {
              beginAtZero: true
            }
          }]
        }
      }
    });

    var ctx3 = document.getElementById('monthlyIncomeChart').getContext('2d');
    var monthlyIncomeChart = new Chart(ctx3, {
      type: 'line',
      data: {
        labels: <?php echo json_encode(array_column($monthlyIncomeData, 'month')); ?>,
        datasets: [{
          label: 'Monthly Income',
          data: <?php echo json_encode(array_column($monthlyIncomeData, 'total')); ?>,
          backgroundColor: 'rgba(75, 192, 192, 0.2)',
          borderColor: 'rgba(75, 192, 192, 1)',
          borderWidth: 1,
          fill: false
        }]
      },
      options: {
        scales: {
          yAxes: [{
            ticks: {
              beginAtZero: true
            }
          }]
        }
      }
    });

    var ctx4 = document.getElementById('monthlyExpenseChart').getContext('2d');
    var monthlyExpenseChart = new Chart(ctx4, {
      type: 'line',
      data: {
        labels: <?php echo json_encode(array_column($monthlyExpenseData, 'month')); ?>,
        datasets: [{
          label: 'Monthly Expenses',
          data: <?php echo json_encode(array_column($monthlyExpenseData, 'total')); ?>,
          backgroundColor: 'rgba(255, 99, 132, 0.2)',
          borderColor: 'rgba(255, 99, 132, 1)',
          borderWidth: 1,
          fill: false
        }]
      },
      options: {
        scales: {
          yAxes: [{
            ticks: {
              beginAtZero: true
            }
          }]
        }
      }
    });

    var ctx5 = document.getElementById('freelancingAccountsChart').getContext('2d');
    var freelancingAccountsChart = new Chart(ctx5, {
      type: 'bar',
      data: {
        labels: <?php echo json_encode(array_column($freelancingAccountsData, 'account_name')); ?>,
        datasets: [{
          label: 'Total Amount',
          data: <?php echo json_encode(array_column($freelancingAccountsData, 'total')); ?>,
          backgroundColor: ['#007bff', '#28a745', '#ffc107', '#dc3545', '#17a2b8', '#6c757d']
        }]
      },
      options: {
        scales: {
          yAxes: [{
            ticks: {
              beginAtZero: true
            }
          }]
        }
      }
    });

    var ctx6 = document.getElementById('outsourcingChart').getContext('2d');
    var outsourcingChart = new Chart(ctx6, {
      type: 'bar',
      data: {
        labels: <?php echo json_encode(array_column($outsourcingData, 'platform')); ?>,
        datasets: [{
          label: 'Outsourcing Amount',
          data: <?php echo json_encode(array_column($outsourcingData, 'total')); ?>,
          backgroundColor: ['#007bff', '#28a745', '#ffc107', '#dc3545']
        }]
      },
      options: {
        scales: {
          yAxes: [{
            ticks: {
              beginAtZero: true
            }
          }]
        }
      }
    });

    var ctx7 = document.getElementById('monthlyIncomeExpenseChart').getContext('2d');
    var monthlyIncomeExpenseChart = new Chart(ctx7, {
      type: 'line',
      data: {
        labels: <?php echo json_encode(array_column($monthlyIncomeData, 'month')); ?>,
        datasets: [
          {
            label: 'Monthly Income',
            data: <?php echo json_encode(array_column($monthlyIncomeData, 'total')); ?>,
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
            borderColor: 'rgba(75, 192, 192, 1)',
            borderWidth: 1,
            fill: false
          },
          {
            label: 'Monthly Expenses',
            data: <?php echo json_encode(array_column($monthlyExpenseData, 'total')); ?>,
            backgroundColor: 'rgba(255, 99, 132, 0.2)',
            borderColor: 'rgba(255, 99, 132, 1)',
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
        }
      }
    });

    var ctx8 = document.getElementById('teamMemberSalaryChart').getContext('2d');
    var teamMemberSalaryChart = new Chart(ctx8, {
      type: 'line',
      data: {
        labels: <?php echo json_encode(array_values(array_unique(array_column($teamMemberSalaryData, 'month')))); ?>,
        datasets: <?php
          $teamMembers = [];
          foreach ($teamMemberSalaryData as $data) {
            if (!isset($teamMembers[$data['username']])) {
              $teamMembers[$data['username']] = [
                'label' => $data['username'],
                'data' => [],
                'backgroundColor' => 'rgba(0, 123, 255, 0.2)',
                'borderColor' => 'rgba(0, 123, 255, 1)',
                'borderWidth' => 1,
                'fill' => false
              ];
            }
            $teamMembers[$data['username']]['data'][] = $data['total'];
          }
          echo json_encode(array_values($teamMembers));
        ?>
      },
      options: {
        scales: {
          yAxes: [{
            ticks: {
              beginAtZero: true
            }
          }]
        }
      }
    });

    var ctx9 = document.getElementById('monthlyOutsourcingChart').getContext('2d');
    var monthlyOutsourcingChart = new Chart(ctx9, {
      type: 'line',
      data: {
        labels: <?php echo json_encode(array_column($monthlyOutsourcingData, 'month')); ?>,
        datasets: [{
          label: 'Monthly Outsourcing Projects',
          data: <?php echo json_encode(array_column($monthlyOutsourcingData, 'total')); ?>,
          backgroundColor: 'rgba(153, 102, 255, 0.2)',
          borderColor: 'rgba(153, 102, 255, 1)',
          borderWidth: 1,
          fill: false
        }]
      },
      options: {
        scales: {
          yAxes: [{
            ticks: {
              beginAtZero: true
            }
          }]
        }
      }
    });
  </script>
</body>
</html>
