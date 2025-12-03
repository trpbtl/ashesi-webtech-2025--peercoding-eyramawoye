<?php
require_once 'config.php';
require_once 'helpers.php';

requireRole('faculty');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('active_sessions.php', 'Invalid request method', 'error');
}

if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    redirect('active_sessions.php', 'Invalid security token', 'error');
}

$sessionId = intval($_POST['session_id'] ?? 0);
$facultyId = $_SESSION['user_id'];

if (empty($sessionId)) {
    redirect('active_sessions.php', 'Invalid session', 'error');
}

try {
    $pdo = getDatabaseConnection();
    
    $stmt = $pdo->prepare("
        SELECT ats.session_id
        FROM attendance_sessions ats
        JOIN courses c ON ats.course_id = c.course_id
        WHERE ats.session_id = :session_id AND c.faculty_id = :faculty_id
    ");
    $stmt->execute(['session_id' => $sessionId, 'faculty_id' => $facultyId]);
    
    if (!$stmt->fetch()) {
        redirect('active_sessions.php', 'Session not found or unauthorized', 'error');
    }
    
    $stmt = $pdo->prepare("
        UPDATE attendance_sessions 
        SET is_active = 0
        WHERE session_id = :session_id
    ");
    $stmt->execute(['session_id' => $sessionId]);
    
    redirect('active_sessions.php', 'Session ended successfully', 'success');
    
} catch (PDOException $e) {
    error_log("End session error: " . $e->getMessage());
    redirect('active_sessions.php', 'An error occurred', 'error');
}
?>
