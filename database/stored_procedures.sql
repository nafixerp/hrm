-- ============================================================================
-- HRMS - Stored Procedures
-- Business Logic Implementation
-- ============================================================================

USE hrms_db;

DELIMITER $$

-- ============================================================================
-- 1. RECORD SALES AND CALCULATE INCENTIVES
-- ============================================================================

DROP PROCEDURE IF EXISTS sp_record_sales_transaction$$
CREATE PROCEDURE sp_record_sales_transaction(
    IN p_transaction_code VARCHAR(50),
    IN p_transaction_date DATE,
    IN p_category_id INT,
    IN p_product_description TEXT,
    IN p_quantity DECIMAL(10,3),
    IN p_weight_grams DECIMAL(10,3),
    IN p_sale_value DECIMAL(12,2),
    IN p_primary_staff_id INT,
    IN p_secondary_staff_id INT,
    IN p_branch_id INT,
    IN p_department_id INT,
    IN p_payment_mode VARCHAR(20),
    IN p_customer_name VARCHAR(100),
    IN p_customer_phone VARCHAR(20),
    IN p_created_by INT,
    OUT p_transaction_id INT,
    OUT p_message VARCHAR(255)
)
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SET p_message = 'Error: Transaction failed';
        SET p_transaction_id = 0;
    END;

    START TRANSACTION;

    -- Insert sales transaction
    INSERT INTO sales_transactions (
        transaction_code, transaction_date, category_id, product_description,
        quantity, weight_grams, sale_value, primary_staff_id, secondary_staff_id,
        branch_id, department_id, payment_mode, customer_name, customer_phone,
        status, created_by
    ) VALUES (
        p_transaction_code, p_transaction_date, p_category_id, p_product_description,
        p_quantity, p_weight_grams, p_sale_value, p_primary_staff_id, p_secondary_staff_id,
        p_branch_id, p_department_id, p_payment_mode, p_customer_name, p_customer_phone,
        'Completed', p_created_by
    );

    SET p_transaction_id = LAST_INSERT_ID();

    -- Calculate incentives for this transaction
    CALL sp_calculate_transaction_incentives(p_transaction_id, @incentive_message);

    COMMIT;
    SET p_message = CONCAT('Sales transaction recorded successfully. ID: ', p_transaction_id);
END$$

-- ============================================================================
-- 2. CALCULATE INCENTIVES FOR A TRANSACTION
-- ============================================================================

DROP PROCEDURE IF EXISTS sp_calculate_transaction_incentives$$
CREATE PROCEDURE sp_calculate_transaction_incentives(
    IN p_transaction_id INT,
    OUT p_message VARCHAR(255)
)
BEGIN
    DECLARE v_category_id INT;
    DECLARE v_sale_value DECIMAL(12,2);
    DECLARE v_weight_grams DECIMAL(10,3);
    DECLARE v_quantity DECIMAL(10,3);
    DECLARE v_primary_staff_id INT;
    DECLARE v_transaction_date DATE;
    DECLARE v_department_id INT;

    DECLARE v_scheme_id INT;
    DECLARE v_calculation_type VARCHAR(20);
    DECLARE v_individual_rate DECIMAL(10,4);
    DECLARE v_individual_percentage DECIMAL(5,2);
    DECLARE v_manager_rate DECIMAL(10,4);
    DECLARE v_manager_percentage DECIMAL(5,2);
    DECLARE v_common_rate DECIMAL(10,4);
    DECLARE v_common_percentage DECIMAL(5,2);
    DECLARE v_sales_dept_share DECIMAL(5,2);
    DECLARE v_other_dept_share DECIMAL(5,2);

    DECLARE v_individual_incentive DECIMAL(10,2);
    DECLARE v_manager_incentive DECIMAL(10,2);
    DECLARE v_common_incentive DECIMAL(10,2);
    DECLARE v_is_sales_dept BOOLEAN;
    DECLARE v_is_manager BOOLEAN;

    -- Get transaction details
    SELECT category_id, sale_value, weight_grams, quantity,
           primary_staff_id, transaction_date, department_id
    INTO v_category_id, v_sale_value, v_weight_grams, v_quantity,
         v_primary_staff_id, v_transaction_date, v_department_id
    FROM sales_transactions
    WHERE transaction_id = p_transaction_id;

    -- Get incentive scheme for this category
    SELECT scheme_id, calculation_type,
           individual_rate, individual_percentage,
           manager_rate, manager_percentage,
           common_rate, common_percentage,
           sales_dept_share, other_dept_share
    INTO v_scheme_id, v_calculation_type,
         v_individual_rate, v_individual_percentage,
         v_manager_rate, v_manager_percentage,
         v_common_rate, v_common_percentage,
         v_sales_dept_share, v_other_dept_share
    FROM incentive_schemes
    WHERE category_id = v_category_id
    AND is_active = TRUE
    AND (effective_from IS NULL OR effective_from <= v_transaction_date)
    AND (effective_to IS NULL OR effective_to >= v_transaction_date)
    LIMIT 1;

    -- Check if staff is from sales department
    SELECT d.is_sales_department INTO v_is_sales_dept
    FROM staff s
    JOIN departments d ON s.department_id = d.department_id
    WHERE s.staff_id = v_primary_staff_id;

    -- Check if staff is a manager
    SELECT dg.is_manager INTO v_is_manager
    FROM staff s
    JOIN designations dg ON s.designation_id = dg.designation_id
    WHERE s.staff_id = v_primary_staff_id;

    -- Calculate incentives based on scheme type
    IF v_calculation_type = 'Percentage' THEN
        -- Calculate percentage based incentives
        SET v_individual_incentive = v_sale_value * v_individual_percentage / 100;
        SET v_manager_incentive = v_sale_value * v_manager_percentage / 100;
        SET v_common_incentive = v_sale_value * v_common_percentage / 100;

    ELSEIF v_calculation_type = 'PerGram' THEN
        -- Calculate per gram incentives
        SET v_individual_incentive = v_weight_grams * v_individual_rate;
        SET v_manager_incentive = v_weight_grams * v_manager_rate;
        SET v_common_incentive = v_weight_grams * v_common_rate;

    ELSEIF v_calculation_type = 'PerPiece' THEN
        -- Calculate per piece incentives
        SET v_individual_incentive = v_quantity * v_individual_rate;
        SET v_manager_incentive = v_quantity * v_manager_rate;
        SET v_common_incentive = v_quantity * v_common_rate;

    ELSEIF v_calculation_type = 'TargetBased' THEN
        -- Calculate target based incentives (Gold Scheme)
        SET v_individual_incentive = v_weight_grams * v_individual_rate;

        -- Apply department share
        IF v_is_sales_dept THEN
            SET v_individual_incentive = v_individual_incentive * v_sales_dept_share / 100;
        ELSE
            SET v_individual_incentive = v_individual_incentive * v_other_dept_share / 100;
        END IF;
    END IF;

    -- Insert individual incentive
    IF v_individual_incentive > 0 THEN
        INSERT INTO incentive_calculations (
            transaction_id, staff_id, scheme_id, calculation_date,
            base_amount, incentive_type, incentive_amount,
            share_percentage, final_amount, status
        ) VALUES (
            p_transaction_id, v_primary_staff_id, v_scheme_id, v_transaction_date,
            v_sale_value, 'Individual', v_individual_incentive,
            100.00, v_individual_incentive, 'Pending'
        );
    END IF;

    -- Insert manager incentive if applicable
    IF v_manager_incentive > 0 AND v_is_manager THEN
        INSERT INTO incentive_calculations (
            transaction_id, staff_id, scheme_id, calculation_date,
            base_amount, incentive_type, incentive_amount,
            share_percentage, final_amount, status
        ) VALUES (
            p_transaction_id, v_primary_staff_id, v_scheme_id, v_transaction_date,
            v_sale_value, 'Manager', v_manager_incentive,
            100.00, v_manager_incentive, 'Pending'
        );
    END IF;

    -- Insert common pool incentive
    IF v_common_incentive > 0 THEN
        INSERT INTO incentive_calculations (
            transaction_id, staff_id, scheme_id, calculation_date,
            base_amount, incentive_type, incentive_amount,
            share_percentage, final_amount, status
        ) VALUES (
            p_transaction_id, v_primary_staff_id, v_scheme_id, v_transaction_date,
            v_sale_value, 'Common', v_common_incentive,
            100.00, v_common_incentive, 'Pending'
        );
    END IF;

    SET p_message = 'Incentives calculated successfully';
END$$

-- ============================================================================
-- 3. MARK ATTENDANCE
-- ============================================================================

DROP PROCEDURE IF EXISTS sp_mark_attendance$$
CREATE PROCEDURE sp_mark_attendance(
    IN p_staff_id INT,
    IN p_attendance_date DATE,
    IN p_check_in_time DATETIME,
    IN p_check_out_time DATETIME,
    IN p_schedule_id INT,
    IN p_biometric_verified BOOLEAN,
    IN p_created_by INT,
    OUT p_attendance_id INT,
    OUT p_message VARCHAR(255)
)
BEGIN
    DECLARE v_shift_start TIME;
    DECLARE v_grace_period INT;
    DECLARE v_working_hours DECIMAL(4,2);
    DECLARE v_actual_hours DECIMAL(4,2);
    DECLARE v_is_late BOOLEAN DEFAULT FALSE;
    DECLARE v_late_minutes INT DEFAULT 0;
    DECLARE v_overtime DECIMAL(4,2) DEFAULT 0;
    DECLARE v_status VARCHAR(20) DEFAULT 'Present';

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SET p_message = 'Error: Attendance marking failed';
        SET p_attendance_id = 0;
    END;

    START TRANSACTION;

    -- Get schedule details
    SELECT shift_start_time, grace_period_minutes, working_hours
    INTO v_shift_start, v_grace_period, v_working_hours
    FROM duty_schedules
    WHERE schedule_id = p_schedule_id;

    -- Calculate if late
    IF TIME(p_check_in_time) > ADDTIME(v_shift_start, SEC_TO_TIME(v_grace_period * 60)) THEN
        SET v_is_late = TRUE;
        SET v_late_minutes = TIMESTAMPDIFF(MINUTE,
            TIMESTAMP(p_attendance_date, v_shift_start),
            p_check_in_time);
    END IF;

    -- Calculate working hours if check_out is provided
    IF p_check_out_time IS NOT NULL THEN
        SET v_actual_hours = TIMESTAMPDIFF(MINUTE, p_check_in_time, p_check_out_time) / 60;

        -- Calculate overtime
        IF v_actual_hours > v_working_hours THEN
            SET v_overtime = v_actual_hours - v_working_hours;
        END IF;

        -- Determine status
        IF v_actual_hours < (v_working_hours / 2) THEN
            SET v_status = 'Half Day';
        END IF;
    END IF;

    -- Insert or update attendance
    INSERT INTO attendance (
        staff_id, attendance_date, check_in_time, check_out_time,
        schedule_id, status, is_late, late_by_minutes,
        total_working_hours, overtime_hours, biometric_verified, created_by
    ) VALUES (
        p_staff_id, p_attendance_date, p_check_in_time, p_check_out_time,
        p_schedule_id, v_status, v_is_late, v_late_minutes,
        v_actual_hours, v_overtime, p_biometric_verified, p_created_by
    )
    ON DUPLICATE KEY UPDATE
        check_out_time = p_check_out_time,
        total_working_hours = v_actual_hours,
        overtime_hours = v_overtime,
        status = v_status;

    SET p_attendance_id = LAST_INSERT_ID();

    COMMIT;
    SET p_message = CONCAT('Attendance marked successfully. Status: ', v_status);
END$$

-- ============================================================================
-- 4. APPLY FOR LEAVE
-- ============================================================================

DROP PROCEDURE IF EXISTS sp_apply_leave$$
CREATE PROCEDURE sp_apply_leave(
    IN p_staff_id INT,
    IN p_leave_type_id INT,
    IN p_from_date DATE,
    IN p_to_date DATE,
    IN p_reason TEXT,
    OUT p_application_id INT,
    OUT p_message VARCHAR(255)
)
BEGIN
    DECLARE v_total_days DECIMAL(5,2);
    DECLARE v_available_balance DECIMAL(5,2);
    DECLARE v_current_year INT;

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SET p_message = 'Error: Leave application failed';
        SET p_application_id = 0;
    END;

    START TRANSACTION;

    -- Calculate total days
    SET v_total_days = DATEDIFF(p_to_date, p_from_date) + 1;
    SET v_current_year = YEAR(p_from_date);

    -- Check available balance
    SELECT balance INTO v_available_balance
    FROM leave_balance
    WHERE staff_id = p_staff_id
    AND leave_type_id = p_leave_type_id
    AND year = v_current_year;

    -- Validate balance
    IF v_available_balance < v_total_days THEN
        SET p_message = CONCAT('Insufficient leave balance. Available: ', v_available_balance, ' days');
        SET p_application_id = 0;
        ROLLBACK;
    ELSE
        -- Insert leave application
        INSERT INTO leave_applications (
            staff_id, leave_type_id, from_date, to_date,
            total_days, reason, status
        ) VALUES (
            p_staff_id, p_leave_type_id, p_from_date, p_to_date,
            v_total_days, p_reason, 'Pending'
        );

        SET p_application_id = LAST_INSERT_ID();
        COMMIT;
        SET p_message = 'Leave application submitted successfully';
    END IF;
END$$

-- ============================================================================
-- 5. APPROVE/REJECT LEAVE
-- ============================================================================

DROP PROCEDURE IF EXISTS sp_process_leave_application$$
CREATE PROCEDURE sp_process_leave_application(
    IN p_application_id INT,
    IN p_approved_by INT,
    IN p_action VARCHAR(20), -- 'Approve' or 'Reject'
    IN p_rejection_reason TEXT,
    OUT p_message VARCHAR(255)
)
BEGIN
    DECLARE v_staff_id INT;
    DECLARE v_leave_type_id INT;
    DECLARE v_total_days DECIMAL(5,2);
    DECLARE v_from_date DATE;
    DECLARE v_current_year INT;

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SET p_message = 'Error: Leave processing failed';
    END;

    START TRANSACTION;

    -- Get application details
    SELECT staff_id, leave_type_id, total_days, from_date
    INTO v_staff_id, v_leave_type_id, v_total_days, v_from_date
    FROM leave_applications
    WHERE application_id = p_application_id;

    SET v_current_year = YEAR(v_from_date);

    IF p_action = 'Approve' THEN
        -- Update application status
        UPDATE leave_applications
        SET status = 'Approved',
            approved_by = p_approved_by,
            approved_date = NOW()
        WHERE application_id = p_application_id;

        -- Deduct from leave balance
        UPDATE leave_balance
        SET used = used + v_total_days
        WHERE staff_id = v_staff_id
        AND leave_type_id = v_leave_type_id
        AND year = v_current_year;

        -- Mark attendance as Leave for the period
        INSERT INTO attendance (staff_id, attendance_date, status, created_by)
        WITH RECURSIVE date_range AS (
            SELECT v_from_date as date
            UNION ALL
            SELECT DATE_ADD(date, INTERVAL 1 DAY)
            FROM date_range
            WHERE date < (SELECT to_date FROM leave_applications WHERE application_id = p_application_id)
        )
        SELECT v_staff_id, date, 'Leave', p_approved_by
        FROM date_range
        ON DUPLICATE KEY UPDATE status = 'Leave';

        SET p_message = 'Leave approved successfully';

    ELSEIF p_action = 'Reject' THEN
        -- Update application status
        UPDATE leave_applications
        SET status = 'Rejected',
            approved_by = p_approved_by,
            approved_date = NOW(),
            rejection_reason = p_rejection_reason
        WHERE application_id = p_application_id;

        SET p_message = 'Leave rejected';
    END IF;

    COMMIT;
END$$

-- ============================================================================
-- 6. GENERATE PAYROLL FOR PERIOD
-- ============================================================================

DROP PROCEDURE IF EXISTS sp_generate_payroll$$
CREATE PROCEDURE sp_generate_payroll(
    IN p_period_id INT,
    IN p_processed_by INT,
    OUT p_message VARCHAR(255)
)
BEGIN
    DECLARE done INT DEFAULT FALSE;
    DECLARE v_staff_id INT;
    DECLARE v_package_id INT;

    -- Cursor for all active staff
    DECLARE staff_cursor CURSOR FOR
        SELECT s.staff_id, ssm.package_id
        FROM staff s
        JOIN staff_salary_mapping ssm ON s.staff_id = ssm.staff_id
        WHERE s.employee_status = 'Active' AND ssm.is_current = TRUE;

    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SET p_message = 'Error: Payroll generation failed';
    END;

    START TRANSACTION;

    OPEN staff_cursor;

    read_loop: LOOP
        FETCH staff_cursor INTO v_staff_id, v_package_id;
        IF done THEN
            LEAVE read_loop;
        END IF;

        -- Generate payroll for each staff
        CALL sp_generate_staff_payroll(p_period_id, v_staff_id, v_package_id, @staff_message);
    END LOOP;

    CLOSE staff_cursor;

    -- Update period status
    UPDATE payroll_periods
    SET status = 'Completed',
        processed_by = p_processed_by,
        processed_date = NOW()
    WHERE period_id = p_period_id;

    COMMIT;
    SET p_message = 'Payroll generated successfully for all staff';
END$$

-- ============================================================================
-- 7. GENERATE INDIVIDUAL STAFF PAYROLL
-- ============================================================================

DROP PROCEDURE IF EXISTS sp_generate_staff_payroll$$
CREATE PROCEDURE sp_generate_staff_payroll(
    IN p_period_id INT,
    IN p_staff_id INT,
    IN p_package_id INT,
    OUT p_message VARCHAR(255)
)
BEGIN
    DECLARE v_from_date DATE;
    DECLARE v_to_date DATE;
    DECLARE v_working_days INT;
    DECLARE v_days_present DECIMAL(5,2);
    DECLARE v_days_absent DECIMAL(5,2);
    DECLARE v_paid_leaves DECIMAL(5,2);
    DECLARE v_unpaid_leaves DECIMAL(5,2);
    DECLARE v_overtime_hours DECIMAL(6,2);
    DECLARE v_total_incentives DECIMAL(10,2);

    DECLARE v_basic_salary DECIMAL(10,2);
    DECLARE v_hra DECIMAL(10,2);
    DECLARE v_transport DECIMAL(10,2);
    DECLARE v_medical DECIMAL(10,2);
    DECLARE v_special DECIMAL(10,2);
    DECLARE v_pf DECIMAL(10,2);
    DECLARE v_esi DECIMAL(10,2);
    DECLARE v_pt DECIMAL(10,2);

    -- Get period dates
    SELECT from_date, to_date INTO v_from_date, v_to_date
    FROM payroll_periods WHERE period_id = p_period_id;

    -- Calculate working days in month
    SET v_working_days = DAY(LAST_DAY(v_from_date));

    -- Get attendance summary
    SELECT
        COALESCE(SUM(CASE WHEN status = 'Present' THEN 1 ELSE 0 END), 0),
        COALESCE(SUM(CASE WHEN status = 'Absent' THEN 1 ELSE 0 END), 0),
        COALESCE(SUM(CASE WHEN status = 'Leave' THEN 1 ELSE 0 END), 0),
        COALESCE(SUM(overtime_hours), 0)
    INTO v_days_present, v_days_absent, v_paid_leaves, v_overtime_hours
    FROM attendance
    WHERE staff_id = p_staff_id
    AND attendance_date BETWEEN v_from_date AND v_to_date;

    -- Get total incentives for the period
    SELECT COALESCE(SUM(final_amount), 0)
    INTO v_total_incentives
    FROM incentive_calculations
    WHERE staff_id = p_staff_id
    AND calculation_date BETWEEN v_from_date AND v_to_date
    AND status IN ('Approved', 'Pending');

    -- Get salary package details
    SELECT basic_salary, hra, transport_allowance, medical_allowance,
           special_allowance, pf_deduction, esi_deduction, professional_tax
    INTO v_basic_salary, v_hra, v_transport, v_medical,
         v_special, v_pf, v_esi, v_pt
    FROM salary_packages
    WHERE package_id = p_package_id;

    -- Calculate pro-rated salary based on attendance
    SET v_basic_salary = v_basic_salary * (v_days_present + v_paid_leaves) / v_working_days;
    SET v_hra = v_hra * (v_days_present + v_paid_leaves) / v_working_days;
    SET v_transport = v_transport * (v_days_present + v_paid_leaves) / v_working_days;
    SET v_medical = v_medical * (v_days_present + v_paid_leaves) / v_working_days;
    SET v_special = v_special * (v_days_present + v_paid_leaves) / v_working_days;

    -- Insert payroll record
    INSERT INTO payroll (
        period_id, staff_id, basic_salary, hra, transport_allowance,
        medical_allowance, special_allowance, total_working_days,
        days_present, days_absent, paid_leaves, unpaid_leaves,
        overtime_hours, total_incentives, pf_deduction, esi_deduction,
        professional_tax, payment_status, created_by
    ) VALUES (
        p_period_id, p_staff_id, v_basic_salary, v_hra, v_transport,
        v_medical, v_special, v_working_days,
        v_days_present, v_days_absent, v_paid_leaves, v_unpaid_leaves,
        v_overtime_hours, v_total_incentives, v_pf, v_esi,
        v_pt, 'Pending', 1
    )
    ON DUPLICATE KEY UPDATE
        basic_salary = v_basic_salary,
        days_present = v_days_present,
        total_incentives = v_total_incentives;

    SET p_message = 'Payroll generated for staff';
END$$

-- ============================================================================
-- 8. GET STAFF PERFORMANCE REPORT
-- ============================================================================

DROP PROCEDURE IF EXISTS sp_get_staff_performance$$
CREATE PROCEDURE sp_get_staff_performance(
    IN p_staff_id INT,
    IN p_from_date DATE,
    IN p_to_date DATE
)
BEGIN
    SELECT
        s.employee_code,
        CONCAT(s.first_name, ' ', s.last_name) as staff_name,
        d.designation_name,
        dept.department_name,

        -- Sales Performance
        COUNT(DISTINCT st.transaction_id) as total_sales,
        COALESCE(SUM(st.sale_value), 0) as total_sales_value,

        -- Incentives
        COALESCE(SUM(ic.final_amount), 0) as total_incentives_earned,

        -- Attendance
        COUNT(DISTINCT a.attendance_id) as attendance_days,
        COALESCE(SUM(a.overtime_hours), 0) as total_overtime_hours,
        SUM(CASE WHEN a.is_late = TRUE THEN 1 ELSE 0 END) as late_days,

        -- Leave
        COALESCE(SUM(la.total_days), 0) as leaves_taken

    FROM staff s
    LEFT JOIN designations d ON s.designation_id = d.designation_id
    LEFT JOIN departments dept ON s.department_id = dept.department_id
    LEFT JOIN sales_transactions st ON s.staff_id = st.primary_staff_id
        AND st.transaction_date BETWEEN p_from_date AND p_to_date
    LEFT JOIN incentive_calculations ic ON s.staff_id = ic.staff_id
        AND ic.calculation_date BETWEEN p_from_date AND p_to_date
    LEFT JOIN attendance a ON s.staff_id = a.staff_id
        AND a.attendance_date BETWEEN p_from_date AND p_to_date
    LEFT JOIN leave_applications la ON s.staff_id = la.staff_id
        AND la.status = 'Approved'
        AND la.from_date BETWEEN p_from_date AND p_to_date
    WHERE s.staff_id = p_staff_id
    GROUP BY s.staff_id;
END$$

-- ============================================================================
-- 9. GET DEPARTMENT WISE SALES REPORT
-- ============================================================================

DROP PROCEDURE IF EXISTS sp_get_department_sales_report$$
CREATE PROCEDURE sp_get_department_sales_report(
    IN p_from_date DATE,
    IN p_to_date DATE
)
BEGIN
    SELECT
        d.department_name,
        pc.category_name,
        COUNT(st.transaction_id) as total_transactions,
        SUM(st.quantity) as total_quantity,
        SUM(st.weight_grams) as total_weight,
        SUM(st.sale_value) as total_sales_value,
        SUM(ic.final_amount) as total_incentives
    FROM departments d
    LEFT JOIN staff s ON d.department_id = s.department_id
    LEFT JOIN sales_transactions st ON s.staff_id = st.primary_staff_id
        AND st.transaction_date BETWEEN p_from_date AND p_to_date
    LEFT JOIN product_categories pc ON st.category_id = pc.category_id
    LEFT JOIN incentive_calculations ic ON st.transaction_id = ic.transaction_id
    WHERE st.status = 'Completed'
    GROUP BY d.department_id, pc.category_id
    ORDER BY d.department_name, total_sales_value DESC;
END$$

-- ============================================================================
-- 10. GET MONTHLY DASHBOARD STATISTICS
-- ============================================================================

DROP PROCEDURE IF EXISTS sp_get_dashboard_stats$$
CREATE PROCEDURE sp_get_dashboard_stats(
    IN p_month INT,
    IN p_year INT
)
BEGIN
    DECLARE v_from_date DATE;
    DECLARE v_to_date DATE;

    SET v_from_date = DATE(CONCAT(p_year, '-', p_month, '-01'));
    SET v_to_date = LAST_DAY(v_from_date);

    -- Overall Statistics
    SELECT
        'Overview' as section,
        COUNT(DISTINCT s.staff_id) as total_active_staff,
        COUNT(DISTINCT st.transaction_id) as total_sales,
        COALESCE(SUM(st.sale_value), 0) as total_sales_value,
        COALESCE(SUM(ic.final_amount), 0) as total_incentives,
        COALESCE(SUM(p.net_salary), 0) as total_payroll
    FROM staff s
    LEFT JOIN sales_transactions st ON s.staff_id = st.primary_staff_id
        AND st.transaction_date BETWEEN v_from_date AND v_to_date
    LEFT JOIN incentive_calculations ic ON st.transaction_id = ic.transaction_id
    LEFT JOIN payroll p ON s.staff_id = p.staff_id
        AND p.period_id IN (
            SELECT period_id FROM payroll_periods
            WHERE month = p_month AND year = p_year
        )
    WHERE s.employee_status = 'Active';
END$$

DELIMITER ;
