<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'Dashboard'; ?> - <?php echo APP_NAME; ?></title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />

    <style>
        :root {
            --primary-color: #667eea;
            --secondary-color: #764ba2;
            --sidebar-width: 260px;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f7fa;
        }

        /* Sidebar Styles */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: var(--sidebar-width);
            background: linear-gradient(180deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            overflow-y: auto;
            transition: all 0.3s;
            z-index: 1000;
        }

        .sidebar-header {
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar-header h3 {
            font-size: 24px;
            font-weight: bold;
            margin: 0;
        }

        .sidebar-menu {
            padding: 20px 0;
        }

        .sidebar-menu .menu-item {
            padding: 12px 20px;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            display: flex;
            align-items: center;
            transition: all 0.3s;
        }

        .sidebar-menu .menu-item:hover,
        .sidebar-menu .menu-item.active {
            background: rgba(255, 255, 255, 0.1);
            color: white;
        }

        .sidebar-menu .menu-item i {
            width: 30px;
            font-size: 18px;
        }

        .sidebar-menu .menu-category {
            padding: 20px 20px 10px;
            font-size: 12px;
            text-transform: uppercase;
            color: rgba(255, 255, 255, 0.5);
            font-weight: bold;
        }

        /* Main Content */
        .main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
        }

        /* Top Navbar */
        .top-navbar {
            background: white;
            padding: 15px 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
        }

        /* Content Area */
        .content-area {
            padding: 30px;
        }

        /* Cards */
        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        }

        .stat-card .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: white;
            margin-bottom: 15px;
        }

        .stat-card .stat-value {
            font-size: 32px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .stat-card .stat-label {
            color: #666;
            font-size: 14px;
        }

        /* Buttons */
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            border: none;
        }

        .btn-primary:hover {
            opacity: 0.9;
        }

        /* Tables */
        .table-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .table-card .card-header {
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }

        /* Badges */
        .badge {
            padding: 6px 12px;
            border-radius: 6px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.show {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }
        }
    </style>

    <?php if (isset($customCSS)): ?>
        <style><?php echo $customCSS; ?></style>
    <?php endif; ?>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <i class="fas fa-users-cog fa-2x mb-2"></i>
            <h3>HRMS</h3>
            <small>v<?php echo APP_VERSION; ?></small>
        </div>

        <div class="sidebar-menu">
            <a href="<?php echo APP_URL; ?>/dashboard.php" class="menu-item <?php echo ($activePage ?? '') == 'dashboard' ? 'active' : ''; ?>">
                <i class="fas fa-home"></i>
                <span>Dashboard</span>
            </a>

            <?php if (hasPermission(getCurrentUserRole(), 'Admin')): ?>
            <div class="menu-category">Administration</div>
            <a href="<?php echo APP_URL; ?>/modules/users/index.php" class="menu-item <?php echo ($activePage ?? '') == 'users' ? 'active' : ''; ?>">
                <i class="fas fa-users"></i>
                <span>User Management</span>
            </a>
            <?php endif; ?>

            <div class="menu-category">Employee Management</div>
            <a href="<?php echo APP_URL; ?>/modules/staff/index.php" class="menu-item <?php echo ($activePage ?? '') == 'staff' ? 'active' : ''; ?>">
                <i class="fas fa-user-tie"></i>
                <span>Staff</span>
            </a>
            <a href="<?php echo APP_URL; ?>/modules/attendance/index.php" class="menu-item <?php echo ($activePage ?? '') == 'attendance' ? 'active' : ''; ?>">
                <i class="fas fa-calendar-check"></i>
                <span>Attendance</span>
            </a>
            <a href="<?php echo APP_URL; ?>/modules/leave/index.php" class="menu-item <?php echo ($activePage ?? '') == 'leave' ? 'active' : ''; ?>">
                <i class="fas fa-calendar-minus"></i>
                <span>Leave Management</span>
            </a>

            <div class="menu-category">Financial</div>
            <a href="<?php echo APP_URL; ?>/modules/salary/index.php" class="menu-item <?php echo ($activePage ?? '') == 'salary' ? 'active' : ''; ?>">
                <i class="fas fa-money-bill-wave"></i>
                <span>Salary Packages</span>
            </a>
            <a href="<?php echo APP_URL; ?>/modules/sales/index.php" class="menu-item <?php echo ($activePage ?? '') == 'sales' ? 'active' : ''; ?>">
                <i class="fas fa-shopping-cart"></i>
                <span>Sales</span>
            </a>
            <a href="<?php echo APP_URL; ?>/modules/incentives/index.php" class="menu-item <?php echo ($activePage ?? '') == 'incentives' ? 'active' : ''; ?>">
                <i class="fas fa-trophy"></i>
                <span>Incentives</span>
            </a>
            <a href="<?php echo APP_URL; ?>/modules/payroll/index.php" class="menu-item <?php echo ($activePage ?? '') == 'payroll' ? 'active' : ''; ?>">
                <i class="fas fa-file-invoice-dollar"></i>
                <span>Payroll</span>
            </a>

            <?php if (hasPermission(getCurrentUserRole(), 'Admin')): ?>
            <div class="menu-category">Security</div>
            <a href="<?php echo APP_URL; ?>/modules/biometric/index.php" class="menu-item <?php echo ($activePage ?? '') == 'biometric' ? 'active' : ''; ?>">
                <i class="fas fa-fingerprint"></i>
                <span>Biometric Auth</span>
            </a>
            <?php endif; ?>

            <div class="menu-category">Reports</div>
            <a href="<?php echo APP_URL; ?>/modules/reports/index.php" class="menu-item <?php echo ($activePage ?? '') == 'reports' ? 'active' : ''; ?>">
                <i class="fas fa-chart-bar"></i>
                <span>Reports</span>
            </a>

            <div class="menu-category">Account</div>
            <a href="<?php echo APP_URL; ?>/logout.php" class="menu-item">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Navbar -->
        <div class="top-navbar">
            <div>
                <h4 class="mb-0"><?php echo $pageTitle ?? 'Dashboard'; ?></h4>
            </div>
            <div class="user-info">
                <?php
                $currentUser = getCurrentUser();
                $userName = $currentUser['username'] ?? 'User';
                $userRole = $currentUser['role'] ?? 'Staff';
                $userInitial = strtoupper(substr($userName, 0, 1));
                ?>
                <div class="user-avatar"><?php echo $userInitial; ?></div>
                <div>
                    <div style="font-weight: bold;"><?php echo htmlspecialchars($userName); ?></div>
                    <small class="text-muted"><?php echo htmlspecialchars($userRole); ?></small>
                </div>
            </div>
        </div>

        <!-- Content Area -->
        <div class="content-area">
            <?php displayFlashMessage(); ?>
