<?php
include '../includes/db_connect.php';

session_start();
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

// Determine the selected period (monthly or yearly)
$viewPeriod = isset($_GET['view']) ? $_GET['view'] : 'monthly';

// Helper function to get the start date based on the filter
function getStartDate($filter) {
    switch ($filter) {
        case 'last_week':
            return date('Y-m-d', strtotime('-1 week'));
        case 'last_2_weeks':
            return date('Y-m-d', strtotime('-2 weeks'));
        case 'last_month':
            return date('Y-m-d', strtotime('-1 month'));
        case 'last_3_months':
            return date('Y-m-d', strtotime('-3 months'));
        case 'last_6_months':
            return date('Y-m-d', strtotime('-6 months'));
        case 'last_year':
            return date('Y-m-d', strtotime('-1 year'));
        case 'last_2_years':
            return date('Y-m-d', strtotime('-2 years'));
        case 'last_3_years':
            return date('Y-m-d', strtotime('-3 years'));
        default:
            return date('Y-m-01'); // Default to current month
    }
}

// Get the selected filter from the request
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'current_month';
$startDate = getStartDate($filter);
$endDate = date('Y-m-d');

// Fetch overall summary data
$totalIncome = $conn->query("SELECT SUM(amount) AS total FROM FreelancingAccounts WHERE date BETWEEN '$startDate' AND '$endDate'")->fetch_assoc()['total'];
$totalExpenses = $conn->query("SELECT SUM(amount) AS total FROM Expenses WHERE date BETWEEN '$startDate' AND '$endDate'")->fetch_assoc()['total'];
$totalOutsourcing = $conn->query("SELECT SUM(amount) AS total FROM OutsourcingProjects WHERE date BETWEEN '$startDate' AND '$endDate'")->fetch_assoc()['total'];


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
                                WHERE Payments.date BETWEEN '$startDate' AND '$endDate'
                                GROUP BY Users.username 
                                ORDER BY total_salary DESC")->fetch_all(MYSQLI_ASSOC);

$totalSalary = array_sum(array_column($teamSummaryData, 'total'));

// Calculate total expenses including salaries and outsourcing
$totalExpensesIncludingSalariesAndOutsourcing = $totalExpenses + $totalOutsourcing + $totalSalary;
$totalSavings = $totalIncome - $totalExpensesIncludingSalariesAndOutsourcing;

// Fetch total domestic and business expenses
$totalDomesticExpenses = $conn->query("SELECT SUM(amount) AS total FROM Expenses WHERE category = 'Domestic' AND date BETWEEN '$startDate' AND '$endDate'")->fetch_assoc()['total'];
$totalBusinessExpenses = $conn->query("SELECT SUM(amount) AS total FROM Expenses WHERE category = 'Business' AND date BETWEEN '$startDate' AND '$endDate'")->fetch_assoc()['total'];

// Fetch data for charts based on the selected period
if ($viewPeriod == 'yearly') {
    $periodFormat = '%Y';
} else {
    $periodFormat = '%Y-%m';
}

$monthlyIncomeData = $conn->query("SELECT DATE_FORMAT(date, '$periodFormat') AS period, SUM(amount) AS total FROM FreelancingAccounts WHERE date BETWEEN '$startDate' AND '$endDate' GROUP BY period ORDER BY period")->fetch_all(MYSQLI_ASSOC);
$monthlyExpenseData = $conn->query("SELECT DATE_FORMAT(date, '$periodFormat') AS period, SUM(amount) AS total FROM Expenses WHERE date BETWEEN '$startDate' AND '$endDate' GROUP BY period ORDER BY period")->fetch_all(MYSQLI_ASSOC);
$monthlyOutsourcingData = $conn->query("SELECT DATE_FORMAT(date, '$periodFormat') AS period, SUM(amount) AS total FROM OutsourcingProjects WHERE date BETWEEN '$startDate' AND '$endDate' GROUP BY period ORDER BY period")->fetch_all(MYSQLI_ASSOC);
$monthlySalaryData = $conn->query("SELECT DATE_FORMAT(date, '$periodFormat') AS period, SUM(amount + IFNULL(medical, 0) + IFNULL(bonuses, 0) - IFNULL(tax, 0) - IFNULL(deductions, 0)) AS total FROM Payments WHERE date BETWEEN '$startDate' AND '$endDate' GROUP BY period ORDER BY period")->fetch_all(MYSQLI_ASSOC);

// Fetch data for other charts
$expensesData = $conn->query("SELECT category, SUM(amount) AS total FROM Expenses WHERE date BETWEEN '$startDate' AND '$endDate' GROUP BY category")->fetch_all(MYSQLI_ASSOC);
$outsourcingData = $conn->query("SELECT platform, SUM(amount) AS total FROM OutsourcingProjects WHERE date BETWEEN '$startDate' AND '$endDate' GROUP BY platform")->fetch_all(MYSQLI_ASSOC);
$freelancingAccountsData = $conn->query("SELECT account_name, SUM(amount) AS total FROM FreelancingAccounts WHERE date BETWEEN '$startDate' AND '$endDate' GROUP BY account_name")->fetch_all(MYSQLI_ASSOC);
$teamMemberSalaryData = $conn->query("SELECT Users.username, DATE_FORMAT(Payments.date, '$periodFormat') AS month, SUM(Payments.amount + Payments.medical + Payments.bonuses - Payments.tax - Payments.deductions) AS total FROM Payments JOIN Users ON Payments.team_member_id = Users.id WHERE Payments.date BETWEEN '$startDate' AND '$endDate' GROUP BY Users.username, month ORDER BY month, Users.username")->fetch_all(MYSQLI_ASSOC);

// Fetch goals for the current filter period
$goalQuery = "SELECT * FROM MonthlyGoals WHERE CONCAT(year, '-', LPAD(month, 2, '0')) BETWEEN DATE_FORMAT('$startDate', '%Y-%m') AND DATE_FORMAT('$endDate', '%Y-%m')";
$goals = $conn->query($goalQuery);

if (!$goals) {
    die("Query Failed: " . $conn->error);
}

$goalData = [];
while ($row = $goals->fetch_assoc()) {
    $goalData[] = $row;
}
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
    .progress {
      height: 25px;
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
      <div class="container-fluid   mt-4 px-4">
       <div class="container-fluid mt-4 px-4">
  <div class="d-flex justify-content-between mb-3 sticky-top" style="top: 0; background-color: white; z-index: 1000;">
    <div>
      <h5 class="card-header ">
        <i class="fas fa-calendar-alt me-1"></i>
        Current Summary (<?php echo ucfirst(str_replace('_', ' ', $filter)); ?>)
      </h5>
    </div>
    <div>
      <select id="filter" class="form-control" onchange="changeFilter()">
        <option value="current_month" <?php echo $filter == 'current_month' ? 'selected' : ''; ?>>Current Month</option>
        <option value="last_week" <?php echo $filter == 'last_week' ? 'selected' : ''; ?>>Last Week</option>
        <option value="last_2_weeks" <?php echo $filter == 'last_2_weeks' ? 'selected' : ''; ?>>Last 2 Weeks</option>
        <option value="last_month" <?php echo $filter == 'last_month' ? 'selected' : ''; ?>>Last Month</option>
        <option value="last_3_months" <?php echo $filter == 'last_3_months' ? 'selected' : ''; ?>>Last 3 Months</option>
        <option value="last_6_months" <?php echo $filter == 'last_6_months' ? 'selected' : ''; ?>>Last 6 Months</option>
        <option value="last_year" <?php echo $filter == 'last_year' ? 'selected' : ''; ?>>Last Year</option>
        <option value="last_2_years" <?php echo $filter == 'last_2_years' ? 'selected' : ''; ?>>Last 2 Years</option>
        <option value="last_3_years" <?php echo $filter == 'last_3_years' ? 'selected' : ''; ?>>Last 3 Years</option>
      </select>
    </div>
  </div>

  <div class="card mt-3" style="border: 2px solid black; text-align:center; box-shadow: 10px 10px grey;">
    <table class="table table-striped">
      <thead>
        <tr>
          <th scope="col">Income</th>
          <th scope="col">Expenses</th>
          <th scope="col">Outsourcing</th>
          <th scope="col">Salaries</th>
          <th scope="col">Savings</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td onclick="loadData('income')"><i class="fas fa-wallet me-2"></i>₨ <?php echo number_format($totalIncome); ?></td>
          <td onclick="loadData('expenses')"><i class="fas fa-money-bill-wave me-2"></i>₨ <?php echo number_format($totalExpenses); ?></td>
          <td onclick="loadData('outsourcing')"><i class="fas fa-external-link-alt me-2"></i>₨ <?php echo number_format($totalOutsourcing); ?></td>
          <td onclick="loadData('salaries')"><i class="fas fa-user-tie me-2"></i>₨ <?php echo number_format($totalSalary); ?></td>
          <td onclick="loadData('savings')"><i class="fas fa-piggy-bank me-2"></i>₨ <?php echo number_format($totalSavings); ?></td>
        </tr>
      </tbody>
    </table>
  </div>
</div>



        <div class="row mt-4">
          <div class="col-xl-4">
            <div class="card mb-4">
              <div class="card-header">
                <i class="fas fa-chart-bar me-1"></i>
                Earnings Goals
              </div>
              <div class="card-body"><canvas id="earningsGoalChart" width="100%" height="40"></canvas></div>
            </div>
          </div>
          <div class="col-xl-4">
            <div class="card mb-4">
              <div class="card-header">
                <i class="fas fa-chart-bar me-1"></i>
                Expenses Goals
              </div>
              <div class="card-body"><canvas id="expensesGoalChart" width="100%" height="40"></canvas></div>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-xl-4">
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
          <div class="col-xl-4">
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
          <div class="col-xl-4">
            <div class="card mb-4">
              <div class="card-header">
                <i class="fas fa-chart-area me-1"></i>
                Expense Distribution
              </div>
              <div class="card-body"><canvas id="expenseChart" width="100%" height="40"></canvas></div>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-xl-4">
            <div class="card mb-4">
              <div class="card-header">
                <i class="fas fa-chart-bar me-1"></i>
                Income vs Expenses
              </div>
              <div class="card-body"><canvas id="incomeExpenseChart" width="100%" height="40"></canvas></div>
            </div>
          </div>
          <div class="col-xl-4">
            <div class="card mb-4">
              <div class="card-header">
                <i class="fas fa-chart-bar me-1"></i>
                Freelancing Accounts Earning
              </div>
              <div class="card-body"><canvas id="freelancingAccountsChart" width="100%" height="40"></canvas></div>
            </div>
          </div>
          <div class="col-xl-4">
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
function changeFilter() {
  var filter = document.getElementById('filter').value;
  window.location.href = 'dashboard.php?filter=' + filter;
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
  type: 'bar',
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

var ctx10 = document.getElementById('earningsGoalChart').getContext('2d');
var earningsGoalChart = new Chart(ctx10, {
  type: 'bar',
  data: {
    labels: ['Earnings'],
    datasets: [{
      label: 'Earnings',
      data: [<?php echo $totalIncome; ?>],
      backgroundColor: 'rgba(75, 192, 192, 0.2)',
      borderColor: 'rgba(75, 192, 192, 1)',
      borderWidth: 1
    }, {
      label: 'Goal',
      data: [<?php echo array_sum(array_column(array_filter($goalData, function($goal) { return $goal['goal_type'] == 'Earning'; }), 'goal')); ?>],
      backgroundColor: 'rgba(255, 99, 132, 0.2)',
      borderColor: 'rgba(255, 99, 132, 1)',
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

var ctx11 = document.getElementById('expensesGoalChart').getContext('2d');
var expensesGoalChart = new Chart(ctx11, {
  type: 'bar',
  data: {
    labels: ['Domestic Expenses', 'Business Expenses'],
    datasets: [{
      label: 'Expenses',
      data: [<?php echo $totalDomesticExpenses; ?>, <?php echo $totalBusinessExpenses; ?>],
      backgroundColor: 'rgba(153, 102, 255, 0.2)',
      borderColor: 'rgba(153, 102, 255, 1)',
      borderWidth: 1
    }, {
      label: 'Goal',
      data: [<?php echo array_sum(array_column(array_filter($goalData, function($goal) { return $goal['goal_type'] == 'Expense' && $goal['category'] == 'Domestic'; }), 'goal')); ?>, <?php echo array_sum(array_column(array_filter($goalData, function($goal) { return $goal['goal_type'] == 'Expense' && $goal['category'] == 'Business'; }), 'goal')); ?>],
      backgroundColor: 'rgba(255, 159, 64, 0.2)',
      borderColor: 'rgba(255, 159, 64, 1)',
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

function loadData(type) {
  $.ajax({
    url: 'load_data.php',
    type: 'GET',
    data: { type: type },
    success: function(response) {
      $('#modalTitle').text(type.charAt(0).toUpperCase() + type.slice(1) + ' Details');
      $('#modalBody').html(response);
      $('#dataModal').modal('show');
    }
  });
}
</script>

<!-- Add Goal Modal -->
<div class="modal fade" id="addGoalModal" tabindex="-1" role="dialog" aria-labelledby="addGoalModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="addGoalModalLabel">Add New Goal</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form id="addGoalForm" method="POST" action="">
          <div class="form-group">
            <label for="goal_type">Goal Type</label>
            <select class="form-control" id="goal_type" name="goal_type" required>
              <option value="Earning">Earning</option>
              <option value="Expense">Expense</option>
            </select>
          </div>
          <div class="form-group" id="category_group">
            <label for="category">Category</label>
            <select class="form-control" id="category" name="category">
              <option value="Domestic">Domestic</option>
              <option value="Business">Business</option>
            </select>
          </div>
          <div class="form-group">
            <label for="goal">Goal</label>
            <input type="number" class="form-control" id="goal" name="goal" required>
          </div>
          <div class="form-group">
            <label for="month">Month</label>
            <input type="number" class="form-control" id="month" name="month" required value="<?php echo date('m'); ?>" readonly>
          </div>
          <div class="form-group">
            <label for="year">Year</label>
            <input type="number" class="form-control" id="year" name="year" required value="<?php echo date('Y'); ?>" readonly>
          </div>
          <button type="submit" class="btn btn-primary" name="add_goal">Add Goal</button>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Data Modal -->
<div class="modal fade" id="dataModal" tabindex="-1" role="dialog" aria-labelledby="dataModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalTitle"></h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body" id="modalBody">
        <!-- Data will be loaded here -->
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

</body>
</html>
