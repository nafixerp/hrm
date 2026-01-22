<?php
/**
 * Reports & Analytics
 * HRMS - Human Resource Management System
 */

require_once '../../config/config.php';
requireLogin();

$pageTitle = 'Reports & Analytics';
$activePage = 'reports';

include '../../templates/header.php';
?>

<div class="row mb-4">
    <div class="col-md-12">
        <h2><i class="fas fa-chart-bar"></i> Reports & Analytics</h2>
    </div>
</div>

<!-- Report Categories -->
<div class="row g-4">
    <!-- Employee Reports -->
    <div class="col-md-4">
        <div class="table-card hover-card">
            <div class="card-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                <h5 class="mb-0"><i class="fas fa-users"></i> Employee Reports</h5>
            </div>
            <div class="list-group list-group-flush">
                <a href="employee-list.php" class="list-group-item list-group-item-action">
                    <i class="fas fa-list"></i> Employee List Report
                </a>
                <a href="employee-details.php" class="list-group-item list-group-item-action">
                    <i class="fas fa-id-card"></i> Employee Details Report
                </a>
                <a href="department-wise.php" class="list-group-item list-group-item-action">
                    <i class="fas fa-building"></i> Department-wise Report
                </a>
                <a href="new-joiners.php" class="list-group-item list-group-item-action">
                    <i class="fas fa-user-plus"></i> New Joiners Report
                </a>
            </div>
        </div>
    </div>

    <!-- Attendance Reports -->
    <div class="col-md-4">
        <div class="table-card hover-card">
            <div class="card-header" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); color: white;">
                <h5 class="mb-0"><i class="fas fa-calendar-check"></i> Attendance Reports</h5>
            </div>
            <div class="list-group list-group-flush">
                <a href="daily-attendance.php" class="list-group-item list-group-item-action">
                    <i class="fas fa-calendar-day"></i> Daily Attendance Report
                </a>
                <a href="monthly-attendance.php" class="list-group-item list-group-item-action">
                    <i class="fas fa-calendar-alt"></i> Monthly Attendance Report
                </a>
                <a href="attendance-summary.php" class="list-group-item list-group-item-action">
                    <i class="fas fa-chart-pie"></i> Attendance Summary
                </a>
                <a href="overtime-report.php" class="list-group-item list-group-item-action">
                    <i class="fas fa-clock"></i> Overtime Report
                </a>
                <a href="late-arrivals.php" class="list-group-item list-group-item-action">
                    <i class="fas fa-exclamation-triangle"></i> Late Arrivals Report
                </a>
            </div>
        </div>
    </div>

    <!-- Leave Reports -->
    <div class="col-md-4">
        <div class="table-card hover-card">
            <div class="card-header" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); color: white;">
                <h5 class="mb-0"><i class="fas fa-calendar-minus"></i> Leave Reports</h5>
            </div>
            <div class="list-group list-group-flush">
                <a href="leave-balance.php" class="list-group-item list-group-item-action">
                    <i class="fas fa-balance-scale"></i> Leave Balance Report
                </a>
                <a href="leave-applications.php" class="list-group-item list-group-item-action">
                    <i class="fas fa-file-alt"></i> Leave Applications Report
                </a>
                <a href="leave-summary.php" class="list-group-item list-group-item-action">
                    <i class="fas fa-chart-bar"></i> Leave Summary
                </a>
            </div>
        </div>
    </div>

    <!-- Sales Reports -->
    <div class="col-md-4">
        <div class="table-card hover-card">
            <div class="card-header" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white;">
                <h5 class="mb-0"><i class="fas fa-shopping-cart"></i> Sales Reports</h5>
            </div>
            <div class="list-group list-group-flush">
                <a href="daily-sales.php" class="list-group-item list-group-item-action">
                    <i class="fas fa-calendar-day"></i> Daily Sales Report
                </a>
                <a href="monthly-sales.php" class="list-group-item list-group-item-action">
                    <i class="fas fa-calendar-alt"></i> Monthly Sales Report
                </a>
                <a href="category-wise.php" class="list-group-item list-group-item-action">
                    <i class="fas fa-tags"></i> Category-wise Sales
                </a>
                <a href="staff-sales.php" class="list-group-item list-group-item-action">
                    <i class="fas fa-user-tag"></i> Staff-wise Sales
                </a>
                <a href="sales-trend.php" class="list-group-item list-group-item-action">
                    <i class="fas fa-chart-line"></i> Sales Trend Analysis
                </a>
            </div>
        </div>
    </div>

    <!-- Incentive Reports -->
    <div class="col-md-4">
        <div class="table-card hover-card">
            <div class="card-header" style="background: linear-gradient(135deg, #fbc2eb 0%, #a6c1ee 100%); color: white;">
                <h5 class="mb-0"><i class="fas fa-trophy"></i> Incentive Reports</h5>
            </div>
            <div class="list-group list-group-flush">
                <a href="incentive-summary.php" class="list-group-item list-group-item-action">
                    <i class="fas fa-list"></i> Incentive Summary
                </a>
                <a href="scheme-wise.php" class="list-group-item list-group-item-action">
                    <i class="fas fa-award"></i> Scheme-wise Report
                </a>
                <a href="staff-incentives.php" class="list-group-item list-group-item-action">
                    <i class="fas fa-user-check"></i> Staff Incentive Report
                </a>
                <a href="pending-incentives.php" class="list-group-item list-group-item-action">
                    <i class="fas fa-hourglass-half"></i> Pending Incentives
                </a>
            </div>
        </div>
    </div>

    <!-- Payroll Reports -->
    <div class="col-md-4">
        <div class="table-card hover-card">
            <div class="card-header" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white;">
                <h5 class="mb-0"><i class="fas fa-file-invoice-dollar"></i> Payroll Reports</h5>
            </div>
            <div class="list-group list-group-flush">
                <a href="monthly-payroll.php" class="list-group-item list-group-item-action">
                    <i class="fas fa-calendar-alt"></i> Monthly Payroll Report
                </a>
                <a href="payroll-summary.php" class="list-group-item list-group-item-action">
                    <i class="fas fa-chart-bar"></i> Payroll Summary
                </a>
                <a href="salary-register.php" class="list-group-item list-group-item-action">
                    <i class="fas fa-book"></i> Salary Register
                </a>
                <a href="deductions-report.php" class="list-group-item list-group-item-action">
                    <i class="fas fa-minus-circle"></i> Deductions Report
                </a>
            </div>
        </div>
    </div>

    <!-- Performance Reports -->
    <div class="col-md-4">
        <div class="table-card hover-card">
            <div class="card-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                <h5 class="mb-0"><i class="fas fa-chart-line"></i> Performance Reports</h5>
            </div>
            <div class="list-group list-group-flush">
                <a href="staff-performance.php" class="list-group-item list-group-item-action">
                    <i class="fas fa-user-chart"></i> Staff Performance
                </a>
                <a href="department-performance.php" class="list-group-item list-group-item-action">
                    <i class="fas fa-building"></i> Department Performance
                </a>
                <a href="top-performers.php" class="list-group-item list-group-item-action">
                    <i class="fas fa-medal"></i> Top Performers
                </a>
            </div>
        </div>
    </div>

    <!-- Custom Reports -->
    <div class="col-md-4">
        <div class="table-card hover-card">
            <div class="card-header" style="background: linear-gradient(135deg, #30cfd0 0%, #330867 100%); color: white;">
                <h5 class="mb-0"><i class="fas fa-cog"></i> Custom Reports</h5>
            </div>
            <div class="list-group list-group-flush">
                <a href="custom-report.php" class="list-group-item list-group-item-action">
                    <i class="fas fa-plus-circle"></i> Create Custom Report
                </a>
                <a href="export-data.php" class="list-group-item list-group-item-action">
                    <i class="fas fa-download"></i> Export Data
                </a>
            </div>
        </div>
    </div>
</div>

<?php include '../../templates/footer.php'; ?>
