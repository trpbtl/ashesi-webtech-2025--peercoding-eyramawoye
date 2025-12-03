<?php
require_once 'config.php';
require_once 'helpers.php';

requireRole('faculty');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('faculty_dashboard.php', 'Invalid request method', 'error');
}

$csrfToken = $_POST['csrf_token'] ?? '';
if (!verifyCSRFToken($csrfToken)) {
    redirect('faculty_dashboard.php', 'Invalid security token', 'error');
}

$courseId = $_POST['course_id'] ?? '';
$sessionName = trim($_POST['session_name'] ?? '');
$sessionDate = $_POST['session_date'] ?? '';
$startTime = $_POST['start_time'] ?? '';
$endTime = $_POST['end_time'] ?? '';
$codeDuration = (int)($_POST['code_duration'] ?? 10);

if (empty($courseId) || empty($sessionName) || empty($sessionDate) || empty($startTime) || empty($endTime)) {
    redirect("create_session.php?course_id=$courseId", 'Please fill in all fields', 'error');
}

$pdo = getDatabaseConnection();

$stmt = $pdo->prepare("SELECT * FROM courses WHERE course_id = :course_id AND faculty_id = :faculty_id");
$stmt->execute(['course_id' => $courseId, 'faculty_id' => $_SESSION['user_id']]);
$course = $stmt->fetch();

if (!$course) {
    redirect('faculty_dashboard.php', 'Course not found or access denied', 'error');
}

$attendanceCode = strtoupper(substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZ23456789'), 0, 6));

$codeExpiresAt = date('Y-m-d H:i:s', strtotime("$sessionDate $startTime") + ($codeDuration * 60));

try {
    $stmt = $pdo->prepare("
        INSERT INTO attendance_sessions 
        (course_id, session_name, session_date, start_time, end_time, attendance_code, code_expires_at, created_by, is_active)
        VALUES 
        (:course_id, :session_name, :session_date, :start_time, :end_time, :attendance_code, :code_expires_at, :created_by, TRUE)
    ");
    
    $stmt->execute([
        'course_id' => $courseId,
        'session_name' => $sessionName,
        'session_date' => $sessionDate,
        'start_time' => $startTime,
        'end_time' => $endTime,
        'attendance_code' => $attendanceCode,
        'code_expires_at' => $codeExpiresAt,
        'created_by' => $_SESSION['user_id']
    ]);
    
    $sessionId = $pdo->lastInsertId();
    
    redirect("view_sessions.php?course_id=$courseId", "Session created successfully! Code: $attendanceCode", 'success');
    
} catch (PDOException $e) {
    redirect("create_session.php?course_id=$courseId", 'Error creating session: ' . $e->getMessage(), 'error');
}
?>
