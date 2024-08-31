<?php

include '../includes/db_connect.php';



session_start();
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}


// Determine the selected period (monthly or yearly)
$viewPeriod = isset($_GET['view']) ? $_GET['view'] : 'monthly';

// Fetch overall summary data
$totalIncome = $conn->query("SELECT SUM(amount) AS total FROM FreelancingAccounts")->fetch_assoc()['total'];
$totalExpenses = $conn->query("SELECT SUM(amount) AS total FROM Expenses")->fetch_assoc()['total'];
$totalOutsourcing = $conn->query("SELECT SUM(amount) AS total FROM OutsourcingProjects")->fetch_assoc()['total'];

// Fetch total salary
$teamSummaryData = $conn->query("SELECT Users.username, 
                                SUM(Payments.amount) AS total_salary, 
                                SUM(Payments.medical) AS total_medical, 
                                SUM(Payments.bonuses) AS total_bonuses, 
                                SUM(Payments.tax) AS total_tax, 
                                SUM(Payments.deductions) AS total_deductions, 
                                (SUM(Payments.amount) + IFNULL(SUM(Payments.medical), 0) + IFNULL(SUM(Payments.bonuses), 0) - IFNULL(SUM(Payments.tax), 0) - IFNULL(SUM(Payments.deductions), 0)) AS total 
                                FROM Payments 
                                JOIN Users ON Payments.team_member_id = Users.id 
                                GROUP BY Users.username 
                                ORDER BY total_salary DESC")->fetch_all(MYSQLI_ASSOC);

$totalSalary = array_sum(array_column($teamSummaryData, 'total'));

// Calculate total expenses including salaries and outsourcing
$totalExpensesIncludingSalariesAndOutsourcing = $totalExpenses + $totalOutsourcing + $totalSalary;

$totalSavings = $totalIncome - $totalExpensesIncludingSalariesAndOutsourcing;

// Fetch total domestic expense
$totalDomesticExpenses = $conn->query("SELECT SUM(amount) AS total FROM Expenses WHERE category = 'Domestic'")->fetch_assoc()['total'];

// Fetch total business expense
$totalBusinessExpenses = $conn->query("SELECT SUM(amount) AS total FROM Expenses WHERE category = 'Business'")->fetch_assoc()['total'];

// Fetch data based on the selected period
if ($viewPeriod == 'yearly') {
    $monthlyIncomeData = $conn->query("SELECT DATE_FORMAT(date, '%Y') AS period, SUM(amount) AS total FROM FreelancingAccounts GROUP BY period ORDER BY period")->fetch_all(MYSQLI_ASSOC);
    $monthlyExpenseData = $conn->query("SELECT DATE_FORMAT(date, '%Y') AS period, SUM(amount) AS total FROM Expenses GROUP BY period ORDER BY period")->fetch_all(MYSQLI_ASSOC);
    $monthlyOutsourcingData = $conn->query("SELECT DATE_FORMAT(date, '%Y') AS period, SUM(amount) AS total FROM OutsourcingProjects GROUP BY period ORDER BY period")->fetch_all(MYSQLI_ASSOC);
    $monthlySalaryData = $conn->query("SELECT DATE_FORMAT(date, '%Y') AS period, SUM(amount + IFNULL(medical, 0) + IFNULL(bonuses, 0) - IFNULL(tax, 0) - IFNULL(deductions, 0)) AS total FROM Payments GROUP BY period ORDER BY period")->fetch_all(MYSQLI_ASSOC);
} else {
    $monthlyIncomeData = $conn->query("SELECT DATE_FORMAT(date, '%Y-%m') AS period, SUM(amount) AS total FROM FreelancingAccounts GROUP BY period ORDER BY period")->fetch_all(MYSQLI_ASSOC);
    $monthlyExpenseData = $conn->query("SELECT DATE_FORMAT(date, '%Y-%m') AS period, SUM(amount) AS total FROM Expenses GROUP BY period ORDER BY period")->fetch_all(MYSQLI_ASSOC);
    $monthlyOutsourcingData = $conn->query("SELECT DATE_FORMAT(date, '%Y-%m') AS period, SUM(amount) AS total FROM OutsourcingProjects GROUP BY period ORDER BY period")->fetch_all(MYSQLI_ASSOC);
    $monthlySalaryData = $conn->query("SELECT DATE_FORMAT(date, '%Y-%m') AS period, SUM(amount + IFNULL(medical, 0) + IFNULL(bonuses, 0) - IFNULL(tax, 0) - IFNULL(deductions, 0)) AS total FROM Payments GROUP BY period ORDER BY period")->fetch_all(MYSQLI_ASSOC);
}



// Fetch data for charts
$expensesData = $conn->query("SELECT category, SUM(amount) AS total FROM Expenses GROUP BY category")->fetch_all(MYSQLI_ASSOC);
$outsourcingData = $conn->query("SELECT platform, SUM(amount) AS total FROM OutsourcingProjects GROUP BY platform")->fetch_all(MYSQLI_ASSOC);
$freelancingAccountsData = $conn->query("SELECT account_name, SUM(amount) AS total FROM FreelancingAccounts GROUP BY account_name")->fetch_all(MYSQLI_ASSOC);
$teamMemberSalaryData = $conn->query("SELECT Users.username, DATE_FORMAT(Payments.date, '%Y-%m') AS month, SUM(Payments.amount + Payments.medical + Payments.bonuses - Payments.tax - Payments.deductions) AS total FROM Payments JOIN Users ON Payments.team_member_id = Users.id GROUP BY Users.username, month ORDER BY month, Users.username")->fetch_all(MYSQLI_ASSOC);
$domesticSubcategories = $conn->query("SELECT subcategory, SUM(amount) AS total FROM Expenses WHERE category = 'Domestic' GROUP BY subcategory")->fetch_all(MYSQLI_ASSOC);
$businessSubcategories = $conn->query("SELECT subcategory, SUM(amount) AS total FROM Expenses WHERE category = 'Business' GROUP BY subcategory")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>Financial Dashboard</title>

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
  <style>
    .stat-box {

      padding: 20px;
      margin: 10px;
      text-align: center;
    }
    .stat-title {
      font-weight: bold;
    }
    .stat-value {
      font-size: 1.5em;
      color: white;
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
    
        <div class="container-fluid px-4">

<!-- One row section for quick actions -->

<?php
include "summary.php";
?>



<?php
include "monthlydata.php";
?>

      

          <div class="d-flex justify-content-between mb-3">
            <h2>Overall Summary</h2>
            <div>
              <select id="viewToggle" class="form-control" onchange="changeView()">
                <option value="monthly" <?php echo $viewPeriod == 'monthly' ? 'selected' : ''; ?>>Monthly</option>
                <option value="yearly" <?php echo $viewPeriod == 'yearly' ? 'selected' : ''; ?>>Yearly</option>
              </select>
            </div>
          </div>
          <div class="row">
            <div class="col-xl-3 col-md-6">
              <div class="card bg-primary text-white mb-4">
                <div class="card-body">Total Income</div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                  <h5 class="card-title">₨ <?php echo number_format($totalIncome, 2); ?></h5>
                </div>
              </div>
            </div>
            <div class="col-xl-3 col-md-6">
              <div class="card bg-warning text-white mb-4">
                <div class="card-body">Total Expenses</div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                  <h5 class="card-title">₨ <?php echo number_format($totalExpensesIncludingSalariesAndOutsourcing, 2); ?></h5>
                </div>
              </div>
            </div>
            <div class="col-xl-3 col-md-6">
              <div class="card bg-danger text-white mb-4">
                <div class="card-body">Total Outsourcing</div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                  <h5 class="card-title">₨ <?php echo number_format($totalOutsourcing, 2); ?></h5>
                </div>
              </div>
            </div>
            <div class="col-xl-3 col-md-6">
              <div class="card bg-success text-white mb-4">
                <div class="card-body">Total Savings</div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                  <h5 class="card-title">₨ <?php echo number_format($totalSavings, 2); ?></h5>
                </div>
              </div>
            </div>
          </div>

        

          <div class="row">
    <div class="col-xl-6">
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-chart-line me-1"></i>
                Monthly Income vs Monthly Expenses
            </div>
            <div class="card-body">
                <canvas id="monthlyIncomeExpenseChart" width="100%" height="40"></canvas>
            </div>
        </div>
    </div>

    <div class="col-xl-6">
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-chart-line me-1"></i>
                Monthly Outsourcing Projects
            </div>
            <div class="card-body">
                <canvas id="monthlyOutsourcingChart" width="100%" height="40"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row">
            <div class="col-xl-6">
              <div class="card mb-4">
                <div class="card-header">
                  <i class="fas fa-chart-area me-1"></i>
                  Expense Distribution
                </div>
                <div class="card-body"><canvas id="expenseChart" width="100%" height="40"></canvas></div>
              </div>
            </div>
            <div class="col-xl-6">
              <div class="card mb-4">
                <div class="card-header">
                  <i class="fas fa-chart-bar me-1"></i>
                  Income vs Expenses
                </div>
                <div class="card-body"><canvas id="incomeExpenseChart" width="100%" height="40"></canvas></div>
              </div>
            </div>
          </div>


          <div class="row">
            <div class="col-xl-6">
              <div class="card mb-4">
                <div class="card-header">
                  <i class="fas fa-chart-bar me-1"></i>
                  Freelancing Accounts Earning
                </div>
                <div class="card-body"><canvas id="freelancingAccountsChart" width="100%" height="40"></canvas></div>
              </div>
            </div>
            <div class="col-xl-6">
              <div class="card mb-4">
                <div class="card-header">
                  <i class="fas fa-chart-bar me-1"></i>
                  Outsourcing Projects Cost
                </div>
                <div class="card-body"><canvas id="outsourcingChart" width="100%" height="40"></canvas></div>
              </div>
            </div>
          </div>

          <div class="row">
            



            <div class="col-xl-12">
              <div class="card mb-4">
                <div class="card-header">
                  <i class="fas fa-chart-line me-1"></i>
                  Team Member Salary by Month
                </div>
                <div class="card-body"><canvas id="teamMemberSalaryChart" width="100%" height="40"></canvas></div>
              </div>
            </div>
          </div>

        

         

        </div>
      </main>
      <footer class="py-4 bg-light mt-auto">
        <div class="container-fluid px-4">
          <div class="d-flex align-items-center justify-content-between small">
            <div class="text-muted">Copyright &copy; Your Website 2023</div>
            <div>
              <a href="#">Privacy Policy</a>
              &middot;
              <a href="#">Terms &amp; Conditions</a>
            </div>
          </div>
        </div>
      </footer>
    </div>
  </div>
  <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
  <script src="../js/scripts.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js" crossorigin="anonymous"></script>
  <script src="assets/demo/chart-area-demo.js"></script>
  <script src="assets/demo/chart-bar-demo.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js" crossorigin="anonymous"></script>
  <script src="../js/datatables-simple-demo.js"></script>
  <script>
    function changeView() {
      var view = document.getElementById('viewToggle').value;
      window.location.href = 'dashboard.php?view=' + view;
    }

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
          data: [<?php echo $totalIncome; ?>, <?php echo $totalExpensesIncludingSalariesAndOutsourcing; ?>],
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


    var ctx9 = document.getElementById('monthlyOutsourcingChart').getContext('2d');
    var monthlyOutsourcingChart = new Chart(ctx9, {
      type: 'line',
      data: {
        labels: <?php echo json_encode(array_column($monthlyOutsourcingData, 'period')); ?>,
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


    var ctx7 = document.getElementById('monthlyIncomeExpenseChart').getContext('2d');
var monthlyIncomeExpenseChart = new Chart(ctx7, {
  type: 'line',
  data: {
    labels: <?php echo json_encode(array_column($monthlyIncomeData, 'period')); ?>,
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
      },
      {
        label: 'Monthly Outsourcing',
        data: <?php echo json_encode(array_column($monthlyOutsourcingData, 'total')); ?>,
        backgroundColor: 'rgba(153, 102, 255, 0.2)',
        borderColor: 'rgba(153, 102, 255, 1)',
        borderWidth: 1,
        fill: false
      },
      {
        label: 'Monthly Salary',
        data: <?php echo json_encode(array_column($monthlySalaryData, 'total')); ?>,
        backgroundColor: 'rgba(54, 162, 235, 0.2)',
        borderColor: 'rgba(54, 162, 235, 1)',
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


    var ctx10 = document.getElementById('totalIncomeExpenseChart').getContext('2d');
    var totalIncomeExpenseChart = new Chart(ctx10, {
      type: 'bar',
      data: {
        labels: ['Total Income', 'Total Expenses'],
        datasets: [{
          label: 'Amount',
          data: [<?php echo $totalIncome; ?>, <?php echo $totalExpensesIncludingSalariesAndOutsourcing; ?>],
          backgroundColor: ['rgba(75, 192, 192, 0.2)', 'rgba(255, 99, 132, 0.2)'],
          borderColor: ['rgba(75, 192, 192, 1)', 'rgba(255, 99, 132, 1)'],
          borderWidth: 1
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
