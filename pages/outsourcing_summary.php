<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: ../login.php");
    exit();
}

include '../includes/db_connect.php';

// Fetch outsourcing summary data
$outsourcingSummaryData = $conn->query("SELECT platform, SUM(amount) AS total FROM OutsourcingProjects GROUP BY platform")->fetch_all(MYSQLI_ASSOC);
$monthlyOutsourcingData = $conn->query("SELECT DATE_FORMAT(date, '%Y-%m') AS month, platform, SUM(amount) AS total FROM OutsourcingProjects GROUP BY month, platform ORDER BY month")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Outsourcing Summary</title>
  <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.3/Chart.bundle.min.js"></script>
</head>
<body class="sb-nav-fixed">
<?php include '../includes/header.php'; ?>
<div id="layoutSidenav">
  <div id="layoutSidenav_nav">
    <?php include '../includes/sidebar.php'; ?>
  </div>
  <div id="layoutSidenav_content">
    <main>
      <div class="container mt-4">
        <div class="card mb-3">
          <div class="card-header">Outsourcing Summary</div>
          <div class="card-body">
            <table class="table table-bordered">
              <thead>
                <tr>
                  <th>Platform</th>
                  <th>Total Outsourcing Cost</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($outsourcingSummaryData as $data): ?>
                <tr>
                  <td><?php echo $data['platform']; ?></td>
                  <td>₨ <?php echo number_format($data['total'], 2); ?></td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
        <div class="card mb-3">
          <div class="card-header">Monthly Outsourcing Cost by Platform</div>
          <div class="card-body">
            <canvas id="outsourcingChart"></canvas>
          </div>
        </div>
      </div>
    </main>
    <?php include '../includes/footer.php'; ?>
  </div>
</div>

<script>
  var ctx = document.getElementById('outsourcingChart').getContext('2d');
  var monthlyOutsourcingData = <?php echo json_encode($monthlyOutsourcingData); ?>;
  var platforms = [...new Set(monthlyOutsourcingData.map(item => item.platform))];
  var months = [...new Set(monthlyOutsourcingData.map(item => item.month))];
  
  var datasets = platforms.map(platform => {
    return {
      label: platform,
      data: months.map(month => {
        var record = monthlyOutsourcingData.find(item => item.month === month && item.platform === platform);
        return record ? record.total : 0;
      }),
      borderColor: 'rgba(' + Math.floor(Math.random() * 255) + ',' + Math.floor(Math.random() * 255) + ',' + Math.floor(Math.random() * 255) + ',1)',
      borderWidth: 1,
      fill: false
    };
  });

  var outsourcingChart = new Chart(ctx, {
    type: 'line',
    data: {
      labels: months,
      datasets: datasets
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
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
