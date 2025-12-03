<?php
require_once 'config.php';
require_once 'helpers.php';

requireRole('faculty');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('create_course.php', 'Invalid request method', 'error');
}

if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    redirect('create_course.php', 'Invalid security token', 'error');
}

$courseCode = strtoupper(sanitizeInput($_POST['course_code'] ?? ''));
$courseName = sanitizeInput($_POST['course_name'] ?? '');
$semester = sanitizeInput($_POST['semester'] ?? '');
$year = intval($_POST['year'] ?? 0);
$description = sanitizeInput($_POST['description'] ?? '');
$facultyId = $_SESSION['user_id'];

if (empty($courseCode) || empty($courseName) || empty($semester) || empty($year)) {
    redirect('create_course.php', 'Please fill in all required fields', 'error');
}

if (!preg_match('/^[A-Z]{2,4}[0-9]{3}$/', $courseCode)) {
    redirect('create_course.php', 'Invalid course code format. Use format like CS101', 'error');
}

if ($year < 2024 || $year > 2030) {
    redirect('create_course.php', 'Invalid year', 'error');
}

$validSemesters = ['Fall', 'Spring', 'Summer'];
if (!in_array($semester, $validSemesters)) {
    redirect('create_course.php', 'Invalid semester', 'error');
}

try {
    $pdo = getDatabaseConnection();
    
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM courses WHERE course_code = :course_code");
    $stmt->execute(['course_code' => $courseCode]);
    
    if ($stmt->fetchColumn() > 0) {
        redirect('create_course.php', 'Course code already exists. Please use a different code.', 'error');
    }
    
    $stmt = $pdo->prepare("
        INSERT INTO courses (course_code, course_name, faculty_id, semester, year, description) 
        VALUES (:course_code, :course_name, :faculty_id, :semester, :year, :description)
    ");
    
    $stmt->execute([
        'course_code' => $courseCode,
        'course_name' => $courseName,
        'faculty_id' => $facultyId,
        'semester' => $semester,
        'year' => $year,
        'description' => $description
    ]);
    
    redirect('dashboard.php', 'Course created successfully!', 'success');
    
} catch (PDOException $e) {
    error_log("Create course error: " . $e->getMessage());
    redirect('create_course.php', 'An error occurred. Please try again.', 'error');
}
?>
