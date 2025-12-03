<?php
require_once 'config.php';
require_once 'helpers.php';

requireRole('student');

$flashMessage = getFlashMessage();
$studentId = $_SESSION['user_id'];
$studentName = $_SESSION['name'];

$pdo = getDatabaseConnection();

$stmt = $pdo->prepare("
    SELECT ats.*, c.course_code, c.course_name, u.name as faculty_name,
           ar.record_id, ar.status as attendance_status, ar.marked_at,
           TIMESTAMPDIFF(MINUTE, NOW(), ats.code_expires_at) as minutes_remaining
    FROM attendance_sessions ats
    JOIN courses c ON ats.course_id = c.course_id
    JOIN enrollments e ON c.course_id = e.course_id
    LEFT JOIN users u ON c.faculty_id = u.user_id
    LEFT JOIN attendance_records ar ON ats.session_id = ar.session_id AND ar.student_id = :student_id
    WHERE e.student_id = :student_id2 
    AND e.status = 'active'
    AND ats.is_active = 1
    AND ats.code_expires_at > NOW()
    ORDER BY ats.created_at DESC
");
$stmt->execute(['student_id' => $studentId, 'student_id2' => $studentId]);
$activeSessions = $stmt->fetchAll();

$stmt = $pdo->prepare("
    SELECT ats.*, c.course_code, c.course_name,
           ar.status as attendance_status, ar.marked_at
    FROM attendance_sessions ats
    JOIN courses c ON ats.course_id = c.course_id
    JOIN enrollments e ON c.course_id = e.course_id
    LEFT JOIN attendance_records ar ON ats.session_id = ar.session_id AND ar.student_id = :student_id
    WHERE e.student_id = :student_id2
    AND e.status = 'active'
    AND ats.session_date = CURDATE()
    AND (ats.is_active = 0 OR ats.code_expires_at <= NOW())
    ORDER BY ats.created_at DESC
    LIMIT 10
");
$stmt->execute(['student_id' => $studentId, 'student_id2' => $studentId]);
$recentSessions = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Join Session - Student</title>
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
    <script>
        setInterval(function() {
            location.reload();
        }, 30000);
    </script>
</head>
<body class="bg-gray-50 min-h-screen">
    <nav class="bg-ashesi-maroon text-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center">
                    <i class="fas fa-graduation-cap text-3xl mr-3"></i>
                    <div>
                        <h1 class="text-xl font-bold">Join Active Sessions</h1>
                        <p class="text-xs opacity-90">Student Portal</p>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="dashboard.php" class="hover:bg-red-900 px-4 py-2 rounded">
                        <i class="fas fa-home mr-2"></i>Dashboard
                    </a>
                    <a href="logout.php" class="bg-red-600 px-4 py-2 rounded hover:bg-red-700">
                        <i class="fas fa-sign-out-alt mr-2"></i>Logout
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 py-8">
        <?php if ($flashMessage): ?>
            <div class="mb-6 p-4 rounded-lg <?php echo $flashMessage['type'] === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                <i class="fas <?php echo $flashMessage['type'] === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?> mr-2"></i>
                <?php echo htmlspecialchars($flashMessage['message']); ?>
            </div>
        <?php endif; ?>

        <div class="mb-8">
            <h2 class="text-3xl font-bold text-gray-800 mb-2">
                <i class="fas fa-signal text-green-600 mr-3"></i>Live Sessions
            </h2>
            <p class="text-gray-600">Active attendance sessions for your enrolled courses</p>
        </div>

        <?php if (empty($activeSessions)): ?>
            <div class="bg-white rounded-lg shadow-md p-12 text-center">
                <i class="fas fa-clock text-6xl text-gray-300 mb-4"></i>
                <p class="text-gray-500 text-lg mb-2">No active sessions right now</p>
                <p class="text-gray-400">Check back when your instructor starts a session</p>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                <?php foreach ($activeSessions as $session): ?>
                    <div class="bg-white rounded-lg shadow-md p-6 border-l-4 <?php echo $session['record_id'] ? 'border-green-500' : 'border-blue-500'; ?>">
                        <div class="flex justify-between items-start mb-4">
                            <div class="flex-1">
                                <h3 class="text-lg font-bold text-gray-800 mb-1"><?php echo htmlspecialchars($session['session_name']); ?></h3>
                                <p class="text-sm text-gray-600 mb-1"><?php echo htmlspecialchars($session['course_code']); ?></p>
                                <p class="text-xs text-gray-500"><?php echo htmlspecialchars($session['course_name']); ?></p>
                            </div>
                            <?php if ($session['record_id']): ?>
                                <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs font-semibold">
                                    <i class="fas fa-check"></i> Marked
                                </span>
                            <?php else: ?>
                                <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs font-semibold animate-pulse">
                                    <i class="fas fa-circle"></i> LIVE
                                </span>
                            <?php endif; ?>
                        </div>

                        <div class="bg-gray-50 rounded p-3 mb-4">
                            <p class="text-xs text-gray-600 mb-1">Instructor</p>
                            <p class="text-sm font-semibold text-gray-800"><?php echo htmlspecialchars($session['faculty_name']); ?></p>
                        </div>

                        <?php if ($session['record_id']): ?>
                            <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-4">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-sm font-semibold text-green-800">Attendance Recorded</span>
                                    <span class="px-2 py-1 rounded text-xs font-bold <?php 
                                        echo $session['attendance_status'] === 'present' ? 'bg-green-200 text-green-900' : 
                                            ($session['attendance_status'] === 'late' ? 'bg-yellow-200 text-yellow-900' : 'bg-red-200 text-red-900'); 
                                    ?>">
                                        <?php echo strtoupper($session['attendance_status']); ?>
                                    </span>
                                </div>
                                <p class="text-xs text-green-700">
                                    <i class="fas fa-clock mr-1"></i>
                                    Marked at <?php echo date('g:i A', strtotime($session['marked_at'])); ?>
                                </p>
                            </div>
                        <?php else: ?>
                            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3 mb-4">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs text-yellow-800">
                                        <i class="fas fa-hourglass-half mr-1"></i>Expires in
                                    </span>
                                    <span class="text-lg font-bold <?php echo $session['minutes_remaining'] <= 2 ? 'text-red-600' : 'text-yellow-800'; ?>">
                                        <?php echo max(0, $session['minutes_remaining']); ?> min
                                    </span>
                                </div>
                            </div>

                            <form action="mark_attendance_handler.php" method="POST">
                                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                <div class="mb-4">
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Enter Attendance Code</label>
                                    <input type="text" 
                                           name="attendance_code" 
                                           placeholder="6-DIGIT CODE" 
                                           maxlength="6"
                                           required
                                           class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-ashesi-maroon text-center text-xl font-bold uppercase tracking-widest"
                                           pattern="[A-Z0-9]{6}">
                                </div>
                                <button type="submit" class="w-full bg-ashesi-maroon text-white py-3 rounded-lg hover:bg-red-900 transition font-semibold">
                                    <i class="fas fa-check mr-2"></i>Mark Attendance
                                </button>
                            </form>
                        <?php endif; ?>

                        <div class="mt-4 pt-4 border-t border-gray-200">
                            <div class="flex items-center justify-between text-xs text-gray-500">
                                <span><i class="fas fa-calendar mr-1"></i><?php echo date('M j, Y', strtotime($session['session_date'])); ?></span>
                                <span><i class="fas fa-clock mr-1"></i><?php echo date('g:i A', strtotime($session['start_time'])); ?></span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($recentSessions)): ?>
            <div class="mb-8">
                <h2 class="text-2xl font-bold text-gray-800 mb-4">
                    <i class="fas fa-history text-gray-600 mr-3"></i>Today's Previous Sessions
                </h2>
            </div>

            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Session</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Course</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Time</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Your Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php foreach ($recentSessions as $session): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <div class="font-semibold text-gray-800"><?php echo htmlspecialchars($session['session_name']); ?></div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-600"><?php echo htmlspecialchars($session['course_code']); ?></div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-600"><?php echo date('g:i A', strtotime($session['start_time'])); ?></div>
                                </td>
                                <td class="px-6 py-4">
                                    <?php if ($session['attendance_status']): ?>
                                        <span class="px-3 py-1 rounded-full text-xs font-semibold <?php 
                                            echo $session['attendance_status'] === 'present' ? 'bg-green-100 text-green-800' : 
                                                ($session['attendance_status'] === 'late' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800'); 
                                        ?>">
                                            <?php echo ucfirst($session['attendance_status']); ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="px-3 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-800">
                                            Absent
                                        </span>
                                    <?php endif; ?>
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
