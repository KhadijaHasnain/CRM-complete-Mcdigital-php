<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: ../login.php");
    exit();
}

include '../includes/db_connect.php';

// Function to fetch data based on filters
function fetchData($conn, $reportType, $filterType, $filterValue, $specificYear = null) {
    $query = "";

    if ($reportType == 'expenses') {
        if ($filterType == 'weekly') {
            $query = "SELECT WEEK(date) as week, SUM(amount) as total FROM Expenses";
        } elseif ($filterType == 'monthly') {
            $query = "SELECT MONTHNAME(date) as month, SUM(amount) as total FROM Expenses";
        } elseif ($filterType == 'yearly') {
            $query = "SELECT YEAR(date) as year, SUM(amount) as total FROM Expenses";
        }
    } elseif ($reportType == 'outsourcing') {
        if ($filterType == 'weekly') {
            $query = "SELECT WEEK(date) as week, SUM(amount) as total FROM OutsourcingProjects";
        } elseif ($filterType == 'monthly') {
            $query = "SELECT MONTHNAME(date) as month, SUM(amount) as total FROM OutsourcingProjects";
        } elseif ($filterType == 'yearly') {
            $query = "SELECT YEAR(date) as year, SUM(amount) as total FROM OutsourcingProjects";
        }
    } elseif ($reportType == 'accounts') {
        if ($filterType == 'weekly') {
            $query = "SELECT WEEK(date) as week, SUM(amount) as total FROM FreelancingAccounts";
        } elseif ($filterType == 'monthly') {
            $query = "SELECT MONTHNAME(date) as month, SUM(amount) as total FROM FreelancingAccounts";
        } elseif ($filterType == 'yearly') {
            $query = "SELECT YEAR(date) as year, SUM(amount) as total FROM FreelancingAccounts";
        }
    } elseif ($reportType == 'payments') {
        if ($filterType == 'weekly') {
            $query = "SELECT WEEK(date) as week, SUM(amount + IFNULL(medical, 0) + IFNULL(bonuses, 0) - IFNULL(tax, 0) - IFNULL(deductions, 0)) as total FROM Payments";
        } elseif ($filterType == 'monthly') {
            $query = "SELECT MONTHNAME(date) as month, SUM(amount + IFNULL(medical, 0) + IFNULL(bonuses, 0) - IFNULL(tax, 0) - IFNULL(deductions, 0)) as total FROM Payments";
        } elseif ($filterType == 'yearly') {
            $query = "SELECT YEAR(date) as year, SUM(amount + IFNULL(medical, 0) + IFNULL(bonuses, 0) - IFNULL(tax, 0) - IFNULL(deductions, 0)) as total FROM Payments";
        }
    }

    if ($specificYear) {
        $query .= " WHERE YEAR(date) = " . intval($specificYear);
    }

    if ($filterType == 'weekly') {
        $query .= " GROUP BY week";
    } elseif ($filterType == 'monthly') {
        $query .= " GROUP BY month";
    } elseif ($filterType == 'yearly') {
        $query .= " GROUP BY year";
    }

    return $conn->query($query)->fetch_all(MYSQLI_ASSOC);
}

// Handle form submission to fetch filtered data
$filterType = isset($_POST['filterType']) ? $_POST['filterType'] : 'monthly';
$reportType = isset($_POST['reportType']) ? $_POST['reportType'] : 'expenses';
$filterValue = isset($_POST['filterValue']) ? $_POST['filterValue'] : '';
$specificYear = isset($_POST['specificYear']) ? $_POST['specificYear'] : null;

$data = fetchData($conn, $reportType, $filterType, $filterValue, $specificYear);

// Calculate total amount
$totalAmount = array_sum(array_column($data, 'total'));

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Reports Management</title>
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
      cursor: pointer;
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
    .recent-activities {
      height: 300px;
      overflow-y: scroll;
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
          <h1 class="mt-4">Reports Management</h1>
          <ol class="breadcrumb mb-4">
            <li class="breadcrumb-item active">Reports</li>
          </ol>

          <form method="post" class="form-inline mb-3">
            <div class="form-group mr-2">
              <label for="filterType" class="mr-2">Filter Type:</label>
              <select name="filterType" id="filterType" class="form-control">
                <option value="weekly" <?php echo $filterType == 'weekly' ? 'selected' : ''; ?>>Weekly</option>
                <option value="monthly" <?php echo $filterType == 'monthly' ? 'selected' : ''; ?>>Monthly</option>
                <option value="yearly" <?php echo $filterType == 'yearly' ? 'selected' : ''; ?>>Yearly</option>
              </select>
            </div>
            <div class="form-group mr-2">
              <label for="reportType" class="mr-2">Report Type:</label>
              <select name="reportType" id="reportType" class="form-control">
                <option value="expenses" <?php echo $reportType == 'expenses' ? 'selected' : ''; ?>>Expenses</option>
                <option value="outsourcing" <?php echo $reportType == 'outsourcing' ? 'selected' : ''; ?>>Outsourcing</option>
                <option value="accounts" <?php echo $reportType == 'accounts' ? 'selected' : ''; ?>>Accounts</option>
                <option value="payments" <?php echo $reportType == 'payments' ? 'selected' : ''; ?>>Payments</option>
              </select>
            </div>
            <div class="form-group mr-2">
              <label for="specificYear" class="mr-2">Year (Optional):</label>
              <input type="number" name="specificYear" id="specificYear" class="form-control" value="<?php echo $specificYear; ?>">
            </div>
            <div class="form-group mr-2  ">
              <button type="submit" class="btn btn-primary">Filter</button>
            </div>
          </form>

   
          <div class="table-responsive mt-4">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <h4>Total Amount: ₨ <?php echo number_format($totalAmount, 2); ?></h4>
            </div>
            <table class="table table-striped table-bordered">
              <thead>
                <tr>
                  <th>Date</th>
                  <th>Description</th>
                  <th>Amount</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($data as $row): ?>
                <tr>
                  <td><?php echo isset($row['week']) ? "Week " . $row['week'] : (isset($row['month']) ? $row['month'] : (isset($row['year']) ? $row['year'] : '')); ?></td>
                  <td><?php echo $reportType; ?></td>
                  <td>₨ <?php echo number_format($row['total'], 2); ?></td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>

        </div>
      </main>
      <?php include '../includes/footer.php';?>
    </div>
  </div>

  <script>
    function generateReport(timeframe, reportType) {
      document.getElementById('filterType').value = timeframe;
      document.getElementById('reportType').value = reportType;
      document.forms[0].submit();
    }
  </script>
</body>
</html>
