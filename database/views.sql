-- ============================================================================
-- HRMS - Database Views for Reporting
-- ============================================================================

USE hrms_db;

-- ============================================================================
-- 1. STAFF DETAILS VIEW
-- ============================================================================

CREATE OR REPLACE VIEW vw_staff_details AS
SELECT
    s.staff_id,
    s.employee_code,
    CONCAT(s.first_name, ' ', s.last_name) as full_name,
    s.email,
    s.phone,
    s.date_of_joining,
    s.employee_status,
    b.branch_name,
    d.department_name,
    dg.designation_name,
    dg.is_manager,
    sp.package_name,
    sp.net_salary as current_salary,
    u.username,
    u.role as user_role
FROM staff s
LEFT JOIN branches b ON s.branch_id = b.branch_id
LEFT JOIN departments d ON s.department_id = d.department_id
LEFT JOIN designations dg ON s.designation_id = dg.designation_id
LEFT JOIN staff_salary_mapping ssm ON s.staff_id = ssm.staff_id AND ssm.is_current = TRUE
LEFT JOIN salary_packages sp ON ssm.package_id = sp.package_id
LEFT JOIN users u ON s.user_id = u.user_id;

-- ============================================================================
-- 2. ATTENDANCE SUMMARY VIEW
-- ============================================================================

CREATE OR REPLACE VIEW vw_attendance_summary AS
SELECT
    a.staff_id,
    sd.full_name,
    sd.employee_code,
    sd.department_name,
    YEAR(a.attendance_date) as year,
    MONTH(a.attendance_date) as month,
    COUNT(a.attendance_id) as total_days_marked,
    SUM(CASE WHEN a.status = 'Present' THEN 1 ELSE 0 END) as days_present,
    SUM(CASE WHEN a.status = 'Absent' THEN 1 ELSE 0 END) as days_absent,
    SUM(CASE WHEN a.status = 'Half Day' THEN 1 ELSE 0 END) as half_days,
    SUM(CASE WHEN a.status = 'Leave' THEN 1 ELSE 0 END) as leaves_taken,
    SUM(CASE WHEN a.is_late = TRUE THEN 1 ELSE 0 END) as late_days,
    SUM(a.total_working_hours) as total_working_hours,
    SUM(a.overtime_hours) as total_overtime_hours
FROM attendance a
JOIN vw_staff_details sd ON a.staff_id = sd.staff_id
GROUP BY a.staff_id, YEAR(a.attendance_date), MONTH(a.attendance_date);

-- ============================================================================
-- 3. LEAVE BALANCE VIEW
-- ============================================================================

CREATE OR REPLACE VIEW vw_leave_balance AS
SELECT
    lb.staff_id,
    sd.full_name,
    sd.employee_code,
    lt.leave_type_name,
    lt.is_paid,
    lb.year,
    lb.opening_balance,
    lb.earned,
    lb.used,
    lb.balance as available_balance
FROM leave_balance lb
JOIN vw_staff_details sd ON lb.staff_id = sd.staff_id
JOIN leave_types lt ON lb.leave_type_id = lt.leave_type_id
WHERE lb.year = YEAR(CURDATE());

-- ============================================================================
-- 4. SALES TRANSACTIONS VIEW
-- ============================================================================

CREATE OR REPLACE VIEW vw_sales_transactions AS
SELECT
    st.transaction_id,
    st.transaction_code,
    st.transaction_date,
    pc.category_name,
    st.product_description,
    st.quantity,
    st.weight_grams,
    st.sale_value,
    CONCAT(s1.first_name, ' ', s1.last_name) as primary_staff_name,
    s1.employee_code as primary_staff_code,
    CONCAT(s2.first_name, ' ', s2.last_name) as secondary_staff_name,
    b.branch_name,
    d.department_name,
    st.payment_mode,
    st.customer_name,
    st.customer_phone,
    st.status,
    st.created_at
FROM sales_transactions st
JOIN product_categories pc ON st.category_id = pc.category_id
JOIN staff s1 ON st.primary_staff_id = s1.staff_id
LEFT JOIN staff s2 ON st.secondary_staff_id = s2.staff_id
LEFT JOIN branches b ON st.branch_id = b.branch_id
LEFT JOIN departments d ON st.department_id = d.department_id
WHERE st.status = 'Completed';

-- ============================================================================
-- 5. INCENTIVE SUMMARY VIEW
-- ============================================================================

CREATE OR REPLACE VIEW vw_incentive_summary AS
SELECT
    ic.staff_id,
    sd.full_name,
    sd.employee_code,
    sd.department_name,
    YEAR(ic.calculation_date) as year,
    MONTH(ic.calculation_date) as month,
    isch.scheme_name,
    pc.category_name,
    COUNT(ic.calculation_id) as transaction_count,
    SUM(ic.base_amount) as total_sales_value,
    ic.incentive_type,
    SUM(ic.incentive_amount) as total_incentive_amount,
    SUM(ic.final_amount) as total_final_amount,
    ic.status
FROM incentive_calculations ic
JOIN vw_staff_details sd ON ic.staff_id = sd.staff_id
JOIN incentive_schemes isch ON ic.scheme_id = isch.scheme_id
LEFT JOIN product_categories pc ON isch.category_id = pc.category_id
GROUP BY
    ic.staff_id,
    YEAR(ic.calculation_date),
    MONTH(ic.calculation_date),
    isch.scheme_id,
    ic.incentive_type,
    ic.status;

-- ============================================================================
-- 6. PAYROLL SUMMARY VIEW
-- ============================================================================

CREATE OR REPLACE VIEW vw_payroll_summary AS
SELECT
    p.payroll_id,
    pp.period_name,
    pp.month,
    pp.year,
    p.staff_id,
    sd.full_name,
    sd.employee_code,
    sd.department_name,
    sd.designation_name,

    -- Earnings
    p.basic_salary,
    p.hra,
    p.transport_allowance,
    p.medical_allowance,
    p.special_allowance,
    p.other_allowances,
    p.overtime_amount,
    p.total_incentives,
    p.gross_salary,

    -- Attendance
    p.total_working_days,
    p.days_present,
    p.days_absent,
    p.paid_leaves,
    p.overtime_hours,

    -- Deductions
    p.pf_deduction,
    p.esi_deduction,
    p.professional_tax,
    p.tds,
    p.loan_deduction,
    p.advance_deduction,
    p.other_deductions,
    p.total_deductions,

    -- Net
    p.net_salary,
    p.payment_status,
    p.payment_date

FROM payroll p
JOIN payroll_periods pp ON p.period_id = pp.period_id
JOIN vw_staff_details sd ON p.staff_id = sd.staff_id;

-- ============================================================================
-- 7. DEPARTMENT PERFORMANCE VIEW
-- ============================================================================

CREATE OR REPLACE VIEW vw_department_performance AS
SELECT
    d.department_id,
    d.department_name,
    YEAR(st.transaction_date) as year,
    MONTH(st.transaction_date) as month,

    -- Staff Count
    COUNT(DISTINCT s.staff_id) as total_staff,

    -- Sales Performance
    COUNT(st.transaction_id) as total_sales,
    SUM(st.sale_value) as total_sales_value,

    -- Incentives
    SUM(ic.final_amount) as total_incentives_paid,

    -- Attendance
    AVG(att.days_present) as avg_attendance_days,
    AVG(att.total_overtime_hours) as avg_overtime_hours

FROM departments d
LEFT JOIN staff s ON d.department_id = s.department_id AND s.employee_status = 'Active'
LEFT JOIN sales_transactions st ON s.staff_id = st.primary_staff_id
LEFT JOIN incentive_calculations ic ON st.transaction_id = ic.transaction_id
LEFT JOIN vw_attendance_summary att ON s.staff_id = att.staff_id
    AND YEAR(st.transaction_date) = att.year
    AND MONTH(st.transaction_date) = att.month
WHERE st.status = 'Completed'
GROUP BY d.department_id, YEAR(st.transaction_date), MONTH(st.transaction_date);

-- ============================================================================
-- 8. CATEGORY WISE SALES VIEW
-- ============================================================================

CREATE OR REPLACE VIEW vw_category_sales AS
SELECT
    pc.category_id,
    pc.category_name,
    DATE(st.transaction_date) as sale_date,
    COUNT(st.transaction_id) as transaction_count,
    SUM(st.quantity) as total_quantity,
    SUM(st.weight_grams) as total_weight,
    SUM(st.sale_value) as total_sales_value,
    AVG(st.sale_value) as avg_sale_value,
    SUM(ic.final_amount) as total_incentives
FROM product_categories pc
LEFT JOIN sales_transactions st ON pc.category_id = st.category_id AND st.status = 'Completed'
LEFT JOIN incentive_calculations ic ON st.transaction_id = ic.transaction_id
GROUP BY pc.category_id, DATE(st.transaction_date);

-- ============================================================================
-- 9. MONTHLY SALES TREND VIEW
-- ============================================================================

CREATE OR REPLACE VIEW vw_monthly_sales_trend AS
SELECT
    YEAR(transaction_date) as year,
    MONTH(transaction_date) as month,
    DATE_FORMAT(transaction_date, '%Y-%m') as year_month,
    COUNT(transaction_id) as total_transactions,
    SUM(sale_value) as total_sales,
    AVG(sale_value) as avg_transaction_value,
    COUNT(DISTINCT primary_staff_id) as active_sales_staff,
    COUNT(DISTINCT customer_phone) as unique_customers
FROM sales_transactions
WHERE status = 'Completed'
GROUP BY YEAR(transaction_date), MONTH(transaction_date)
ORDER BY year DESC, month DESC;

-- ============================================================================
-- 10. STAFF PERFORMANCE RANKING VIEW
-- ============================================================================

CREATE OR REPLACE VIEW vw_staff_performance_ranking AS
SELECT
    s.staff_id,
    sd.full_name,
    sd.employee_code,
    sd.department_name,
    sd.designation_name,
    YEAR(st.transaction_date) as year,
    MONTH(st.transaction_date) as month,

    -- Sales Metrics
    COUNT(st.transaction_id) as total_sales,
    SUM(st.sale_value) as total_sales_value,

    -- Incentives
    SUM(ic.final_amount) as total_incentives,

    -- Attendance Score (Present + Leave / Total Days * 100)
    ROUND(((att.days_present + att.leaves_taken) / att.total_days_marked) * 100, 2) as attendance_percentage,

    -- Ranking
    RANK() OVER (
        PARTITION BY YEAR(st.transaction_date), MONTH(st.transaction_date)
        ORDER BY SUM(st.sale_value) DESC
    ) as sales_rank

FROM staff s
JOIN vw_staff_details sd ON s.staff_id = sd.staff_id
LEFT JOIN sales_transactions st ON s.staff_id = st.primary_staff_id AND st.status = 'Completed'
LEFT JOIN incentive_calculations ic ON st.transaction_id = ic.transaction_id
LEFT JOIN vw_attendance_summary att ON s.staff_id = att.staff_id
    AND YEAR(st.transaction_date) = att.year
    AND MONTH(st.transaction_date) = att.month
WHERE s.employee_status = 'Active'
GROUP BY s.staff_id, YEAR(st.transaction_date), MONTH(st.transaction_date);

-- ============================================================================
-- 11. LEAVE APPLICATIONS VIEW
-- ============================================================================

CREATE OR REPLACE VIEW vw_leave_applications AS
SELECT
    la.application_id,
    la.staff_id,
    sd.full_name,
    sd.employee_code,
    sd.department_name,
    lt.leave_type_name,
    lt.is_paid,
    la.from_date,
    la.to_date,
    la.total_days,
    la.reason,
    la.status,
    la.applied_date,
    u.username as approved_by_name,
    la.approved_date,
    la.rejection_reason
FROM leave_applications la
JOIN vw_staff_details sd ON la.staff_id = sd.staff_id
JOIN leave_types lt ON la.leave_type_id = lt.leave_type_id
LEFT JOIN users u ON la.approved_by = u.user_id;

-- ============================================================================
-- 12. BIOMETRIC AUTH LOGS VIEW
-- ============================================================================

CREATE OR REPLACE VIEW vw_biometric_auth_logs AS
SELECT
    bal.log_id,
    bal.staff_id,
    sd.full_name,
    sd.employee_code,
    bd.device_name,
    bd.device_type,
    bal.biometric_type,
    bal.auth_type,
    bal.match_score,
    bal.status,
    bal.failure_reason,
    bal.liveness_check,
    bal.auth_timestamp,
    bal.ip_address
FROM biometric_auth_logs bal
LEFT JOIN vw_staff_details sd ON bal.staff_id = sd.staff_id
LEFT JOIN biometric_devices bd ON bal.device_id = bd.device_id;

-- ============================================================================
-- 13. PENDING APPROVALS VIEW
-- ============================================================================

CREATE OR REPLACE VIEW vw_pending_approvals AS
SELECT
    'Leave Application' as approval_type,
    la.application_id as record_id,
    sd.full_name as staff_name,
    sd.employee_code,
    lt.leave_type_name as details,
    CONCAT(la.from_date, ' to ', la.to_date) as period,
    la.applied_date as request_date
FROM leave_applications la
JOIN vw_staff_details sd ON la.staff_id = sd.staff_id
JOIN leave_types lt ON la.leave_type_id = lt.leave_type_id
WHERE la.status = 'Pending'

UNION ALL

SELECT
    'Incentive Approval' as approval_type,
    ic.calculation_id as record_id,
    sd.full_name as staff_name,
    sd.employee_code,
    isch.scheme_name as details,
    DATE_FORMAT(ic.calculation_date, '%Y-%m-%d') as period,
    ic.created_at as request_date
FROM incentive_calculations ic
JOIN vw_staff_details sd ON ic.staff_id = sd.staff_id
JOIN incentive_schemes isch ON ic.scheme_id = isch.scheme_id
WHERE ic.status = 'Pending'

UNION ALL

SELECT
    'Payroll Approval' as approval_type,
    p.payroll_id as record_id,
    sd.full_name as staff_name,
    sd.employee_code,
    pp.period_name as details,
    CONCAT(pp.month, '/', pp.year) as period,
    p.created_at as request_date
FROM payroll p
JOIN vw_staff_details sd ON p.staff_id = sd.staff_id
JOIN payroll_periods pp ON p.period_id = pp.period_id
WHERE p.payment_status = 'Pending'
ORDER BY request_date DESC;
