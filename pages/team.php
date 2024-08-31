<?php


// Database connection
$conn = new mysqli('localhost', 'root', '', 'freelance_bussiness');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// List of specific team members
$team_members = ["Ilyas", "Usman", "Mehrab", "Naqeeb", "Ammar", "Zulqarnain", "Momina", "Hafsa", "Ami", "Mahnoor","Waqas"];
$placeholders = implode(',', array_fill(0, count($team_members), '?'));

// Prepare and bind
$stmt = $conn->prepare("SELECT Users.username, 
        SUM(Payments.amount + IFNULL(Payments.medical, 0) + IFNULL(Payments.bonuses, 0)) AS total_amount 
        FROM Payments 
        JOIN Users ON Payments.team_member_id = Users.id 
        WHERE Users.username IN ($placeholders)
        GROUP BY Users.username");
$stmt->bind_param(str_repeat('s', count($team_members)), ...$team_members);

$stmt->execute();
$result = $stmt->get_result();

$labels = [];
$amounts = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $labels[] = $row['username'];
        $amounts[] = $row['total_amount'];
    }
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Team Payments Overview</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="container mt-5">
        <h2 class="text-center">Team Payments Overview</h2>
        <canvas id="paymentsChart" width="400" height="200"></canvas>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            var ctx = document.getElementById('paymentsChart').getContext('2d');
            var paymentsChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: <?php echo json_encode($labels); ?>,
                    datasets: [{
                        label: 'Total Amount',
                        data: <?php echo json_encode($amounts); ?>,
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        borderColor: 'rgba(75, 192, 192, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        });
    </script>
</body>
</html>
