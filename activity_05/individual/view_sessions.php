<?php
require_once 'config.php';
require_once 'helpers.php';

requireRole('faculty');

$courseId = $_GET['course_id'] ?? null;

if (!$courseId) {
    redirect('faculty_dashboard.php', 'Invalid course', 'error');
}

$pdo = getDatabaseConnection();

$stmt = $pdo->prepare("SELECT * FROM courses WHERE course_id = :course_id AND faculty_id = :faculty_id");
$stmt->execute(['course_id' => $courseId, 'faculty_id' => $_SESSION['user_id']]);
$course = $stmt->fetch();

if (!$course) {
    redirect('faculty_dashboard.php', 'Course not found or access denied', 'error');
}

$stmt = $pdo->prepare("
    SELECT ats.*, 
           COUNT(ar.record_id) as attendance_count,
           (SELECT COUNT(*) FROM enrollments WHERE course_id = :course_id AND status = 'active') as total_students
    FROM attendance_sessions ats
    LEFT JOIN attendance_records ar ON ats.session_id = ar.session_id
    WHERE ats.course_id = :course_id2
    GROUP BY ats.session_id
    ORDER BY ats.session_date DESC, ats.start_time DESC
");
$stmt->execute(['course_id' => $courseId, 'course_id2' => $courseId]);
$sessions = $stmt->fetchAll();

$flashMessage = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sessions - <?php echo htmlspecialchars($course['course_code']); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'ashesi-maroon': '#820507',
                    }
                }
            }
        }
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <nav class="bg-ashesi-maroon text-white p-4 shadow-lg">
        <div class="container mx-auto flex justify-between items-center">
            <div class="flex items-center space-x-4">
                <a href="faculty_dashboard.php" class="hover:text-gray-200"><i class="fas fa-arrow-left mr-2"></i>Back</a>
                <h1 class="text-xl font-bold">Attendance Sessions</h1>
            </div>
            <span><i class="fas fa-user mr-2"></i><?php echo htmlspecialchars($_SESSION['name']); ?></span>
        </div>
    </nav>

    <div class="container mx-auto p-6">
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <div class="flex justify-between items-start">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800"><?php echo htmlspecialchars($course['course_code']); ?></h2>
                    <p class="text-gray-600"><?php echo htmlspecialchars($course['course_name']); ?></p>
                </div>
                <a href="create_session.php?course_id=<?php echo $courseId; ?>" 
                   class="bg-ashesi-maroon text-white px-4 py-2 rounded hover:bg-red-900 transition">
                    <i class="fas fa-plus mr-2"></i>New Session
                </a>
            </div>
        </div>

        <?php if ($flashMessage): ?>
            <div class="mb-6 p-4 rounded-lg <?php echo $flashMessage['type'] === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                <i class="fas <?php echo $flashMessage['type'] === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?> mr-2"></i>
                <?php echo htmlspecialchars($flashMessage['message']); ?>
            </div>
        <?php endif; ?>

        <?php if (empty($sessions)): ?>
            <div class="bg-white rounded-lg shadow-md p-12 text-center">
                <i class="fas fa-calendar-times text-6xl text-gray-300 mb-4"></i>
                <p class="text-gray-500 text-lg">No sessions created yet</p>
                <p class="text-gray-400 text-sm mt-2">Create your first attendance session to get started</p>
            </div>
        <?php else: ?>
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Session</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date & Time</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Code</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Attendance</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($sessions as $session): ?>
                            <?php 
                            $percentage = $session['total_students'] > 0 
                                ? round(($session['attendance_count'] / $session['total_students']) * 100) 
                                : 0;
                            ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($session['session_name']); ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900"><?php echo date('M d, Y', strtotime($session['session_date'])); ?></div>
                                    <div class="text-sm text-gray-500"><?php echo date('g:i A', strtotime($session['start_time'])) . ' - ' . date('g:i A', strtotime($session['end_time'])); ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                        <?php echo htmlspecialchars($session['attendance_code']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php if ($session['is_active']): ?>
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                            <i class="fas fa-check-circle mr-1"></i>Active
                                        </span>
                                    <?php else: ?>
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                            <i class="fas fa-lock mr-1"></i>Closed
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <span class="text-sm text-gray-900"><?php echo $session['attendance_count']; ?> / <?php echo $session['total_students']; ?></span>
                                        <span class="ml-2 text-xs text-gray-500">(<?php echo $percentage; ?>%)</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                    <a href="view_attendance.php?session_id=<?php echo $session['session_id']; ?>" 
                                       class="text-blue-600 hover:text-blue-900">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="mark_attendance.php?session_id=<?php echo $session['session_id']; ?>" 
                                       class="text-green-600 hover:text-green-900">
                                        <i class="fas fa-check-square"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
