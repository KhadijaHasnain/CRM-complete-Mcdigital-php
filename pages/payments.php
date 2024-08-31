<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}
include '../includes/db_connect.php';

// Fetch distinct years and months with payments
$years = $conn->query("SELECT DISTINCT YEAR(date) AS year FROM Payments ORDER BY year DESC");

if (!$years) {
    die("Query Failed: " . $conn->error);
}

$currentYear = isset($_GET['year']) ? $_GET['year'] : date('Y');
$currentMonth = isset($_GET['month']) ? $_GET['month'] : date('m');

// Fetch distinct months for the current year
$months = $conn->query("SELECT DISTINCT MONTH(date) AS month FROM Payments WHERE YEAR(date) = $currentYear ORDER BY month DESC");

if (!$months) {
    die("Query Failed: " . $conn->error);
}

// Fetch payments for the selected year and month
$payments = $conn->query("SELECT Users.username, Users.id as user_id, Payments.id, Payments.amount, Payments.medical, Payments.bonuses, Payments.tax, Payments.deductions, Payments.date 
                          FROM Payments 
                          JOIN Users ON Payments.team_member_id = Users.id 
                          WHERE YEAR(Payments.date) = $currentYear AND MONTH(Payments.date) = $currentMonth
                          ORDER BY Payments.date DESC, Users.username");

if (!$payments) {
    die("Query Failed: " . $conn->error);
}

$totalAmount = 0;
$totalMedical = 0;
$totalBonuses = 0;
$totalTax = 0;
$totalDeductions = 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Payments Management</title>
  <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet">
  <link href="../css/styles.css" rel="stylesheet">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/js/bootstrap.bundle.min.js"></script>
  <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.3/Chart.bundle.min.js"></script>
  <style>
.table-summary th{
  background-color: rgb(33 37 41);
  color: white;
}
.table-summary th, .table-summary td {
      text-align: center;
      font-weight: bold;
      border: 1px solid rgb(33 37 41);
    }
    .table-summary .total-row {
      font-weight: bold;
      background-color: #f8f9fa;
    }
    .table-summary .total-row th, .table-summary .total-row td {
      border-top: 2px solid #dee2e6;
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



      <div class="container-fluid px-4 mt-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h2>Payments for <?php echo date('F Y', strtotime("$currentYear-$currentMonth-01")); ?></h2>
          <div>
            <select id="yearSelect" class="form-control d-inline-block w-auto">
              <?php while ($yearRow = $years->fetch_assoc()): ?>
                <option value="<?php echo $yearRow['year']; ?>" <?php if ($yearRow['year'] == $currentYear) echo 'selected'; ?>>
                  <?php echo $yearRow['year']; ?>
                </option>
              <?php endwhile; ?>
            </select>
            <select id="monthSelect" class="form-control d-inline-block w-auto">
              <?php while ($monthRow = $months->fetch_assoc()): ?>
                <option value="<?php echo $monthRow['month']; ?>" <?php if ($monthRow['month'] == $currentMonth) echo 'selected'; ?>>
                  <?php echo date('F', mktime(0, 0, 0, $monthRow['month'], 1)); ?>
                </option> 
              <?php endwhile; ?>
            </select>
          </div>
        </div>
        <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addPaymentModal">Add Payment</button>

        
        <table class="table table-bordered table-summary">
          <thead class="thead-dark">
            <tr>
              <th>Team Member</th>
              <th>Payments</th>
              <th>3% Medical</th>
              <th>Bonuses</th>
              <th>Tax</th>
              <th>Deductions</th>
              <th>Total</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php while ($row = $payments->fetch_assoc()): 
              $total = $row['amount'] + $row['medical'] + $row['bonuses'] - $row['tax'] - $row['deductions'];
              $totalAmount += $row['amount'];
              $totalMedical += $row['medical'];
              $totalBonuses += $row['bonuses'];
              $totalTax += $row['tax'];
              $totalDeductions += $row['deductions'];
            ?>
            <tr>
              <td><?php echo $row['username']; ?></td>
              <td><?php echo number_format($row['amount'], 2); ?></td>
              <td><?php echo number_format($row['medical'], 2); ?></td>
              <td><?php echo number_format($row['bonuses'], 2); ?></td>
              <td><?php echo number_format($row['tax'], 2); ?></td>
              <td><?php echo number_format($row['deductions'], 2); ?></td>
              <td><?php echo number_format($total, 2); ?></td>
              <td>
                <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editPaymentModal" 
                        data-id="<?php echo $row['id']; ?>" 
                        data-team_member_id="<?php echo $row['user_id']; ?>"
                        data-amount="<?php echo $row['amount']; ?>" 
                        data-medical="<?php echo $row['medical']; ?>" 
                        data-bonuses="<?php echo $row['bonuses']; ?>" 
                        data-tax="<?php echo $row['tax']; ?>" 
                        data-deductions="<?php echo $row['deductions']; ?>"
                        data-date="<?php echo $row['date']; ?>">Edit</button>
                <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deletePaymentModal"
                        data-id="<?php echo $row['id']; ?>">Delete</button>
              </td>
            </tr>
            <?php endwhile; ?>
          </tbody>
          <tfoot>
            <tr class="total-row">
              <th>Total</th>
              <td><?php echo number_format($totalAmount, 2); ?></td>
              <td><?php echo number_format($totalMedical, 2); ?></td>
              <td><?php echo number_format($totalBonuses, 2); ?></td>
              <td><?php echo number_format($totalTax, 2); ?></td>
              <td><?php echo number_format($totalDeductions, 2); ?></td>
              <td><?php echo number_format($totalAmount + $totalMedical + $totalBonuses - $totalTax - $totalDeductions, 2); ?></td>
              <td></td>
            </tr>
          </tfoot>
        </table>
      </div>
    </main>
    <?php include '../includes/footer.php';?>
  </div>
</div>

<!-- Add Payment Modal -->
<div class="modal fade" id="addPaymentModal" tabindex="-1" role="dialog" aria-labelledby="addPaymentModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="addPaymentModalLabel">Add New Payment</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="addPaymentForm" method="POST" action="add_payment.php">
          <div class="form-group">
            <label for="teamMember">Team Member</label>
            <select class="form-control" id="teamMember" name="team_member_id" required>
              <?php
              $users = $conn->query("SELECT id, username FROM Users");
              while($user = $users->fetch_assoc()) {
                  echo '<option value="'.$user['id'].'">'.$user['username'].'</option>';
              }
              ?>
              <option value="new">New Team Member</option>
            </select>
          </div>
          <div class="form-group new-team-member" style="display: none;">
            <label for="username">New Team Member Name</label>
            <input type="text" class="form-control" id="username" name="username">
          </div>
          <div class="form-group">
            <label for="amount">Amount</label>
            <input type="number" class="form-control" id="amount" name="amount" required>
          </div>
          <div class="form-group">
            <label for="medical">Medical</label>
            <input type="number" class="form-control" id="medical" name="medical">
          </div>
          <div class="form-group">
            <label for="bonuses">Bonuses</label>
            <input type="number" class="form-control" id="bonuses" name="bonuses">
          </div>
          <div class="form-group">
            <label for="tax">Tax</label>
            <input type="number" class="form-control" id="tax" name="tax">
          </div>
          <div class="form-group">
            <label for="deductions">Deductions</label>
            <input type="number" class="form-control" id="deductions" name="deductions">
          </div>
          <div class="form-group">
            <label for="date">Date</label>
            <input type="date" class="form-control" id="date" name="date" required>
          </div>
          <button type="submit" class="btn btn-primary">Add Payment</button>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Edit Payment Modal -->
<div class="modal fade" id="editPaymentModal" tabindex="-1" role="dialog" aria-labelledby="editPaymentModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="editPaymentModalLabel">Edit Payment</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="editPaymentForm" method="POST" action="edit_payment.php">
          <input type="hidden" id="editPaymentId" name="id">
          <div class="form-group">
            <label for="editTeamMember">Team Member</label>
            <select class="form-control" id="editTeamMember" name="team_member_id" required>
              <?php
              $users = $conn->query("SELECT id, username FROM Users");
              while($user = $users->fetch_assoc()) {
                  echo '<option value="'.$user['id'].'">'.$user['username'].'</option>';
              }
              ?>
            </select>
          </div>
          <div class="form-group">
            <label for="editAmount">Amount</label>
            <input type="number" class="form-control" id="editAmount" name="amount" required>
          </div>
          <div class="form-group">
            <label for="editMedical">Medical</label>
            <input type="number" class="form-control" id="editMedical" name="medical">
          </div>
          <div class="form-group">
            <label for="editBonuses">Bonuses</label>
            <input type="number" class="form-control" id="editBonuses" name="bonuses">
          </div>
          <div class="form-group">
            <label for="editTax">Tax</label>
            <input type="number" class="form-control" id="editTax" name="tax">
          </div>
          <div class="form-group">
            <label for="editDeductions">Deductions</label>
            <input type="number" class="form-control" id="editDeductions" name="deductions">
          </div>
          <div class="form-group">
            <label for="editDate">Date</label>
            <input type="date" class="form-control" id="editDate" name="date" required>
          </div>
          <button type="submit" class="btn btn-primary">Save Changes</button>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Delete Payment Modal -->
<div class="modal fade" id="deletePaymentModal" tabindex="-1" role="dialog" aria-labelledby="deletePaymentModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="deletePaymentModalLabel">Delete Payment</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="deletePaymentForm" method="POST" action="delete_payment.php">
          <input type="hidden" id="deletePaymentId" name="id">
          <p>Are you sure you want to delete this payment?</p>
          <button type="submit" class="btn btn-danger">Delete</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        </form>
      </div>
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
<script>
  $('#editPaymentModal').on('show.bs.modal', function (event) {
    var button = $(event.relatedTarget); // Button that triggered the modal
    var id = button.data('id');
    var team_member_id = button.data('team_member_id');
    var amount = button.data('amount');
    var medical = button.data('medical');
    var bonuses = button.data('bonuses');
    var tax = button.data('tax');
    var deductions = button.data('deductions');
    var date = button.data('date');

    var modal = $(this);
    modal.find('#editPaymentId').val(id);
    modal.find('#editTeamMember').val(team_member_id);
    modal.find('#editAmount').val(amount);
    modal.find('#editMedical').val(medical);
    modal.find('#editBonuses').val(bonuses);
    modal.find('#editTax').val(tax);
    modal.find('#editDeductions').val(deductions);
    modal.find('#editDate').val(date);
  });

  $('#deletePaymentModal').on('show.bs.modal', function (event) {
    var button = $(event.relatedTarget); // Button that triggered the modal
    var id = button.data('id');

    var modal = $(this);
    modal.find('#deletePaymentId').val(id);
  });

  // Show or hide new team member input
  $('#teamMember').change(function() {
    if ($(this).val() === 'new') {
      $('.new-team-member').show();
    } else {
      $('.new-team-member').hide();
    }
  });

  // Handle year and month selection
  $('#yearSelect, #monthSelect').change(function() {
    var year = $('#yearSelect').val();
    var month = $('#monthSelect').val();
    window.location.href = '?year=' + year + '&month=' + month;
  });

 

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
