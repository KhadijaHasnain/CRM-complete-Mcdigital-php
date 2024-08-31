<?php


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
    <h2 style="text-align:center;" >Monthly Goals for Income and Expense <?php echo date('F Y'); ?></h2>
    <button class="btn btn-primary mb-3" data-toggle="modal" data-target="#addGoalModal">Add Goal</button>

    <div class="row">
        <div class="col-md-6">
            <canvas id="earningsChart"></canvas>
        </div>
        <div class="col-md-6">
            <canvas id="expensesChart"></canvas>
        </div>
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
                <form id="addGoalForm" method="POST" action="goal.php">
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
</script>
</body>
</html>
