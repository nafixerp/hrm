<?php
/**
 * Salary Package Management
 * HRMS - Human Resource Management System
 */

require_once '../../config/config.php';
requireRole('Manager');

$pageTitle = 'Salary Package Management';
$activePage = 'salary';

// Get salary packages
try {
    $db = getDB();
    $stmt = $db->query("
        SELECT * FROM salary_packages
        ORDER BY gross_salary DESC
    ");
    $packages = $stmt->fetchAll();
} catch (Exception $e) {
    $packages = [];
}

include '../../templates/header.php';
?>

<div class="row mb-4">
    <div class="col-md-6">
        <h2><i class="fas fa-money-bill-wave"></i> Salary Package Management</h2>
    </div>
    <div class="col-md-6 text-end">
        <a href="add.php" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add New Package
        </a>
    </div>
</div>

<!-- Salary Packages Table -->
<div class="table-card">
    <div class="table-responsive">
        <table class="table table-hover datatable">
            <thead>
                <tr>
                    <th>Package Name</th>
                    <th>Basic Salary</th>
                    <th>HRA</th>
                    <th>Transport</th>
                    <th>Medical</th>
                    <th>Special</th>
                    <th>Gross Salary</th>
                    <th>Total Deductions</th>
                    <th>Net Salary</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($packages as $package): ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($package['package_name']); ?></strong></td>
                    <td><?php echo formatCurrency($package['basic_salary']); ?></td>
                    <td><?php echo formatCurrency($package['hra']); ?></td>
                    <td><?php echo formatCurrency($package['transport_allowance']); ?></td>
                    <td><?php echo formatCurrency($package['medical_allowance']); ?></td>
                    <td><?php echo formatCurrency($package['special_allowance']); ?></td>
                    <td><strong><?php echo formatCurrency($package['gross_salary']); ?></strong></td>
                    <td><?php echo formatCurrency($package['total_deductions']); ?></td>
                    <td><strong class="text-success"><?php echo formatCurrency($package['net_salary']); ?></strong></td>
                    <td>
                        <?php if ($package['is_active']): ?>
                            <span class="badge bg-success">Active</span>
                        <?php else: ?>
                            <span class="badge bg-danger">Inactive</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="view.php?id=<?php echo $package['package_id']; ?>" class="btn btn-sm btn-info">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="edit.php?id=<?php echo $package['package_id']; ?>" class="btn btn-sm btn-warning">
                            <i class="fas fa-edit"></i>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../../templates/footer.php'; ?>
