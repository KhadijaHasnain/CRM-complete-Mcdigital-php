<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$conn = new mysqli('localhost', 'root', '', 'freelance_bussiness');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Determine the selected period
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'current_month';
$year = isset($_GET['year']) ? $_GET['year'] : date('Y');
$month = isset($_GET['month']) ? $_GET['month'] : date('m');

$currentYear = date('Y');
$currentMonth = date('m');
$previousMonth = date('m', strtotime('-1 month'));
$previousMonthYear = date('Y', strtotime('-1 month'));

switch ($filter) {
    case 'previous_month':
        $startDate = "$previousMonthYear-$previousMonth-01";
        $endDate = date("Y-m-t", strtotime($startDate));
        $heading = "Expenses for " . date("F Y", strtotime($startDate));
        break;
    case 'yearly':
        $startDate = "$year-01-01";
        $endDate = "$year-12-31";
        $heading = "Expenses for Year $year";
        break;
    case 'custom_month':
        $startDate = "$year-$month-01";
        $endDate = date("Y-m-t", strtotime($startDate));
        $heading = "Expenses for " . date("F Y", strtotime($startDate));
        break;
    case 'current_month':
    default:
        $startDate = "$currentYear-$currentMonth-01";
        $endDate = date("Y-m-t", strtotime($startDate));
        $heading = "Expenses for " . date("F Y", strtotime($startDate));
        break;
}

// Fetch domestic expenses
$domesticSubcategories = $conn->query("SELECT subcategory, description, date, SUM(amount) AS total 
                                       FROM Expenses 
                                       WHERE category = 'Domestic' 
                                       AND date BETWEEN '$startDate' AND '$endDate' 
                                       GROUP BY subcategory, description, date")->fetch_all(MYSQLI_ASSOC);

// Fetch business expenses
$businessSubcategories = $conn->query("SELECT subcategory, description, date, SUM(amount) AS total 
                                       FROM Expenses 
                                       WHERE category = 'Business' 
                                       AND date BETWEEN '$startDate' AND '$endDate' 
                                       GROUP BY subcategory, description, date")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>Expenses</title>
  <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
  <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
  <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet">
  <link href="css/styles.css" rel="stylesheet">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/js/bootstrap.min.js"></script>
  <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
  <style>
    .table-container {
      margin-top: 20px;
    }
    .card-header {
      font-weight: bold;
      font-size: 1.2em;
    }
  </style>
</head>
<body class="sb-nav-fixed">
<?php include 'header.php'; ?>

<div id="layoutSidenav">
  <div id="layoutSidenav_nav">
    <?php include 'sidebar.php'; ?>
  </div>
  <div id="layoutSidenav_content">
    <main>
      <div class="container-fluid px-4">
        <h1 class="mt-4">Expenses</h1>
        <ol class="breadcrumb mb-4">
          <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
          <li class="breadcrumb-item active">Expenses</li>
        </ol>

        <form method="GET" action="expense.php">
          <div class="form-group row">
            <label for="filter" class="col-sm-2 col-form-label">Filter</label>
            <div class="col-sm-4">
              <select class="form-control" id="filter" name="filter" onchange="this.form.submit()">
                <option value="current_month" <?php echo $filter == 'current_month' ? 'selected' : ''; ?>>Current Month</option>
                <option value="previous_month" <?php echo $filter == 'previous_month' ? 'selected' : ''; ?>>Previous Month</option>
                <option value="yearly" <?php echo $filter == 'yearly' ? 'selected' : ''; ?>>Yearly</option>
                <option value="custom_month" <?php echo $filter == 'custom_month' ? 'selected' : ''; ?>>Custom Month</option>
              </select>
            </div>
            <div class="col-sm-3">
              <input type="number" class="form-control" name="year" placeholder="Year" value="<?php echo $year; ?>" onchange="this.form.submit()">
            </div>
            <div class="col-sm-3">
              <select class="form-control" name="month" onchange="this.form.submit()">
                <?php for ($m = 1; $m <= 12; $m++): ?>
                  <option value="<?php echo $m; ?>" <?php echo $month == $m ? 'selected' : ''; ?>><?php echo date('F', mktime(0, 0, 0, $m, 1)); ?></option>
                <?php endfor; ?>
              </select>
            </div>
          </div>
        </form>

        <h2><?php echo $heading; ?></h2>

        <div class="table-container">
          <div class="row">
            <div class="col-md-6">
              <div class="card mb-3">
                <div class="card-header">Domestic Expenses</div>
                <div class="card-body">
                  <table class="table">
                    <thead>
                      <tr>
                        <th>Subcategory</th>
                        <th>Description</th>
                        <th>Date</th>
                        <th>Total</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach ($domesticSubcategories as $subcategory): ?>
                      <tr>
                        <td><?php echo $subcategory['subcategory']; ?></td>
                        <td><?php echo $subcategory['description']; ?></td>
                        <td><?php echo date('d M, Y', strtotime($subcategory['date'])); ?></td>
                        <td>₨ <?php echo number_format($subcategory['total'], 2); ?></td>
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
                        <th>Description</th>
                        <th>Date</th>
                        <th>Total</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach ($businessSubcategories as $subcategory): ?>
                      <tr>
                        <td><?php echo $subcategory['subcategory']; ?></td>
                        <td><?php echo $subcategory['description']; ?></td>
                        <td><?php echo date('d M, Y', strtotime($subcategory['date'])); ?></td>
                        <td>₨ <?php echo number_format($subcategory['total'], 2); ?></td>
                      </tr>
                      <?php endforeach; ?>
                    </tbody>
                  </table>
                </div>
              </div>
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

</body>
</html>
