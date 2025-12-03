<?php
require_once 'config.php';
require_once 'helpers.php';

requireRole('student');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('student_dashboard.php', 'Invalid request method', 'error');
}

$csrfToken = $_POST['csrf_token'] ?? '';
if (!verifyCSRFToken($csrfToken)) {
    redirect('student_dashboard.php', 'Invalid security token', 'error');
}

$attendanceCode = strtoupper(trim($_POST['attendance_code'] ?? ''));

if (empty($attendanceCode) || strlen($attendanceCode) !== 6) {
    redirect('student_dashboard.php', 'Invalid attendance code format', 'error');
}

$pdo = getDatabaseConnection();
$studentId = $_SESSION['user_id'];

$stmt = $pdo->prepare("
    SELECT ats.*, c.course_code, c.course_name
    FROM attendance_sessions ats
    JOIN courses c ON ats.course_id = c.course_id
    WHERE ats.attendance_code = :code 
    AND ats.is_active = TRUE
    AND ats.code_expires_at > NOW()
");
$stmt->execute(['code' => $attendanceCode]);
$session = $stmt->fetch();

if (!$session) {
    redirect('student_dashboard.php', 'Invalid or expired attendance code', 'error');
}

$stmt = $pdo->prepare("
    SELECT * FROM enrollments 
    WHERE student_id = :student_id 
    AND course_id = :course_id 
    AND status = 'active'
");
$stmt->execute([
    'student_id' => $studentId,
    'course_id' => $session['course_id']
]);
$enrollment = $stmt->fetch();

if (!$enrollment) {
    redirect('student_dashboard.php', 'You are not enrolled in this course', 'error');
}

$stmt = $pdo->prepare("
    SELECT * FROM attendance_records 
    WHERE session_id = :session_id 
    AND student_id = :student_id
");
$stmt->execute([
    'session_id' => $session['session_id'],
    'student_id' => $studentId
]);
$existingRecord = $stmt->fetch();

if ($existingRecord) {
    redirect('student_dashboard.php', 'You have already marked attendance for this session', 'error');
}

$sessionStart = strtotime($session['session_date'] . ' ' . $session['start_time']);
$now = time();
$minutesLate = max(0, round(($now - $sessionStart) / 60));

$status = 'present';
if ($minutesLate > 15) {
    $status = 'late';
}

try {
    $stmt = $pdo->prepare("
        INSERT INTO attendance_records 
        (session_id, student_id, status, marked_by_code)
        VALUES 
        (:session_id, :student_id, :status, TRUE)
    ");
    
    $stmt->execute([
        'session_id' => $session['session_id'],
        'student_id' => $studentId,
        'status' => $status
    ]);
    
    $message = $status === 'present' 
        ? "Attendance marked successfully for {$session['course_code']}"
        : "Attendance marked as LATE for {$session['course_code']} ($minutesLate minutes late)";
    
    redirect('student_dashboard.php', $message, 'success');
    
} catch (PDOException $e) {
    redirect('student_dashboard.php', 'Error marking attendance: ' . $e->getMessage(), 'error');
}
?>
