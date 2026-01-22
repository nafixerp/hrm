# HRMS Installation Guide

Complete step-by-step installation guide for HRMS (Human Resource Management System) on XAMPP.

## Prerequisites

Before you begin, ensure you have:
- Windows, macOS, or Linux operating system
- At least 2GB RAM
- 500MB free disk space
- Administrative privileges on your computer

## Step 1: Install XAMPP

### For Windows:

1. Download XAMPP from [https://www.apachefriends.org/download.html](https://www.apachefriends.org/download.html)
2. Run the installer (xampp-windows-x64-X.X.X-installer.exe)
3. Follow the installation wizard:
   - Choose installation directory (default: C:\xampp)
   - Select components (ensure Apache and MySQL are selected)
   - Click Install
4. After installation, launch XAMPP Control Panel
5. Start Apache and MySQL services

### For macOS:

1. Download XAMPP for macOS
2. Open the .dmg file
3. Drag XAMPP folder to Applications
4. Open Terminal and run:
   ```bash
   sudo /Applications/XAMPP/xamppfiles/xampp start
   ```

### For Linux:

1. Download XAMPP for Linux
2. Open Terminal and run:
   ```bash
   chmod +x xampp-linux-x64-X.X.X-installer.run
   sudo ./xampp-linux-x64-X.X.X-installer.run
   ```
3. Start XAMPP:
   ```bash
   sudo /opt/lampp/lampp start
   ```

## Step 2: Verify XAMPP Installation

1. Open your web browser
2. Navigate to: `http://localhost`
3. You should see the XAMPP welcome page
4. Click on "phpMyAdmin" to verify MySQL is running
5. You should see the phpMyAdmin interface

## Step 3: Download HRMS

### Option A: Download ZIP

1. Download the HRMS project ZIP file
2. Extract the ZIP file
3. Rename the extracted folder to `hrm`

### Option B: Git Clone

```bash
git clone <repository-url> hrm
```

## Step 4: Place Project Files

### For Windows:
1. Copy the `hrm` folder to: `C:\xampp\htdocs\`
2. Final path should be: `C:\xampp\htdocs\hrm\`

### For macOS:
1. Copy the `hrm` folder to: `/Applications/XAMPP/xamppfiles/htdocs/`
2. Final path should be: `/Applications/XAMPP/xamppfiles/htdocs/hrm/`

### For Linux:
1. Copy the `hrm` folder to: `/opt/lampp/htdocs/`
2. Final path should be: `/opt/lampp/htdocs/hrm/`
3. Set proper permissions:
   ```bash
   sudo chmod -R 755 /opt/lampp/htdocs/hrm
   sudo chown -R daemon:daemon /opt/lampp/htdocs/hrm
   ```

## Step 5: Create Upload Directories

Navigate to the `hrm` folder and create the following directories if they don't exist:

```
hrm/assets/uploads/
hrm/assets/uploads/staff_photos/
hrm/assets/uploads/documents/
```

### For Windows (Command Prompt):
```cmd
cd C:\xampp\htdocs\hrm\assets
mkdir uploads
cd uploads
mkdir staff_photos
mkdir documents
```

### For macOS/Linux (Terminal):
```bash
cd /path/to/htdocs/hrm/assets
mkdir -p uploads/staff_photos
mkdir -p uploads/documents
chmod -R 777 uploads
```

## Step 6: Configure Database Connection

1. Open `config/database.php` in a text editor
2. Verify the default settings:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   define('DB_NAME', 'hrms_db');
   ```
3. If your MySQL has a password, update `DB_PASS`
4. Save the file

## Step 7: Install Database

### Method 1: Automatic Setup (Recommended)

1. Open your web browser
2. Navigate to: `http://localhost/hrm`
3. You will be redirected to the setup page automatically
4. Click the **"Setup Database"** button
5. Wait for the process to complete (may take 30-60 seconds)
6. You will see a success message
7. Click **"Go to Login"**

### Method 2: Manual Setup via phpMyAdmin

1. Open phpMyAdmin: `http://localhost/phpmyadmin`
2. Click "New" to create a new database
3. Database name: `hrms_db`
4. Collation: `utf8mb4_unicode_ci`
5. Click "Create"
6. Select the `hrms_db` database
7. Click "Import" tab
8. Import files in this order:
   - Click "Choose File" → Select `database/schema.sql` → Click "Go"
   - Click "Choose File" → Select `database/sample_data.sql` → Click "Go"
   - Click "Choose File" → Select `database/stored_procedures.sql` → Click "Go"
   - Click "Choose File" → Select `database/views.sql` → Click "Go"

## Step 8: Verify Installation

1. Open your browser
2. Navigate to: `http://localhost/hrm`
3. You should see the login page

## Step 9: Login

Use one of these default credentials:

**Administrator:**
- Username: `admin`
- Password: `admin123`

**Manager:**
- Username: `manager1`
- Password: `manager123`

**Staff:**
- Username: `staff1`
- Password: `staff123`

## Step 10: Post-Installation Security

### Important: Change Default Passwords

1. Login as admin
2. Go to User Management
3. Change passwords for all default users
4. Create new admin account with strong password
5. Disable or delete default accounts

### Set Proper Permissions

**For Linux/macOS:**
```bash
# Make files readable
chmod -R 644 /path/to/hrm/*

# Make directories executable
find /path/to/hrm -type d -exec chmod 755 {} \;

# Make uploads writable
chmod -R 777 /path/to/hrm/assets/uploads

# Protect sensitive files
chmod 600 /path/to/hrm/config/*.php
```

## Troubleshooting

### Issue: "Database connection failed"

**Solution:**
1. Verify MySQL is running in XAMPP Control Panel
2. Check database credentials in `config/database.php`
3. Ensure `hrms_db` database exists in phpMyAdmin
4. Test connection:
   - Open phpMyAdmin
   - Try to access `hrms_db`

### Issue: "404 Not Found"

**Solution:**
1. Verify project is in correct htdocs folder
2. Check folder name is exactly `hrm` (lowercase)
3. Restart Apache from XAMPP Control Panel
4. Try: `http://localhost/hrm/index.php`

### Issue: "Permission denied" errors

**Solution (Linux/macOS):**
```bash
sudo chmod -R 755 /path/to/hrm
sudo chmod -R 777 /path/to/hrm/assets/uploads
```

### Issue: "Setup page not loading"

**Solution:**
1. Clear browser cache
2. Check PHP errors:
   - Look in `C:\xampp\php\logs\php_error_log` (Windows)
   - Look in `/opt/lampp/logs/error_log` (Linux)
3. Enable error display in `config/config.php`:
   ```php
   error_reporting(E_ALL);
   ini_set('display_errors', 1);
   ```

### Issue: "SQL errors during setup"

**Solution:**
1. Drop existing database in phpMyAdmin
2. Create fresh database
3. Try setup again
4. Or use manual import method

### Issue: "Blank page after login"

**Solution:**
1. Check PHP version (must be 8.0+)
2. Enable required PHP extensions:
   - Open `php.ini` in XAMPP
   - Uncomment these lines (remove semicolon):
     ```
     extension=mysqli
     extension=pdo_mysql
     extension=mbstring
     extension=openssl
     ```
3. Restart Apache

### Issue: "Upload directory not writable"

**Solution:**
1. Create uploads directory if missing
2. Set proper permissions:
   ```bash
   chmod -R 777 assets/uploads
   ```
3. On Windows, check folder properties → Security → Edit
4. Give full control to current user

## Testing the Installation

After successful installation, test these features:

1. **Login System**
   - Try logging in with different roles
   - Test logout functionality

2. **Dashboard**
   - View dashboard statistics
   - Check if charts are displaying

3. **Navigation**
   - Click through different menu items
   - Verify all pages load correctly

4. **Sample Data**
   - View staff list
   - Check attendance records
   - View salary packages

## Next Steps

1. **Configure System Settings**
   - Update company information
   - Set working hours
   - Configure incentive schemes

2. **Add Real Data**
   - Add actual employees
   - Set up departments
   - Configure salary packages

3. **Customize**
   - Update logo and branding
   - Adjust color scheme
   - Configure reports

4. **Security**
   - Change all default passwords
   - Set up regular backups
   - Configure SSL certificate (for production)

## Getting Help

If you encounter issues:

1. Check this guide again
2. Review error logs
3. Search for specific error messages
4. Check phpMyAdmin for database issues
5. Verify XAMPP services are running

## System Requirements Summary

| Component | Requirement |
|-----------|-------------|
| PHP | 8.0 or higher |
| MySQL | 5.7 or higher |
| Apache | 2.4 or higher |
| RAM | 2GB minimum |
| Disk Space | 500MB free |
| Browser | Chrome, Firefox, Safari, Edge |

## Production Deployment

For production deployment:

1. Use a real web server (not XAMPP)
2. Configure SSL certificate
3. Set secure passwords
4. Disable error display
5. Set up automated backups
6. Configure email settings
7. Review security settings
8. Test thoroughly

## Backup

To backup your HRMS:

1. **Database Backup:**
   - Open phpMyAdmin
   - Select `hrms_db`
   - Click "Export"
   - Choose "Quick" export method
   - Click "Go"
   - Save the .sql file

2. **Files Backup:**
   - Copy entire `hrm` folder
   - Especially backup `assets/uploads`

## Support

For additional support:
- Check README.md for feature documentation
- Review database schema in `database/schema.sql`
- Contact system administrator

---

**Installation Complete!**

You now have a fully functional HRMS system. Start by changing default passwords and adding your organization's data.
