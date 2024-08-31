<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Database connection
include '../includes/db_connect.php';

// Fetch data for each specified team member
$team_members = ["Mehrab", "Naqeeb", "Ammar", "Zulqarnain", "Momina", "Hafsa", "Mahnoor"];
$monthlyBonusesData = [];

foreach ($team_members as $member) {
    $sql = "SELECT DATE_FORMAT(date, '%Y-%m') AS month, SUM(bonuses) AS total_bonus 
            FROM Payments 
            JOIN Users ON Payments.team_member_id = Users.id 
            WHERE Users.username = '$member'
            GROUP BY month 
            ORDER BY month";
    $result = $conn->query($sql);

    $monthlyBonusesData[$member] = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $monthlyBonusesData[$member][$row['month']] = $row['total_bonus'];
        }
    }
}

// Prepare data for the chart
$months = array_unique(array_merge(...array_map('array_keys', $monthlyBonusesData)));
sort($months);

$chartData = [];
foreach ($team_members as $member) {
    $data = [];
    foreach ($months as $month) {
        $data[] = isset($monthlyBonusesData[$member][$month]) ? $monthlyBonusesData[$member][$month] : 0;
    }
    $chartData[] = [
        'label' => $member,
        'data' => $data,
        'borderColor' => 'rgba(' . rand(0, 255) . ',' . rand(0, 255) . ',' . rand(0, 255) . ',1)',
        'borderWidth' => 1,
        'fill' => false
    ];
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Bonus Summary</title>
  <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.3/Chart.bundle.min.js"></script>
</head>
<body>
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <a class="navbar-brand" href="dashboard.php">Financial Dashboard</a>
    <div class="collapse navbar-collapse">
      <ul class="navbar-nav ml-auto">
        <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
        <li class="nav-item"><a class="nav-link" href="accounts.php">Freelancing Accounts</a></li>
        <li class="nav-item"><a class="nav-link" href="payments.php">Payments</a></li>
        <li class="nav-item"><a class="nav-link" href="outsourcing.php">Outsourcing Projects</a></li>
        <li class="nav-item"><a class="nav-link" href="expenses.php">Expenses</a></li>
        <li class="nav-item"><a class="nav-link" href="reports.php">Reports</a></li>
        <li class="nav-item"><a class="nav-link" href="team_summary.php">Team Summary</a></li>
        <li class="nav-item"><a class="nav-link" href="income_summary.php">Income Summary</a></li>
        <li class="nav-item"><a class="nav-link" href="expense_summary.php">Expense Summary</a></li>
        <li class="nav-item"><a class="nav-link" href="outsourcing_summary.php">Outsourcing Summary</a></li>
        <li class="nav-item"><a class="nav-link" href="overall_summary.php">Overall Summary</a></li>
        <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
      </ul>
    </div>
  </nav>

  <div class="container mt-4">
    <div class="card mb-3">
      <div class="card-header">Monthly Bonuses for Team Members</div>
      <div class="card-body">
        <canvas id="bonusChart"></canvas>
      </div>
    </div>
  </div>

  <script>
    var ctx = document.getElementById('bonusChart').getContext('2d');
    var bonusChart = new Chart(ctx, {
      type: 'line',
      data: {
        labels: <?php echo json_encode($months); ?>,
        datasets: <?php echo json_encode($chartData); ?>
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
