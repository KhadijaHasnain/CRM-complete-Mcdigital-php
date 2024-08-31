<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}
include '../includes/db_connect.php';

// Fetch distinct account names for the dropdown
$accountNames = $conn->query("SELECT DISTINCT account_name FROM FreelancingAccounts")->fetch_all(MYSQLI_ASSOC);

$filter_start_date = isset($_POST['start_date']) ? $_POST['start_date'] : '';
$filter_end_date = isset($_POST['end_date']) ? $_POST['end_date'] : '';
$filter_account_name = isset($_POST['account_name']) ? $_POST['account_name'] : '';

$sql = "SELECT * FROM FreelancingAccounts WHERE 1=1";
if ($filter_start_date && $filter_end_date) {
    $sql .= " AND date >= '$filter_start_date' AND date <= '$filter_end_date'";
}
if ($filter_account_name) {
    $sql .= " AND account_name = '$filter_account_name'";
}
$sql .= " ORDER BY date";

$result = $conn->query($sql);
$totalAmount = 0;
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $totalAmount += $row['amount'];
    }
}
$conn->close();

// Get the current month, total days, and year
$currentMonth = date('F');
$totalDays = date('t');
$currentYear = date('Y');
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Income Summary</title>
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
    body {
      font-family: Arial, sans-serif;
      background-color: #f8f9fa;
    }
    .table tbody tr:hover {
      background-color: #f1f1f1;
    }
    .summary-info {
      font-size: 1.5em;
      font-weight: bold;
      text-align: center;
      margin-bottom: 20px;
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
    <h2 class="text-center">Income Summary</h2>
    <div class="summary-info">
      <p>Month: <?php echo $currentMonth; ?> | Total Days: <?php echo $totalDays; ?> | Year: <?php echo $currentYear; ?></p>
      <p>Total Amount: ₨ <?php echo number_format($totalAmount, 2); ?></p>
    </div>

    <form method="post" class="form-inline mb-3">
      <div class="form-group mx-sm-3 mb-2">
        <label for="start_date" class="sr-only">Start Date</label>
        <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo $filter_start_date; ?>">
      </div>
      <div class="form-group mx-sm-3 mb-2">
        <label for="end_date" class="sr-only">End Date</label>
        <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo $filter_end_date; ?>">
      </div>
      <div class="form-group mx-sm-3 mb-2">
        <label for="account_name" class="sr-only">Account Name</label>
        <select class="form-control" id="account_name" name="account_name">
          <option value="">Select Account</option>
          <?php foreach ($accountNames as $account) {
              echo "<option value='" . $account['account_name'] . "'";
              if ($filter_account_name == $account['account_name']) {
                  echo " selected";
              }
              echo ">" . $account['account_name'] . "</option>";
          } ?>
        </select>
      </div>
      <button type="submit" class="btn btn-primary mb-2">Filter</button>
    </form>

    <table class="table table-bordered table-striped">
      <thead>
        <tr>
          <th>Date</th>
          <th>Account Name</th>
          <th>Amount</th>
          <th>Description</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $result->data_seek(0); // Reset result pointer to the beginning
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<tr>
                        <td>{$row['date']}</td>
                        <td>{$row['account_name']}</td>
                        <td>₨ " . number_format($row['amount'], 2) . "</td>
                        <td>" . (isset($row['description']) ? $row['description'] : '') . "</td>
                      </tr>";
            }
        } else {
            echo "<tr><td colspan='4' class='text-center'>No data available</td></tr>";
        }
        ?>
      </tbody>
    </table>
  </div>

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
