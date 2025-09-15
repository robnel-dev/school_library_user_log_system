-- Create database
CREATE DATABASE IF NOT EXISTS library_stats CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE library_stats;

-- Students table
CREATE TABLE students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_no VARCHAR(20) UNIQUE NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    year_course VARCHAR(50) NOT NULL,
    contact_no VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Staff table
CREATE TABLE staff (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    role ENUM('admin', 'librarian') DEFAULT 'librarian',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Visits table
CREATE TABLE visits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    date DATE NOT NULL,
    time_in DATETIME NOT NULL,
    time_out DATETIME NULL,
    purpose VARCHAR(50) NOT NULL,
    notes TEXT,
    created_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES staff(id) ON DELETE SET NULL
);

-- Audit log table
CREATE TABLE audit_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    action VARCHAR(50) NOT NULL,
    table_name VARCHAR(50) NOT NULL,
    record_id INT NOT NULL,
    performed_by INT NULL,
    details TEXT,
    performed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (performed_by) REFERENCES staff(id) ON DELETE SET NULL
);

-- Create indexes for better performance
CREATE INDEX idx_students_student_no ON students(student_no);
CREATE INDEX idx_visits_date ON visits(date);
CREATE INDEX idx_visits_time_in ON visits(time_in);
CREATE INDEX idx_visits_time_out ON visits(time_out);
CREATE INDEX idx_visits_student_id ON visits(student_id);
CREATE INDEX idx_audit_log_performed_at ON audit_log(performed_at);