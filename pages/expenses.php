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

// Fetch expenses for the current month
$expenses = $conn->query("SELECT * FROM Expenses WHERE YEAR(date) = '$currentYear' AND MONTH(date) = '$currentMonth' ORDER BY date DESC");

if (!$expenses) {
    die("Query Failed: " . $conn->error);
}

$totalBusiness = 0;
$totalDomestic = 0;

$dataBusiness = [];
$dataDomestic = [];

while ($row = $expenses->fetch_assoc()) {
    switch ($row['category']) {
        case 'Business':
            $totalBusiness += $row['amount'];
            $dataBusiness[] = $row;
            break;
        case 'Domestic':
            $totalDomestic += $row['amount'];
            $dataDomestic[] = $row;
            break;
    }
}

// Fetch distinct years
$years = $conn->query("SELECT DISTINCT YEAR(date) as year FROM Expenses ORDER BY year DESC");

// Fetch distinct months for the selected year
$months = $conn->query("SELECT DISTINCT MONTH(date) as month FROM Expenses WHERE YEAR(date) = '$currentYear' ORDER BY month DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Expenses Management</title>
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
      <div class="container-fluid px-4 mt-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h2>Expenses for <?php echo date('F Y', strtotime("$currentYear-$currentMonth-01")); ?></h2>
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
            <strong>Total Amount:</strong> <?php echo number_format($totalBusiness + $totalDomestic, 2); ?>
          </div>
        </div>
        <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addExpenseModal">Add Expense</button>
        <table class="table table-bordered table-summary">
          <thead class="thead-dark">
            <tr>
              <th>Category</th>
              <th>Subcategory</th>
              <th>Amount</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach (array_merge($dataBusiness, $dataDomestic) as $row): ?>
              <tr>
                <td><?php echo $row['category']; ?></td>
                <td><?php echo $row['subcategory']; ?></td>
                <td><?php echo number_format($row['amount'], 2); ?><br><small><?php echo $row['date']; ?></small></td>
                <td>
                  <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editExpenseModal" data-id="<?php echo $row['id']; ?>" data-category="<?php echo $row['category']; ?>" data-subcategory="<?php echo $row['subcategory']; ?>" data-amount="<?php echo $row['amount']; ?>" data-date="<?php echo $row['date']; ?>">Edit</button>
                  <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteExpenseModal" data-id="<?php echo $row['id']; ?>">Delete</button>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </main>
    <?php include '../includes/footer.php';?>
  </div>
</div>

<!-- Add Expense Modal -->
<div class="modal fade" id="addExpenseModal" tabindex="-1" role="dialog" aria-labelledby="addExpenseModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="addExpenseModalLabel">Add New Expense</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="addExpenseForm" method="POST" action="add_expense.php">
          <div class="form-group">
            <label for="category">Category</label>
            <select class="form-control" id="category" name="category" required>
              <option value="Domestic">Domestic</option>
              <option value="Business">Business</option>
            </select>
          </div>
          <div class="form-group">
            <label for="subcategory">Subcategory</label>
            <select class="form-control" id="subcategory" name="subcategory" required>
              <optgroup label="Domestic">
                <option value="House Rent">House Rent</option>
                <option value="Electricity Bill">Electricity Bill</option>
                <option value="Gas">Gas</option>
                <option value="Home Expenses">Home Expenses</option>
                <option value="Car & Bike Fuel">Car & Bike Fuel</option>
                <option value="Car & Bike Repair">Car & Bike Repair</option>
                <option value="Other">Other</option>
              </optgroup>
              <optgroup label="Business">
                <option value="Netflix">Netflix</option>
                <option value="TransWorld">TransWorld</option>
                <option value="Teramind">Teramind</option>
                <option value="Amazon">Amazon</option>
                <option value="Upwork">Upwork</option>
                <option value="Blaze">Blaze</option>
                <option value="ChatGPT membership">ChatGPT membership</option>
                <option value="Call pkg">Call pkg</option>
                <option value="Youtube">Youtube</option>
                <option value="Freelancer">Freelancer</option>
                <option value="Other">Other</option>
              </optgroup>
            </select>
          </div>
          <div class="form-group" id="otherSubcategoryDiv" style="display: none;">
            <label for="otherSubcategory">Other Subcategory</label>
            <input type="text" class="form-control" id="otherSubcategory" name="other_subcategory">
          </div>
          <div class="form-group">
            <label for="description">Description</label>
            <input type="text" class="form-control" id="description" name="description" required>
          </div>
          <div class="form-group">
            <label for="amount">Amount</label>
            <input type="number" class="form-control" id="amount" name="amount" required>
          </div>
          <div class="form-group">
            <label for="date">Date</label>
            <input type="date" class="form-control" id="date" name="date" required>
          </div>
          <button type="submit" class="btn btn-primary">Add Expense</button>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Edit Expense Modal -->
<div class="modal fade" id="editExpenseModal" tabindex="-1" role="dialog" aria-labelledby="editExpenseModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="editExpenseModalLabel">Edit Expense</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="editExpenseForm" method="POST" action="edit_expense.php">
          <input type="hidden" id="editExpenseId" name="id">
          <div class="form-group">
            <label for="editCategory">Category</label>
            <select class="form-control" id="editCategory" name="category" required>
              <option value="Domestic">Domestic</option>
              <option value="Business">Business</option>
            </select>
          </div>
          <div class="form-group">
            <label for="editSubcategory">Subcategory</label>
            <select class="form-control" id="editSubcategory" name="subcategory" required>
              <optgroup label="Domestic">
                <option value="House Rent">House Rent</option>
                <option value="Electricity Bill">Electricity Bill</option>
                <option value="Gas">Gas</option>
                <option value="Home Expenses">Home Expenses</option>
                <option value="Car & Bike Fuel">Car & Bike Fuel</option>
                <option value="Car & Bike Repair">Car & Bike Repair</option>
                <option value="Other">Other</option>
              </optgroup>
              <optgroup label="Business">
                <option value="Netflix">Netflix</option>
                <option value="TransWorld">TransWorld</option>
                <option value="Teramind">Teramind</option>
                <option value="Amazon">Amazon</option>
                <option value="Upwork">Upwork</option>
                <option value="Blaze">Blaze</option>
                <option value="ChatGPT membership">ChatGPT membership</option>
                <option value="Call pkg">Call pkg</option>
                <option value="Youtube">Youtube</option>
                <option value="Freelancer">Freelancer</option>
                <option value="Other">Other</option>
              </optgroup>
            </select>
          </div>
          <div class="form-group" id="editOtherSubcategoryDiv" style="display: none;">
            <label for="editOtherSubcategory">Other Subcategory</label>
            <input type="text" class="form-control" id="editOtherSubcategory" name="other_subcategory">
          </div>
          <div class="form-group">
            <label for="editDescription">Description</label>
            <input type="text" class="form-control" id="editDescription" name="description" required>
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

<!-- Delete Expense Modal -->
<div class="modal fade" id="deleteExpenseModal" tabindex="-1" role="dialog" aria-labelledby="deleteExpenseModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="deleteExpenseModalLabel">Delete Expense</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="deleteExpenseForm" method="POST" action="delete_expense.php">
          <input type="hidden" id="deleteExpenseId" name="id">
          <p>Are you sure you want to delete this expense?</p>
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
<script src="js/scripts.js"></script>
<script>
  $('#editExpenseModal').on('show.bs.modal', function (event) {
    var button = $(event.relatedTarget); // Button that triggered the modal
    var id = button.data('id');
    var category = button.data('category');
    var subcategory = button.data('subcategory');
    var amount = button.data('amount');
    var date = button.data('date');
    var description = button.data('description');

    var modal = $(this);
    modal.find('#editExpenseId').val(id);
    modal.find('#editCategory').val(category);
    modal.find('#editSubcategory').val(subcategory);
    modal.find('#editAmount').val(amount);
    modal.find('#editDate').val(date);
    modal.find('#editDescription').val(description);

    if (subcategory === 'Other') {
      $('#editOtherSubcategoryDiv').show();
    } else {
      $('#editOtherSubcategoryDiv').hide();
    }
  });

  $('#deleteExpenseModal').on('show.bs.modal', function (event) {
    var button = $(event.relatedTarget); // Button that triggered the modal
    var id = button.data('id');

    var modal = $(this);
    modal.find('#deleteExpenseId').val(id);
  });

  // Handle year and month selection
  $('#yearSelect, #monthSelect').change(function() {
    var year = $('#yearSelect').val();
    var month = $('#monthSelect').val();
    window.location.href = '?year=' + year + '&month=' + month;
  });

  // Show or hide the Other Subcategory field based on the selected subcategory
  $('#subcategory').change(function() {
    if ($(this).val() === 'Other') {
      $('#otherSubcategoryDiv').show();
    } else {
      $('#otherSubcategoryDiv').hide();
    }
  });

  $('#editSubcategory').change(function() {
    if ($(this).val() === 'Other') {
      $('#editOtherSubcategoryDiv').show();
    } else {
      $('#editOtherSubcategoryDiv').hide();
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
