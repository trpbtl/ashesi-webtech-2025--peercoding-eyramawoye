USE webtech_2025A_eyram_awoye;

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

CREATE TABLE IF NOT EXISTS attendance_sessions (
    session_id INT PRIMARY KEY AUTO_INCREMENT,
    course_id INT NOT NULL,
    session_name VARCHAR(100) NOT NULL,
    session_date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    attendance_code VARCHAR(6) NOT NULL,
    code_expires_at DATETIME NOT NULL,
    created_by INT NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(course_id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_course_session (course_id, session_date),
    INDEX idx_attendance_code (attendance_code),
    INDEX idx_active_sessions (is_active, code_expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS attendance_records (
    record_id INT PRIMARY KEY AUTO_INCREMENT,
    session_id INT NOT NULL,
    student_id INT NOT NULL,
    marked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('present', 'late', 'absent') DEFAULT 'present',
    marked_by_code BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (session_id) REFERENCES attendance_sessions(session_id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES users(user_id) ON DELETE CASCADE,
    UNIQUE KEY unique_attendance (session_id, student_id),
    INDEX idx_student_attendance (student_id),
    INDEX idx_session_records (session_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO users (name, email, password, role, ashesi_id) VALUES
('John Doe', 'john.doe@ashesi.edu.gh', '$2y$10$hsUZAfURxiajmfNPH/X3ZOgqbA7uIOS61RdiSiWH.iV4yNFqEgdJa', 'student', '2026001'),
('Alice Johnson', 'alice.johnson@ashesi.edu.gh', '$2y$10$hsUZAfURxiajmfNPH/X3ZOgqbA7uIOS61RdiSiWH.iV4yNFqEgdJa', 'student', '2026002'),
('Bob Wilson', 'bob.wilson@ashesi.edu.gh', '$2y$10$hsUZAfURxiajmfNPH/X3ZOgqbA7uIOS61RdiSiWH.iV4yNFqEgdJa', 'student', '2026003'),
('Dr. Jane Smith', 'jane.smith@ashesi.edu.gh', '$2y$10$hsUZAfURxiajmfNPH/X3ZOgqbA7uIOS61RdiSiWH.iV4yNFqEgdJa', 'faculty', 'FAC001'),
('Prof. Michael Brown', 'michael.brown@ashesi.edu.gh', '$2y$10$hsUZAfURxiajmfNPH/X3ZOgqbA7uIOS61RdiSiWH.iV4yNFqEgdJa', 'faculty', 'FAC002')
ON DUPLICATE KEY UPDATE name=name;

INSERT INTO courses (course_code, course_name, faculty_id, semester, year, description) VALUES
('CS101', 'Introduction to Computer Science', 4, 'Fall', 2025, 'Fundamentals of programming and problem-solving'),
('CS201', 'Data Structures and Algorithms', 4, 'Spring', 2025, 'Study of common data structures and algorithms'),
('MATH101', 'Calculus I', 5, 'Fall', 2025, 'Introduction to differential and integral calculus')
ON DUPLICATE KEY UPDATE course_name=course_name;

INSERT INTO enrollments (student_id, course_id, status) VALUES
(1, 1, 'active'),
(1, 2, 'active'),
(2, 1, 'active'),
(2, 2, 'active'),
(3, 1, 'active')
ON DUPLICATE KEY UPDATE status=status;
