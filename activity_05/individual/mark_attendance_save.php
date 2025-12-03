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

$sessionId = $_POST['session_id'] ?? '';
$attendanceData = $_POST['attendance'] ?? [];

if (empty($sessionId)) {
    redirect('faculty_dashboard.php', 'Invalid session', 'error');
}

$pdo = getDatabaseConnection();

$stmt = $pdo->prepare("
    SELECT ats.*, c.course_id
    FROM attendance_sessions ats
    JOIN courses c ON ats.course_id = c.course_id
    WHERE ats.session_id = :session_id AND c.faculty_id = :faculty_id
");
$stmt->execute(['session_id' => $sessionId, 'faculty_id' => $_SESSION['user_id']]);
$session = $stmt->fetch();

if (!$session) {
    redirect('faculty_dashboard.php', 'Session not found or access denied', 'error');
}

try {
    $pdo->beginTransaction();
    
    foreach ($attendanceData as $studentId => $status) {
        if (!in_array($status, ['present', 'late', 'absent'])) {
            continue;
        }
        
        $stmt = $pdo->prepare("
            INSERT INTO attendance_records (session_id, student_id, status, marked_by_code)
            VALUES (:session_id, :student_id, :status, FALSE)
            ON DUPLICATE KEY UPDATE status = :status2, marked_at = CURRENT_TIMESTAMP
        ");
        
        $stmt->execute([
            'session_id' => $sessionId,
            'student_id' => $studentId,
            'status' => $status,
            'status2' => $status
        ]);
    }
    
    $pdo->commit();
    redirect("view_sessions.php?course_id={$session['course_id']}", 'Attendance saved successfully', 'success');
    
} catch (PDOException $e) {
    $pdo->rollBack();
    redirect("mark_attendance.php?session_id=$sessionId", 'Error saving attendance: ' . $e->getMessage(), 'error');
}
?>
