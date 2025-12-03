<?php
require_once 'config.php';
require_once 'helpers.php';

requireRole('faculty');

$flashMessage = getFlashMessage();
$facultyId = $_SESSION['user_id'];
$facultyName = $_SESSION['name'];

$pdo = getDatabaseConnection();

$stmt = $pdo->prepare("
    SELECT c.course_id, c.course_code, c.course_name, c.semester, c.year,
           COUNT(DISTINCT e.student_id) as enrolled_count,
           COUNT(DISTINCT ats.session_id) as session_count
    FROM courses c
    LEFT JOIN enrollments e ON c.course_id = e.course_id AND e.status = 'active'
    LEFT JOIN attendance_sessions ats ON c.course_id = ats.course_id
    WHERE c.faculty_id = :faculty_id
    GROUP BY c.course_id
    ORDER BY c.course_code
");
$stmt->execute(['faculty_id' => $facultyId]);
$courses = $stmt->fetchAll();

$stmt = $pdo->prepare("
    SELECT ats.*, c.course_code, c.course_name,
           COUNT(ar.record_id) as attendance_count
    FROM attendance_sessions ats
    JOIN courses c ON ats.course_id = c.course_id
    LEFT JOIN attendance_records ar ON ats.session_id = ar.session_id
    WHERE c.faculty_id = :faculty_id
    AND ats.session_date >= CURDATE() - INTERVAL 7 DAY
    GROUP BY ats.session_id
    ORDER BY ats.session_date DESC, ats.start_time DESC
    LIMIT 10
");
$stmt->execute(['faculty_id' => $facultyId]);
$recentSessions = $stmt->fetchAll();

$stmt = $pdo->prepare("
    SELECT COUNT(*) 
    FROM course_requests cr
    JOIN courses c ON cr.course_id = c.course_id
    WHERE c.faculty_id = :faculty_id AND cr.status = 'pending'
");
$stmt->execute(['faculty_id' => $facultyId]);
$pendingRequestsCount = $stmt->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faculty Dashboard - Attendance System</title>
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
                <i class="fas fa-graduation-cap text-2xl"></i>
                <h1 class="text-xl font-bold">Attendance Management System</h1>
            </div>
            <div class="flex items-center space-x-6">
                <span><i class="fas fa-user mr-2"></i><?php echo htmlspecialchars($facultyName); ?></span>
                <span class="bg-white text-ashesi-maroon px-3 py-1 rounded text-sm font-semibold">Faculty</span>
                <a href="logout.php" class="hover:text-gray-200"><i class="fas fa-sign-out-alt mr-2"></i>Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mx-auto p-6">
        <?php if ($flashMessage): ?>
            <div class="mb-6 p-4 rounded-lg <?php echo $flashMessage['type'] === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                <i class="fas <?php echo $flashMessage['type'] === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?> mr-2"></i>
                <?php echo htmlspecialchars($flashMessage['message']); ?>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white p-6 rounded-lg shadow-md">
                <div class="flex items-center">
                    <div class="bg-blue-100 p-4 rounded-full">
                        <i class="fas fa-book text-2xl text-blue-600"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-gray-600">My Courses</p>
                        <p class="text-3xl font-bold"><?php echo count($courses); ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-lg shadow-md">
                <div class="flex items-center">
                    <div class="bg-green-100 p-4 rounded-full">
                        <i class="fas fa-calendar-check text-2xl text-green-600"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-gray-600">Total Sessions</p>
                        <p class="text-3xl font-bold"><?php echo array_sum(array_column($courses, 'session_count')); ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-lg shadow-md">
                <div class="flex items-center">
                    <div class="bg-purple-100 p-4 rounded-full">
                        <i class="fas fa-users text-2xl text-purple-600"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-gray-600">Total Students</p>
                        <p class="text-3xl font-bold"><?php echo array_sum(array_column($courses, 'enrolled_count')); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <a href="manage_requests.php" class="bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-lg shadow-md p-6 hover:shadow-lg transition transform hover:scale-105">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-xl font-bold mb-2">Manage Course Requests</h3>
                        <p class="text-blue-100 text-sm">Approve/reject student enrollment requests</p>
                        <?php if ($pendingRequestsCount > 0): ?>
                            <span class="inline-block mt-2 bg-yellow-400 text-yellow-900 px-3 py-1 rounded-full text-xs font-bold">
                                <?php echo $pendingRequestsCount; ?> pending
                            </span>
                        <?php endif; ?>
                    </div>
                    <i class="fas fa-tasks text-4xl opacity-75"></i>
                </div>
            </a>
            
            <a href="active_sessions.php" class="bg-gradient-to-r from-green-600 to-green-700 text-white rounded-lg shadow-md p-6 hover:shadow-lg transition transform hover:scale-105">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-xl font-bold mb-2">Start Live Session</h3>
                        <p class="text-green-100 text-sm">Create attendance sessions with codes</p>
                    </div>
                    <i class="fas fa-broadcast-tower text-4xl opacity-75"></i>
                </div>
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-2xl font-bold text-gray-800">My Courses</h2>
                    <a href="create_course.php" class="bg-ashesi-maroon text-white px-4 py-2 rounded hover:bg-red-900 transition">
                        <i class="fas fa-plus mr-2"></i>New Course
                    </a>
                </div>

                <?php if (empty($courses)): ?>
                    <p class="text-gray-500 text-center py-8">No courses yet. Create your first course!</p>
                <?php else: ?>
                    <div class="space-y-4">
                        <?php foreach ($courses as $course): ?>
                            <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition">
                                <div class="flex justify-between items-start mb-2">
                                    <div>
                                        <h3 class="font-bold text-lg text-gray-800"><?php echo htmlspecialchars($course['course_code']); ?></h3>
                                        <p class="text-gray-600"><?php echo htmlspecialchars($course['course_name']); ?></p>
                                    </div>
                                    <span class="bg-gray-100 px-3 py-1 rounded text-sm">
                                        <?php echo htmlspecialchars($course['semester'] . ' ' . $course['year']); ?>
                                    </span>
                                </div>
                                <div class="flex items-center space-x-4 text-sm text-gray-600 mt-3">
                                    <span><i class="fas fa-users mr-1"></i><?php echo $course['enrolled_count']; ?> students</span>
                                    <span><i class="fas fa-calendar mr-1"></i><?php echo $course['session_count']; ?> sessions</span>
                                </div>
                                <div class="mt-4 flex space-x-2">
                                    <a href="create_session.php?course_id=<?php echo $course['course_id']; ?>" 
                                       class="flex-1 bg-blue-600 text-white px-3 py-2 rounded text-center hover:bg-blue-700 transition text-sm">
                                        <i class="fas fa-plus-circle mr-1"></i>Create Session
                                    </a>
                                    <a href="view_sessions.php?course_id=<?php echo $course['course_id']; ?>" 
                                       class="flex-1 bg-gray-600 text-white px-3 py-2 rounded text-center hover:bg-gray-700 transition text-sm">
                                        <i class="fas fa-list mr-1"></i>View Sessions
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-2xl font-bold text-gray-800 mb-4">Recent Sessions</h2>
                
                <?php if (empty($recentSessions)): ?>
                    <p class="text-gray-500 text-center py-8">No recent sessions</p>
                <?php else: ?>
                    <div class="space-y-4">
                        <?php foreach ($recentSessions as $session): ?>
                            <div class="border border-gray-200 rounded-lg p-4">
                                <div class="flex justify-between items-start mb-2">
                                    <div>
                                        <h3 class="font-semibold text-gray-800"><?php echo htmlspecialchars($session['session_name']); ?></h3>
                                        <p class="text-sm text-gray-600"><?php echo htmlspecialchars($session['course_code']); ?> - <?php echo htmlspecialchars($session['course_name']); ?></p>
                                    </div>
                                    <?php if ($session['is_active']): ?>
                                        <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs font-semibold">Active</span>
                                    <?php else: ?>
                                        <span class="bg-gray-100 text-gray-800 px-2 py-1 rounded text-xs font-semibold">Closed</span>
                                    <?php endif; ?>
                                </div>
                                <div class="flex items-center space-x-4 text-sm text-gray-600 mb-3">
                                    <span><i class="fas fa-calendar mr-1"></i><?php echo date('M d, Y', strtotime($session['session_date'])); ?></span>
                                    <span><i class="fas fa-clock mr-1"></i><?php echo date('g:i A', strtotime($session['start_time'])); ?></span>
                                    <span><i class="fas fa-user-check mr-1"></i><?php echo $session['attendance_count']; ?> attended</span>
                                </div>
                                <div class="flex space-x-2">
                                    <a href="mark_attendance.php?session_id=<?php echo $session['session_id']; ?>" 
                                       class="flex-1 bg-ashesi-maroon text-white px-3 py-2 rounded text-center hover:bg-red-900 transition text-sm">
                                        <i class="fas fa-check-square mr-1"></i>Mark Attendance
                                    </a>
                                    <a href="view_attendance.php?session_id=<?php echo $session['session_id']; ?>" 
                                       class="flex-1 bg-gray-600 text-white px-3 py-2 rounded text-center hover:bg-gray-700 transition text-sm">
                                        <i class="fas fa-eye mr-1"></i>View Records
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
