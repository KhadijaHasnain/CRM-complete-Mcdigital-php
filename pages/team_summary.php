<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

include '../includes/db_connect.php';

// Fetch team summary data
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

// Calculate the total amount for all employees
$totalAmountAllEmployees = 0;
foreach ($teamSummaryData as $data) {
    $totalAmountAllEmployees += $data['total'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Team Summary</title>
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
 

  <div class="container mt-4">
    <div class="card mb-3">
      <div class="card-header">Team Summary</div>
      <div class="card-body">
        <table class="table table-bordered">
          <thead>
            <tr>
              <th>Team Member</th>
              <th>Total Salary</th>
              <th>Total Medical</th>
              <th>Total Bonuses</th>
              <th>Total Tax</th>
              <th>Total Deductions</th>
              <th>Total</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($teamSummaryData as $data): ?>
            <tr>
              <td><?php echo $data['username']; ?></td>
              <td>₨ <?php echo number_format($data['total_salary'], 2); ?></td>
              <td>₨ <?php echo number_format($data['total_medical'], 2); ?></td>
              <td>₨ <?php echo number_format($data['total_bonuses'], 2); ?></td>
              <td>₨ <?php echo number_format($data['total_tax'], 2); ?></td>
              <td>₨ <?php echo number_format($data['total_deductions'], 2); ?></td>
              <td>₨ <?php echo number_format($data['total'], 2); ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

    <div class="card mb-3">
      <div class="card-header">Total Amount for All Employees</div>
      <div class="card-body">
        <h5 class="card-title">₨ <?php echo number_format($totalAmountAllEmployees, 2); ?></h5>
      </div>
    </div>
  </div>

  


    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
<script src="assets/demo/chart-area-demo.js"></script>
  <script src="assets/demo/chart-bar-demo.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js" crossorigin="anonymous"></script>
  <script src="../js/datatables-simple-demo.js"></script>


  <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

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
