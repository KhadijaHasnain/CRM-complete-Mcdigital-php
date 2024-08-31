<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Database connection
include '../includes/db_connect.php';

// List of specific team members
$team_members = ["Ilyas", "Usman", "Mehrab", "Naqeeb", "Ammar", "Zulqarnain", "Momina", "Hafsa", "Ami", "Mahnoor" , "Waqas"];
$placeholders = implode(',', array_fill(0, count($team_members), '?'));

// Prepare and bind for team payments
$stmt = $conn->prepare("SELECT Users.username, 
        SUM(Payments.amount + IFNULL(Payments.medical, 0) + IFNULL(Payments.bonuses, 0)) AS total_amount 
        FROM Payments 
        JOIN Users ON Payments.team_member_id = Users.id 
        WHERE Users.username IN ($placeholders)
        GROUP BY Users.username");
$stmt->bind_param(str_repeat('s', count($team_members)), ...$team_members);

$stmt->execute();
$result = $stmt->get_result();

$labels = [];
$amounts = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $labels[] = $row['username'];
        $amounts[] = $row['total_amount'];
    }
}

$stmt->close();

// Prepare and bind for monthly income
$monthly_income_stmt = $conn->prepare("SELECT Users.username, 
        DATE_FORMAT(Payments.date, '%Y-%m') AS month, 
        SUM(Payments.amount + IFNULL(Payments.medical, 0) + IFNULL(Payments.bonuses, 0)) AS monthly_total 
        FROM Payments 
        JOIN Users ON Payments.team_member_id = Users.id 
        WHERE Users.username IN ($placeholders)
        GROUP BY Users.username, month 
        ORDER BY month");
$monthly_income_stmt->bind_param(str_repeat('s', count($team_members)), ...$team_members);

$monthly_income_stmt->execute();
$monthly_income_result = $monthly_income_stmt->get_result();

$monthly_income_data = [];
$months = [];

if ($monthly_income_result->num_rows > 0) {
    while ($row = $monthly_income_result->fetch_assoc()) {
        $username = $row['username'];
        $month = $row['month'];
        $monthly_total = $row['monthly_total'];

        if (!in_array($month, $months)) {
            $months[] = $month;
        }

        if (!isset($monthly_income_data[$username])) {
            $monthly_income_data[$username] = [];
        }

        $monthly_income_data[$username][$month] = $monthly_total;
    }
}

$monthly_income_stmt->close();
$conn->close();

// Fill in missing months with 0 values
foreach ($monthly_income_data as $username => $data) {
    foreach ($months as $month) {
        if (!isset($monthly_income_data[$username][$month])) {
            $monthly_income_data[$username][$month] = 0;
        }
    }
}

// Prepare data for Chart.js
$datasets = [];
$colors = ['rgba(75, 192, 192, 1)', 'rgba(255, 99, 132, 1)', 'rgba(255, 206, 86, 1)', 'rgba(54, 162, 235, 1)', 'rgba(153, 102, 255, 1)', 'rgba(255, 159, 64, 1)', 'rgba(199, 199, 199, 1)', 'rgba(83, 102, 255, 1)', 'rgba(255, 99, 255, 1)', 'rgba(99, 255, 99, 1)'];

foreach ($monthly_income_data as $username => $data) {
    $datasets[] = [
        'label' => $username,
        'data' => array_values($data),
        'borderColor' => array_shift($colors),
        'fill' => false
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Team Payments and Monthly Income Overview</title>
    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet">
  <link href="../css/styles.css" rel="stylesheet">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/js/bootstrap.bundle.min.js"></script>
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

        <h2 class="text-center mt-5">Monthly Income Overview</h2>
        <canvas id="monthlyIncomeChart" width="400" height="200"></canvas>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            var ctx1 = document.getElementById('paymentsChart').getContext('2d');
            var paymentsChart = new Chart(ctx1, {
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

            var ctx2 = document.getElementById('monthlyIncomeChart').getContext('2d');
            var monthlyIncomeChart = new Chart(ctx2, {
                type: 'line',
                data: {
                    labels: <?php echo json_encode($months); ?>,
                    datasets: <?php echo json_encode($datasets); ?>
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


        
    </script>
</body>
</html>
