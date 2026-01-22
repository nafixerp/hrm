-- ============================================================================
-- HRMS - Sample Data and Initial Configuration
-- ============================================================================

USE hrms_db;

-- ============================================================================
-- 1. DEFAULT USERS (Password: admin123, manager123, staff123 - hashed using SHA256)
-- ============================================================================

INSERT INTO users (username, email, password_hash, role, is_active) VALUES
('admin', 'admin@hrms.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin', TRUE),
('manager1', 'manager1@hrms.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Manager', TRUE),
('manager2', 'manager2@hrms.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Manager', TRUE),
('staff1', 'staff1@hrms.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Staff', TRUE),
('staff2', 'staff2@hrms.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Staff', TRUE),
('staff3', 'staff3@hrms.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Staff', TRUE);

-- ============================================================================
-- 2. BRANCHES
-- ============================================================================

INSERT INTO branches (branch_name, branch_code, address, city, state, pincode, phone, email) VALUES
('Main Branch', 'BR001', '123 Main Street, Downtown', 'Mumbai', 'Maharashtra', '400001', '022-12345678', 'main@hrms.com'),
('North Branch', 'BR002', '456 North Avenue', 'Delhi', 'Delhi', '110001', '011-23456789', 'north@hrms.com'),
('South Branch', 'BR003', '789 South Road', 'Bangalore', 'Karnataka', '560001', '080-34567890', 'south@hrms.com'),
('Workshop 1', 'WS001', 'Industrial Area Phase 1', 'Mumbai', 'Maharashtra', '400002', '022-45678901', 'workshop1@hrms.com');

-- ============================================================================
-- 3. DEPARTMENTS
-- ============================================================================

INSERT INTO departments (department_name, department_code, description, is_sales_department) VALUES
('Sales', 'SALES', 'Sales and Customer Service', TRUE),
('Production', 'PROD', 'Manufacturing and Production', FALSE),
('Quality Control', 'QC', 'Quality Assurance and Control', FALSE),
('Design', 'DESIGN', 'Product Design and Development', FALSE),
('Administration', 'ADMIN', 'Administration and HR', FALSE),
('Finance', 'FIN', 'Finance and Accounts', FALSE),
('Marketing', 'MKT', 'Marketing and Promotions', FALSE),
('Workshop', 'WS', 'Workshop Operations', FALSE);

-- ============================================================================
-- 4. DESIGNATIONS
-- ============================================================================

INSERT INTO designations (designation_name, level, description, is_manager) VALUES
('Managing Director', 1, 'Top Management', TRUE),
('General Manager', 2, 'Department Head', TRUE),
('Manager', 3, 'Team Manager', TRUE),
('Assistant Manager', 4, 'Assistant Management', TRUE),
('Senior Executive', 5, 'Senior Staff', FALSE),
('Executive', 6, 'Staff Member', FALSE),
('Junior Executive', 7, 'Junior Staff', FALSE),
('Trainee', 8, 'Training Staff', FALSE),
('Craftsman', 5, 'Skilled Worker', FALSE),
('Helper', 7, 'Assistant Worker', FALSE);

-- ============================================================================
-- 5. PRODUCT CATEGORIES
-- ============================================================================

INSERT INTO product_categories (category_name, category_code, description) VALUES
('Gold', 'GOLD', 'Gold Jewelry and Ornaments'),
('Diamond', 'DIAMOND', 'Diamond Jewelry'),
('Silver', 'SILVER', 'Silver Jewelry and Items'),
('Precious', 'PRECIOUS', 'Precious Stone Jewelry'),
('Studex', 'STUDEX', 'Studex Items'),
('PIMS', 'PIMS', 'PIMS Category Items'),
('18K Gold', '18K', '18 Karat Gold Items'),
('Premium', 'PREMIUM', 'Premium Collection'),
('3g Item', '3G', '3 Gram Special Items'),
('Urgent', 'URGENT', 'Urgent Order Items');

-- ============================================================================
-- 6. INCENTIVE SCHEMES (All 10 Schemes as per Requirements)
-- ============================================================================

-- 1. Studex Scheme - ₹25 per piece
INSERT INTO incentive_schemes (
    scheme_name, scheme_code, category_id, calculation_type,
    individual_rate, description, is_active, effective_from
) VALUES (
    'Studex Scheme', 'STUDEX',
    (SELECT category_id FROM product_categories WHERE category_code = 'STUDEX'),
    'PerPiece', 25.00,
    'Per Piece: ₹25/- per individual item',
    TRUE, '2024-01-01'
);

-- 2. PIMS Scheme - Individual 0.4%, Manager 0.3%
INSERT INTO incentive_schemes (
    scheme_name, scheme_code, category_id, calculation_type,
    individual_percentage, manager_percentage,
    description, is_active, effective_from
) VALUES (
    'PIMS Scheme', 'PIMS',
    (SELECT category_id FROM product_categories WHERE category_code = 'PIMS'),
    'Percentage', 0.40, 0.30,
    'Individual: 0.4% of sales value, Managers: 0.3% of sales value',
    TRUE, '2024-01-01'
);

-- 3. 18K Scheme - Individual 0.6%, Common 0.4%
INSERT INTO incentive_schemes (
    scheme_name, scheme_code, category_id, calculation_type,
    individual_percentage, common_percentage,
    description, is_active, effective_from
) VALUES (
    '18K Gold Scheme', '18K',
    (SELECT category_id FROM product_categories WHERE category_code = '18K'),
    'Percentage', 0.60, 0.40,
    'Individual: 0.6% of sales value, Common: 0.4% of sales value',
    TRUE, '2024-01-01'
);

-- 4. Premium Scheme - Individual 0.5%, Manager 0.3%
INSERT INTO incentive_schemes (
    scheme_name, scheme_code, category_id, calculation_type,
    individual_percentage, manager_percentage,
    description, is_active, effective_from
) VALUES (
    'Premium Scheme', 'PREMIUM',
    (SELECT category_id FROM product_categories WHERE category_code = 'PREMIUM'),
    'Percentage', 0.50, 0.30,
    'Individual: 0.5% of sales value, Managers: 0.3% of sales value',
    TRUE, '2024-01-01'
);

-- 5. Gold Scheme - Target Based (Per gram/2)
INSERT INTO incentive_schemes (
    scheme_name, scheme_code, category_id, calculation_type,
    individual_rate, sales_dept_share, other_dept_share,
    daily_target, target_unit,
    description, is_active, effective_from
) VALUES (
    'Gold Scheme', 'GOLD',
    (SELECT category_id FROM product_categories WHERE category_code = 'GOLD'),
    'TargetBased', 0.50, 100.00, 50.00,
    100.00, 'grams',
    'Daily target based: Per gram/2, Sales Department: Full share, Other Departments: 50% share',
    TRUE, '2024-01-01'
);

-- 6. Diamond Scheme - Individual 1%, Common 0.15%
INSERT INTO incentive_schemes (
    scheme_name, scheme_code, category_id, calculation_type,
    individual_percentage, common_percentage,
    description, is_active, effective_from
) VALUES (
    'Diamond Scheme', 'DIAMOND',
    (SELECT category_id FROM product_categories WHERE category_code = 'DIAMOND'),
    'Percentage', 1.00, 0.15,
    'Individual: 1% of sales value, Common: 0.15% of sales value. Distribution: Managers (2), Cash (1.5), Sales (1), Others (0.5)',
    TRUE, '2024-01-01'
);

-- 7. 3g Item Scheme - Individual per gram/3, Common 3 Rs/gram
INSERT INTO incentive_schemes (
    scheme_name, scheme_code, category_id, calculation_type,
    individual_rate, common_rate,
    description, is_active, effective_from
) VALUES (
    '3g Item Scheme', '3G',
    (SELECT category_id FROM product_categories WHERE category_code = '3G'),
    'PerGram', 0.33, 3.00,
    'Individual: Per gram/3 Rs, Common: 3 Rs/gram. Same distribution as Diamond',
    TRUE, '2024-01-01'
);

-- 8. Urgent Scheme - Individual 1%, Manager 0.25%
INSERT INTO incentive_schemes (
    scheme_name, scheme_code, category_id, calculation_type,
    individual_percentage, manager_percentage,
    description, is_active, effective_from
) VALUES (
    'Urgent Scheme', 'URGENT',
    (SELECT category_id FROM product_categories WHERE category_code = 'URGENT'),
    'Percentage', 1.00, 0.25,
    'Individual: 1% of sales value, Managers: 0.25% of sales value',
    TRUE, '2024-01-01'
);

-- 9. Precious Scheme - Individual 0.5%, Manager 0.3%
INSERT INTO incentive_schemes (
    scheme_name, scheme_code, category_id, calculation_type,
    individual_percentage, manager_percentage,
    description, is_active, effective_from
) VALUES (
    'Precious Scheme', 'PRECIOUS',
    (SELECT category_id FROM product_categories WHERE category_code = 'PRECIOUS'),
    'Percentage', 0.50, 0.30,
    'Individual: 0.5% of sales value, Managers: 0.3% of sales value',
    TRUE, '2024-01-01'
);

-- 10. Silver Scheme - Individual 6%, Manager 2%
INSERT INTO incentive_schemes (
    scheme_name, scheme_code, category_id, calculation_type,
    individual_percentage, manager_percentage,
    description, is_active, effective_from
) VALUES (
    'Silver Scheme', 'SILVER',
    (SELECT category_id FROM product_categories WHERE category_code = 'SILVER'),
    'Percentage', 6.00, 2.00,
    'Individual: 6% of sales value, Managers: 2% of sales value',
    TRUE, '2024-01-01'
);

-- ============================================================================
-- 7. LEAVE TYPES
-- ============================================================================

INSERT INTO leave_types (leave_type_name, annual_quota, is_paid, is_carry_forward, max_carry_forward) VALUES
('Casual Leave', 12, TRUE, TRUE, 5),
('Sick Leave', 12, TRUE, FALSE, 0),
('Earned Leave', 15, TRUE, TRUE, 10),
('Maternity Leave', 180, TRUE, FALSE, 0),
('Paternity Leave', 7, TRUE, FALSE, 0),
('Compensatory Off', 0, TRUE, FALSE, 0),
('Leave Without Pay', 0, FALSE, FALSE, 0);

-- ============================================================================
-- 8. DUTY SCHEDULES
-- ============================================================================

INSERT INTO duty_schedules (schedule_name, shift_start_time, shift_end_time, grace_period_minutes, working_hours) VALUES
('Morning Shift', '09:00:00', '18:00:00', 15, 8.00),
('Day Shift', '10:00:00', '19:00:00', 15, 8.00),
('Afternoon Shift', '14:00:00', '22:00:00', 15, 8.00),
('General Shift', '09:30:00', '18:30:00', 15, 8.00);

-- ============================================================================
-- 9. SALARY COMPONENTS
-- ============================================================================

INSERT INTO salary_components (component_name, component_type, is_fixed, is_taxable, calculation_type) VALUES
('Basic Salary', 'Earning', TRUE, TRUE, 'Fixed'),
('House Rent Allowance', 'Earning', TRUE, TRUE, 'Percentage'),
('Transport Allowance', 'Earning', TRUE, FALSE, 'Fixed'),
('Medical Allowance', 'Earning', TRUE, FALSE, 'Fixed'),
('Special Allowance', 'Earning', TRUE, TRUE, 'Fixed'),
('Overtime', 'Earning', FALSE, TRUE, 'Formula'),
('Incentive', 'Earning', FALSE, TRUE, 'Formula'),
('Provident Fund', 'Deduction', TRUE, FALSE, 'Percentage'),
('ESI', 'Deduction', TRUE, FALSE, 'Percentage'),
('Professional Tax', 'Deduction', TRUE, FALSE, 'Fixed'),
('TDS', 'Deduction', FALSE, FALSE, 'Formula'),
('Loan Deduction', 'Deduction', FALSE, FALSE, 'Fixed');

-- ============================================================================
-- 10. SAMPLE SALARY PACKAGES
-- ============================================================================

INSERT INTO salary_packages (
    package_name, basic_salary, hra, transport_allowance, medical_allowance,
    special_allowance, pf_deduction, esi_deduction, professional_tax,
    effective_from, is_active
) VALUES
('Entry Level', 15000.00, 6000.00, 1600.00, 1250.00, 3000.00, 1800.00, 450.00, 200.00, '2024-01-01', TRUE),
('Junior Level', 20000.00, 8000.00, 1600.00, 1250.00, 4000.00, 2400.00, 600.00, 200.00, '2024-01-01', TRUE),
('Mid Level', 30000.00, 12000.00, 2000.00, 1500.00, 6000.00, 3600.00, 0.00, 200.00, '2024-01-01', TRUE),
('Senior Level', 45000.00, 18000.00, 2500.00, 2000.00, 9000.00, 5400.00, 0.00, 200.00, '2024-01-01', TRUE),
('Manager Level', 60000.00, 24000.00, 3000.00, 2500.00, 12000.00, 7200.00, 0.00, 200.00, '2024-01-01', TRUE);

-- ============================================================================
-- 11. SAMPLE STAFF RECORDS
-- ============================================================================

INSERT INTO staff (
    user_id, employee_code, first_name, last_name, date_of_birth, gender,
    phone, email, address, city, state, pincode,
    branch_id, department_id, designation_id, date_of_joining,
    employment_type, employee_status,
    bank_name, account_number, ifsc_code, pan_number, aadhar_number
) VALUES
(2, 'EMP001', 'Rajesh', 'Kumar', '1990-05-15', 'Male',
 '9876543210', 'rajesh.kumar@hrms.com', '123 Manager Street', 'Mumbai', 'Maharashtra', '400001',
 1, 1, 3, '2020-01-15', 'Permanent', 'Active',
 'HDFC Bank', '12345678901234', 'HDFC0001234', 'ABCDE1234F', '123456789012'),

(3, 'EMP002', 'Priya', 'Sharma', '1992-08-20', 'Female',
 '9876543211', 'priya.sharma@hrms.com', '456 Manager Avenue', 'Delhi', 'Delhi', '110001',
 2, 1, 3, '2020-03-01', 'Permanent', 'Active',
 'ICICI Bank', '23456789012345', 'ICIC0001234', 'BCDEF2345G', '234567890123'),

(4, 'EMP003', 'Amit', 'Patel', '1995-03-10', 'Male',
 '9876543212', 'amit.patel@hrms.com', '789 Staff Road', 'Mumbai', 'Maharashtra', '400002',
 1, 1, 6, '2021-06-15', 'Permanent', 'Active',
 'SBI', '34567890123456', 'SBIN0001234', 'CDEFG3456H', '345678901234'),

(5, 'EMP004', 'Sneha', 'Reddy', '1994-11-25', 'Female',
 '9876543213', 'sneha.reddy@hrms.com', '321 Sales Street', 'Bangalore', 'Karnataka', '560001',
 3, 1, 6, '2021-09-01', 'Permanent', 'Active',
 'Axis Bank', '45678901234567', 'UTIB0001234', 'DEFGH4567I', '456789012345'),

(6, 'EMP005', 'Vikram', 'Singh', '1993-07-18', 'Male',
 '9876543214', 'vikram.singh@hrms.com', '654 Workshop Lane', 'Mumbai', 'Maharashtra', '400002',
 4, 2, 9, '2020-08-10', 'Permanent', 'Active',
 'PNB', '56789012345678', 'PUNB0001234', 'EFGHI5678J', '567890123456');

-- ============================================================================
-- 12. STAFF SALARY MAPPING
-- ============================================================================

INSERT INTO staff_salary_mapping (staff_id, package_id, effective_from, is_current) VALUES
(1, 5, '2020-01-15', TRUE),
(2, 5, '2020-03-01', TRUE),
(3, 3, '2021-06-15', TRUE),
(4, 3, '2021-09-01', TRUE),
(5, 2, '2020-08-10', TRUE);

-- ============================================================================
-- 13. LEAVE BALANCE (Current Year)
-- ============================================================================

INSERT INTO leave_balance (staff_id, leave_type_id, year, opening_balance, earned) VALUES
(1, 1, YEAR(CURDATE()), 2, 12),
(1, 2, YEAR(CURDATE()), 0, 12),
(1, 3, YEAR(CURDATE()), 5, 15),
(2, 1, YEAR(CURDATE()), 3, 12),
(2, 2, YEAR(CURDATE()), 0, 12),
(2, 3, YEAR(CURDATE()), 4, 15),
(3, 1, YEAR(CURDATE()), 0, 12),
(3, 2, YEAR(CURDATE()), 0, 12),
(3, 3, YEAR(CURDATE()), 0, 15),
(4, 1, YEAR(CURDATE()), 0, 12),
(4, 2, YEAR(CURDATE()), 0, 12),
(4, 3, YEAR(CURDATE()), 0, 15),
(5, 1, YEAR(CURDATE()), 0, 12),
(5, 2, YEAR(CURDATE()), 0, 12),
(5, 3, YEAR(CURDATE()), 0, 15);

-- ============================================================================
-- 14. SOCIAL MEDIA PLATFORMS
-- ============================================================================

INSERT INTO social_media_platforms (platform_name, platform_icon) VALUES
('Facebook', 'fab fa-facebook'),
('Instagram', 'fab fa-instagram'),
('Twitter', 'fab fa-twitter'),
('LinkedIn', 'fab fa-linkedin'),
('YouTube', 'fab fa-youtube'),
('Pinterest', 'fab fa-pinterest');

-- ============================================================================
-- 15. SYSTEM SETTINGS
-- ============================================================================

INSERT INTO system_settings (setting_key, setting_value, setting_type, description) VALUES
('company_name', 'HRMS Company', 'text', 'Company Name'),
('company_address', '123 Main Street, City', 'text', 'Company Address'),
('company_phone', '+91-1234567890', 'text', 'Company Phone'),
('company_email', 'info@hrms.com', 'email', 'Company Email'),
('currency_symbol', '₹', 'text', 'Currency Symbol'),
('date_format', 'd-m-Y', 'text', 'Date Format'),
('timezone', 'Asia/Kolkata', 'text', 'System Timezone'),
('working_days_per_month', '26', 'number', 'Default Working Days per Month'),
('overtime_multiplier', '1.5', 'decimal', 'Overtime Pay Multiplier'),
('late_grace_minutes', '15', 'number', 'Late Coming Grace Period (minutes)'),
('biometric_match_threshold', '75', 'number', 'Biometric Match Threshold Percentage'),
('biometric_liveness_check', '1', 'boolean', 'Enable Biometric Liveness Detection'),
('max_failed_auth_attempts', '3', 'number', 'Maximum Failed Authentication Attempts'),
('session_timeout_minutes', '60', 'number', 'Session Timeout (minutes)'),
('payroll_cutoff_day', '25', 'number', 'Payroll Processing Cutoff Day of Month');
