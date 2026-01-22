<?php
/**
 * Sales Tracking
 * HRMS - Human Resource Management System
 */

require_once '../../config/config.php';
requireLogin();

$pageTitle = 'Sales Tracking';
$activePage = 'sales';

// Get sales transactions
try {
    $db = getDB();
    $stmt = $db->query("
        SELECT st.*, pc.category_name,
               CONCAT(s.first_name, ' ', s.last_name) as staff_name,
               s.employee_code,
               b.branch_name, d.department_name
        FROM sales_transactions st
        JOIN product_categories pc ON st.category_id = pc.category_id
        JOIN staff s ON st.primary_staff_id = s.staff_id
        LEFT JOIN branches b ON st.branch_id = b.branch_id
        LEFT JOIN departments d ON st.department_id = d.department_id
        WHERE st.status = 'Completed'
        ORDER BY st.transaction_date DESC, st.created_at DESC
        LIMIT 100
    ");
    $salesList = $stmt->fetchAll();

    // Get statistics for current month
    $stmt = $db->query("
        SELECT COUNT(*) as total_sales,
               SUM(sale_value) as total_value,
               SUM(quantity) as total_quantity
        FROM sales_transactions
        WHERE MONTH(transaction_date) = MONTH(CURDATE())
              AND YEAR(transaction_date) = YEAR(CURDATE())
              AND status = 'Completed'
    ");
    $stats = $stmt->fetch();
} catch (Exception $e) {
    $salesList = [];
    $stats = ['total_sales' => 0, 'total_value' => 0, 'total_quantity' => 0];
}

include '../../templates/header.php';
?>

<div class="row mb-4">
    <div class="col-md-6">
        <h2><i class="fas fa-shopping-cart"></i> Sales Tracking</h2>
    </div>
    <div class="col-md-6 text-end">
        <a href="add.php" class="btn btn-primary">
            <i class="fas fa-plus-circle"></i> Record Sale
        </a>
        <a href="report.php" class="btn btn-info">
            <i class="fas fa-chart-line"></i> Sales Report
        </a>
    </div>
</div>

<!-- Statistics -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <i class="fas fa-shopping-bag"></i>
            </div>
            <div class="stat-value"><?php echo $stats['total_sales']; ?></div>
            <div class="stat-label">Sales This Month</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
                <i class="fas fa-rupee-sign"></i>
            </div>
            <div class="stat-value"><?php echo formatCurrency($stats['total_value']); ?></div>
            <div class="stat-label">Total Sales Value</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">
                <i class="fas fa-boxes"></i>
            </div>
            <div class="stat-value"><?php echo formatNumber($stats['total_quantity'], 0); ?></div>
            <div class="stat-label">Items Sold</div>
        </div>
    </div>
</div>

<!-- Sales Table -->
<div class="table-card">
    <div class="card-header">
        <h5 class="mb-0">Recent Sales Transactions</h5>
    </div>
    <div class="table-responsive">
        <table class="table table-hover datatable">
            <thead>
                <tr>
                    <th>Transaction Code</th>
                    <th>Date</th>
                    <th>Category</th>
                    <th>Staff</th>
                    <th>Customer</th>
                    <th>Quantity</th>
                    <th>Weight (g)</th>
                    <th>Sale Value</th>
                    <th>Payment</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($salesList as $sale): ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($sale['transaction_code']); ?></strong></td>
                    <td><?php echo formatDate($sale['transaction_date']); ?></td>
                    <td><span class="badge bg-info"><?php echo htmlspecialchars($sale['category_name']); ?></span></td>
                    <td>
                        <?php echo htmlspecialchars($sale['staff_name']); ?><br>
                        <small class="text-muted"><?php echo $sale['employee_code']; ?></small>
                    </td>
                    <td>
                        <?php echo htmlspecialchars($sale['customer_name'] ?? '-'); ?><br>
                        <small><?php echo htmlspecialchars($sale['customer_phone'] ?? ''); ?></small>
                    </td>
                    <td><?php echo formatNumber($sale['quantity'], 2); ?></td>
                    <td><?php echo formatNumber($sale['weight_grams'], 3); ?></td>
                    <td><strong><?php echo formatCurrency($sale['sale_value']); ?></strong></td>
                    <td><span class="badge bg-primary"><?php echo $sale['payment_mode']; ?></span></td>
                    <td>
                        <a href="view.php?id=<?php echo $sale['transaction_id']; ?>" class="btn btn-sm btn-info">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="invoice.php?id=<?php echo $sale['transaction_id']; ?>" class="btn btn-sm btn-success">
                            <i class="fas fa-file-invoice"></i>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../../templates/footer.php'; ?>
