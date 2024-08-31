<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Database connection
$host = 'localhost';
$db = 'freelance_bussiness';
$user = 'root';
$pass = '';

$mysqli = new mysqli($host, $user, $pass, $db);

if ($mysqli->connect_error) {
    die('Connect Error (' . $mysqli->connect_errno . ') ' . $mysqli->connect_error);
}

// Handle CRUD operations
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'];
    
    if ($action == 'add_salary') {
        $name = $_POST['name'];
        $basic = $_POST['basic'];
        $bonus = $_POST['bonus'];
        $deduction = $_POST['deduction'];
        $other = $_POST['other'];
        
        $stmt = $mysqli->prepare("INSERT INTO salaries (name, basic, bonus, deduction, other) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param('sdddd', $name, $basic, $bonus, $deduction, $other);
        $stmt->execute();
        $stmt->close();
    } elseif ($action == 'edit_salary') {
        $id = $_POST['id'];
        $name = $_POST['name'];
        $basic = $_POST['basic'];
        $bonus = $_POST['bonus'];
        $deduction = $_POST['deduction'];
        $other = $_POST['other'];
        
        $stmt = $mysqli->prepare("UPDATE salaries SET name=?, basic=?, bonus=?, deduction=?, other=? WHERE id=?");
        $stmt->bind_param('sddddi', $name, $basic, $bonus, $deduction, $other, $id);
        $stmt->execute();
        $stmt->close();
    } elseif ($action == 'delete_salary') {
        $id = $_POST['id'];
        
        $stmt = $mysqli->prepare("DELETE FROM salaries WHERE id=?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->close();
    } elseif ($action == 'add_business_expense') {
        $subcategory = $_POST['subcategory'];
        $amount = $_POST['amount'];
        
        $stmt = $mysqli->prepare("INSERT INTO business_expenses (subcategory, amount) VALUES (?, ?)");
        $stmt->bind_param('sd', $subcategory, $amount);
        $stmt->execute();
        $stmt->close();
    } elseif ($action == 'edit_business_expense') {
        $id = $_POST['id'];
        $subcategory = $_POST['subcategory'];
        $amount = $_POST['amount'];
        
        $stmt = $mysqli->prepare("UPDATE business_expenses SET subcategory=?, amount=? WHERE id=?");
        $stmt->bind_param('sdi', $subcategory, $amount, $id);
        $stmt->execute();
        $stmt->close();
    } elseif ($action == 'delete_business_expense') {
        $id = $_POST['id'];
        
        $stmt = $mysqli->prepare("DELETE FROM business_expenses WHERE id=?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->close();
    } elseif ($action == 'add_domestic_expense') {
        $subcategory = $_POST['subcategory'];
        $amount = $_POST['amount'];
        
        $stmt = $mysqli->prepare("INSERT INTO domestic_expenses (subcategory, amount) VALUES (?, ?)");
        $stmt->bind_param('sd', $subcategory, $amount);
        $stmt->execute();
        $stmt->close();
    } elseif ($action == 'edit_domestic_expense') {
        $id = $_POST['id'];
        $subcategory = $_POST['subcategory'];
        $amount = $_POST['amount'];
        
        $stmt = $mysqli->prepare("UPDATE domestic_expenses SET subcategory=?, amount=? WHERE id=?");
        $stmt->bind_param('sdi', $subcategory, $amount, $id);
        $stmt->execute();
        $stmt->close();
    } elseif ($action == 'delete_domestic_expense') {
        $id = $_POST['id'];
        
        $stmt = $mysqli->prepare("DELETE FROM domestic_expenses WHERE id=?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->close();
    }
}
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
          
          <!-- Salary Table -->
          <div class="table-responsive">
            <h3 class="text-center">Salary <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addSalaryModal">Add New</button></h3>
            <table class="table table-bordered">
              <thead>
                <tr>
                  <th>Name</th>
                  <th>Basic Salary (₨)</th>
                  <th>3% Medical (₨)</th>
            
                  <th>Total (₨)</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php
                $result = $mysqli->query("SELECT * FROM salaries");
                $salary_total = 0;
                while ($row = $result->fetch_assoc()) {
                  echo "<tr>
                    <td>{$row['name']}</td>
                    <td>" . number_format($row['basic'], 2) . "</td>
                    <td>" . number_format($row['bonus'], 2) . "</td>
              
                    <td>" . number_format($row['total'], 2) . "</td>
                    <td>
                      <button class='btn btn-warning btn-sm' data-toggle='modal' data-target='#editSalaryModal' data-id='{$row['id']}' data-name='{$row['name']}' data-basic='{$row['basic']}' data-bonus='{$row['bonus']}' data-deduction='{$row['deduction']}' data-other='{$row['other']}'>Edit</button>
                      <button class='btn btn-danger btn-sm' data-toggle='modal' data-target='#deleteSalaryModal' data-id='{$row['id']}'>Delete</button>
                    </td>
                  </tr>";
                  $salary_total += $row['total'];
                }
                ?>
                <tr>
                  <th colspan="3">Total</th>
                  <th>₨ <?php echo number_format($salary_total, 2); ?></th>
                  <th></th>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Business Expenses Table -->
          <div class="table-responsive">
            <h3 class="text-center">Business Expenses <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addBusinessModal">Add New</button></h3>
            <table class="table table-bordered">
              <thead>
                <tr>
                  <th>Subcategory</th>
                  <th>Total Amount (₨)</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php
                $result = $mysqli->query("SELECT * FROM business_expenses");
                $business_total = 0;
                while ($row = $result->fetch_assoc()) {
                  echo "<tr>
                    <td>{$row['subcategory']}</td>
                    <td>" . number_format($row['amount'], 2) . "</td>
                    <td>
                      <button class='btn btn-warning btn-sm' data-toggle='modal' data-target='#editBusinessModal' data-id='{$row['id']}' data-subcategory='{$row['subcategory']}' data-amount='{$row['amount']}'>Edit</button>
                      <button class='btn btn-danger btn-sm' data-toggle='modal' data-target='#deleteBusinessModal' data-id='{$row['id']}'>Delete</button>
                    </td>
                  </tr>";
                  $business_total += $row['amount'];
                }
                ?>
                <tr>
                  <th>Total</th>
                  <th>₨ <?php echo number_format($business_total, 2); ?></th>
                  <th></th>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Domestic Expenses Table -->
          <div class="table-responsive">
            <h3 class="text-center">Domestic Expenses <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addDomesticModal">Add New</button></h3>
            <table class="table table-bordered">
              <thead>
                <tr>
                  <th>Subcategory</th>
                  <th>Total Amount (₨)</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php
                $result = $mysqli->query("SELECT * FROM domestic_expenses");
                $domestic_total = 0;
                while ($row = $result->fetch_assoc()) {
                  echo "<tr>
                    <td>{$row['subcategory']}</td>
                    <td>" . number_format($row['amount'], 2) . "</td>
                    <td>
                      <button class='btn btn-warning btn-sm' data-toggle='modal' data-target='#editDomesticModal' data-id='{$row['id']}' data-subcategory='{$row['subcategory']}' data-amount='{$row['amount']}'>Edit</button>
                      <button class='btn btn-danger btn-sm' data-toggle='modal' data-target='#deleteDomesticModal' data-id='{$row['id']}'>Delete</button>
                    </td>
                  </tr>";
                  $domestic_total += $row['amount'];
                }
                ?>
                <tr>
                  <th>Total</th>
                  <th>₨ <?php echo number_format($domestic_total, 2); ?></th>
                  <th></th>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Total Summary Row -->
          <div class="row mt-4">
            <div class="col-md-12">
              <table class="table table-bordered">
                <thead>
                  <tr>
                    <th>Total Salary Amount (₨)</th>
                    <th>Business Total Amount (₨)</th>
                    <th>Domestic Total Amount (₨)</th>
                    <th>Overall Total Amount (₨)</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td>₨ <?php echo number_format($salary_total, 2); ?></td>
                    <td>₨ <?php echo number_format($business_total, 2); ?></td>
                    <td>₨ <?php echo number_format($domestic_total, 2); ?></td>
                    <td>₨ <?php echo number_format($salary_total + $business_total + $domestic_total, 2); ?></td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </main>
    </div>
  </div>

  <!-- Modals for CRUD operations -->
  <!-- Add Salary Modal -->
  <div class="modal fade" id="addSalaryModal" tabindex="-1" aria-labelledby="addSalaryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="addSalaryModalLabel">Add New Salary</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <form method="post">
          <div class="modal-body">
            <input type="hidden" name="action" value="add_salary">
            <div class="form-group">
              <label>Name</label>
              <input type="text" name="name" class="form-control" required>
            </div>
            <div class="form-group">
              <label>Basic Salary (₨)</label>
              <input type="number" step="0.01" name="basic" class="form-control" required>
            </div>
            <div class="form-group">
              <label>3% Medical (₨)</label>
              <input type="number" step="0.01" name="bonus" class="form-control" required>
            </div>
            <div class="form-group">
              <label>Deduction (₨)</label>
              <input type="number" step="0.01" name="deduction" class="form-control" required>
            </div>
            <div class="form-group">
              <label>Other (₨)</label>
              <input type="number" step="0.01" name="other" class="form-control" required>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-primary">Save changes</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Edit Salary Modal -->
  <div class="modal fade" id="editSalaryModal" tabindex="-1" aria-labelledby="editSalaryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="editSalaryModalLabel">Edit Salary</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <form method="post">
          <div class="modal-body">
            <input type="hidden" name="action" value="edit_salary">
            <input type="hidden" name="id" id="edit-salary-id">
            <div class="form-group">
              <label>Name</label>
              <input type="text" name="name" id="edit-salary-name" class="form-control" required>
            </div>
            <div class="form-group">
              <label>Basic Salary (₨)</label>
              <input type="number" step="0.01" name="basic" id="edit-salary-basic" class="form-control" required>
            </div>
            <div class="form-group">
              <label>3% Medical (₨)</label>
              <input type="number" step="0.01" name="bonus" id="edit-salary-bonus" class="form-control" required>
            </div>
            <div class="form-group">
              <label>Deduction (₨)</label>
              <input type="number" step="0.01" name="deduction" id="edit-salary-deduction" class="form-control" required>
            </div>
            <div class="form-group">
              <label>Other (₨)</label>
              <input type="number" step="0.01" name="other" id="edit-salary-other" class="form-control" required>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-primary">Save changes</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Delete Salary Modal -->
  <div class="modal fade" id="deleteSalaryModal" tabindex="-1" aria-labelledby="deleteSalaryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="deleteSalaryModalLabel">Delete Salary</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <form method="post">
          <div class="modal-body">
            <input type="hidden" name="action" value="delete_salary">
            <input type="hidden" name="id" id="delete-salary-id">
            <p>Are you sure you want to delete this record?</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-danger">Delete</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Add Business Expense Modal -->
  <div class="modal fade" id="addBusinessModal" tabindex="-1" aria-labelledby="addBusinessModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="addBusinessModalLabel">Add New Business Expense</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <form method="post">
          <div class="modal-body">
            <input type="hidden" name="action" value="add_business_expense">
            <div class="form-group">
              <label>Subcategory</label>
              <input type="text" name="subcategory" class="form-control" required>
            </div>
            <div class="form-group">
              <label>Total Amount (₨)</label>
              <input type="number" step="0.01" name="amount" class="form-control" required>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-primary">Save changes</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Edit Business Expense Modal -->
  <div class="modal fade" id="editBusinessModal" tabindex="-1" aria-labelledby="editBusinessModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="editBusinessModalLabel">Edit Business Expense</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <form method="post">
          <div class="modal-body">
            <input type="hidden" name="action" value="edit_business_expense">
            <input type="hidden" name="id" id="edit-business-id">
            <div class="form-group">
              <label>Subcategory</label>
              <input type="text" name="subcategory" id="edit-business-subcategory" class="form-control" required>
            </div>
            <div class="form-group">
              <label>Total Amount (₨)</label>
              <input type="number" step="0.01" name="amount" id="edit-business-amount" class="form-control" required>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-primary">Save changes</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Delete Business Expense Modal -->
  <div class="modal fade" id="deleteBusinessModal" tabindex="-1" aria-labelledby="deleteBusinessModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="deleteBusinessModalLabel">Delete Business Expense</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <form method="post">
          <div class="modal-body">
            <input type="hidden" name="action" value="delete_business_expense">
            <input type="hidden" name="id" id="delete-business-id">
            <p>Are you sure you want to delete this record?</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-danger">Delete</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Add Domestic Expense Modal -->
  <div class="modal fade" id="addDomesticModal" tabindex="-1" aria-labelledby="addDomesticModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="addDomesticModalLabel">Add New Domestic Expense</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <form method="post">
          <div class="modal-body">
            <input type="hidden" name="action" value="add_domestic_expense">
            <div class="form-group">
              <label>Subcategory</label>
              <input type="text" name="subcategory" class="form-control" required>
            </div>
            <div class="form-group">
              <label>Total Amount (₨)</label>
              <input type="number" step="0.01" name="amount" class="form-control" required>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-primary">Save changes</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Edit Domestic Expense Modal -->
  <div class="modal fade" id="editDomesticModal" tabindex="-1" aria-labelledby="editDomesticModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="editDomesticModalLabel">Edit Domestic Expense</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <form method="post">
          <div class="modal-body">
            <input type="hidden" name="action" value="edit_domestic_expense">
            <input type="hidden" name="id" id="edit-domestic-id">
            <div class="form-group">
              <label>Subcategory</label>
              <input type="text" name="subcategory" id="edit-domestic-subcategory" class="form-control" required>
            </div>
            <div class="form-group">
              <label>Total Amount (₨)</label>
              <input type="number" step="0.01" name="amount" id="edit-domestic-amount" class="form-control" required>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-primary">Save changes</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Delete Domestic Expense Modal -->
  <div class="modal fade" id="deleteDomesticModal" tabindex="-1" aria-labelledby="deleteDomesticModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="deleteDomesticModalLabel">Delete Domestic Expense</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <form method="post">
          <div class="modal-body">
            <input type="hidden" name="action" value="delete_domestic_expense">
            <input type="hidden" name="id" id="delete-domestic-id">
            <p>Are you sure you want to delete this record?</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-danger">Delete</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script>
    $('#editSalaryModal').on('show.bs.modal', function (event) {
      var button = $(event.relatedTarget);
      var id = button.data('id');
      var name = button.data('name');
      var basic = button.data('basic');
      var bonus = button.data('bonus');
      var deduction = button.data('deduction');
      var other = button.data('other');

      var modal = $(this);
      modal.find('#edit-salary-id').val(id);
      modal.find('#edit-salary-name').val(name);
      modal.find('#edit-salary-basic').val(basic);
      modal.find('#edit-salary-bonus').val(bonus);
      modal.find('#edit-salary-deduction').val(deduction);
      modal.find('#edit-salary-other').val(other);
    });

    $('#deleteSalaryModal').on('show.bs.modal', function (event) {
      var button = $(event.relatedTarget);
      var id = button.data('id');

      var modal = $(this);
      modal.find('#delete-salary-id').val(id);
    });

    $('#editBusinessModal').on('show.bs.modal', function (event) {
      var button = $(event.relatedTarget);
      var id = button.data('id');
      var subcategory = button.data('subcategory');
      var amount = button.data('amount');

      var modal = $(this);
      modal.find('#edit-business-id').val(id);
      modal.find('#edit-business-subcategory').val(subcategory);
      modal.find('#edit-business-amount').val(amount);
    });

    $('#deleteBusinessModal').on('show.bs.modal', function (event) {
      var button = $(event.relatedTarget);
      var id = button.data('id');

      var modal = $(this);
      modal.find('#delete-business-id').val(id);
    });

    $('#editDomesticModal').on('show.bs.modal', function (event) {
      var button = $(event.relatedTarget);
      var id = button.data('id');
      var subcategory = button.data('subcategory');
      var amount = button.data('amount');

      var modal = $(this);
      modal.find('#edit-domestic-id').val(id);
      modal.find('#edit-domestic-subcategory').val(subcategory);
      modal.find('#edit-domestic-amount').val(amount);
    });

    $('#deleteDomesticModal').on('show.bs.modal', function (event) {
      var button = $(event.relatedTarget);
      var id = button.data('id');

      var modal = $(this);
      modal.find('#delete-domestic-id').val(id);
    });
  </script>
</body>
</html>
