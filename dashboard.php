<?php
/**
 * Dashboard
 * HRMS - Human Resource Management System
 */

require_once 'config/config.php';
requireLogin();

$pageTitle = 'Dashboard';
$activePage = 'dashboard';

// Get dashboard statistics
try {
    $db = getDB();

    // Get current month and year
    $currentMonth = date('n');
    $currentYear = date('Y');

    // Call dashboard stats procedure
    $stmt = $db->prepare("CALL sp_get_dashboard_stats(?, ?)");
    $stmt->execute([$currentMonth, $currentYear]);
    $dashboardStats = $stmt->fetch() ?: [];
    $stmt->closeCursor();

    // Get active staff count
    $stmt = $db->query("SELECT COUNT(*) as count FROM staff WHERE employee_status = 'Active'");
    $activeStaff = $stmt->fetch()['count'];

    // Get today's attendance
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM attendance WHERE attendance_date = CURDATE() AND status = 'Present'");
    $stmt->execute();
    $todayAttendance = $stmt->fetch()['count'];

    // Get pending leave applications
    $stmt = $db->query("SELECT COUNT(*) as count FROM leave_applications WHERE status = 'Pending'");
    $pendingLeaves = $stmt->fetch()['count'];

    // Get this month's sales
    $stmt = $db->prepare("
        SELECT COUNT(*) as total_sales, COALESCE(SUM(sale_value), 0) as total_value
        FROM sales_transactions
        WHERE MONTH(transaction_date) = ? AND YEAR(transaction_date) = ? AND status = 'Completed'
    ");
    $stmt->execute([$currentMonth, $currentYear]);
    $salesData = $stmt->fetch();

    // Get pending incentives
    $stmt = $db->query("SELECT COUNT(*) as count, COALESCE(SUM(final_amount), 0) as total FROM incentive_calculations WHERE status = 'Pending'");
    $pendingIncentives = $stmt->fetch();

    // Get recent sales
    $stmt = $db->query("
        SELECT st.*, pc.category_name, CONCAT(s.first_name, ' ', s.last_name) as staff_name
        FROM sales_transactions st
        JOIN product_categories pc ON st.category_id = pc.category_id
        JOIN staff s ON st.primary_staff_id = s.staff_id
        WHERE st.status = 'Completed'
        ORDER BY st.transaction_date DESC, st.created_at DESC
        LIMIT 10
    ");
    $recentSales = $stmt->fetchAll();

    // Get recent leave applications
    $stmt = $db->query("
        SELECT la.*, lt.leave_type_name, CONCAT(s.first_name, ' ', s.last_name) as staff_name
        FROM leave_applications la
        JOIN leave_types lt ON la.leave_type_id = lt.leave_type_id
        JOIN staff s ON la.staff_id = s.staff_id
        ORDER BY la.applied_date DESC
        LIMIT 5
    ");
    $recentLeaves = $stmt->fetchAll();

    // Get monthly sales trend for chart (last 6 months)
    $stmt = $db->query("
        SELECT DATE_FORMAT(transaction_date, '%Y-%m') as month,
               SUM(sale_value) as total_sales
        FROM sales_transactions
        WHERE transaction_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
              AND status = 'Completed'
        GROUP BY DATE_FORMAT(transaction_date, '%Y-%m')
        ORDER BY month ASC
    ");
    $salesTrend = $stmt->fetchAll();

} catch (Exception $e) {
    error_log("Dashboard error: " . $e->getMessage());
    $activeStaff = 0;
    $todayAttendance = 0;
    $pendingLeaves = 0;
    $salesData = ['total_sales' => 0, 'total_value' => 0];
    $pendingIncentives = ['count' => 0, 'total' => 0];
    $recentSales = [];
    $recentLeaves = [];
    $salesTrend = [];
}

include 'templates/header.php';
?>

<div class="row mb-4">
    <div class="col-12">
        <h2>Welcome back, <?php echo htmlspecialchars(getCurrentUser()['username'] ?? 'User'); ?>!</h2>
        <p class="text-muted">Here's what's happening with your organization today.</p>
    </div>
</div>

<!-- Stats Cards -->
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-value"><?php echo $activeStaff; ?></div>
            <div class="stat-label">Active Employees</div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                <i class="fas fa-calendar-check"></i>
            </div>
            <div class="stat-value"><?php echo $todayAttendance; ?></div>
            <div class="stat-label">Present Today</div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                <i class="fas fa-shopping-cart"></i>
            </div>
            <div class="stat-value"><?php echo $salesData['total_sales']; ?></div>
            <div class="stat-label">Sales This Month</div>
            <small class="text-muted"><?php echo formatCurrency($salesData['total_value']); ?></small>
        </div>
    </div>

    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
                <i class="fas fa-trophy"></i>
            </div>
            <div class="stat-value"><?php echo $pendingIncentives['count']; ?></div>
            <div class="stat-label">Pending Incentives</div>
            <small class="text-muted"><?php echo formatCurrency($pendingIncentives['total']); ?></small>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Sales Trend Chart -->
    <div class="col-md-8">
        <div class="table-card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-chart-line"></i> Sales Trend (Last 6 Months)</h5>
            </div>
            <canvas id="salesChart" height="80"></canvas>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="col-md-4">
        <div class="table-card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-bolt"></i> Quick Actions</h5>
            </div>
            <div class="d-grid gap-2">
                <a href="<?php echo APP_URL; ?>/modules/attendance/mark.php" class="btn btn-outline-primary">
                    <i class="fas fa-calendar-check"></i> Mark Attendance
                </a>
                <a href="<?php echo APP_URL; ?>/modules/sales/add.php" class="btn btn-outline-success">
                    <i class="fas fa-plus-circle"></i> Add Sale
                </a>
                <a href="<?php echo APP_URL; ?>/modules/leave/apply.php" class="btn btn-outline-warning">
                    <i class="fas fa-calendar-minus"></i> Apply Leave
                </a>
                <?php if (hasPermission(getCurrentUserRole(), 'Manager')): ?>
                <a href="<?php echo APP_URL; ?>/modules/staff/add.php" class="btn btn-outline-info">
                    <i class="fas fa-user-plus"></i> Add Employee
                </a>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($pendingLeaves > 0 && hasPermission(getCurrentUserRole(), 'Manager')): ?>
        <div class="table-card mt-3">
            <div class="alert alert-warning mb-0">
                <i class="fas fa-exclamation-triangle"></i>
                <strong><?php echo $pendingLeaves; ?></strong> pending leave applications require approval.
                <a href="<?php echo APP_URL; ?>/modules/leave/index.php" class="alert-link">Review now</a>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<div class="row g-4">
    <!-- Recent Sales -->
    <div class="col-md-7">
        <div class="table-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-shopping-cart"></i> Recent Sales</h5>
                <a href="<?php echo APP_URL; ?>/modules/sales/index.php" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Transaction</th>
                            <th>Date</th>
                            <th>Category</th>
                            <th>Staff</th>
                            <th>Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recentSales)): ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted">No sales recorded yet</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($recentSales as $sale): ?>
                                <tr>
                                    <td><small class="text-muted"><?php echo htmlspecialchars($sale['transaction_code']); ?></small></td>
                                    <td><?php echo formatDate($sale['transaction_date']); ?></td>
                                    <td><span class="badge bg-info"><?php echo htmlspecialchars($sale['category_name']); ?></span></td>
                                    <td><?php echo htmlspecialchars($sale['staff_name']); ?></td>
                                    <td><strong><?php echo formatCurrency($sale['sale_value']); ?></strong></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Recent Leave Applications -->
    <div class="col-md-5">
        <div class="table-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-calendar-minus"></i> Recent Leave Requests</h5>
                <a href="<?php echo APP_URL; ?>/modules/leave/index.php" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Staff</th>
                            <th>Type</th>
                            <th>Days</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recentLeaves)): ?>
                            <tr>
                                <td colspan="4" class="text-center text-muted">No leave applications</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($recentLeaves as $leave): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($leave['staff_name']); ?></td>
                                    <td><small><?php echo htmlspecialchars($leave['leave_type_name']); ?></small></td>
                                    <td><?php echo $leave['total_days']; ?></td>
                                    <td>
                                        <?php
                                        $badgeClass = 'bg-secondary';
                                        if ($leave['status'] == 'Approved') $badgeClass = 'bg-success';
                                        elseif ($leave['status'] == 'Rejected') $badgeClass = 'bg-danger';
                                        elseif ($leave['status'] == 'Pending') $badgeClass = 'bg-warning';
                                        ?>
                                        <span class="badge <?php echo $badgeClass; ?>"><?php echo $leave['status']; ?></span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php
$customJS = "
// Sales Trend Chart
const ctx = document.getElementById('salesChart');
if (ctx) {
    const salesData = " . json_encode($salesTrend) . ";
    const labels = salesData.map(item => item.month);
    const data = salesData.map(item => parseFloat(item.total_sales));

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Sales',
                data: data,
                borderColor: 'rgb(102, 126, 234)',
                backgroundColor: 'rgba(102, 126, 234, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '₹ ' + value.toLocaleString();
                        }
                    }
                }
            }
        }
    });
}
";
?>

<?php include 'templates/footer.php'; ?>
