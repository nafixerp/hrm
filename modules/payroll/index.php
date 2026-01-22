<?php
/**
 * Payroll Management
 * HRMS - Human Resource Management System
 */

require_once '../../config/config.php';
requireRole('Manager');

$pageTitle = 'Payroll Management';
$activePage = 'payroll';

// Get payroll periods
try {
    $db = getDB();
    $stmt = $db->query("
        SELECT pp.*, COUNT(p.payroll_id) as employee_count,
               SUM(p.net_salary) as total_payroll
        FROM payroll_periods pp
        LEFT JOIN payroll p ON pp.period_id = p.period_id
        GROUP BY pp.period_id
        ORDER BY pp.year DESC, pp.month DESC
    ");
    $periods = $stmt->fetchAll();

    // Get current period payroll if exists
    $currentMonth = date('n');
    $currentYear = date('Y');

    $stmt = $db->prepare("
        SELECT p.*, CONCAT(s.first_name, ' ', s.last_name) as staff_name,
               s.employee_code, d.department_name
        FROM payroll p
        JOIN payroll_periods pp ON p.period_id = pp.period_id
        JOIN staff s ON p.staff_id = s.staff_id
        LEFT JOIN departments d ON s.department_id = d.department_id
        WHERE pp.month = ? AND pp.year = ?
        ORDER BY s.employee_code ASC
    ");
    $stmt->execute([$currentMonth, $currentYear]);
    $currentPayroll = $stmt->fetchAll();

    // Get statistics
    $totalPayroll = array_sum(array_column($currentPayroll, 'net_salary'));
    $pendingCount = count(array_filter($currentPayroll, fn($p) => $p['payment_status'] == 'Pending'));
    $approvedCount = count(array_filter($currentPayroll, fn($p) => $p['payment_status'] == 'Approved'));
    $paidCount = count(array_filter($currentPayroll, fn($p) => $p['payment_status'] == 'Paid'));
} catch (Exception $e) {
    $periods = [];
    $currentPayroll = [];
    $totalPayroll = $pendingCount = $approvedCount = $paidCount = 0;
}

include '../../templates/header.php';
?>

<div class="row mb-4">
    <div class="col-md-6">
        <h2><i class="fas fa-file-invoice-dollar"></i> Payroll Management</h2>
    </div>
    <div class="col-md-6 text-end">
        <a href="generate.php" class="btn btn-primary">
            <i class="fas fa-cogs"></i> Generate Payroll
        </a>
        <a href="periods.php" class="btn btn-info">
            <i class="fas fa-calendar-alt"></i> Payroll Periods
        </a>
    </div>
</div>

<!-- Statistics -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <i class="fas fa-money-bill-wave"></i>
            </div>
            <div class="stat-value"><?php echo formatCurrency($totalPayroll); ?></div>
            <div class="stat-label">Total Payroll (This Month)</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #fbc2eb 0%, #a6c1ee 100%);">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-value"><?php echo $pendingCount; ?></div>
            <div class="stat-label">Pending</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-value"><?php echo $approvedCount; ?></div>
            <div class="stat-label">Approved</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">
                <i class="fas fa-check-double"></i>
            </div>
            <div class="stat-value"><?php echo $paidCount; ?></div>
            <div class="stat-label">Paid</div>
        </div>
    </div>
</div>

<!-- Current Month Payroll -->
<div class="table-card">
    <div class="card-header">
        <h5 class="mb-0">Payroll for <?php echo getMonthName($currentMonth) . ' ' . $currentYear; ?></h5>
    </div>
    <div class="table-responsive">
        <table class="table table-hover datatable">
            <thead>
                <tr>
                    <th>Employee Code</th>
                    <th>Name</th>
                    <th>Department</th>
                    <th>Basic Salary</th>
                    <th>Allowances</th>
                    <th>Incentives</th>
                    <th>Gross Salary</th>
                    <th>Deductions</th>
                    <th>Net Salary</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($currentPayroll)): ?>
                <tr>
                    <td colspan="11" class="text-center text-muted">No payroll generated for this month</td>
                </tr>
                <?php else: ?>
                    <?php foreach ($currentPayroll as $payroll): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($payroll['employee_code']); ?></strong></td>
                        <td><?php echo htmlspecialchars($payroll['staff_name']); ?></td>
                        <td><?php echo htmlspecialchars($payroll['department_name'] ?? '-'); ?></td>
                        <td><?php echo formatCurrency($payroll['basic_salary']); ?></td>
                        <td><?php echo formatCurrency($payroll['hra'] + $payroll['transport_allowance'] + $payroll['medical_allowance'] + $payroll['special_allowance']); ?></td>
                        <td><?php echo formatCurrency($payroll['total_incentives']); ?></td>
                        <td><strong><?php echo formatCurrency($payroll['gross_salary']); ?></strong></td>
                        <td><?php echo formatCurrency($payroll['total_deductions']); ?></td>
                        <td><strong class="text-success"><?php echo formatCurrency($payroll['net_salary']); ?></strong></td>
                        <td>
                            <?php
                            $statusClass = 'bg-secondary';
                            if ($payroll['payment_status'] == 'Pending') $statusClass = 'bg-warning';
                            elseif ($payroll['payment_status'] == 'Approved') $statusClass = 'bg-info';
                            elseif ($payroll['payment_status'] == 'Paid') $statusClass = 'bg-success';
                            ?>
                            <span class="badge <?php echo $statusClass; ?>"><?php echo $payroll['payment_status']; ?></span>
                        </td>
                        <td>
                            <a href="view.php?id=<?php echo $payroll['payroll_id']; ?>" class="btn btn-sm btn-info">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="payslip.php?id=<?php echo $payroll['payroll_id']; ?>" class="btn btn-sm btn-success" target="_blank">
                                <i class="fas fa-file-pdf"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../../templates/footer.php'; ?>
