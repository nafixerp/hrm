<?php
/**
 * User Management - List Users
 * HRMS - Human Resource Management System
 */

require_once '../../config/config.php';
requireRole('Admin');

$pageTitle = 'User Management';
$activePage = 'users';

// Get all users
$users = getAllUsers();

include '../../templates/header.php';
?>

<div class="row mb-4">
    <div class="col-md-6">
        <h2><i class="fas fa-users"></i> User Management</h2>
    </div>
    <div class="col-md-6 text-end">
        <a href="add.php" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add New User
        </a>
    </div>
</div>

<div class="table-card">
    <div class="table-responsive">
        <table class="table table-hover datatable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Full Name</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Last Login</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                <tr>
                    <td><?php echo $user['user_id']; ?></td>
                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                    <td><?php echo htmlspecialchars($user['full_name'] ?? '-'); ?></td>
                    <td><span class="badge bg-info"><?php echo $user['role']; ?></span></td>
                    <td>
                        <?php if ($user['is_active']): ?>
                            <span class="badge bg-success">Active</span>
                        <?php else: ?>
                            <span class="badge bg-danger">Inactive</span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo formatDateTime($user['last_login']); ?></td>
                    <td>
                        <a href="edit.php?id=<?php echo $user['user_id']; ?>" class="btn btn-sm btn-warning">
                            <i class="fas fa-edit"></i>
                        </a>
                        <a href="view.php?id=<?php echo $user['user_id']; ?>" class="btn btn-sm btn-info">
                            <i class="fas fa-eye"></i>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../../templates/footer.php'; ?>
