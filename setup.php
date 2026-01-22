<?php
/**
 * Database Setup Page
 * HRMS - Human Resource Management System
 */

require_once 'config/database.php';

$message = '';
$messageType = '';

// Handle setup
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['setup'])) {
    try {
        $db = getDB();

        // Read and execute schema
        $schema = file_get_contents(__DIR__ . '/database/schema.sql');
        $db->exec($schema);

        // Read and execute sample data
        $sampleData = file_get_contents(__DIR__ . '/database/sample_data.sql');
        $db->exec($sampleData);

        // Read and execute stored procedures
        $procedures = file_get_contents(__DIR__ . '/database/stored_procedures.sql');
        $db->exec($procedures);

        // Read and execute views
        $views = file_get_contents(__DIR__ . '/database/views.sql');
        $db->exec($views);

        $message = 'Database setup completed successfully! You can now login.';
        $messageType = 'success';

    } catch (Exception $e) {
        $message = 'Error during setup: ' . $e->getMessage();
        $messageType = 'danger';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup - HRMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .setup-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            padding: 50px;
            max-width: 600px;
            width: 100%;
        }
        .setup-icon {
            font-size: 64px;
            color: #667eea;
            text-align: center;
            margin-bottom: 20px;
        }
        .setup-title {
            font-size: 32px;
            font-weight: bold;
            text-align: center;
            margin-bottom: 30px;
        }
        .btn-setup {
            width: 100%;
            padding: 15px;
            font-size: 18px;
            border-radius: 10px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="setup-container">
        <div class="setup-icon">
            <i class="fas fa-database"></i>
        </div>
        <h1 class="setup-title">HRMS Database Setup</h1>

        <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?>" role="alert">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <?php if ($messageType == 'success'): ?>
            <a href="login.php" class="btn btn-setup">
                <i class="fas fa-arrow-right"></i> Go to Login
            </a>
        <?php else: ?>
            <div class="mb-4">
                <h5>Setup Information:</h5>
                <ul>
                    <li>This will create all database tables</li>
                    <li>Sample data will be inserted</li>
                    <li>Stored procedures will be created</li>
                    <li>Database views will be created</li>
                    <li>Default users will be created</li>
                </ul>

                <div class="alert alert-info mt-3">
                    <strong>Default Credentials:</strong><br>
                    Admin: admin / admin123<br>
                    Manager: manager1 / manager123<br>
                    Staff: staff1 / staff123
                </div>

                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Warning:</strong> This will drop the existing database if it exists!
                </div>
            </div>

            <form method="POST" action="">
                <button type="submit" name="setup" class="btn btn-setup" onclick="return confirm('Are you sure you want to setup the database? This will delete all existing data!')">
                    <i class="fas fa-cog"></i> Setup Database
                </button>
            </form>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
