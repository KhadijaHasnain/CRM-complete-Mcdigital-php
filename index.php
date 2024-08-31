<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Freelance Business Management App</title>
  <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
  <style>
    .navbar-nav .nav-item {
      cursor: pointer;
    }
    .section {
      padding: 60px 0;
    }
    .section-title {
      margin-bottom: 40px;
    }
    .section img {
      max-width: 100%;
      height: auto;
    }
  </style>
</head>
<body>
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <a class="navbar-brand" href="#">Freelance Business Management</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ml-auto">
        <li class="nav-item" onclick="scrollToSection('about')">
          <a class="nav-link">About</a>
        </li>
        <li class="nav-item" onclick="scrollToSection('dashboard')">
          <a class="nav-link">Dashboard</a>
        </li>
        <li class="nav-item" onclick="scrollToSection('account')">
          <a class="nav-link">Account</a>
        </li>
        <li class="nav-item" onclick="scrollToSection('payment')">
          <a class="nav-link">Payment</a>
        </li>
        <li class="nav-item" onclick="scrollToSection('salary')">
          <a class="nav-link">Salary</a>
        </li>
        <li class="nav-item">
          <a class="nav-link btn btn-primary" href="login.php">Login</a>
        </li>
      </ul>
    </div>
  </nav>

  <div class="container">
    <!-- About Section -->
    <section id="about" class="section">
      <div class="row">
        <div class="col-md-12">
          <h2 class="section-title">About the App</h2>
          <p>This freelance business management application helps freelancers and small teams manage their projects, payments, accounts, and more, all in one place.</p>
          <p>With an intuitive dashboard and comprehensive features, you can easily track your income, expenses, and team performance.</p>
        </div>
       
      </div>
    </section>

    <!-- Dashboard Section -->
    <section id="dashboard" class="section bg-light">
      <div class="row">
        <div class="col-md-6">
          <img src="assets/img/dashboard.jpg" alt="Dashboard Image">
        </div>
        <div class="col-md-6">
          <h2 class="section-title">Dashboard</h2>
          <p>The dashboard provides a comprehensive overview of your business, including total income, expenses, outsourcing costs, and savings. You can also view detailed charts and graphs to analyze your financial performance over time.</p>
        </div>
      </div>
    </section>

    <!-- Account Section -->
    <section id="account" class="section">
      <div class="row">
        <div class="col-md-6">
          <h2 class="section-title">Account Management</h2>
          <p>Manage your freelancing accounts efficiently with features that allow you to track income, manage expenses, and monitor account balances.</p>
          <p>Keep your finances organized and easily accessible.</p>
        </div>
        <div class="col-md-6">
        <img src="assets/img/account.jpg" alt="Dashboard Image">
        </div>
      </div>
    </section>

    <!-- Payment Section -->
    <section id="payment" class="section bg-light">
      <div class="row">
        <div class="col-md-6">
        <img src="assets/img/payments.jpg" alt="Dashboard Image">
        </div>
        <div class="col-md-6">
          <h2 class="section-title">Payment Management</h2>
          <p>Efficiently manage the salaries of your team members with detailed records of basic salary, medical benefits, bonuses, and deductions.</p>
          <p>Maintain transparency and accuracy in salary distribution.</p>
        </div>
      </div>
    </section>

    <!-- Salary Section -->
    <section id="salary" class="section">
      <div class="row">
        <div class="col-md-6">
          <h2 class="section-title">Expense Management</h2>
          <p>Efficiently manage the salaries of your team members with detailed records of basic salary, medical benefits, bonuses, and deductions.</p>
          <p>Maintain transparency and accuracy in salary distribution.</p>
        </div>
        <div class="col-md-6">
        <img src="assets/img/expense.jpg" alt="Dashboard Image">
        </div>
      </div>
    </section>
  </div>

  <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
  <script>
    function scrollToSection(sectionId) {
      document.getElementById(sectionId).scrollIntoView({ behavior: 'smooth' });
    }
  </script>
</body>
</html>
