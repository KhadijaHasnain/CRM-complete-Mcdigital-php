<?php

// Database connection
$host = 'localhost';
$db = 'freelance_bussiness';
$user = 'root';
$pass = '';

$mysqli = new mysqli($host, $user, $pass, $db);

if ($mysqli->connect_error) {
    die('Connect Error (' . $mysqli->connect_errno . ') ' . $mysqli->connect_error);
}

// Fetch totals
$salary_result = $mysqli->query("SELECT SUM(total) AS total FROM salaries");
$business_result = $mysqli->query("SELECT SUM(amount) AS total FROM business_expenses");
$domestic_result = $mysqli->query("SELECT SUM(amount) AS total FROM domestic_expenses");

$salary_total = $salary_result->fetch_assoc()['total'];
$business_total = $business_result->fetch_assoc()['total'];
$domestic_total = $domestic_result->fetch_assoc()['total'];
$overall_total = $salary_total + $business_total + $domestic_total;
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Summary</title>
  <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
  <div class="container mt-4">
    <h2 class="text-center">Liability</h2>
    <div class="row mt-4">
      <div class="col-md-12">
        <table class="table table-bordered">
          <thead>
            <tr>
              <th>Salary <a href="fixedamount.php#addSalaryModal" class="text-primary">+</a></th>
              <th>Business Expense <a href="fixedamount.php#addBusinessModal" class="text-primary">+</a></th>
              <th>Domestic Expense <a href="fixedamount.php#addDomesticModal" class="text-primary">+</a></th>
              <th>Total Liability</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>₨ <?php echo number_format($salary_total, ); ?></td>
              <td>₨ <?php echo number_format($business_total, ); ?></td>
              <td>₨ <?php echo number_format($domestic_total, ); ?></td>
              <td>₨ <?php echo number_format($overall_total, ); ?></td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
  <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
