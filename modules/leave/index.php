<?php
/**
 * Leave Management
 * HRMS - Human Resource Management System
 */

require_once '../../config/config.php';
requireLogin();

$pageTitle = 'Leave Management';
$activePage = 'leave';

// Get leave applications
try {
    $db = getDB();
    $sql = "
        SELECT la.*, lt.leave_type_name, lt.is_paid,
               CONCAT(s.first_name, ' ', s.last_name) as staff_name,
               s.employee_code,
               CONCAT(u.username) as approved_by_name
        FROM leave_applications la
        JOIN leave_types lt ON la.leave_type_id = lt.leave_type_id
        JOIN staff s ON la.staff_id = s.staff_id
        LEFT JOIN users u ON la.approved_by = u.user_id
    ";

    // Filter by status if not admin/manager
    if (!hasPermission(getCurrentUserRole(), 'Manager')) {
        $currentUser = getCurrentUser();
        $sql .= " WHERE la.staff_id = " . $currentUser['staff_id'];
    }

    $sql .= " ORDER BY la.applied_date DESC";

    $stmt = $db->query($sql);
    $leaveApplications = $stmt->fetchAll();

    // Get statistics
    $pending = count(array_filter($leaveApplications, fn($l) => $l['status'] == 'Pending'));
    $approved = count(array_filter($leaveApplications, fn($l) => $l['status'] == 'Approved'));
    $rejected = count(array_filter($leaveApplications, fn($l) => $l['status'] == 'Rejected'));
} catch (Exception $e) {
    $leaveApplications = [];
    $pending = $approved = $rejected = 0;
}

include '../../templates/header.php';
?>

<div class="row mb-4">
    <div class="col-md-6">
        <h2><i class="fas fa-calendar-minus"></i> Leave Management</h2>
    </div>
    <div class="col-md-6 text-end">
        <a href="apply.php" class="btn btn-primary">
            <i class="fas fa-plus-circle"></i> Apply for Leave
        </a>
        <a href="balance.php" class="btn btn-info">
            <i class="fas fa-balance-scale"></i> Leave Balance
        </a>
    </div>
</div>

<!-- Statistics -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #fbc2eb 0%, #a6c1ee 100%);">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-value"><?php echo $pending; ?></div>
            <div class="stat-label">Pending Applications</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-value"><?php echo $approved; ?></div>
            <div class="stat-label">Approved</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">
                <i class="fas fa-times-circle"></i>
            </div>
            <div class="stat-value"><?php echo $rejected; ?></div>
            <div class="stat-label">Rejected</div>
        </div>
    </div>
</div>

<!-- Leave Applications Table -->
<div class="table-card">
    <div class="table-responsive">
        <table class="table table-hover datatable">
            <thead>
                <tr>
                    <th>App ID</th>
                    <th>Employee</th>
                    <th>Leave Type</th>
                    <th>From Date</th>
                    <th>To Date</th>
                    <th>Days</th>
                    <th>Applied Date</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($leaveApplications as $leave): ?>
                <tr>
                    <td><strong>#<?php echo $leave['application_id']; ?></strong></td>
                    <td>
                        <?php echo htmlspecialchars($leave['staff_name']); ?><br>
                        <small class="text-muted"><?php echo $leave['employee_code']; ?></small>
                    </td>
                    <td>
                        <?php echo htmlspecialchars($leave['leave_type_name']); ?>
                        <?php if ($leave['is_paid']): ?>
                            <span class="badge bg-success">Paid</span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo formatDate($leave['from_date']); ?></td>
                    <td><?php echo formatDate($leave['to_date']); ?></td>
                    <td><strong><?php echo $leave['total_days']; ?></strong></td>
                    <td><?php echo formatDate($leave['applied_date']); ?></td>
                    <td>
                        <?php
                        $badgeClass = 'bg-secondary';
                        if ($leave['status'] == 'Pending') $badgeClass = 'bg-warning';
                        elseif ($leave['status'] == 'Approved') $badgeClass = 'bg-success';
                        elseif ($leave['status'] == 'Rejected') $badgeClass = 'bg-danger';
                        ?>
                        <span class="badge <?php echo $badgeClass; ?>"><?php echo $leave['status']; ?></span>
                    </td>
                    <td>
                        <a href="view.php?id=<?php echo $leave['application_id']; ?>" class="btn btn-sm btn-info">
                            <i class="fas fa-eye"></i>
                        </a>
                        <?php if ($leave['status'] == 'Pending' && hasPermission(getCurrentUserRole(), 'Manager')): ?>
                        <a href="approve.php?id=<?php echo $leave['application_id']; ?>" class="btn btn-sm btn-success">
                            <i class="fas fa-check"></i>
                        </a>
                        <a href="reject.php?id=<?php echo $leave['application_id']; ?>" class="btn btn-sm btn-danger">
                            <i class="fas fa-times"></i>
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
