<?php
require_once 'config.php';
require_once 'helpers.php';

requireRole('faculty');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('manage_requests.php', 'Invalid request method', 'error');
}

if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    redirect('manage_requests.php', 'Invalid security token', 'error');
}

$requestId = intval($_POST['request_id'] ?? 0);
$action = $_POST['action'] ?? '';
$comments = sanitizeInput($_POST['comments'] ?? '');
$facultyId = $_SESSION['user_id'];

if (empty($requestId) || !in_array($action, ['approve', 'reject'])) {
    redirect('manage_requests.php', 'Invalid request', 'error');
}

try {
    $pdo = getDatabaseConnection();
    
    // Verify the request belongs to faculty's course
    $stmt = $pdo->prepare("
        SELECT cr.student_id, cr.course_id, c.course_code, c.course_name, u.name as student_name
        FROM course_requests cr
        JOIN courses c ON cr.course_id = c.course_id
        JOIN users u ON cr.student_id = u.user_id
        WHERE cr.request_id = :request_id AND c.faculty_id = :faculty_id AND cr.status = 'pending'
    ");
    $stmt->execute(['request_id' => $requestId, 'faculty_id' => $facultyId]);
    $request = $stmt->fetch();
    
    if (!$request) {
        redirect('manage_requests.php', 'Request not found or already processed', 'error');
    }
    
    $pdo->beginTransaction();
    
    if ($action === 'approve') {
        // Update request status
        $stmt = $pdo->prepare("
            UPDATE course_requests 
            SET status = 'approved', reviewed_at = NOW(), reviewed_by = :faculty_id, comments = :comments
            WHERE request_id = :request_id
        ");
        $stmt->execute([
            'faculty_id' => $facultyId,
            'comments' => $comments,
            'request_id' => $requestId
        ]);
        
        // Add to enrollments
        $stmt = $pdo->prepare("
            INSERT INTO enrollments (student_id, course_id, status, enrollment_date) 
            VALUES (:student_id, :course_id, 'active', CURDATE())
            ON DUPLICATE KEY UPDATE status = 'active'
        ");
        $stmt->execute([
            'student_id' => $request['student_id'],
            'course_id' => $request['course_id']
        ]);
        
        $pdo->commit();
        
        $message = "Approved " . $request['student_name'] . " for " . $request['course_code'];
        redirect('manage_requests.php', $message, 'success');
        
    } else if ($action === 'reject') {
        // Update request status
        $stmt = $pdo->prepare("
            UPDATE course_requests 
            SET status = 'rejected', reviewed_at = NOW(), reviewed_by = :faculty_id, comments = :comments
            WHERE request_id = :request_id
        ");
        $stmt->execute([
            'faculty_id' => $facultyId,
            'comments' => $comments,
            'request_id' => $requestId
        ]);
        
        $pdo->commit();
        
        $message = "Rejected request from " . $request['student_name'] . " for " . $request['course_code'];
        redirect('manage_requests.php', $message, 'success');
    }
    
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Process request error: " . $e->getMessage());
    redirect('manage_requests.php', 'An error occurred. Please try again.', 'error');
}
?>
