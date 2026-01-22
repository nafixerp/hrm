<?php
/**
 * Biometric Authentication Management
 * HRMS - Human Resource Management System
 */

require_once '../../config/config.php';
requireRole('Admin');

$pageTitle = 'Biometric Authentication';
$activePage = 'biometric';

// Get biometric devices
try {
    $db = getDB();
    $stmt = $db->query("
        SELECT bd.*, b.branch_name
        FROM biometric_devices bd
        LEFT JOIN branches b ON bd.branch_id = b.branch_id
        ORDER BY bd.device_name ASC
    ");
    $devices = $stmt->fetchAll();

    // Get recent auth logs
    $stmt = $db->query("
        SELECT bal.*, CONCAT(s.first_name, ' ', s.last_name) as staff_name,
               s.employee_code, bd.device_name
        FROM biometric_auth_logs bal
        LEFT JOIN staff s ON bal.staff_id = s.staff_id
        LEFT JOIN biometric_devices bd ON bal.device_id = bd.device_id
        ORDER BY bal.auth_timestamp DESC
        LIMIT 50
    ");
    $authLogs = $stmt->fetchAll();

    // Get statistics
    $successCount = count(array_filter($authLogs, fn($log) => $log['status'] == 'Success'));
    $failedCount = count(array_filter($authLogs, fn($log) => $log['status'] == 'Failed'));
} catch (Exception $e) {
    $devices = [];
    $authLogs = [];
    $successCount = $failedCount = 0;
}

include '../../templates/header.php';
?>

<div class="row mb-4">
    <div class="col-md-6">
        <h2><i class="fas fa-fingerprint"></i> Biometric Authentication</h2>
    </div>
    <div class="col-md-6 text-end">
        <a href="devices.php" class="btn btn-primary">
            <i class="fas fa-tablet-alt"></i> Manage Devices
        </a>
        <a href="templates.php" class="btn btn-info">
            <i class="fas fa-id-card"></i> Manage Templates
        </a>
    </div>
</div>

<!-- Statistics -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <i class="fas fa-tablet-alt"></i>
            </div>
            <div class="stat-value"><?php echo count($devices); ?></div>
            <div class="stat-label">Active Devices</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-value"><?php echo $successCount; ?></div>
            <div class="stat-label">Successful Auth (Recent)</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">
                <i class="fas fa-times-circle"></i>
            </div>
            <div class="stat-value"><?php echo $failedCount; ?></div>
            <div class="stat-label">Failed Auth (Recent)</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                <i class="fas fa-percentage"></i>
            </div>
            <div class="stat-value">
                <?php echo $successCount + $failedCount > 0 ? round(($successCount / ($successCount + $failedCount)) * 100, 1) : 0; ?>%
            </div>
            <div class="stat-label">Success Rate</div>
        </div>
    </div>
</div>

<!-- Biometric Devices -->
<div class="table-card mb-4">
    <div class="card-header">
        <h5 class="mb-0">Registered Biometric Devices</h5>
    </div>
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Device Name</th>
                    <th>Device Code</th>
                    <th>Type</th>
                    <th>Branch</th>
                    <th>Location</th>
                    <th>IP Address</th>
                    <th>Last Sync</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($devices as $device): ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($device['device_name']); ?></strong></td>
                    <td><?php echo htmlspecialchars($device['device_code']); ?></td>
                    <td><span class="badge bg-primary"><?php echo $device['device_type']; ?></span></td>
                    <td><?php echo htmlspecialchars($device['branch_name'] ?? '-'); ?></td>
                    <td><?php echo htmlspecialchars($device['location'] ?? '-'); ?></td>
                    <td><code><?php echo htmlspecialchars($device['ip_address'] ?? '-'); ?></code></td>
                    <td><?php echo formatDateTime($device['last_sync']); ?></td>
                    <td>
                        <?php if ($device['is_active']): ?>
                            <span class="badge bg-success">Active</span>
                        <?php else: ?>
                            <span class="badge bg-danger">Inactive</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="device-view.php?id=<?php echo $device['device_id']; ?>" class="btn btn-sm btn-info">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="device-edit.php?id=<?php echo $device['device_id']; ?>" class="btn btn-sm btn-warning">
                            <i class="fas fa-edit"></i>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Recent Authentication Logs -->
<div class="table-card">
    <div class="card-header">
        <h5 class="mb-0">Recent Authentication Logs</h5>
    </div>
    <div class="table-responsive">
        <table class="table table-hover datatable">
            <thead>
                <tr>
                    <th>Timestamp</th>
                    <th>Employee</th>
                    <th>Device</th>
                    <th>Biometric Type</th>
                    <th>Auth Type</th>
                    <th>Match Score</th>
                    <th>Liveness</th>
                    <th>Status</th>
                    <th>Failure Reason</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($authLogs as $log): ?>
                <tr>
                    <td><?php echo formatDateTime($log['auth_timestamp']); ?></td>
                    <td>
                        <?php echo htmlspecialchars($log['staff_name'] ?? 'Unknown'); ?><br>
                        <small class="text-muted"><?php echo $log['employee_code'] ?? '-'; ?></small>
                    </td>
                    <td><?php echo htmlspecialchars($log['device_name'] ?? '-'); ?></td>
                    <td><span class="badge bg-info"><?php echo $log['biometric_type']; ?></span></td>
                    <td><?php echo $log['auth_type']; ?></td>
                    <td>
                        <?php if ($log['match_score']): ?>
                            <div class="progress" style="height: 20px;">
                                <div class="progress-bar <?php echo $log['match_score'] >= 75 ? 'bg-success' : 'bg-warning'; ?>"
                                     style="width: <?php echo $log['match_score']; ?>%">
                                    <?php echo $log['match_score']; ?>%
                                </div>
                            </div>
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($log['liveness_check']): ?>
                            <span class="badge bg-success"><i class="fas fa-check"></i></span>
                        <?php else: ?>
                            <span class="badge bg-secondary"><i class="fas fa-times"></i></span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php
                        $statusClass = 'bg-secondary';
                        if ($log['status'] == 'Success') $statusClass = 'bg-success';
                        elseif ($log['status'] == 'Failed') $statusClass = 'bg-danger';
                        elseif ($log['status'] == 'Rejected') $statusClass = 'bg-warning';
                        ?>
                        <span class="badge <?php echo $statusClass; ?>"><?php echo $log['status']; ?></span>
                    </td>
                    <td><?php echo htmlspecialchars($log['failure_reason'] ?? '-'); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../../templates/footer.php'; ?>
