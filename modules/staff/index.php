<?php
/**
 * Staff Management - List Staff
 * HRMS - Human Resource Management System
 */

require_once '../../config/config.php';
requireLogin();

$pageTitle = 'Staff Management';
$activePage = 'staff';

// Get all staff
try {
    $db = getDB();
    $stmt = $db->query("
        SELECT s.*, b.branch_name, d.department_name, dg.designation_name
        FROM staff s
        LEFT JOIN branches b ON s.branch_id = b.branch_id
        LEFT JOIN departments d ON s.department_id = d.department_id
        LEFT JOIN designations dg ON s.designation_id = dg.designation_id
        ORDER BY s.employee_code ASC
    ");
    $staffList = $stmt->fetchAll();
} catch (Exception $e) {
    $staffList = [];
}

include '../../templates/header.php';
?>

<div class="row mb-4">
    <div class="col-md-6">
        <h2><i class="fas fa-user-tie"></i> Staff Management</h2>
    </div>
    <div class="col-md-6 text-end">
        <?php if (hasPermission(getCurrentUserRole(), 'Manager')): ?>
        <a href="add.php" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add New Employee
        </a>
        <?php endif; ?>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-value"><?php echo count($staffList); ?></div>
            <div class="stat-label">Total Employees</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-value">
                <?php echo count(array_filter($staffList, fn($s) => $s['employee_status'] == 'Active')); ?>
            </div>
            <div class="stat-label">Active</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-value">
                <?php echo count(array_filter($staffList, fn($s) => $s['employee_status'] == 'Inactive')); ?>
            </div>
            <div class="stat-label">Inactive</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-value">
                <?php echo count(array_filter($staffList, fn($s) => $s['employee_status'] == 'Resigned')); ?>
            </div>
            <div class="stat-label">Resigned</div>
        </div>
    </div>
</div>

<div class="table-card">
    <div class="table-responsive">
        <table class="table table-hover datatable">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Department</th>
                    <th>Designation</th>
                    <th>Join Date</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($staffList as $staff): ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($staff['employee_code']); ?></strong></td>
                    <td><?php echo htmlspecialchars($staff['first_name'] . ' ' . $staff['last_name']); ?></td>
                    <td><?php echo htmlspecialchars($staff['email'] ?? '-'); ?></td>
                    <td><?php echo htmlspecialchars($staff['phone'] ?? '-'); ?></td>
                    <td><?php echo htmlspecialchars($staff['department_name'] ?? '-'); ?></td>
                    <td><?php echo htmlspecialchars($staff['designation_name'] ?? '-'); ?></td>
                    <td><?php echo formatDate($staff['date_of_joining']); ?></td>
                    <td>
                        <?php
                        $badgeClass = 'bg-secondary';
                        if ($staff['employee_status'] == 'Active') $badgeClass = 'bg-success';
                        elseif ($staff['employee_status'] == 'Inactive') $badgeClass = 'bg-warning';
                        elseif ($staff['employee_status'] == 'Resigned') $badgeClass = 'bg-danger';
                        ?>
                        <span class="badge <?php echo $badgeClass; ?>"><?php echo $staff['employee_status']; ?></span>
                    </td>
                    <td>
                        <a href="view.php?id=<?php echo $staff['staff_id']; ?>" class="btn btn-sm btn-info">
                            <i class="fas fa-eye"></i>
                        </a>
                        <?php if (hasPermission(getCurrentUserRole(), 'Manager')): ?>
                        <a href="edit.php?id=<?php echo $staff['staff_id']; ?>" class="btn btn-sm btn-warning">
                            <i class="fas fa-edit"></i>
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
