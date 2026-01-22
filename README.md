# HRMS - Human Resource Management System

A comprehensive Human Resource Management System built with PHP, MySQL, and Bootstrap for XAMPP environment. This system provides complete employee lifecycle management, payroll processing, attendance tracking, advanced incentive calculations, and biometric authentication.

![HRMS Dashboard](https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-005C84?style=for-the-badge&logo=mysql&logoColor=white)
![Bootstrap](https://img.shields.io/badge/Bootstrap-563D7C?style=for-the-badge&logo=bootstrap&logoColor=white)

## 🌟 Features

### Core Modules

1. **User Management**
   - Multi-role authentication (Admin, Manager, Staff)
   - JWT-based security with session management
   - Role-based access control
   - Password encryption and management

2. **Staff Management**
   - Complete employee lifecycle management
   - Employee records with joining date, designation, department
   - Document management and photo uploads
   - Employment status tracking

3. **Branch & Department Management**
   - Multi-location organization support
   - Department-wise employee allocation
   - Branch-wise operations tracking

4. **Attendance Tracking**
   - Daily attendance with check-in/check-out
   - Overtime calculation
   - Late arrival tracking with grace periods
   - Duty schedule management
   - Biometric verification support

5. **Leave Management**
   - Multiple leave types (Casual, Sick, Earned, etc.)
   - Leave balance tracking
   - Application and approval workflow
   - Carry forward support
   - Leave history and reports

6. **Salary Package Management**
   - Flexible salary structures
   - Multiple allowances (HRA, Transport, Medical, Special)
   - Deductions (PF, ESI, Professional Tax)
   - Package assignment to employees
   - Salary revision tracking

7. **Sales Tracking**
   - Transaction recording with product details
   - Multiple payment modes
   - Customer information capture
   - Staff-wise sales tracking
   - Branch and department allocation

8. **Incentive System** - 10 Advanced Schemes
   - **Studex Scheme**: ₹25 per piece
   - **PIMS Scheme**: Individual 0.4%, Manager 0.3%
   - **18K Gold Scheme**: Individual 0.6%, Common 0.4%
   - **Premium Scheme**: Individual 0.5%, Manager 0.3%
   - **Gold Scheme**: Target-based (Per gram/2), Department shares
   - **Diamond Scheme**: Individual 1%, Common 0.15%
   - **3g Item Scheme**: Per gram/3, Common 3 Rs/gram
   - **Urgent Scheme**: Individual 1%, Manager 0.25%
   - **Precious Scheme**: Individual 0.5%, Manager 0.3%
   - **Silver Scheme**: Individual 6%, Manager 2%

9. **Payroll Processing**
   - Automated monthly payroll generation
   - Attendance-based salary calculation
   - Incentive integration
   - Multiple deductions support
   - Payslip generation
   - Payment tracking

10. **Biometric Authentication**
    - Multi-modal support (Fingerprint, Face, Iris, Palm Vein)
    - Secure template storage with AES-256 encryption
    - Liveness detection
    - Anti-spoofing measures
    - Device management
    - Complete audit trail

11. **Social Media Management**
    - Platform tracking (Facebook, Instagram, Twitter, etc.)
    - Post engagement metrics
    - Campaign performance

12. **Reporting & Analytics**
    - Dashboard with real-time statistics
    - Sales performance reports
    - Employee performance metrics
    - Department analytics
    - Attendance reports
    - Payroll summaries

## 📋 Requirements

- **XAMPP** 8.0 or higher
  - PHP 8.0+
  - MySQL 5.7+ or MariaDB 10.4+
  - Apache 2.4+
- **Web Browser** (Chrome, Firefox, Safari, Edge)
- **Minimum 2GB RAM**
- **500MB free disk space**

## 🚀 Installation

### Step 1: Download and Setup XAMPP

1. Download XAMPP from [https://www.apachefriends.org](https://www.apachefriends.org)
2. Install XAMPP on your system
3. Start Apache and MySQL services from XAMPP Control Panel

### Step 2: Clone/Download the Project

```bash
# Clone the repository
git clone <repository-url> hrm

# Or download and extract the ZIP file
# Place the 'hrm' folder in your XAMPP htdocs directory
# Path should be: C:\xampp\htdocs\hrm (Windows) or /opt/lampp/htdocs/hrm (Linux)
```

### Step 3: Database Setup

1. Open your web browser and navigate to: `http://localhost/hrm`
2. You will be automatically redirected to the setup page
3. Click on **"Setup Database"** button
4. Wait for the setup to complete (this will create all tables, insert sample data, and create stored procedures)
5. Once setup is complete, click **"Go to Login"**

**Alternative Method** (Manual Setup):
1. Open phpMyAdmin: `http://localhost/phpmyadmin`
2. Create a new database named `hrms_db`
3. Import the following SQL files in order:
   - `database/schema.sql`
   - `database/sample_data.sql`
   - `database/stored_procedures.sql`
   - `database/views.sql`

### Step 4: Login to the System

Navigate to: `http://localhost/hrm`

**Default Login Credentials:**

| Role    | Username  | Password    |
|---------|-----------|-------------|
| Admin   | admin     | admin123    |
| Manager | manager1  | manager123  |
| Staff   | staff1    | staff123    |

### Step 5: Change Default Passwords (Important!)

After first login, please change all default passwords for security.

## 📁 Project Structure

```
hrm/
├── assets/
│   ├── css/              # Custom stylesheets
│   ├── js/               # Custom JavaScript files
│   ├── images/           # Images and icons
│   └── uploads/          # User uploaded files
├── config/
│   ├── config.php        # Application configuration
│   └── database.php      # Database connection
├── database/
│   ├── schema.sql        # Database schema
│   ├── sample_data.sql   # Sample/seed data
│   ├── stored_procedures.sql  # Business logic procedures
│   └── views.sql         # Database views for reporting
├── includes/
│   ├── functions.php     # Helper functions
│   └── auth.php          # Authentication functions
├── modules/
│   ├── auth/             # Authentication module
│   ├── users/            # User management
│   ├── staff/            # Staff management
│   ├── attendance/       # Attendance tracking
│   ├── leave/            # Leave management
│   ├── salary/           # Salary packages
│   ├── sales/            # Sales tracking
│   ├── incentives/       # Incentive calculations
│   ├── payroll/          # Payroll processing
│   ├── biometric/        # Biometric authentication
│   ├── dashboard/        # Dashboard analytics
│   └── reports/          # Reporting module
├── templates/
│   ├── header.php        # Page header template
│   └── footer.php        # Page footer template
├── index.php             # Main entry point
├── login.php             # Login page
├── dashboard.php         # Main dashboard
├── logout.php            # Logout handler
├── setup.php             # Database setup page
└── README.md             # This file
```

## 🎯 Key Features Explained

### Incentive Calculation System

The system implements 10 different incentive schemes with automatic calculation based on:
- **Product Category**: Each product category has its own incentive scheme
- **Staff Role**: Different rates for individual staff vs managers
- **Department**: Sales department vs other departments may have different shares
- **Calculation Type**: Percentage, Per Gram, Per Piece, or Target-based

Example workflow:
1. Sales transaction is recorded
2. System automatically identifies applicable incentive scheme
3. Calculates incentive based on scheme rules
4. Creates incentive records for approval
5. Approved incentives are included in payroll

### Attendance and Overtime

- Configurable duty schedules with grace periods
- Automatic late marking beyond grace period
- Overtime calculation for work beyond scheduled hours
- Biometric integration for secure attendance marking
- Support for manual attendance entry with approval

### Payroll Processing

Automated payroll generation includes:
- Basic salary components (HRA, Transport, Medical, etc.)
- Pro-rated calculation based on attendance
- Incentive integration
- Multiple deductions (PF, ESI, PT, TDS, Loans)
- Net salary calculation
- Payslip generation

## 🔐 Security Features

- Password hashing using bcrypt
- Session management with timeout
- Role-based access control
- SQL injection prevention (PDO prepared statements)
- XSS protection (input sanitization)
- Biometric template encryption (AES-256)
- Audit trail for all critical operations
- Liveness detection for biometric auth
- Anti-spoofing measures

## 📊 Database Schema

The system uses 15+ interconnected tables:
- `users` - User accounts and authentication
- `staff` - Employee information
- `branches` - Branch/location details
- `departments` - Department information
- `designations` - Job designations
- `attendance` - Attendance records
- `leave_types` - Leave type definitions
- `leave_applications` - Leave requests
- `leave_balance` - Leave balance tracking
- `salary_packages` - Salary structures
- `sales_transactions` - Sales records
- `product_categories` - Product categories
- `incentive_schemes` - Incentive configuration
- `incentive_calculations` - Calculated incentives
- `payroll` - Payroll records
- `biometric_templates` - Biometric data
- `biometric_auth_logs` - Authentication logs
- `audit_logs` - System audit trail

## 🔧 Configuration

### Application Settings

Edit `config/config.php` to configure:
- Application URL
- Session timeout
- Upload paths
- Date/time formats
- Currency settings
- Working days per month
- Overtime multiplier
- Grace period for late arrival

### Database Settings

Edit `config/database.php` to configure:
- Database host
- Database name
- Database username
- Database password

### System Settings

System settings can be configured from Admin panel or directly in `system_settings` table:
- Company information
- Payroll cutoff day
- Biometric thresholds
- Session timeout
- Maximum login attempts

## 📱 Browser Compatibility

- Chrome (Recommended)
- Firefox
- Safari
- Edge
- Opera

## 🤝 Support

For issues, questions, or contributions:
- Create an issue in the repository
- Contact the development team
- Check documentation in `/docs` folder

## 📝 License

This project is proprietary software. All rights reserved.

## 🎓 Credits

Developed for comprehensive human resource management with focus on:
- Jewelry/Gold businesses (with specialized incentive schemes)
- Manufacturing units
- Retail chains
- Service industries

## 🔄 Version History

### Version 1.0.0 (Current)
- Initial release
- Complete HRMS functionality
- 10 incentive schemes
- Biometric authentication
- Advanced reporting
- Responsive dashboard

## 📞 Default System Information

**Database Name:** hrms_db
**Default Admin:** admin / admin123
**Time Zone:** Asia/Kolkata
**Currency:** INR (₹)
**Date Format:** DD-MM-YYYY

---

**Note:** This is a production-ready HRMS system. Please ensure you:
1. Change all default passwords after installation
2. Configure backup procedures for your database
3. Set up proper server security
4. Review and adjust security settings for your environment
5. Test thoroughly before deploying to production

For any questions or support, please refer to the documentation or contact the system administrator.
