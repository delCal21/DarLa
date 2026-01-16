-- Run this SQL in your MySQL server to create the database structure

CREATE DATABASE IF NOT EXISTS employee_db
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE employee_db;

-- Main employee table (one row per employee)
CREATE TABLE IF NOT EXISTS employees (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    last_name VARCHAR(100) NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    middle_name VARCHAR(100) DEFAULT NULL,

    birthdate DATE DEFAULT NULL,
    home_address VARCHAR(255) DEFAULT NULL,
    office VARCHAR(255) DEFAULT NULL,
    contact_no VARCHAR(50) DEFAULT NULL,
    email VARCHAR(150) DEFAULT NULL,
    civil_status VARCHAR(50) DEFAULT NULL,
    spouse_name VARCHAR(150) DEFAULT NULL,
    spouse_contact_no VARCHAR(50) DEFAULT NULL,

    employee_number VARCHAR(50) DEFAULT NULL,
    bp_number VARCHAR(50) DEFAULT NULL,
    pagibig_number VARCHAR(50) DEFAULT NULL,
    philhealth_number VARCHAR(50) DEFAULT NULL,
    tin_number VARCHAR(50) DEFAULT NULL,
    sss_number VARCHAR(50) DEFAULT NULL,
    gsis_number VARCHAR(50) DEFAULT NULL,

    trainings TEXT NULL,
    designations TEXT NULL,
    leave_info TEXT NULL,
    service_record TEXT NULL,
    employment_status VARCHAR(100) DEFAULT NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_last_name (last_name)
);

-- Appointment & promotion history per employee
CREATE TABLE IF NOT EXISTS appointments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    employee_id INT UNSIGNED NOT NULL,
    sequence_no TINYINT UNSIGNED NOT NULL DEFAULT 1,
    type_label VARCHAR(100) NOT NULL, -- e.g. 'Original Appointment', '1st Promotion'
    position VARCHAR(150) NOT NULL,
    item_number VARCHAR(100) DEFAULT NULL,
    salary_grade VARCHAR(20) DEFAULT NULL,
    appointment_date DATE DEFAULT NULL,
    salary DECIMAL(15,2) DEFAULT NULL,

    CONSTRAINT fk_appointments_employee
        FOREIGN KEY (employee_id) REFERENCES employees(id)
        ON DELETE CASCADE,
    INDEX idx_employee (employee_id)
);

-- Structured trainings per employee
CREATE TABLE IF NOT EXISTS employee_trainings (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    employee_id INT UNSIGNED NOT NULL,
    title VARCHAR(255) NOT NULL,
    provider VARCHAR(255) DEFAULT NULL,
    location VARCHAR(255) DEFAULT NULL,
    date_from DATE DEFAULT NULL,
    date_to DATE DEFAULT NULL,
    hours DECIMAL(6,2) DEFAULT NULL,
    remarks VARCHAR(255) DEFAULT NULL,

    CONSTRAINT fk_trainings_employee
        FOREIGN KEY (employee_id) REFERENCES employees(id)
        ON DELETE CASCADE,
    INDEX idx_training_employee (employee_id),
    INDEX idx_training_dates (date_from, date_to)
);

-- Structured leave records per employee
CREATE TABLE IF NOT EXISTS employee_leaves (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    employee_id INT UNSIGNED NOT NULL,
    leave_type VARCHAR(100) NOT NULL,
    date_from DATE DEFAULT NULL,
    date_to DATE DEFAULT NULL,
    days DECIMAL(5,2) DEFAULT NULL,
    remarks VARCHAR(255) DEFAULT NULL,

    CONSTRAINT fk_leaves_employee
        FOREIGN KEY (employee_id) REFERENCES employees(id)
        ON DELETE CASCADE,
    INDEX idx_leave_employee (employee_id),
    INDEX idx_leave_dates (date_from, date_to)
);

-- Structured service records per employee
CREATE TABLE IF NOT EXISTS employee_service_records (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    employee_id INT UNSIGNED NOT NULL,
    position VARCHAR(150) NOT NULL,
    office VARCHAR(255) DEFAULT NULL,
    place_of VARCHAR(255) DEFAULT NULL,
    branch VARCHAR(255) DEFAULT NULL,
    assignment VARCHAR(255) DEFAULT NULL,
    lv_abs VARCHAR(255) DEFAULT NULL,
    status VARCHAR(100) DEFAULT NULL,
    date_from DATE DEFAULT NULL,
    date_to DATE DEFAULT NULL,
    salary DECIMAL(15,2) DEFAULT NULL,
    wo_pay VARCHAR(255) DEFAULT NULL,
    separation_date DATE DEFAULT NULL,
    separation_cause VARCHAR(255) DEFAULT NULL,
    remarks VARCHAR(255) DEFAULT NULL,

    CONSTRAINT fk_service_employee
        FOREIGN KEY (employee_id) REFERENCES employees(id)
        ON DELETE CASCADE,
    INDEX idx_service_employee (employee_id),
    INDEX idx_service_dates (date_from, date_to)
);

-- Educational background per employee
CREATE TABLE IF NOT EXISTS employee_educational_background (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    employee_id INT UNSIGNED NOT NULL,
    level VARCHAR(50) NOT NULL,
    school_name VARCHAR(255) NOT NULL,
    degree_course VARCHAR(255) DEFAULT NULL,
    period_from VARCHAR(20) DEFAULT NULL,
    period_to VARCHAR(20) DEFAULT NULL,
    highest_level_units VARCHAR(255) DEFAULT NULL,
    year_graduated VARCHAR(20) DEFAULT NULL,
    scholarship_honors VARCHAR(255) DEFAULT NULL,

    CONSTRAINT fk_education_employee
        FOREIGN KEY (employee_id) REFERENCES employees(id)
        ON DELETE CASCADE,
    INDEX idx_education_employee (employee_id),
    INDEX idx_education_level (level)
);

-- Work experience per employee
CREATE TABLE IF NOT EXISTS employee_work_experience (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    employee_id INT UNSIGNED NOT NULL,
    date_from DATE NOT NULL,
    date_to DATE DEFAULT NULL,
    position_title VARCHAR(255) NOT NULL,
    department_agency VARCHAR(255) NOT NULL,
    monthly_salary DECIMAL(15,2) DEFAULT NULL,
    salary_grade_step VARCHAR(50) DEFAULT NULL, -- Format: "00-0" or similar
    status_of_appointment VARCHAR(100) DEFAULT NULL,
    govt_service ENUM('YES', 'NO') DEFAULT 'YES',
    description_of_duties TEXT DEFAULT NULL,

    CONSTRAINT fk_work_experience_employee
        FOREIGN KEY (employee_id) REFERENCES employees(id)
        ON DELETE CASCADE,
    INDEX idx_work_experience_employee (employee_id),
    INDEX idx_work_experience_dates (date_from, date_to)
);

-- Awards & recognitions per employee
CREATE TABLE IF NOT EXISTS employee_awards (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    employee_id INT UNSIGNED NOT NULL,
    title VARCHAR(255) NOT NULL,
    award_level VARCHAR(100) DEFAULT NULL,
    awarding_body VARCHAR(255) DEFAULT NULL,
    award_date DATE DEFAULT NULL,
    remarks VARCHAR(255) DEFAULT NULL,
    description TEXT DEFAULT NULL,

    CONSTRAINT fk_awards_employee
        FOREIGN KEY (employee_id) REFERENCES employees(id)
        ON DELETE CASCADE,
    INDEX idx_awards_employee (employee_id),
    INDEX idx_awards_date (award_date)
);

-- Example employee for testing
INSERT INTO employees (
    last_name, first_name, middle_name,
    birthdate, home_address, office, contact_no, email, civil_status,
    spouse_name, spouse_contact_no,
    employee_number, pagibig_number, philhealth_number, tin_number, sss_number, gsis_number,
    trainings, designations, leave_info, service_record, employment_status
) VALUES (
    'TATUNAY', 'NEMIA THEODORA', 'MADARANG',
    '1966-04-01',
    '#2 Quirino Street Poblacion, Amligay La Union',
    'LTS',
    '09773222445',
    'nemiamadarang@example.com',
    'Married',
    'Tatunay, Constantine Q.',
    '09178023113 / 09984540328 / 09478940436',
    '105890306',
    '2000132073',
    '1280-0000-1868',
    '100000091396',
    '132221615',
    '0108007170',
    '00600001868',
    'trainings...',
    'designations...',
    'leave info...',
    'service record...',
    'PERMANENT'
);

INSERT INTO appointments (
    employee_id, sequence_no, type_label,
    position, item_number, salary_grade, appointment_date, salary
) VALUES
  (1, 1, 'Original Appointment', 'JR. STAT.', 'JR.STAT.-627-75', 'NA', '1989-01-02', 13033.02),
  (1, 2, '1st Promotion', 'ARPT', 'ARPT-147', 'SG-10', '2003-05-05', 125304.00),
  (1, 3, '2nd Promotion', 'ARPO I', 'ARPO1-989-2014', 'SG-11', '2015-01-12', 222588.00),
  (1, 4, '3rd Promotion', 'AO IV', 'ADOF4-214-2014', 'SG-15', '2022-11-22', 35097.00);


