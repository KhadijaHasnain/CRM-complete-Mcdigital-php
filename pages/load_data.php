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

$type = $_GET['type'];
$currentYear = date('Y');
$currentMonth = date('m');

$data = [];

switch ($type) {
    case 'income':
        $data = $conn->query("SELECT * FROM FreelancingAccounts WHERE YEAR(date) = $currentYear AND MONTH(date) = $currentMonth ORDER BY date DESC")->fetch_all(MYSQLI_ASSOC);
        break;
    case 'expenses':
        $data = $conn->query("SELECT * FROM Expenses WHERE YEAR(date) = $currentYear AND MONTH(date) = $currentMonth ORDER BY date DESC")->fetch_all(MYSQLI_ASSOC);
        break;
    case 'outsourcing':
        $data = $conn->query("SELECT project_name, platform, amount, date FROM OutsourcingProjects WHERE YEAR(date) = $currentYear AND MONTH(date) = $currentMonth ORDER BY date DESC")->fetch_all(MYSQLI_ASSOC);
        break;
    case 'salaries':
        $data = $conn->query("SELECT Users.username, Payments.medical, Payments.amount, Payments.date 
                              FROM Payments 
                              JOIN Users ON Payments.team_member_id = Users.id 
                              WHERE YEAR(Payments.date) = $currentYear AND MONTH(Payments.date) = $currentMonth ORDER BY Payments.date DESC")->fetch_all(MYSQLI_ASSOC);
        break;
    case 'savings':
        $income = $conn->query("SELECT SUM(amount) AS total FROM FreelancingAccounts WHERE YEAR(date) = $currentYear AND MONTH(date) = $currentMonth")->fetch_assoc()['total'];
        $expenses = $conn->query("SELECT SUM(amount) AS total FROM Expenses WHERE YEAR(date) = $currentYear AND MONTH(date) = $currentMonth")->fetch_assoc()['total'];
        $outsourcing = $conn->query("SELECT SUM(amount) AS total FROM OutsourcingProjects WHERE YEAR(date) = $currentYear AND MONTH(date) = $currentMonth")->fetch_assoc()['total'];
        $salaries = $conn->query("SELECT SUM(amount + IFNULL(medical, 0) + IFNULL(bonuses, 0) - IFNULL(tax, 0) - IFNULL(deductions, 0)) AS total FROM Payments WHERE YEAR(date) = $currentYear AND MONTH(date) = $currentMonth")->fetch_assoc()['total'];
        $savings = $income - ($expenses + $outsourcing + $salaries);
        $data = [['description' => 'Savings', 'amount' => $savings, 'date' => date('Y-m-d')]];
        break;
    case 'recent_activities':
        $data = $conn->query("SELECT description, amount, date FROM (
                                SELECT 'Income' AS description, amount, date FROM FreelancingAccounts
                                UNION ALL
                                SELECT 'Expense' AS description, amount, date FROM Expenses
                                UNION ALL
                                SELECT 'Outsourcing' AS description, amount, date FROM OutsourcingProjects
                                UNION ALL
                                SELECT 'Salary' AS description, amount, date FROM Payments
                              ) AS recent_transactions
                              ORDER BY date DESC LIMIT 20")->fetch_all(MYSQLI_ASSOC);
        break;
    default:
        $data = [];
        break;
}

if (!empty($data)) {
    echo '<ul class="list-group">';
    foreach ($data as $item) {
        $description = $item['description'] ?? $type;
        echo '<li class="list-group-item d-flex justify-content-between align-items-center">';
        echo $description . ' - ₨ ' . number_format($item['amount'], 2);
        echo '<span class="badge bg-primary rounded-pill">' . date('d M, Y', strtotime($item['date'])) . '</span>';
        echo '</li>';
    }
    echo '</ul>';
} else {
    echo '<p>No data found.</p>';
}
?>
