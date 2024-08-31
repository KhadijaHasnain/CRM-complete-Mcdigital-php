<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'db_connect.php';

// Fetch recent activities count for the current date
$currentDate = date('Y-m-d');
$recentActivitiesCount = $conn->query("SELECT COUNT(*) AS count FROM (
                                        SELECT date FROM FreelancingAccounts WHERE DATE(date) = '$currentDate'
                                        UNION ALL
                                        SELECT date FROM Expenses WHERE DATE(date) = '$currentDate'
                                        UNION ALL
                                        SELECT date FROM OutsourcingProjects WHERE DATE(date) = '$currentDate'
                                        UNION ALL
                                        SELECT date FROM Payments WHERE DATE(date) = '$currentDate'
                                      ) AS recent_transactions")->fetch_assoc()['count'];
?>




<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Financial Dashboard</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet">
    <link href="../css/styles.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.3/Chart.bundle.min.js"></script>
    <script src="js/scripts.js"></script>
</head>


<nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">
    <a class="navbar-brand ps-3" href="dashboard.php">Financial Dashboard</a>
    <button class="btn btn-link btn-sm order-1 order-lg-0 me-4 me-lg-0" id="sidebarToggle" href="#!"><i class="fas fa-bars"></i></button>
    <!-- Navbar Search-->
    <form class="d-none d-md-inline-block form-inline ms-auto me-0 me-md-3 my-2 my-md-0">
        <div class="input-group">
            <input class="form-control" type="text" placeholder="Search for..." aria-label="Search for..." aria-describedby="btnNavbarSearch" />
            <button class="btn btn-primary" id="btnNavbarSearch" type="button"><i class="fas fa-search"></i></button>
        </div>
    </form>
    <!-- Navbar-->
    <ul class="navbar-nav ms-auto ms-md-0 me-3 me-lg-4">
        <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" id="navbarDropdown" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fas fa-bell fa-fw"></i>
                <?php if ($recentActivitiesCount > 0): ?>
                    <span class="badge bg-danger"><?php echo $recentActivitiesCount; ?></span>
                <?php endif; ?>
            </a>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                <li><h6 class="dropdown-header">Recent Activities</h6></li>
                <?php
                // Fetch recent activities for the current date
                $recentActivities = $conn->query("SELECT description, amount, date FROM (
                                                    SELECT 'Income' AS description, amount, date FROM FreelancingAccounts WHERE DATE(date) = '$currentDate'
                                                    UNION ALL
                                                    SELECT 'Expense' AS description, amount, date FROM Expenses WHERE DATE(date) = '$currentDate'
                                                    UNION ALL
                                                    SELECT 'Outsourcing' AS description, amount, date FROM OutsourcingProjects WHERE DATE(date) = '$currentDate'
                                                    UNION ALL
                                                    SELECT 'Salary' AS description, amount, date FROM Payments WHERE DATE(date) = '$currentDate'
                                                  ) AS recent_transactions
                                                  ORDER BY date DESC")->fetch_all(MYSQLI_ASSOC);
                ?>
                <?php if (!empty($recentActivities)): ?>
                    <?php foreach ($recentActivities as $activity): ?>
                        <li class="dropdown-item d-flex justify-content-between align-items-center">
                            <span><?php echo $activity['description']; ?> - ₨ <?php echo number_format($activity['amount'], 2); ?></span>
                            <span class="badge bg-primary rounded-pill"><?php echo date('d M, Y', strtotime($activity['date'])); ?></span>
                        </li>
                    <?php endforeach; ?>
                <?php else: ?>
                    <li class="dropdown-item">No recent activities</li>
                <?php endif; ?>
                <li><hr class="dropdown-divider" /></li>
                <li><a class="dropdown-item" href="#" onclick="loadData('recent_activities')">View All</a></li>
            </ul>
        </li>
        <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" id="navbarDropdownUser" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false"><i class="fas fa-user fa-fw"></i></a>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdownUser">
                <li><a class="dropdown-item" href="#!">Settings</a></li>
                <li><a class="dropdown-item" href="#!">Activity Log</a></li>
                <li><hr class="dropdown-divider" /></li>
                <li><a class="dropdown-item" href="logout.php">Logout</a></li>
            </ul>
        </li>
    </ul>
</nav>

<script>
    function loadData(type) {
        $.ajax({
            url: 'load_data.php',
            type: 'GET',
            data: { type: type },
            success: function(response) {
                $('#modalTitle').text(type.replace('_', ' ').replace(/\b\w/g, function(l){ return l.toUpperCase() }) + ' Details');
                $('#modalBody').html(response);
                $('#dataModal').modal('show');
            }
        });
    }
</script>
