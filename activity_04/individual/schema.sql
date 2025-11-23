-- Enhanced Database Schema for Activity 04
-- Course Management System with Request Approval

USE webtech_2025A_eyram_awoye;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('student', 'faculty', 'admin') NOT NULL DEFAULT 'student',
    ashesi_id VARCHAR(20) UNIQUE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    INDEX idx_email (email),
    INDEX idx_ashesi_id (ashesi_id),
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Courses table
CREATE TABLE IF NOT EXISTS courses (
    course_id INT PRIMARY KEY AUTO_INCREMENT,
    course_code VARCHAR(20) UNIQUE NOT NULL,
    course_name VARCHAR(100) NOT NULL,
    faculty_id INT,
    semester VARCHAR(20),
    year INT,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (faculty_id) REFERENCES users(user_id) ON DELETE SET NULL,
    INDEX idx_course_code (course_code),
    INDEX idx_faculty (faculty_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Course Requests table (NEW - for student join requests)
CREATE TABLE IF NOT EXISTS course_requests (
    request_id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT NOT NULL,
    course_id INT NOT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    requested_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    reviewed_at TIMESTAMP NULL,
    reviewed_by INT NULL,
    comments TEXT,
    FOREIGN KEY (student_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(course_id) ON DELETE CASCADE,
    FOREIGN KEY (reviewed_by) REFERENCES users(user_id) ON DELETE SET NULL,
    INDEX idx_status (status),
    INDEX idx_student (student_id),
    INDEX idx_course (course_id),
    UNIQUE KEY unique_active_request (student_id, course_id, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Enrollments table
CREATE TABLE IF NOT EXISTS enrollments (
    enrollment_id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT NOT NULL,
    course_id INT NOT NULL,
    enrollment_date DATE DEFAULT (CURRENT_DATE),
    status ENUM('active', 'dropped', 'completed') DEFAULT 'active',
    FOREIGN KEY (student_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(course_id) ON DELETE CASCADE,
    UNIQUE KEY unique_enrollment (student_id, course_id),
    INDEX idx_student_courses (student_id),
    INDEX idx_course_students (course_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample users (password: Password123!)
INSERT INTO users (name, email, password, role, ashesi_id) VALUES
('John Doe', 'john.doe@ashesi.edu.gh', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', '2026001'),
('Alice Johnson', 'alice.johnson@ashesi.edu.gh', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', '2026002'),
('Bob Wilson', 'bob.wilson@ashesi.edu.gh', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', '2026003'),
('Dr. Jane Smith', 'jane.smith@ashesi.edu.gh', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'faculty', 'FAC001'),
('Prof. Michael Brown', 'michael.brown@ashesi.edu.gh', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'faculty', 'FAC002')
ON DUPLICATE KEY UPDATE name=name;

-- Insert sample courses
INSERT INTO courses (course_code, course_name, faculty_id, semester, year, description) VALUES
('CS101', 'Introduction to Computer Science', 4, 'Fall', 2025, 'Fundamentals of programming and problem-solving'),
('CS201', 'Data Structures and Algorithms', 4, 'Spring', 2025, 'Study of common data structures and algorithms'),
('MATH101', 'Calculus I', 5, 'Fall', 2025, 'Introduction to differential and integral calculus')
ON DUPLICATE KEY UPDATE course_name=course_name;
