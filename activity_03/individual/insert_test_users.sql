-- ========================================
-- QUICK TEST USER INSERTION
-- ========================================
-- Use this file to quickly add test users to your database
-- 
-- HOW TO USE:
-- 1. Open phpMyAdmin
-- 2. Select database: webtech_2025A_eyram_awoye
-- 3. Click "SQL" tab
-- 4. Copy and paste this entire file
-- 5. Click "Go"
--
-- DEFAULT PASSWORD FOR ALL USERS: Password123!
-- ========================================

USE webtech_2025A_eyram_awoye;

-- Clear existing test users (optional - uncomment if needed)
-- DELETE FROM users WHERE ashesi_id IN ('2026999', '2026888', 'FAC999', 'FAC888');

-- Test Student 1
-- Email: test.student@ashesi.edu.gh
-- Password: Password123!
INSERT INTO users (name, email, password, role, ashesi_id, created_at) 
VALUES (
    'Test Student',
    'test.student@ashesi.edu.gh',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'student',
    '2026999',
    NOW()
);

-- Test Student 2  
-- Email: john.doe@ashesi.edu.gh
-- Password: Password123!
INSERT INTO users (name, email, password, role, ashesi_id, created_at) 
VALUES (
    'John Doe',
    'john.doe@ashesi.edu.gh',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'student',
    '2026888',
    NOW()
);

-- Test Faculty 1
-- Email: test.faculty@ashesi.edu.gh
-- Password: Password123!
INSERT INTO users (name, email, password, role, ashesi_id, created_at) 
VALUES (
    'Test Faculty',
    'test.faculty@ashesi.edu.gh',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'faculty',
    'FAC999',
    NOW()
);

-- Test Faculty 2
-- Email: dr.smith@ashesi.edu.gh  
-- Password: Password123!
INSERT INTO users (name, email, password, role, ashesi_id, created_at) 
VALUES (
    'Dr. Smith',
    'dr.smith@ashesi.edu.gh',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'faculty',
    'FAC888',
    NOW()
);

-- Verify insertion
SELECT user_id, name, email, role, ashesi_id, created_at 
FROM users 
ORDER BY user_id DESC 
LIMIT 4;

-- ========================================
-- TEST CREDENTIALS:
-- ========================================
--
-- STUDENT LOGIN:
-- Email: test.student@ashesi.edu.gh
-- Password: Password123!
--
-- FACULTY LOGIN:
-- Email: test.faculty@ashesi.edu.gh  
-- Password: Password123!
--
-- ========================================
