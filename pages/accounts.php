<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

include '../includes/db_connect.php';

// Determine current month and year
$currentMonth = isset($_GET['month']) ? $_GET['month'] : date('m');
$currentYear = isset($_GET['year']) ? $_GET['year'] : date('Y');

// Fetch accounts for the current month
$accounts = $conn->query("SELECT * FROM FreelancingAccounts WHERE YEAR(date) = '$currentYear' AND MONTH(date) = '$currentMonth' ORDER BY date DESC");

if (!$accounts) {
    die("Query Failed: " . $conn->error);
}

$totalGoDesign = 0;
$totalKP = 0;
$totalUpwork = 0;
$totalLocal = 0;
$totalOther = 0;

$dataGoDesign = [];
$dataKP = [];
$dataUpwork = [];
$dataLocal = [];
$dataOther = [];

while ($row = $accounts->fetch_assoc()) {
    switch ($row['account_name']) {
        case 'GoDesign':
            $totalGoDesign += $row['amount'];
            $dataGoDesign[] = $row;
            break;
        case 'KP':
            $totalKP += $row['amount'];
            $dataKP[] = $row;
            break;
        case 'Upwork':
            $totalUpwork += $row['amount'];
            $dataUpwork[] = $row;
            break;
        case 'Local':
            $totalLocal += $row['amount'];
            $dataLocal[] = $row;
            break;
        case 'Other':
            $totalOther += $row['amount'];
            $dataOther[] = $row;
            break;
    }
}

// Fetch distinct years
$years = $conn->query("SELECT DISTINCT YEAR(date) as year FROM FreelancingAccounts ORDER BY year DESC");

// Fetch distinct months for the selected year
$months = $conn->query("SELECT DISTINCT MONTH(date) as month FROM FreelancingAccounts WHERE YEAR(date) = '$currentYear' ORDER BY month DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>Freelancing Accounts Management</title>
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
    .horizontal-scroll {
      overflow-x: auto;
      white-space: nowrap;
      margin-top: 20px;
    }
    .horizontal-scroll a {
      display: inline-block;
      padding: 10px;
      background: #f8f9fa;
      margin-right: 10px;
      text-decoration: none;
      color: #333;
    }
    .horizontal-scroll a.selected {
      background: #007bff;
      color: #fff;
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
          <h2 class="mt-4">Freelancing Accounts for <?php echo date('F Y', strtotime("$currentYear-$currentMonth-01")); ?></h2>
          <div class="d-flex justify-content-between align-items-center mb-3">
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
            <div class="total-amount">
              <strong>Total Amount:</strong> <?php echo number_format($totalGoDesign + $totalKP + $totalUpwork + $totalLocal + $totalOther, 2); ?>
            </div>
          </div>
          <button class="btn btn-primary mb-3" data-toggle="modal" data-target="#addAccountModal">Add Payment</button>
          <table class="table table-bordered table-summary">
            <thead class="thead-dark">
              <tr>
                <th>KP</th>
                <th>GoDesign</th>
                <th>Local</th>
                <th>Upwork</th>
                <th>Other</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>
                  <?php foreach ($dataKP as $row): ?>
                      <?php echo number_format($row['amount'], 2) . '<br><small>' . $row['date'] . '</small>'; ?><br>
                  <?php endforeach; ?>
                  <?php if ($totalKP == 0) echo '0.00'; ?>
                </td>
                <td>
                  <?php foreach ($dataGoDesign as $row): ?>
                      <?php echo number_format($row['amount'], 2) . '<br><small>' . $row['date'] . '</small>'; ?><br>
                  <?php endforeach; ?>
                  <?php if ($totalGoDesign == 0) echo '0.00'; ?>
                </td>
                <td>
                  <?php foreach ($dataLocal as $row): ?>
                      <?php echo number_format($row['amount'], 2) . '<br><small>' . $row['date'] . '</small>'; ?><br>
                  <?php endforeach; ?>
                  <?php if ($totalLocal == 0) echo '0.00'; ?>
                </td>
                <td>
                  <?php foreach ($dataUpwork as $row): ?>
                      <?php echo number_format($row['amount'], 2) . '<br><small>' . $row['date'] . '</small>'; ?><br>
                  <?php endforeach; ?>
                  <?php if ($totalUpwork == 0) echo '0.00'; ?>
                </td>
                <td>
                  <?php foreach ($dataOther as $row): ?>
                      <?php echo number_format($row['amount'], 2) . '<br><small>' . $row['date'] . '</small>'; ?><br>
                  <?php endforeach; ?>
                  <?php if ($totalOther == 0) echo '0.00'; ?>
                </td>
                <td>
                  <?php foreach (array_merge($dataKP, $dataGoDesign, $dataLocal, $dataUpwork, $dataOther) as $row): ?>
                    <button class="btn btn-warning btn-sm" data-toggle="modal" data-target="#editAccountModal" data-id="<?php echo $row['id']; ?>" data-account_name="<?php echo $row['account_name']; ?>" data-amount="<?php echo $row['amount']; ?>" data-date="<?php echo $row['date']; ?>">Edit</button>
                    <button class="btn btn-danger btn-sm" data-toggle="modal" data-target="#deleteAccountModal" data-id="<?php echo $row['id']; ?>">Delete</button>
                    <br><br>
                  <?php endforeach; ?>
                </td>
              </tr>
            </tbody>
          </table>
          
        </div>
      </main>

      <?php include '../includes/footer.php';?>
    
  </div>
</div>

<!-- Add Account Modal -->
<div class="modal fade" id="addAccountModal" tabindex="-1" role="dialog" aria-labelledby="addAccountModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="addAccountModalLabel">Add New Account</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form id="addAccountForm" method="POST" action="add_account.php">
          <div class="form-group">
            <label for="accountName">Account Name</label>
            <select class="form-control" id="accountName" name="account_name" required>
              <option value="GoDesign">GoDesign</option>
              <option value="KP">KP</option>
              <option value="Upwork">Upwork</option>
              <option value="Local">Local</option>
              <option value="Other">Other</option>
            </select>
          </div>
          <div class="form-group">
            <label for="amount">Amount</label>
            <input type="number" class="form-control" id="amount" name="amount" required>
          </div>
          <div class="form-group">
            <label for="date">Date</label>
            <input type="date" class="form-control" id="date" name="date" required>
          </div>
          <button type="submit" class="btn btn-primary">Add Account</button>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Edit Account Modal -->
<div class="modal fade" id="editAccountModal" tabindex="-1" role="dialog" aria-labelledby="editAccountModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="editAccountModalLabel">Edit Account</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form id="editAccountForm" method="POST" action="edit_account.php">
          <input type="hidden" id="editAccountId" name="id">
          <div class="form-group">
            <label for="editAccountName">Account Name</label>
            <select class="form-control" id="editAccountName" name="account_name" required>
              <option value="GoDesign">GoDesign</option>
              <option value="KP">KP</option>
              <option value="Upwork">Upwork</option>
              <option value="Local">Local</option>
              <option value="Other">Other</option>
            </select>
          </div>
          <div class="form-group">
            <label for="editAmount">Amount</label>
            <input type="number" class="form-control" id="editAmount" name="amount" required>
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

<!-- Delete Account Modal -->
<div class="modal fade" id="deleteAccountModal" tabindex="-1" role="dialog" aria-labelledby="deleteAccountModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="deleteAccountModalLabel">Delete Account</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form id="deleteAccountForm" method="POST" action="delete_account.php">
          <input type="hidden" id="deleteAccountId" name="id">
          <p>Are you sure you want to delete this account?</p>
          <button type="submit" class="btn btn-danger">Delete</button>
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
        </form>
      </div>
    </div>
  </div>
</div>
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
  <script src="../js/scripts.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js" crossorigin="anonymous"></script>
  <script src="assets/demo/chart-area-demo.js"></script>
  <script src="assets/demo/chart-bar-demo.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js" crossorigin="anonymous"></script>
  <script src="../js/datatables-simple-demo.js"></script>
<script>
  $('#editAccountModal').on('show.bs.modal', function (event) {
    var button = $(event.relatedTarget); // Button that triggered the modal
    var id = button.data('id');
    var account_name = button.data('account_name');
    var amount = button.data('amount');
    var date = button.data('date');

    var modal = $(this);
    modal.find('#editAccountId').val(id);
    modal.find('#editAccountName').val(account_name);
    modal.find('#editAmount').val(amount);
    modal.find('#editDate').val(date);
  });

  $('#deleteAccountModal').on('show.bs.modal', function (event) {
    var button = $(event.relatedTarget); // Button that triggered the modal
    var id = button.data('id');

    var modal = $(this);
    modal.find('#deleteAccountId').val(id);
  });

  // Handle year and month selection
  $('#yearSelect, #monthSelect').change(function() {
    var year = $('#yearSelect').val();
    var month = $('#monthSelect').val();
    window.location.href = '?year=' + year + '&month=' + month;
  });
</script>



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
