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

// Fetch recent activities (last 20 transactions for detailed view)
$recentActivities = $conn->query("SELECT description, amount, date FROM (
                                    SELECT 'Income' AS description, amount, date FROM FreelancingAccounts
                                    UNION ALL
                                    SELECT 'Expense' AS description, amount, date FROM Expenses
                                    UNION ALL
                                    SELECT 'Outsourcing' AS description, amount, date FROM OutsourcingProjects
                                    UNION ALL
                                    SELECT 'Salary' AS description, amount, date FROM Payments
                                  ) AS recent_transactions
                                  ORDER BY date DESC LIMIT 20")->fetch_all(MYSQLI_ASSOC);
?>

<?php if (!empty($recentActivities)): ?>
<ul class="list-group">
    <?php foreach ($recentActivities as $activity): ?>
    <li class="list-group-item d-flex justify-content-between align-items-center">
        <?php echo $activity['description']; ?> - ₨ <?php echo number_format($activity['amount'], 2); ?>
        <span class="badge bg-primary rounded-pill"><?php echo date('d M, Y', strtotime($activity['date'])); ?></span>
    </li>
    <?php endforeach; ?>
</ul>
<?php else: ?>
<p>No recent activities found.</p>
<?php endif; ?>
