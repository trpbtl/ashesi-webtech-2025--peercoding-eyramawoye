<?php
require_once 'config.php';
require_once 'helpers.php';

requireRole('student');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('browse_courses.php', 'Invalid request method', 'error');
}

if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    redirect('browse_courses.php', 'Invalid security token', 'error');
}

$courseId = intval($_POST['course_id'] ?? 0);
$studentId = $_SESSION['user_id'];

if (empty($courseId)) {
    redirect('browse_courses.php', 'Invalid course selection', 'error');
}

try {
    $pdo = getDatabaseConnection();
    
    // Check if course exists
    $stmt = $pdo->prepare("SELECT course_code, course_name FROM courses WHERE course_id = :course_id");
    $stmt->execute(['course_id' => $courseId]);
    $course = $stmt->fetch();
    
    if (!$course) {
        redirect('browse_courses.php', 'Course not found', 'error');
    }
    
    // Check if already enrolled
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM enrollments WHERE student_id = :student_id AND course_id = :course_id");
    $stmt->execute(['student_id' => $studentId, 'course_id' => $courseId]);
    
    if ($stmt->fetchColumn() > 0) {
        redirect('browse_courses.php', 'You are already enrolled in this course', 'error');
    }
    
    // Check if pending request exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM course_requests WHERE student_id = :student_id AND course_id = :course_id AND status = 'pending'");
    $stmt->execute(['student_id' => $studentId, 'course_id' => $courseId]);
    
    if ($stmt->fetchColumn() > 0) {
        redirect('browse_courses.php', 'You already have a pending request for this course', 'error');
    }
    
    // Create course request
    $stmt = $pdo->prepare("
        INSERT INTO course_requests (student_id, course_id, status, requested_at) 
        VALUES (:student_id, :course_id, 'pending', NOW())
    ");
    
    $stmt->execute([
        'student_id' => $studentId,
        'course_id' => $courseId
    ]);
    
    redirect('browse_courses.php', 'Course join request submitted successfully! Wait for faculty approval.', 'success');
    
} catch (PDOException $e) {
    error_log("Request join error: " . $e->getMessage());
    redirect('browse_courses.php', 'An error occurred. Please try again.', 'error');
}
?>
