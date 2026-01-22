<?php
/**
 * Placeholder Page
 * This page can be copied and customized for add/edit/view operations
 * HRMS - Human Resource Management System
 */

require_once '../../config/config.php';
requireLogin();

$pageTitle = 'Page Title';
$activePage = 'module';

// Get module and action from URL
$module = basename(dirname(__FILE__));
$action = basename(__FILE__, '.php');

include '../../templates/header.php';
?>

<div class="row mb-4">
    <div class="col-md-12">
        <h2><i class="fas fa-cog"></i> <?php echo ucfirst($module); ?> - <?php echo ucfirst($action); ?></h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="../../dashboard.php">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="index.php"><?php echo ucfirst($module); ?></a></li>
                <li class="breadcrumb-item active"><?php echo ucfirst($action); ?></li>
            </ol>
        </nav>
    </div>
</div>

<div class="table-card">
    <div class="alert alert-info">
        <h5><i class="fas fa-info-circle"></i> Page Under Development</h5>
        <p>This page is currently under development. The functionality will be available soon.</p>
        <p><strong>Module:</strong> <?php echo ucfirst($module); ?><br>
           <strong>Action:</strong> <?php echo ucfirst($action); ?></p>
        <a href="index.php" class="btn btn-primary">Back to <?php echo ucfirst($module); ?> List</a>
    </div>
</div>

<?php include '../../templates/footer.php'; ?>
