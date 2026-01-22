<?php
/**
 * Attendance Management
 * HRMS - Human Resource Management System
 */

require_once '../../config/config.php';
requireLogin();

$pageTitle = 'Attendance Management';
$activePage = 'attendance';

// Get date filter
$filterDate = $_GET['date'] ?? date('Y-m-d');

// Get attendance for the date
try {
    $db = getDB();
    $stmt = $db->prepare("
        SELECT a.*, CONCAT(s.first_name, ' ', s.last_name) as staff_name,
               s.employee_code, d.department_name
        FROM attendance a
        JOIN staff s ON a.staff_id = s.staff_id
        LEFT JOIN departments d ON s.department_id = d.department_id
        WHERE a.attendance_date = ?
        ORDER BY s.employee_code ASC
    ");
    $stmt->execute([$filterDate]);
    $attendanceList = $stmt->fetchAll();

    // Get statistics
    $present = count(array_filter($attendanceList, fn($a) => $a['status'] == 'Present'));
    $absent = count(array_filter($attendanceList, fn($a) => $a['status'] == 'Absent'));
    $leave = count(array_filter($attendanceList, fn($a) => $a['status'] == 'Leave'));
    $late = count(array_filter($attendanceList, fn($a) => $a['is_late'] == 1));
} catch (Exception $e) {
    $attendanceList = [];
    $present = $absent = $leave = $late = 0;
}

include '../../templates/header.php';
?>

<div class="row mb-4">
    <div class="col-md-6">
        <h2><i class="fas fa-calendar-check"></i> Attendance Management</h2>
    </div>
    <div class="col-md-6 text-end">
        <a href="mark.php" class="btn btn-primary">
            <i class="fas fa-plus-circle"></i> Mark Attendance
        </a>
        <a href="bulk.php" class="btn btn-success">
            <i class="fas fa-users"></i> Bulk Attendance
        </a>
    </div>
</div>

<!-- Date Filter -->
<div class="table-card mb-4">
    <form method="GET" class="row g-3 align-items-end">
        <div class="col-md-4">
            <label class="form-label">Select Date</label>
            <input type="date" class="form-control" name="date" value="<?php echo $filterDate; ?>">
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-filter"></i> Filter
            </button>
        </div>
        <div class="col-md-6 text-end">
            <h5 class="mb-0">Date: <?php echo formatDate($filterDate); ?></h5>
        </div>
    </form>
</div>

<!-- Statistics -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-value"><?php echo $present; ?></div>
            <div class="stat-label">Present</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">
                <i class="fas fa-times-circle"></i>
            </div>
            <div class="stat-value"><?php echo $absent; ?></div>
            <div class="stat-label">Absent</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <i class="fas fa-calendar-alt"></i>
            </div>
            <div class="stat-value"><?php echo $leave; ?></div>
            <div class="stat-label">On Leave</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-value"><?php echo $late; ?></div>
            <div class="stat-label">Late Arrivals</div>
        </div>
    </div>
</div>

<!-- Attendance Table -->
<div class="table-card">
    <div class="table-responsive">
        <table class="table table-hover datatable">
            <thead>
                <tr>
                    <th>Employee Code</th>
                    <th>Name</th>
                    <th>Department</th>
                    <th>Check-In</th>
                    <th>Check-Out</th>
                    <th>Working Hours</th>
                    <th>Overtime</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($attendanceList)): ?>
                <tr>
                    <td colspan="9" class="text-center text-muted">No attendance records for this date</td>
                </tr>
                <?php else: ?>
                    <?php foreach ($attendanceList as $att): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($att['employee_code']); ?></strong></td>
                        <td><?php echo htmlspecialchars($att['staff_name']); ?></td>
                        <td><?php echo htmlspecialchars($att['department_name'] ?? '-'); ?></td>
                        <td>
                            <?php echo $att['check_in_time'] ? date('h:i A', strtotime($att['check_in_time'])) : '-'; ?>
                            <?php if ($att['is_late']): ?>
                                <span class="badge bg-warning">Late</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo $att['check_out_time'] ? date('h:i A', strtotime($att['check_out_time'])) : '-'; ?></td>
                        <td><?php echo formatNumber($att['total_working_hours'], 2); ?> hrs</td>
                        <td><?php echo formatNumber($att['overtime_hours'], 2); ?> hrs</td>
                        <td>
                            <?php
                            $badgeClass = 'bg-secondary';
                            if ($att['status'] == 'Present') $badgeClass = 'bg-success';
                            elseif ($att['status'] == 'Absent') $badgeClass = 'bg-danger';
                            elseif ($att['status'] == 'Half Day') $badgeClass = 'bg-warning';
                            elseif ($att['status'] == 'Leave') $badgeClass = 'bg-info';
                            ?>
                            <span class="badge <?php echo $badgeClass; ?>"><?php echo $att['status']; ?></span>
                        </td>
                        <td>
                            <a href="view.php?id=<?php echo $att['attendance_id']; ?>" class="btn btn-sm btn-info">
                                <i class="fas fa-eye"></i>
                            </a>
                            <?php if (hasPermission(getCurrentUserRole(), 'Manager')): ?>
                            <a href="edit.php?id=<?php echo $att['attendance_id']; ?>" class="btn btn-sm btn-warning">
                                <i class="fas fa-edit"></i>
                            </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../../templates/footer.php'; ?>
