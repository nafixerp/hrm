-- ============================================================================
-- HRMS - Human Resource Management System
-- Database Schema for MySQL/phpMyAdmin
-- ============================================================================

-- Drop existing database if exists and create new
DROP DATABASE IF EXISTS hrms_db;
CREATE DATABASE hrms_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE hrms_db;

-- ============================================================================
-- 1. USERS AND AUTHENTICATION
-- ============================================================================

CREATE TABLE users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('Admin', 'Manager', 'Staff') NOT NULL DEFAULT 'Staff',
    is_active BOOLEAN DEFAULT TRUE,
    last_login DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_email (email),
    INDEX idx_role (role)
) ENGINE=InnoDB;

-- ============================================================================
-- 2. BRANCHES AND DEPARTMENTS
-- ============================================================================

CREATE TABLE branches (
    branch_id INT PRIMARY KEY AUTO_INCREMENT,
    branch_name VARCHAR(100) NOT NULL,
    branch_code VARCHAR(20) UNIQUE NOT NULL,
    address TEXT,
    city VARCHAR(50),
    state VARCHAR(50),
    pincode VARCHAR(10),
    phone VARCHAR(20),
    email VARCHAR(100),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_branch_code (branch_code)
) ENGINE=InnoDB;

CREATE TABLE departments (
    department_id INT PRIMARY KEY AUTO_INCREMENT,
    department_name VARCHAR(100) NOT NULL,
    department_code VARCHAR(20) UNIQUE NOT NULL,
    description TEXT,
    is_sales_department BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_department_code (department_code),
    INDEX idx_is_sales (is_sales_department)
) ENGINE=InnoDB;

-- ============================================================================
-- 3. STAFF/EMPLOYEE MANAGEMENT
-- ============================================================================

CREATE TABLE designations (
    designation_id INT PRIMARY KEY AUTO_INCREMENT,
    designation_name VARCHAR(100) NOT NULL,
    level INT,
    description TEXT,
    is_manager BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_level (level)
) ENGINE=InnoDB;

CREATE TABLE staff (
    staff_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT UNIQUE,
    employee_code VARCHAR(20) UNIQUE NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    date_of_birth DATE,
    gender ENUM('Male', 'Female', 'Other'),
    phone VARCHAR(20),
    alternate_phone VARCHAR(20),
    email VARCHAR(100),
    address TEXT,
    city VARCHAR(50),
    state VARCHAR(50),
    pincode VARCHAR(10),

    -- Employment Details
    branch_id INT,
    department_id INT,
    designation_id INT,
    date_of_joining DATE NOT NULL,
    employment_type ENUM('Permanent', 'Contract', 'Temporary') DEFAULT 'Permanent',
    employee_status ENUM('Active', 'Inactive', 'Resigned', 'Terminated') DEFAULT 'Active',

    -- Banking Details
    bank_name VARCHAR(100),
    account_number VARCHAR(50),
    ifsc_code VARCHAR(20),
    pan_number VARCHAR(20),
    aadhar_number VARCHAR(20),

    -- Emergency Contact
    emergency_contact_name VARCHAR(100),
    emergency_contact_phone VARCHAR(20),
    emergency_contact_relation VARCHAR(50),

    photo_path VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE SET NULL,
    FOREIGN KEY (branch_id) REFERENCES branches(branch_id),
    FOREIGN KEY (department_id) REFERENCES departments(department_id),
    FOREIGN KEY (designation_id) REFERENCES designations(designation_id),
    INDEX idx_employee_code (employee_code),
    INDEX idx_status (employee_status),
    INDEX idx_branch_dept (branch_id, department_id)
) ENGINE=InnoDB;

-- ============================================================================
-- 4. SALARY PACKAGE MANAGEMENT
-- ============================================================================

CREATE TABLE salary_components (
    component_id INT PRIMARY KEY AUTO_INCREMENT,
    component_name VARCHAR(100) NOT NULL,
    component_type ENUM('Earning', 'Deduction') NOT NULL,
    is_fixed BOOLEAN DEFAULT TRUE,
    is_taxable BOOLEAN DEFAULT TRUE,
    calculation_type ENUM('Fixed', 'Percentage', 'Formula') DEFAULT 'Fixed',
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE salary_packages (
    package_id INT PRIMARY KEY AUTO_INCREMENT,
    package_name VARCHAR(100) NOT NULL,
    basic_salary DECIMAL(10, 2) NOT NULL,
    hra DECIMAL(10, 2) DEFAULT 0,
    transport_allowance DECIMAL(10, 2) DEFAULT 0,
    medical_allowance DECIMAL(10, 2) DEFAULT 0,
    special_allowance DECIMAL(10, 2) DEFAULT 0,
    other_allowances DECIMAL(10, 2) DEFAULT 0,
    gross_salary DECIMAL(10, 2) GENERATED ALWAYS AS (
        basic_salary + hra + transport_allowance + medical_allowance +
        special_allowance + other_allowances
    ) STORED,
    pf_deduction DECIMAL(10, 2) DEFAULT 0,
    esi_deduction DECIMAL(10, 2) DEFAULT 0,
    professional_tax DECIMAL(10, 2) DEFAULT 0,
    other_deductions DECIMAL(10, 2) DEFAULT 0,
    total_deductions DECIMAL(10, 2) GENERATED ALWAYS AS (
        pf_deduction + esi_deduction + professional_tax + other_deductions
    ) STORED,
    net_salary DECIMAL(10, 2) GENERATED ALWAYS AS (
        basic_salary + hra + transport_allowance + medical_allowance +
        special_allowance + other_allowances - pf_deduction - esi_deduction -
        professional_tax - other_deductions
    ) STORED,
    effective_from DATE NOT NULL,
    effective_to DATE,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_active (is_active)
) ENGINE=InnoDB;

CREATE TABLE staff_salary_mapping (
    mapping_id INT PRIMARY KEY AUTO_INCREMENT,
    staff_id INT NOT NULL,
    package_id INT NOT NULL,
    effective_from DATE NOT NULL,
    effective_to DATE,
    is_current BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (staff_id) REFERENCES staff(staff_id) ON DELETE CASCADE,
    FOREIGN KEY (package_id) REFERENCES salary_packages(package_id),
    INDEX idx_staff_current (staff_id, is_current)
) ENGINE=InnoDB;

-- ============================================================================
-- 5. ATTENDANCE MANAGEMENT
-- ============================================================================

CREATE TABLE duty_schedules (
    schedule_id INT PRIMARY KEY AUTO_INCREMENT,
    schedule_name VARCHAR(100) NOT NULL,
    shift_start_time TIME NOT NULL,
    shift_end_time TIME NOT NULL,
    grace_period_minutes INT DEFAULT 15,
    working_hours DECIMAL(4, 2) NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE attendance (
    attendance_id INT PRIMARY KEY AUTO_INCREMENT,
    staff_id INT NOT NULL,
    attendance_date DATE NOT NULL,
    check_in_time DATETIME,
    check_out_time DATETIME,
    schedule_id INT,

    -- Attendance Status
    status ENUM('Present', 'Absent', 'Half Day', 'Leave', 'Holiday', 'Week Off') DEFAULT 'Present',
    is_late BOOLEAN DEFAULT FALSE,
    late_by_minutes INT DEFAULT 0,

    -- Working Hours
    total_working_hours DECIMAL(4, 2) DEFAULT 0,
    overtime_hours DECIMAL(4, 2) DEFAULT 0,

    -- Biometric Verification
    biometric_verified BOOLEAN DEFAULT FALSE,

    remarks TEXT,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (staff_id) REFERENCES staff(staff_id) ON DELETE CASCADE,
    FOREIGN KEY (schedule_id) REFERENCES duty_schedules(schedule_id),
    UNIQUE KEY unique_attendance (staff_id, attendance_date),
    INDEX idx_date (attendance_date),
    INDEX idx_status (status)
) ENGINE=InnoDB;

-- ============================================================================
-- 6. LEAVE MANAGEMENT
-- ============================================================================

CREATE TABLE leave_types (
    leave_type_id INT PRIMARY KEY AUTO_INCREMENT,
    leave_type_name VARCHAR(50) NOT NULL,
    annual_quota INT DEFAULT 0,
    is_paid BOOLEAN DEFAULT TRUE,
    is_carry_forward BOOLEAN DEFAULT FALSE,
    max_carry_forward INT DEFAULT 0,
    description TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE leave_balance (
    balance_id INT PRIMARY KEY AUTO_INCREMENT,
    staff_id INT NOT NULL,
    leave_type_id INT NOT NULL,
    year INT NOT NULL,
    opening_balance DECIMAL(5, 2) DEFAULT 0,
    earned DECIMAL(5, 2) DEFAULT 0,
    used DECIMAL(5, 2) DEFAULT 0,
    balance DECIMAL(5, 2) GENERATED ALWAYS AS (opening_balance + earned - used) STORED,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (staff_id) REFERENCES staff(staff_id) ON DELETE CASCADE,
    FOREIGN KEY (leave_type_id) REFERENCES leave_types(leave_type_id),
    UNIQUE KEY unique_leave_balance (staff_id, leave_type_id, year)
) ENGINE=InnoDB;

CREATE TABLE leave_applications (
    application_id INT PRIMARY KEY AUTO_INCREMENT,
    staff_id INT NOT NULL,
    leave_type_id INT NOT NULL,
    from_date DATE NOT NULL,
    to_date DATE NOT NULL,
    total_days DECIMAL(5, 2) NOT NULL,
    reason TEXT NOT NULL,

    -- Approval Workflow
    status ENUM('Pending', 'Approved', 'Rejected', 'Cancelled') DEFAULT 'Pending',
    applied_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    approved_by INT,
    approved_date DATETIME,
    rejection_reason TEXT,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (staff_id) REFERENCES staff(staff_id) ON DELETE CASCADE,
    FOREIGN KEY (leave_type_id) REFERENCES leave_types(leave_type_id),
    FOREIGN KEY (approved_by) REFERENCES users(user_id),
    INDEX idx_status (status),
    INDEX idx_dates (from_date, to_date)
) ENGINE=InnoDB;

-- ============================================================================
-- 7. PRODUCT CATEGORIES (for Sales and Incentives)
-- ============================================================================

CREATE TABLE product_categories (
    category_id INT PRIMARY KEY AUTO_INCREMENT,
    category_name VARCHAR(50) NOT NULL,
    category_code VARCHAR(20) UNIQUE NOT NULL,
    description TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_code (category_code)
) ENGINE=InnoDB;

-- ============================================================================
-- 8. INCENTIVE SCHEMES
-- ============================================================================

CREATE TABLE incentive_schemes (
    scheme_id INT PRIMARY KEY AUTO_INCREMENT,
    scheme_name VARCHAR(100) NOT NULL,
    scheme_code VARCHAR(20) UNIQUE NOT NULL,
    category_id INT,

    -- Scheme Configuration
    calculation_type ENUM('Percentage', 'PerGram', 'PerPiece', 'Fixed', 'TargetBased') NOT NULL,

    -- Individual Incentive
    individual_rate DECIMAL(10, 4) DEFAULT 0,
    individual_percentage DECIMAL(5, 2) DEFAULT 0,

    -- Manager Incentive
    manager_rate DECIMAL(10, 4) DEFAULT 0,
    manager_percentage DECIMAL(5, 2) DEFAULT 0,

    -- Common Pool
    common_rate DECIMAL(10, 4) DEFAULT 0,
    common_percentage DECIMAL(5, 2) DEFAULT 0,

    -- Department Shares (for distribution)
    sales_dept_share DECIMAL(5, 2) DEFAULT 100,
    other_dept_share DECIMAL(5, 2) DEFAULT 50,

    -- Target Based Configuration
    daily_target DECIMAL(10, 2) DEFAULT 0,
    target_unit VARCHAR(20),

    description TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    effective_from DATE,
    effective_to DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (category_id) REFERENCES product_categories(category_id),
    INDEX idx_scheme_code (scheme_code),
    INDEX idx_category (category_id)
) ENGINE=InnoDB;

-- ============================================================================
-- 9. SALES TRACKING
-- ============================================================================

CREATE TABLE sales_transactions (
    transaction_id INT PRIMARY KEY AUTO_INCREMENT,
    transaction_code VARCHAR(50) UNIQUE NOT NULL,
    transaction_date DATE NOT NULL,

    -- Product Details
    category_id INT NOT NULL,
    product_description TEXT,

    -- Quantity and Value
    quantity DECIMAL(10, 3) DEFAULT 1,
    weight_grams DECIMAL(10, 3) DEFAULT 0,
    sale_value DECIMAL(12, 2) NOT NULL,

    -- Staff Assignment
    primary_staff_id INT NOT NULL,
    secondary_staff_id INT,

    -- Branch and Department
    branch_id INT,
    department_id INT,

    -- Payment Details
    payment_mode ENUM('Cash', 'Card', 'UPI', 'Net Banking', 'Other') DEFAULT 'Cash',
    customer_name VARCHAR(100),
    customer_phone VARCHAR(20),

    -- Status
    status ENUM('Completed', 'Cancelled', 'Returned') DEFAULT 'Completed',

    remarks TEXT,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (category_id) REFERENCES product_categories(category_id),
    FOREIGN KEY (primary_staff_id) REFERENCES staff(staff_id),
    FOREIGN KEY (secondary_staff_id) REFERENCES staff(staff_id),
    FOREIGN KEY (branch_id) REFERENCES branches(branch_id),
    FOREIGN KEY (department_id) REFERENCES departments(department_id),
    FOREIGN KEY (created_by) REFERENCES users(user_id),

    INDEX idx_transaction_code (transaction_code),
    INDEX idx_transaction_date (transaction_date),
    INDEX idx_category (category_id),
    INDEX idx_staff (primary_staff_id),
    INDEX idx_status (status)
) ENGINE=InnoDB;

-- ============================================================================
-- 10. INCENTIVE CALCULATIONS
-- ============================================================================

CREATE TABLE incentive_calculations (
    calculation_id INT PRIMARY KEY AUTO_INCREMENT,
    transaction_id INT NOT NULL,
    staff_id INT NOT NULL,
    scheme_id INT NOT NULL,

    -- Calculation Details
    calculation_date DATE NOT NULL,
    base_amount DECIMAL(12, 2) NOT NULL,
    incentive_type ENUM('Individual', 'Manager', 'Common') NOT NULL,
    incentive_amount DECIMAL(10, 2) NOT NULL,

    -- Distribution Details
    share_percentage DECIMAL(5, 2) DEFAULT 100,
    final_amount DECIMAL(10, 2) NOT NULL,

    -- Status
    status ENUM('Pending', 'Approved', 'Paid') DEFAULT 'Pending',
    approved_by INT,
    approved_date DATETIME,

    remarks TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (transaction_id) REFERENCES sales_transactions(transaction_id) ON DELETE CASCADE,
    FOREIGN KEY (staff_id) REFERENCES staff(staff_id),
    FOREIGN KEY (scheme_id) REFERENCES incentive_schemes(scheme_id),
    FOREIGN KEY (approved_by) REFERENCES users(user_id),

    INDEX idx_staff_date (staff_id, calculation_date),
    INDEX idx_status (status),
    INDEX idx_transaction (transaction_id)
) ENGINE=InnoDB;

-- ============================================================================
-- 11. PAYROLL PROCESSING
-- ============================================================================

CREATE TABLE payroll_periods (
    period_id INT PRIMARY KEY AUTO_INCREMENT,
    period_name VARCHAR(50) NOT NULL,
    month INT NOT NULL,
    year INT NOT NULL,
    from_date DATE NOT NULL,
    to_date DATE NOT NULL,
    status ENUM('Open', 'Processing', 'Completed', 'Closed') DEFAULT 'Open',
    processed_by INT,
    processed_date DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (processed_by) REFERENCES users(user_id),
    UNIQUE KEY unique_period (month, year),
    INDEX idx_status (status)
) ENGINE=InnoDB;

CREATE TABLE payroll (
    payroll_id INT PRIMARY KEY AUTO_INCREMENT,
    period_id INT NOT NULL,
    staff_id INT NOT NULL,

    -- Salary Components
    basic_salary DECIMAL(10, 2) NOT NULL,
    hra DECIMAL(10, 2) DEFAULT 0,
    transport_allowance DECIMAL(10, 2) DEFAULT 0,
    medical_allowance DECIMAL(10, 2) DEFAULT 0,
    special_allowance DECIMAL(10, 2) DEFAULT 0,
    other_allowances DECIMAL(10, 2) DEFAULT 0,

    -- Attendance Based
    total_working_days INT DEFAULT 0,
    days_present DECIMAL(5, 2) DEFAULT 0,
    days_absent DECIMAL(5, 2) DEFAULT 0,
    paid_leaves DECIMAL(5, 2) DEFAULT 0,
    unpaid_leaves DECIMAL(5, 2) DEFAULT 0,
    overtime_hours DECIMAL(6, 2) DEFAULT 0,
    overtime_amount DECIMAL(10, 2) DEFAULT 0,

    -- Incentives
    total_incentives DECIMAL(10, 2) DEFAULT 0,

    -- Gross Calculation
    gross_salary DECIMAL(10, 2) GENERATED ALWAYS AS (
        basic_salary + hra + transport_allowance + medical_allowance +
        special_allowance + other_allowances + overtime_amount + total_incentives
    ) STORED,

    -- Deductions
    pf_deduction DECIMAL(10, 2) DEFAULT 0,
    esi_deduction DECIMAL(10, 2) DEFAULT 0,
    professional_tax DECIMAL(10, 2) DEFAULT 0,
    tds DECIMAL(10, 2) DEFAULT 0,
    loan_deduction DECIMAL(10, 2) DEFAULT 0,
    advance_deduction DECIMAL(10, 2) DEFAULT 0,
    other_deductions DECIMAL(10, 2) DEFAULT 0,

    total_deductions DECIMAL(10, 2) GENERATED ALWAYS AS (
        pf_deduction + esi_deduction + professional_tax + tds +
        loan_deduction + advance_deduction + other_deductions
    ) STORED,

    -- Net Salary
    net_salary DECIMAL(10, 2) GENERATED ALWAYS AS (
        basic_salary + hra + transport_allowance + medical_allowance +
        special_allowance + other_allowances + overtime_amount + total_incentives -
        pf_deduction - esi_deduction - professional_tax - tds -
        loan_deduction - advance_deduction - other_deductions
    ) STORED,

    -- Payment Status
    payment_status ENUM('Pending', 'Approved', 'Paid') DEFAULT 'Pending',
    payment_date DATE,
    payment_mode VARCHAR(50),
    transaction_reference VARCHAR(100),

    remarks TEXT,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (period_id) REFERENCES payroll_periods(period_id),
    FOREIGN KEY (staff_id) REFERENCES staff(staff_id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(user_id),

    UNIQUE KEY unique_payroll (period_id, staff_id),
    INDEX idx_status (payment_status),
    INDEX idx_period (period_id)
) ENGINE=InnoDB;

-- ============================================================================
-- 12. BIOMETRIC AUTHENTICATION
-- ============================================================================

CREATE TABLE biometric_devices (
    device_id INT PRIMARY KEY AUTO_INCREMENT,
    device_name VARCHAR(100) NOT NULL,
    device_code VARCHAR(50) UNIQUE NOT NULL,
    device_type ENUM('Fingerprint', 'Face Recognition', 'Iris Scan', 'Palm Vein') NOT NULL,
    branch_id INT,
    location VARCHAR(100),
    ip_address VARCHAR(50),
    port INT,
    is_active BOOLEAN DEFAULT TRUE,
    last_sync DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (branch_id) REFERENCES branches(branch_id),
    INDEX idx_device_code (device_code),
    INDEX idx_branch (branch_id)
) ENGINE=InnoDB;

CREATE TABLE biometric_templates (
    template_id INT PRIMARY KEY AUTO_INCREMENT,
    staff_id INT NOT NULL,
    biometric_type ENUM('Fingerprint', 'Face Recognition', 'Iris Scan', 'Palm Vein') NOT NULL,
    template_data MEDIUMBLOB NOT NULL,
    template_hash VARCHAR(255),
    quality_score INT,
    finger_position VARCHAR(20),
    registered_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (staff_id) REFERENCES staff(staff_id) ON DELETE CASCADE,
    INDEX idx_staff_type (staff_id, biometric_type)
) ENGINE=InnoDB;

CREATE TABLE biometric_auth_logs (
    log_id INT PRIMARY KEY AUTO_INCREMENT,
    staff_id INT,
    device_id INT,
    biometric_type ENUM('Fingerprint', 'Face Recognition', 'Iris Scan', 'Palm Vein') NOT NULL,
    auth_type ENUM('Login', 'Attendance', 'Access Control') NOT NULL,
    match_score DECIMAL(5, 2),
    status ENUM('Success', 'Failed', 'Rejected') NOT NULL,
    failure_reason VARCHAR(255),
    liveness_check BOOLEAN DEFAULT FALSE,
    auth_timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(50),
    FOREIGN KEY (staff_id) REFERENCES staff(staff_id),
    FOREIGN KEY (device_id) REFERENCES biometric_devices(device_id),
    INDEX idx_timestamp (auth_timestamp),
    INDEX idx_staff (staff_id),
    INDEX idx_status (status)
) ENGINE=InnoDB;

CREATE TABLE biometric_access_control (
    access_id INT PRIMARY KEY AUTO_INCREMENT,
    staff_id INT NOT NULL,
    access_level ENUM('Full', 'Limited', 'Restricted') DEFAULT 'Full',
    allowed_locations TEXT,
    allowed_time_from TIME,
    allowed_time_to TIME,
    is_active BOOLEAN DEFAULT TRUE,
    effective_from DATE,
    effective_to DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (staff_id) REFERENCES staff(staff_id) ON DELETE CASCADE,
    INDEX idx_staff (staff_id)
) ENGINE=InnoDB;

-- ============================================================================
-- 13. SOCIAL MEDIA MANAGEMENT
-- ============================================================================

CREATE TABLE social_media_platforms (
    platform_id INT PRIMARY KEY AUTO_INCREMENT,
    platform_name VARCHAR(50) NOT NULL,
    platform_icon VARCHAR(100),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE social_media_posts (
    post_id INT PRIMARY KEY AUTO_INCREMENT,
    platform_id INT NOT NULL,
    post_title VARCHAR(200),
    post_content TEXT,
    post_url VARCHAR(500),
    post_date DATE NOT NULL,
    posted_by INT,

    -- Engagement Metrics
    likes_count INT DEFAULT 0,
    shares_count INT DEFAULT 0,
    comments_count INT DEFAULT 0,
    reach_count INT DEFAULT 0,

    status ENUM('Scheduled', 'Published', 'Draft') DEFAULT 'Published',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (platform_id) REFERENCES social_media_platforms(platform_id),
    FOREIGN KEY (posted_by) REFERENCES users(user_id),
    INDEX idx_post_date (post_date),
    INDEX idx_platform (platform_id)
) ENGINE=InnoDB;

-- ============================================================================
-- 14. AUDIT LOGS
-- ============================================================================

CREATE TABLE audit_logs (
    log_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    action_type VARCHAR(50) NOT NULL,
    table_name VARCHAR(100),
    record_id INT,
    old_values JSON,
    new_values JSON,
    ip_address VARCHAR(50),
    user_agent TEXT,
    action_timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    INDEX idx_user (user_id),
    INDEX idx_timestamp (action_timestamp),
    INDEX idx_table (table_name)
) ENGINE=InnoDB;

-- ============================================================================
-- 15. SYSTEM SETTINGS
-- ============================================================================

CREATE TABLE system_settings (
    setting_id INT PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    setting_type VARCHAR(50),
    description TEXT,
    is_editable BOOLEAN DEFAULT TRUE,
    updated_by INT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (updated_by) REFERENCES users(user_id),
    INDEX idx_key (setting_key)
) ENGINE=InnoDB;
