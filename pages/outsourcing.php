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

// Fetch outsourcing data for the current month and year
$outsourcing = $conn->query("SELECT * FROM OutsourcingProjects WHERE YEAR(date) = '$currentYear' AND MONTH(date) = '$currentMonth' ORDER BY date DESC");

if (!$outsourcing) {
    die("Query Failed: " . $conn->error);
}

$totalLocal = 0;
$totalUpwork = 0;
$totalOther = 0;
$totalFiverr = 0;

$dataLocal = [];
$dataUpwork = [];
$dataOther = [];
$dataFiverr = [];

while ($row = $outsourcing->fetch_assoc()) {
    switch ($row['platform']) {
        case 'Local':
            $totalLocal += $row['amount'];
            $dataLocal[] = $row;
            break;
        case 'Upwork':
            $totalUpwork += $row['amount'];
            $dataUpwork[] = $row;
            break;
        case 'Other':
            $totalOther += $row['amount'];
            $dataOther[] = $row;
            break;
        case 'Fiverr':
            $totalFiverr += $row['amount'];
            $dataFiverr[] = $row;
            break;
    }
}

// Fetch distinct years
$years = $conn->query("SELECT DISTINCT YEAR(date) as year FROM OutsourcingProjects ORDER BY year DESC");

// Fetch distinct months for the selected year
$months = $conn->query("SELECT DISTINCT MONTH(date) as month FROM OutsourcingProjects WHERE YEAR(date) = '$currentYear' ORDER BY month DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Outsourcing Projects Management</title>
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
          <h2>Outsourcing Projects for <?php echo date('F Y', strtotime("$currentYear-$currentMonth-01")); ?></h2>
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
            <strong>Total Amount:</strong> <?php echo number_format($totalLocal + $totalUpwork + $totalOther + $totalFiverr, 2); ?>
          </div>
        </div>
        <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addOutsourcingModal">Add Outsourcing Project</button>
        <table class="table table-bordered table-summary">
          <thead class="thead-dark">
            <tr>
              <th>Local</th>
              <th>Upwork</th>
              <th>Other</th>
              <th>Fiverr</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr>
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
                <?php foreach ($dataFiverr as $row): ?>
                    <?php echo number_format($row['amount'], 2) . '<br><small>' . $row['date'] . '</small>'; ?><br>
                <?php endforeach; ?>
                <?php if ($totalFiverr == 0) echo '0.00'; ?>
              </td>
              <td>
                <?php foreach (array_merge($dataLocal, $dataUpwork, $dataOther, $dataFiverr) as $row): ?>
                  <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editOutsourcingModal" data-id="<?php echo $row['id']; ?>" data-platform="<?php echo $row['platform']; ?>" data-amount="<?php echo $row['amount']; ?>" data-date="<?php echo $row['date']; ?>">Edit</button>
                  <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteOutsourcingModal" data-id="<?php echo $row['id']; ?>">Delete</button>
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

<!-- Add Outsourcing Modal -->
<div class="modal fade" id="addOutsourcingModal" tabindex="-1" role="dialog" aria-labelledby="addOutsourcingModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="addOutsourcingModalLabel">Add New Outsourcing Project</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="addOutsourcingForm" method="POST" action="add_project.php">
          <div class="form-group">
            <label for="platform">Platform</label>
            <select class="form-control" id="platform" name="platform" required>
              <option value="Local">Local</option>
              <option value="Upwork">Upwork</option>
              <option value="Other">Other</option>
              <option value="Fiverr">Fiverr</option>
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
          <button type="submit" class="btn btn-primary">Add Outsourcing Project</button>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Edit Outsourcing Modal -->
<div class="modal fade" id="editOutsourcingModal" tabindex="-1" role="dialog" aria-labelledby="editOutsourcingModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="editOutsourcingModalLabel">Edit Outsourcing Project</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="editOutsourcingForm" method="POST" action="edit_project.php">
          <input type="hidden" id="editOutsourcingId" name="id">
          <div class="form-group">
            <label for="editPlatform">Platform</label>
            <select class="form-control" id="editPlatform" name="platform" required>
              <option value="Local">Local</option>
              <option value="Upwork">Upwork</option>
              <option value="Other">Other</option>
              <option value="Fiverr">Fiverr</option>
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

<!-- Delete Outsourcing Modal -->
<div class="modal fade" id="deleteOutsourcingModal" tabindex="-1" role="dialog" aria-labelledby="deleteOutsourcingModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="deleteOutsourcingModalLabel">Delete Outsourcing Project</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="deleteOutsourcingForm" method="POST" action="delete_project.php">
          <input type="hidden" id="deleteOutsourcingId" name="id">
          <p>Are you sure you want to delete this outsourcing project?</p>
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
<script src="../js/scripts.js"></script>
<script>
  $('#editOutsourcingModal').on('show.bs.modal', function (event) {
    var button = $(event.relatedTarget); // Button that triggered the modal
    var id = button.data('id');
    var platform = button.data('platform');
    var amount = button.data('amount');
    var date = button.data('date');

    var modal = $(this);
    modal.find('#editOutsourcingId').val(id);
    modal.find('#editPlatform').val(platform);
    modal.find('#editAmount').val(amount);
    modal.find('#editDate').val(date);
  });

  $('#deleteOutsourcingModal').on('show.bs.modal', function (event) {
    var button = $(event.relatedTarget); // Button that triggered the modal
    var id = button.data('id');

    var modal = $(this);
    modal.find('#deleteOutsourcingId').val(id);
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
