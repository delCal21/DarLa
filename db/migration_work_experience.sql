-- Migration: Create employee_work_experience table
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

