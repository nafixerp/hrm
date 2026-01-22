<?php
/**
 * Incentive Management
 * HRMS - Human Resource Management System
 */

require_once '../../config/config.php';
requireLogin();

$pageTitle = 'Incentive Management';
$activePage = 'incentives';

// Get incentive calculations
try {
    $db = getDB();
    $stmt = $db->query("
        SELECT ic.*, isch.scheme_name, pc.category_name,
               CONCAT(s.first_name, ' ', s.last_name) as staff_name,
               s.employee_code,
               st.transaction_code
        FROM incentive_calculations ic
        JOIN incentive_schemes isch ON ic.scheme_id = isch.scheme_id
        LEFT JOIN product_categories pc ON isch.category_id = pc.category_id
        JOIN staff s ON ic.staff_id = s.staff_id
        JOIN sales_transactions st ON ic.transaction_id = st.transaction_id
        ORDER BY ic.calculation_date DESC, ic.created_at DESC
        LIMIT 100
    ");
    $incentivesList = $stmt->fetchAll();

    // Get statistics for current month
    $stmt = $db->query("
        SELECT COUNT(*) as total_count,
               SUM(final_amount) as total_amount,
               SUM(CASE WHEN status = 'Pending' THEN final_amount ELSE 0 END) as pending_amount,
               SUM(CASE WHEN status = 'Approved' THEN final_amount ELSE 0 END) as approved_amount,
               SUM(CASE WHEN status = 'Paid' THEN final_amount ELSE 0 END) as paid_amount
        FROM incentive_calculations
        WHERE MONTH(calculation_date) = MONTH(CURDATE())
              AND YEAR(calculation_date) = YEAR(CURDATE())
    ");
    $stats = $stmt->fetch();
} catch (Exception $e) {
    $incentivesList = [];
    $stats = ['total_count' => 0, 'total_amount' => 0, 'pending_amount' => 0, 'approved_amount' => 0, 'paid_amount' => 0];
}

include '../../templates/header.php';
?>

<div class="row mb-4">
    <div class="col-md-6">
        <h2><i class="fas fa-trophy"></i> Incentive Management</h2>
    </div>
    <div class="col-md-6 text-end">
        <a href="schemes.php" class="btn btn-info">
            <i class="fas fa-list"></i> Incentive Schemes
        </a>
        <a href="report.php" class="btn btn-success">
            <i class="fas fa-chart-bar"></i> Incentive Report
        </a>
    </div>
</div>

<!-- Statistics -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <i class="fas fa-trophy"></i>
            </div>
            <div class="stat-value"><?php echo $stats['total_count']; ?></div>
            <div class="stat-label">Total Incentives (This Month)</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #fbc2eb 0%, #a6c1ee 100%);">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-value"><?php echo formatCurrency($stats['pending_amount']); ?></div>
            <div class="stat-label">Pending Approval</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-value"><?php echo formatCurrency($stats['approved_amount']); ?></div>
            <div class="stat-label">Approved</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">
                <i class="fas fa-money-bill-wave"></i>
            </div>
            <div class="stat-value"><?php echo formatCurrency($stats['paid_amount']); ?></div>
            <div class="stat-label">Paid</div>
        </div>
    </div>
</div>

<!-- Incentives Table -->
<div class="table-card">
    <div class="card-header">
        <h5 class="mb-0">Incentive Calculations</h5>
    </div>
    <div class="table-responsive">
        <table class="table table-hover datatable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Date</th>
                    <th>Employee</th>
                    <th>Transaction</th>
                    <th>Scheme</th>
                    <th>Category</th>
                    <th>Type</th>
                    <th>Base Amount</th>
                    <th>Incentive</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($incentivesList as $incentive): ?>
                <tr>
                    <td><strong>#<?php echo $incentive['calculation_id']; ?></strong></td>
                    <td><?php echo formatDate($incentive['calculation_date']); ?></td>
                    <td>
                        <?php echo htmlspecialchars($incentive['staff_name']); ?><br>
                        <small class="text-muted"><?php echo $incentive['employee_code']; ?></small>
                    </td>
                    <td><small><?php echo htmlspecialchars($incentive['transaction_code']); ?></small></td>
                    <td><?php echo htmlspecialchars($incentive['scheme_name']); ?></td>
                    <td><span class="badge bg-info"><?php echo htmlspecialchars($incentive['category_name'] ?? '-'); ?></span></td>
                    <td>
                        <?php
                        $typeClass = 'bg-primary';
                        if ($incentive['incentive_type'] == 'Manager') $typeClass = 'bg-success';
                        elseif ($incentive['incentive_type'] == 'Common') $typeClass = 'bg-warning';
                        ?>
                        <span class="badge <?php echo $typeClass; ?>"><?php echo $incentive['incentive_type']; ?></span>
                    </td>
                    <td><?php echo formatCurrency($incentive['base_amount']); ?></td>
                    <td><strong><?php echo formatCurrency($incentive['final_amount']); ?></strong></td>
                    <td>
                        <?php
                        $statusClass = 'bg-secondary';
                        if ($incentive['status'] == 'Pending') $statusClass = 'bg-warning';
                        elseif ($incentive['status'] == 'Approved') $statusClass = 'bg-success';
                        elseif ($incentive['status'] == 'Paid') $statusClass = 'bg-info';
                        ?>
                        <span class="badge <?php echo $statusClass; ?>"><?php echo $incentive['status']; ?></span>
                    </td>
                    <td>
                        <a href="view.php?id=<?php echo $incentive['calculation_id']; ?>" class="btn btn-sm btn-info">
                            <i class="fas fa-eye"></i>
                        </a>
                        <?php if ($incentive['status'] == 'Pending' && hasPermission(getCurrentUserRole(), 'Manager')): ?>
                        <a href="approve.php?id=<?php echo $incentive['calculation_id']; ?>" class="btn btn-sm btn-success">
                            <i class="fas fa-check"></i>
                        </a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../../templates/footer.php'; ?>
