<?php

// Fetch monthly income and expenses data
$monthlyIncomeExpenseData = $conn->query("
    SELECT DATE_FORMAT(date, '%Y-%m') AS month,
           SUM(IF(category = 'income', amount, 0)) AS income,
           SUM(IF(category = 'expense', amount, 0)) AS expense
    FROM (
        SELECT date, amount, 'income' AS category FROM FreelancingAccounts
        UNION ALL
        SELECT date, amount, 'expense' AS category FROM Expenses
    ) AS combined
    GROUP BY month
    ORDER BY month
")->fetch_all(MYSQLI_ASSOC);

$months = array_column($monthlyIncomeExpenseData, 'month');
$monthlyIncomes = array_column($monthlyIncomeExpenseData, 'income');
$monthlyExpenses = array_column($monthlyIncomeExpenseData, 'expense');
?>
<div class="col-xl-6">
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-chart-line me-1"></i>
            Monthly Income vs Monthly Expenses
        </div>
        <div class="card-body">
            <canvas id="monthlyIncomeExpenseLineChart" width="100%" height="40"></canvas>
        </div>
    </div>
</div>

<script>
    var ctxLine = document.getElementById('monthlyIncomeExpenseLineChart').getContext('2d');
    var monthlyIncomeExpenseLineChart = new Chart(ctxLine, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($months); ?>,
            datasets: [
                {
                    label: 'Monthly Income',
                    data: <?php echo json_encode($monthlyIncomes); ?>,
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1,
                    fill: false
                },
                {
                    label: 'Monthly Expenses',
                    data: <?php echo json_encode($monthlyExpenses); ?>,
                    backgroundColor: 'rgba(255, 99, 132, 0.2)',
                    borderColor: 'rgba(255, 99, 132, 1)',
                    borderWidth: 1,
                    fill: false
                }
            ]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
</script>
