<div id="layoutSidenav_nav">
    <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">    
        <div class="sb-sidenav-menu">
            <div class="nav">
                <div class="sb-sidenav-menu-heading">Core</div>
                <a class="nav-link" href="dashboard.php">
                    <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                    Dashboard
                </a>
                <div class="sb-sidenav-menu-heading">Freelance Management</div>
                <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseFreelance" aria-expanded="false" aria-controls="collapseFreelance">
                    <div class="sb-nav-link-icon"><i class="fas fa-briefcase"></i></div>
                    Freelance Management
                    <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                </a>
                <div class="collapse" id="collapseFreelance" aria-labelledby="headingOne" data-bs-parent="#sidenavAccordion">
                    <nav class="sb-sidenav-menu-nested nav">
                        <a class="nav-link" href="accounts.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-wallet"></i></div>
                             Accounts
                        </a>
                        <a class="nav-link" href="payments.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-dollar-sign"></i></div>
                            Payments
                        </a>
                        <a class="nav-link" href="outsourcing.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-project-diagram"></i></div>
                            Outsourcing 
                        </a>
                        <a class="nav-link" href="expenses.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-file-invoice-dollar"></i></div>
                            Expenses
                        </a>
                        <a class="nav-link" href="reports.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-chart-line"></i></div>
                            Reports
                        </a>
                    </nav>
                </div>
                <div class="sb-sidenav-menu-heading">Summaries</div>
                <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseSummaries" aria-expanded="false" aria-controls="collapseSummaries">
                    <div class="sb-nav-link-icon"><i class="fas fa-book"></i></div>
                    Summaries
                    <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                </a>
                <div class="collapse" id="collapseSummaries" aria-labelledby="headingTwo" data-bs-parent="#sidenavAccordion">
                    <nav class="sb-sidenav-menu-nested nav">
                        <a class="nav-link" href="income_summary.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-file-alt"></i></div>
                            Income Summary
                        </a>
                        <a class="nav-link" href="expense_summary.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-file-alt"></i></div>
                            Expense Summary
                        </a>
                        <a class="nav-link" href="outsourcing_summary.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-file-alt"></i></div>
                            Outsourcing Summary
                        </a>
                        <a class="nav-link" href="overall_summary.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-file-alt"></i></div>
                            Overall Summary
                        </a>
                    </nav>
                </div>
                <div class="sb-sidenav-menu-heading">Team Management</div>
                <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseTeamManagement" aria-expanded="false" aria-controls="collapseTeamManagement">
                    <div class="sb-nav-link-icon"><i class="fas fa-users"></i></div>
                    Team Management
                    <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                </a>
                <div class="collapse" id="collapseTeamManagement" aria-labelledby="headingThree" data-bs-parent="#sidenavAccordion">
                    <nav class="sb-sidenav-menu-nested nav">
                        <a class="nav-link" href="team_summary.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-users"></i></div>
                            Team Summary
                        </a>
                        <a class="nav-link" href="teamsalary.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-file-alt"></i></div>
                            Team Salary
                        </a>
                    </nav>
                </div>

             
                <div class="sb-sidenav-menu-heading">Financial</div>
                <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseFinancial" aria-expanded="false" aria-controls="collapseFinancial">
                    <div class="sb-nav-link-icon"><i class="fas fa-chart-pie"></i></div>
                    Financial
                    <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                </a>

                
                <div class="collapse" id="collapseFinancial" aria-labelledby="headingFour" data-bs-parent="#sidenavAccordion">
                    <nav class="sb-sidenav-menu-nested nav">
                        <a class="nav-link" href="profit_margins.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-dollar-sign"></i></div>
                            Profit Margins
                        </a>
                     

                        <a class="nav-link" href=" fixed_expenses.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-dollar-sign"></i></div>
                            Fixed_Expenses
                        </a>
                    </nav>
                </div>
                <a class="nav-link" href="logout.php">
                    <div class="sb-nav-link-icon"><i class="fas fa-sign-out-alt"></i></div>
                    Logout
                </a>
            </div>
        </div>
        <div class="sb-sidenav-footer">
            <div class="small">Logged in as:</div>
            <?php echo $_SESSION['username']; ?>
        </div>
    </nav>
</div>
