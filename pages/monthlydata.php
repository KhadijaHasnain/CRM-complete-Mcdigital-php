



  <?php
// Database connection
$conn = new mysqli('localhost', 'root', '', 'freelance_bussiness');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Add goal
if (isset($_POST['add_goal']) && isset($_POST['goal_type'])) {
    $goal_type = $_POST['goal_type'];
    $category = isset($_POST['category']) ? $_POST['category'] : null;
    $goal = $_POST['goal'];
    $month = $_POST['month'];
    $year = $_POST['year'];

    if ($goal_type && $goal && $month && $year) {
        $stmt = $conn->prepare("INSERT INTO MonthlyGoals (goal_type, category, goal, month, year) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssdii", $goal_type, $category, $goal, $month, $year);
        $stmt->execute();
        $stmt->close();
    }

    // Redirect to avoid form resubmission
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Fetch goals for the current month
$currentMonth = date('m');
$currentYear = date('Y');
$goals = $conn->query("SELECT * FROM MonthlyGoals WHERE month = $currentMonth AND year = $currentYear");

if (!$goals) {
    die("Query Failed: " . $conn->error);
}

$goalData = [];
while ($row = $goals->fetch_assoc()) {
    $goalData[] = $row;
}

// Fetch current earnings and expenses for the current month
$earnings = $conn->query("SELECT SUM(amount) AS total FROM FreelancingAccounts WHERE DATE_FORMAT(date, '%Y-%m') = '$currentYear-$currentMonth'")->fetch_assoc()['total'];
$domesticExpenses = $conn->query("SELECT SUM(amount) AS total FROM Expenses WHERE category = 'Domestic' AND DATE_FORMAT(date, '%Y-%m') = '$currentYear-$currentMonth'")->fetch_assoc()['total'];
$businessExpenses = $conn->query("SELECT SUM(amount) AS total FROM Expenses WHERE category = 'Business' AND DATE_FORMAT(date, '%Y-%m') = '$currentYear-$currentMonth'")->fetch_assoc()['total'];

$earnings = $earnings ?: 0;
$domesticExpenses = $domesticExpenses ?: 0;
$businessExpenses = $businessExpenses ?: 0;

$totalExpenses = $domesticExpenses + $businessExpenses;

// Fetch data for the current month
$currentMonthIncome = $conn->query("SELECT SUM(amount) AS total FROM FreelancingAccounts WHERE YEAR(date) = $currentYear AND MONTH(date) = $currentMonth")->fetch_assoc()['total'];
$currentMonthExpenses = $conn->query("SELECT SUM(amount) AS total FROM Expenses WHERE YEAR(date) = $currentYear AND MONTH(date) = $currentMonth")->fetch_assoc()['total'];
$currentMonthOutsourcing = $conn->query("SELECT SUM(amount) AS total FROM OutsourcingProjects WHERE YEAR(date) = $currentYear AND MONTH(date) = $currentMonth")->fetch_assoc()['total'];
$currentMonthSalaries = $conn->query("SELECT SUM(amount + IFNULL(medical, 0) + IFNULL(bonuses, 0) - IFNULL(tax, 0) - IFNULL(deductions, 0)) AS total FROM Payments WHERE YEAR(date) = $currentYear AND MONTH(date) = $currentMonth")->fetch_assoc()['total'];

$totalSaving = $currentMonthIncome - ($currentMonthExpenses + $currentMonthOutsourcing + $currentMonthSalaries);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monthly Goals</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .progress {
            height: 25px;
        }
        .category-column {
            display: none;
        }
    </style>
</head>
<body>
<div class="container mt-4">

<div class="row">
    <div class="col-lg-3 col-md-6 mb-4">
      <a href="accounts.php" class="btn btn-primary btn-sm d-flex flex-column justify-content-center align-items-center p-3">
        <i class="fas fa-plus mb-2"></i>
        <span>Add Income</span>
      </a>
    </div>
    <div class="col-lg-3 col-md-6 mb-4">
      <a href="expenses.php" class="btn btn-success btn-sm d-flex flex-column justify-content-center align-items-center p-3">
        <i class="fas fa-plus mb-2"></i>
        <span>Add Expense</span>
      </a>
    </div>
    <div class="col-lg-3 col-md-6 mb-4">
      <a href="outsourcing.php" class="btn btn-warning btn-sm d-flex flex-column justify-content-center align-items-center p-3">
        <i class="fas fa-plus mb-2"></i>
        <span>Add Outsourcing</span>
      </a>
    </div>
    <div class="col-lg-3 col-md-6 mb-4">
      <a href="payments.php" class="btn btn-danger btn-sm d-flex flex-column justify-content-center align-items-center p-3">
        <i class="fas fa-plus mb-2"></i>
        <span>Add Salary</span>
      </a>
    </div>
  </div>

    <h2 class="text-center">Monthly Goals for Income and Expense <?php echo date('F Y'); ?></h2>
    <button class="btn btn-primary mb-3" data-toggle="modal" data-target="#addGoalModal">Add Goal</button>

    <div class="row">
        <div class="col-md-6">
            <canvas id="earningsChart"></canvas>
        </div>
        <div class="col-md-6">
            <canvas id="expensesChart"></canvas>
        </div>
    </div>

    <div class="container-fluid px-4">
        <div class="card-header bg-dark text-white">
            <i class="fas fa-calendar-alt me-1"></i>
            Current Month Summary
        </div>
        <table class="table table-dark table-striped">
            <thead>
                <tr>
                    <th scope="col">Income</th>
                    <th scope="col">Expenses</th>
                    <th scope="col">Outsourcing</th>
                    <th scope="col">Salaries</th>
                    <th scope="col">Savings</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td onclick="loadData('income')"><i class="fas fa-wallet me-2"></i>₨ <?php echo number_format($currentMonthIncome); ?></td>
                    <td onclick="loadData('expenses')"><i class="fas fa-money-bill-wave me-2"></i>₨ <?php echo number_format($currentMonthExpenses); ?></td>
                    <td onclick="loadData('outsourcing')"><i class="fas fa-external-link-alt me-2"></i>₨ <?php echo number_format($currentMonthOutsourcing); ?></td>
                    <td onclick="loadData('salaries')"><i class="fas fa-user-tie me-2"></i>₨ <?php echo number_format($currentMonthSalaries); ?></td>
                    <td onclick="loadData('savings')"><i class="fas fa-piggy-bank me-2"></i>₨ <?php echo number_format($totalSaving); ?></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Goal Modal -->
<div class="modal fade" id="addGoalModal" tabindex="-1" role="dialog" aria-labelledby="addGoalModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addGoalModalLabel">Add New Goal</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="addGoalForm" method="POST" action="">
                    <div class="form-group">
                        <label for="goal_type">Goal Type</label>
                        <select class="form-control" id="goal_type" name="goal_type" required>
                            <option value="Earning">Earning</option>
                            <option value="Expense">Expense</option>
                        </select>
                    </div>
                    <div class="form-group" id="category_group">
                        <label for="category">Category</label>
                        <select class="form-control" id="category" name="category">
                            <option value="Domestic">Domestic</option>
                            <option value="Business">Business</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="goal">Goal</label>
                        <input type="number" class="form-control" id="goal" name="goal" required>
                    </div>
                    <div class="form-group">
                        <label for="month">Month</label>
                        <input type="number" class="form-control" id="month" name="month" required value="<?php echo $currentMonth; ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label for="year">Year</label>
                        <input type="number" class="form-control" id="year" name="year" required value="<?php echo $currentYear; ?>" readonly>
                    </div>
                    <button type="submit" class="btn btn-primary" name="add_goal">Add Goal</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="dataModal" tabindex="-1" role="dialog" aria-labelledby="dataModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle"></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="modalBody">
                <!-- Data will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script>
    var goals = <?php echo json_encode($goalData); ?>;
    var earnings = <?php echo $earnings; ?>;
    var domesticExpenses = <?php echo $domesticExpenses; ?>;
    var businessExpenses = <?php echo $businessExpenses; ?>;
    var totalExpenses = <?php echo $totalExpenses; ?>;

    var earningsGoal = 0;
    var expensesGoal = 0;
    var domesticGoal = 0;
    var businessGoal = 0;

    goals.forEach(function (goal) {
        if (goal.goal_type === 'Earning') {
            earningsGoal += parseFloat(goal.goal);
        } else if (goal.goal_type === 'Expense') {
            expensesGoal += parseFloat(goal.goal);
            if (goal.category === 'Domestic') {
                domesticGoal += parseFloat(goal.goal);
            } else if (goal.category === 'Business') {
                businessGoal += parseFloat(goal.goal);
            }
        }
    });

    var earningsData = {
        labels: ['Earnings'],
        datasets: [{
            label: 'Earnings',
            data: [earnings],
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
            borderColor: 'rgba(75, 192, 192, 1)',
            borderWidth: 1
        }, {
            label: 'Goal',
            data: [earningsGoal],
            backgroundColor: 'rgba(255, 99, 132, 0.2)',
            borderColor: 'rgba(255, 99, 132, 1)',
            borderWidth: 1
        }]
    };

    var expensesData = {
        labels: ['Domestic Expenses', 'Business Expenses'],
        datasets: [{
            label: 'Expenses',
            data: [domesticExpenses, businessExpenses],
            backgroundColor: 'rgba(153, 102, 255, 0.2)',
            borderColor: 'rgba(153, 102, 255, 1)',
            borderWidth: 1
        }, {
            label: 'Goal',
            data: [domesticGoal, businessGoal],
            backgroundColor: 'rgba(255, 159, 64, 0.2)',
            borderColor: 'rgba(255, 159, 64, 1)',
            borderWidth: 1
        }]
    };

    var earningsChart = new Chart(document.getElementById('earningsChart'), {
        type: 'bar',
        data: earningsData,
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    var expensesChart = new Chart(document.getElementById('expensesChart'), {
        type: 'bar',
        data: expensesData,
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    function loadData(type) {
        $.ajax({
            url: 'load_data.php',
            type: 'GET',
            data: { type: type },
            success: function(response) {
                $('#modalTitle').text(type.charAt(0).toUpperCase() + type.slice(1) + ' Details');
                $('#modalBody').html(response);
                $('#dataModal').modal('show');
            }
        });
    }
</script>
</body>
</html>
