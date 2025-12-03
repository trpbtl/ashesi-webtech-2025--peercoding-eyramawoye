<?php
require_once 'config.php';
require_once 'helpers.php';

requireRole('student');

$flashMessage = getFlashMessage();
$studentId = $_SESSION['user_id'];
$studentName = $_SESSION['name'];

$pdo = getDatabaseConnection();

$stmt = $pdo->prepare("
    SELECT c.course_id, c.course_code, c.course_name, c.semester, c.year,
           u.name as faculty_name,
           COUNT(DISTINCT ats.session_id) as total_sessions,
           COUNT(DISTINCT ar.record_id) as attended_sessions,
           ROUND(COUNT(DISTINCT ar.record_id) / NULLIF(COUNT(DISTINCT ats.session_id), 0) * 100, 1) as attendance_percentage
    FROM enrollments e
    JOIN courses c ON e.course_id = c.course_id
    LEFT JOIN users u ON c.faculty_id = u.user_id
    LEFT JOIN attendance_sessions ats ON c.course_id = ats.course_id
    LEFT JOIN attendance_records ar ON ats.session_id = ar.session_id AND ar.student_id = :student_id
    WHERE e.student_id = :student_id2 AND e.status = 'active'
    GROUP BY c.course_id
    ORDER BY c.course_code
");
$stmt->execute(['student_id' => $studentId, 'student_id2' => $studentId]);
$courses = $stmt->fetchAll();

$stmt = $pdo->prepare("
    SELECT ats.session_id, ats.session_name, ats.session_date, ats.start_time, ats.is_active,
           c.course_code, c.course_name,
           ar.marked_at, ar.status
    FROM attendance_sessions ats
    JOIN courses c ON ats.course_id = c.course_id
    JOIN enrollments e ON c.course_id = e.course_id
    LEFT JOIN attendance_records ar ON ats.session_id = ar.session_id AND ar.student_id = :student_id
    WHERE e.student_id = :student_id2 AND e.status = 'active'
    AND ats.session_date >= CURDATE() - INTERVAL 7 DAY
    ORDER BY ats.session_date DESC, ats.start_time DESC
    LIMIT 10
");
$stmt->execute(['student_id' => $studentId, 'student_id2' => $studentId]);
$recentSessions = $stmt->fetchAll();

$totalSessions = array_sum(array_column($courses, 'total_sessions'));
$attendedSessions = array_sum(array_column($courses, 'attended_sessions'));
$overallPercentage = $totalSessions > 0 ? round(($attendedSessions / $totalSessions) * 100, 1) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - Attendance System</title>
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
                <a href="browse_courses.php" class="hover:bg-red-900 px-4 py-2 rounded transition">
                    <i class="fas fa-search mr-2"></i>Browse Courses
                </a>
                <a href="join_session.php" class="hover:bg-red-900 px-4 py-2 rounded transition">
                    <i class="fas fa-signal mr-2"></i>Join Session
                </a>
                <span><i class="fas fa-user mr-2"></i><?php echo htmlspecialchars($studentName); ?></span>
                <span class="bg-white text-ashesi-maroon px-3 py-1 rounded text-sm font-semibold">Student</span>
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
                        <p class="text-gray-600">Enrolled Courses</p>
                        <p class="text-3xl font-bold"><?php echo count($courses); ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-lg shadow-md">
                <div class="flex items-center">
                    <div class="bg-green-100 p-4 rounded-full">
                        <i class="fas fa-check-circle text-2xl text-green-600"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-gray-600">Sessions Attended</p>
                        <p class="text-3xl font-bold"><?php echo $attendedSessions; ?> / <?php echo $totalSessions; ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-lg shadow-md">
                <div class="flex items-center">
                    <div class="<?php echo $overallPercentage >= 75 ? 'bg-green-100' : 'bg-yellow-100'; ?> p-4 rounded-full">
                        <i class="fas fa-percentage text-2xl <?php echo $overallPercentage >= 75 ? 'text-green-600' : 'text-yellow-600'; ?>"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-gray-600">Overall Attendance</p>
                        <p class="text-3xl font-bold"><?php echo $overallPercentage; ?>%</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <a href="browse_courses.php" class="bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-lg shadow-md p-6 hover:shadow-lg transition transform hover:scale-105">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-xl font-bold mb-2">Browse & Join Courses</h3>
                        <p class="text-blue-100 text-sm">Request to enroll in new courses</p>
                    </div>
                    <i class="fas fa-search text-4xl opacity-75"></i>
                </div>
            </a>
            
            <a href="join_session.php" class="bg-gradient-to-r from-green-600 to-green-700 text-white rounded-lg shadow-md p-6 hover:shadow-lg transition transform hover:scale-105">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-xl font-bold mb-2">Join Live Sessions</h3>
                        <p class="text-green-100 text-sm">Mark attendance with session codes</p>
                    </div>
                    <i class="fas fa-signal text-4xl opacity-75"></i>
                </div>
            </a>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-2xl font-bold text-gray-800">Quick Mark Attendance</h2>
            </div>
            <form action="mark_attendance_handler.php" method="POST" class="flex gap-4">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                <input 
                    type="text" 
                    name="attendance_code" 
                    placeholder="Enter 6-digit attendance code" 
                    maxlength="6"
                    required
                    class="flex-1 px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-ashesi-maroon uppercase"
                    pattern="[A-Z0-9]{6}"
                >
                <button 
                    type="submit" 
                    class="bg-ashesi-maroon text-white px-6 py-3 rounded-lg hover:bg-red-900 transition font-semibold"
                >
                    <i class="fas fa-check mr-2"></i>Submit
                </button>
            </form>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-2xl font-bold text-gray-800">My Courses</h2>
                    <a href="browse_courses.php" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition">
                        <i class="fas fa-plus mr-2"></i>Join Course
                    </a>
                </div>
                
                <?php if (empty($courses)): ?>
                    <p class="text-gray-500 text-center py-8">No enrolled courses</p>
                <?php else: ?>
                    <div class="space-y-4">
                        <?php foreach ($courses as $course): ?>
                            <?php 
                            $percentage = $course['attendance_percentage'] ?? 0;
                            $color = $percentage >= 75 ? 'green' : ($percentage >= 50 ? 'yellow' : 'red');
                            ?>
                            <div class="border border-gray-200 rounded-lg p-4">
                                <div class="flex justify-between items-start mb-2">
                                    <div>
                                        <h3 class="font-bold text-lg text-gray-800"><?php echo htmlspecialchars($course['course_code']); ?></h3>
                                        <p class="text-gray-600"><?php echo htmlspecialchars($course['course_name']); ?></p>
                                        <p class="text-sm text-gray-500"><?php echo htmlspecialchars($course['faculty_name']); ?></p>
                                    </div>
                                    <span class="bg-<?php echo $color; ?>-100 text-<?php echo $color; ?>-800 px-3 py-1 rounded font-bold">
                                        <?php echo $percentage; ?>%
                                    </span>
                                </div>
                                <div class="flex items-center space-x-4 text-sm text-gray-600 mt-3">
                                    <span><i class="fas fa-calendar mr-1"></i><?php echo $course['total_sessions']; ?> sessions</span>
                                    <span><i class="fas fa-check mr-1"></i><?php echo $course['attended_sessions']; ?> attended</span>
                                </div>
                                <div class="mt-3">
                                    <div class="w-full bg-gray-200 rounded-full h-2">
                                        <div class="bg-<?php echo $color; ?>-600 h-2 rounded-full" style="width: <?php echo $percentage; ?>%"></div>
                                    </div>
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
                                        <p class="text-sm text-gray-600"><?php echo htmlspecialchars($session['course_code']); ?></p>
                                    </div>
                                    <?php if ($session['marked_at']): ?>
                                        <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs font-semibold">
                                            <i class="fas fa-check-circle mr-1"></i>Present
                                        </span>
                                    <?php elseif ($session['is_active']): ?>
                                        <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs font-semibold">
                                            <i class="fas fa-clock mr-1"></i>Active
                                        </span>
                                    <?php else: ?>
                                        <span class="bg-gray-100 text-gray-800 px-2 py-1 rounded text-xs font-semibold">
                                            <i class="fas fa-times-circle mr-1"></i>Missed
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <div class="flex items-center space-x-4 text-sm text-gray-600">
                                    <span><i class="fas fa-calendar mr-1"></i><?php echo date('M d, Y', strtotime($session['session_date'])); ?></span>
                                    <span><i class="fas fa-clock mr-1"></i><?php echo date('g:i A', strtotime($session['start_time'])); ?></span>
                                </div>
                                <?php if ($session['marked_at']): ?>
                                    <p class="text-xs text-gray-500 mt-2">
                                        <i class="fas fa-check mr-1"></i>Marked at <?php echo date('g:i A', strtotime($session['marked_at'])); ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
