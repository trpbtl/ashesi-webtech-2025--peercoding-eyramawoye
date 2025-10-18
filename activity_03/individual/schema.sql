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

CREATE TABLE IF NOT EXISTS sessions (
session_id INT PRIMARY KEY AUTO_INCREMENT,
course_id INT NOT NULL,
session_date DATE NOT NULL,
session_time TIME,
session_type ENUM('lecture', 'lab', 'practical') DEFAULT 'lecture',
notes TEXT,
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
FOREIGN KEY (course_id) REFERENCES courses(course_id) ON DELETE CASCADE,
INDEX idx_session_date (session_date),
INDEX idx_course_session (course_id, session_date),
INDEX idx_session_type (session_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS attendance (
attendance_id INT PRIMARY KEY AUTO_INCREMENT,
student_id INT NOT NULL,
session_id INT NOT NULL,
status ENUM('present', 'absent', 'late') NOT NULL,
marked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
marked_by INT,
notes VARCHAR(255),
FOREIGN KEY (student_id) REFERENCES users(user_id) ON DELETE CASCADE,
FOREIGN KEY (session_id) REFERENCES sessions(session_id) ON DELETE CASCADE,
FOREIGN KEY (marked_by) REFERENCES users(user_id) ON DELETE SET NULL,
UNIQUE KEY unique_attendance (student_id, session_id),
INDEX idx_student_attendance (student_id, session_id),
INDEX idx_status (status)
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

CREATE TABLE IF NOT EXISTS issues (
issue_id INT PRIMARY KEY AUTO_INCREMENT,
student_id INT NOT NULL,
session_id INT NOT NULL,
description TEXT NOT NULL,
status ENUM('pending', 'resolved', 'rejected') DEFAULT 'pending',
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
resolved_at TIMESTAMP NULL,
resolved_by INT,
resolution_notes TEXT,
FOREIGN KEY (student_id) REFERENCES users(user_id) ON DELETE CASCADE,
FOREIGN KEY (session_id) REFERENCES sessions(session_id) ON DELETE CASCADE,
FOREIGN KEY (resolved_by) REFERENCES users(user_id) ON DELETE SET NULL,
INDEX idx_issue_status (status),
INDEX idx_student_issues (student_id),
INDEX idx_session_issues (session_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO users (name, email, password, role, ashesi_id) VALUES
('John Doe', '[john.doe@ashesi.edu.gh](mailto:john.doe@ashesi.edu.gh)', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', '2026001'),
('Alice Johnson', '[alice.johnson@ashesi.edu.gh](mailto:alice.johnson@ashesi.edu.gh)', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', '2026002'),
('Bob Wilson', '[bob.wilson@ashesi.edu.gh](mailto:bob.wilson@ashesi.edu.gh)', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', '2026003'),
('Dr. Jane Smith', '[jane.smith@ashesi.edu.gh](mailto:jane.smith@ashesi.edu.gh)', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'faculty', 'FAC001'),
('Prof. Michael Brown', '[michael.brown@ashesi.edu.gh](mailto:michael.brown@ashesi.edu.gh)', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'faculty', 'FAC002');

INSERT INTO courses (course_code, course_name, faculty_id, semester, year, description) VALUES
('CS101', 'Introduction to Computer Science', 4, 'Fall', 2024, 'Fundamentals of programming and problem-solving'),
('CS201', 'Data Structures and Algorithms', 4, 'Spring', 2025, 'Study of common data structures and algorithms'),
('MATH101', 'Calculus I', 5, 'Fall', 2024, 'Introduction to differential and integral calculus'),
('ENG101', 'English Composition', 5, 'Fall', 2024, 'Academic writing and critical thinking');

INSERT INTO enrollments (student_id, course_id, status) VALUES
(1, 1, 'active'),
(1, 2, 'active'),
(1, 3, 'active'),
(2, 1, 'active'),
(2, 2, 'active'),
(3, 1, 'active'),
(3, 4, 'active');

INSERT INTO sessions (course_id, session_date, session_time, session_type, notes) VALUES
(1, '2024-10-14', '10:00:00', 'lecture', 'Introduction to Python programming'),
(1, '2024-10-16', '14:00:00', 'lab', 'Bring laptop with Python 3.x installed. Lab exercises on variables and data types.'),
(1, '2024-10-18', '10:00:00', 'lecture', 'Control structures and loops'),
(1, '2024-10-21', '10:00:00', 'lecture', 'Functions and modules'),
(2, '2024-10-15', '09:00:00', 'lecture', 'Introduction to arrays and linked lists'),
(2, '2024-10-17', '13:00:00', 'lab', 'Bring laptop. Implementation of linked list data structure.'),
(2, '2024-10-19', '09:00:00', 'lecture', 'Stacks and queues'),
(3, '2024-10-14', '14:00:00', 'lecture', 'Limits and continuity'),
(3, '2024-10-16', '14:00:00', 'lecture', 'Derivatives - basic rules'),
(3, '2024-10-18', '14:00:00', 'practical', 'Bring calculator. Problem-solving session on derivatives.'),
(4, '2024-10-15', '11:00:00', 'lecture', 'Essay structure and thesis statements'),
(4, '2024-10-17', '11:00:00', 'lecture', 'Research methods and citation styles');

INSERT INTO attendance (student_id, session_id, status, marked_by) VALUES
(1, 1, 'present', 4),
(1, 2, 'late', 4),
(1, 3, 'present', 4),
(1, 5, 'present', 4),
(1, 6, 'absent', 4),
(1, 8, 'present', 5),
(1, 9, 'present', 5),
(2, 1, 'present', 4),
(2, 2, 'present', 4),
(2, 3, 'present', 4),
(2, 5, 'late', 4),
(2, 6, 'present', 4),
(3, 1, 'present', 4),
(3, 2, 'present', 4),
(3, 3, 'absent', 4),
(3, 11, 'present', 5),
(3, 12, 'present', 5);

INSERT INTO issues (student_id, session_id, description, status)
VALUES
(1, 6, 'I was present in this lab session but marked absent. I spoke with the TA who can confirm my attendance.', 'pending'),
(3, 3, 'I had a medical emergency and have a doctor''s note. Can this absence be excused?', 'pending');

CREATE OR REPLACE VIEW student_attendance_summary AS
SELECT
u.user_id,
u.name,
u.ashesi_id,
c.course_code,
c.course_name,
COUNT(a.attendance_id) AS total_sessions,
SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) AS present_count,
SUM(CASE WHEN a.status = 'late' THEN 1 ELSE 0 END) AS late_count,
SUM(CASE WHEN a.status = 'absent' THEN 1 ELSE 0 END) AS absent_count,
ROUND(
(SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) / COUNT(a.attendance_id)) * 100,
2
) AS attendance_percentage
FROM users u
JOIN enrollments e
ON u.user_id = e.student_id
JOIN courses c
ON e.course_id = c.course_id
LEFT JOIN attendance a
ON u.user_id = a.student_id
WHERE u.role = 'student'
AND e.status = 'active'
GROUP BY u.user_id, c.course_id;
