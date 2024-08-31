<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

include '../includes/db_connect.php';

$sql = "SELECT subcategory, SUM(amount) AS total FROM Expenses WHERE subcategory IN (
            'Bike 125', 'Car', 'Car & Bike Fuel', 'Car & Bike Repair', 
            'DHA bill', 'Electricity Bill', 'Home Expenses', 'House Rent', 
            'Auto Bidder', 'ChatGPT membership', 'Co Working Space', 
            'Hostinger', 'Skillshare', 'Teramind', 'TransWorld', 'Upwork', 
            'Youtube') 
        GROUP BY subcategory";
$result = $conn->query($sql);
$expenses = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $expenses[$row['subcategory']] = $row['total'];
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Fixed Expenses</title>
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
    .table-responsive {
      margin-top: 20px;
    }
    .table thead th {
      background-color: #f8f9fa;
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
        <div class="container mt-4">
          <h2 class="text-center">Fixed Expenses</h2>
          <div class="table-responsive">
            <table class="table table-bordered">
              <thead>
                <tr>
                  <th>Subcategory</th>
                  <th>Total Amount (₨)</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($expenses as $subcategory => $total): ?>
                  <tr>
                    <td><?php echo htmlspecialchars($subcategory); ?></td>
                    <td><?php echo number_format($total, 2); ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </main>
    </div>
  </div>

  <script>
    window.addEventListener('DOMContentLoaded', event => {
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
