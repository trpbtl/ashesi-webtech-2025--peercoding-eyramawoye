<?php
require_once 'config.php';
require_once 'helpers.php';

requireRole('faculty');

$flashMessage = getFlashMessage();
$facultyId = $_SESSION['user_id'];
$facultyName = $_SESSION['name'];

$pdo = getDatabaseConnection();

$stmt = $pdo->prepare("
    SELECT c.course_id, c.course_code, c.course_name
    FROM courses c
    WHERE c.faculty_id = :faculty_id
    ORDER BY c.course_code
");
$stmt->execute(['faculty_id' => $facultyId]);
$courses = $stmt->fetchAll();

$stmt = $pdo->prepare("
    SELECT ats.*, c.course_code, c.course_name,
           COUNT(DISTINCT e.student_id) as enrolled_count,
           COUNT(DISTINCT ar.record_id) as attendance_count,
           TIMESTAMPDIFF(MINUTE, NOW(), ats.code_expires_at) as minutes_remaining
    FROM attendance_sessions ats
    JOIN courses c ON ats.course_id = c.course_id
    LEFT JOIN enrollments e ON c.course_id = e.course_id AND e.status = 'active'
    LEFT JOIN attendance_records ar ON ats.session_id = ar.session_id
    WHERE c.faculty_id = :faculty_id 
    AND ats.is_active = 1
    AND ats.code_expires_at > NOW()
    GROUP BY ats.session_id
    ORDER BY ats.created_at DESC
");
$stmt->execute(['faculty_id' => $facultyId]);
$activeSessions = $stmt->fetchAll();

$stmt = $pdo->prepare("
    SELECT ats.*, c.course_code, c.course_name,
           COUNT(DISTINCT ar.record_id) as attendance_count
    FROM attendance_sessions ats
    JOIN courses c ON ats.course_id = c.course_id
    LEFT JOIN attendance_records ar ON ats.session_id = ar.session_id
    WHERE c.faculty_id = :faculty_id 
    AND (ats.is_active = 0 OR ats.code_expires_at <= NOW())
    AND ats.session_date = CURDATE()
    GROUP BY ats.session_id
    ORDER BY ats.created_at DESC
");
$stmt->execute(['faculty_id' => $facultyId]);
$recentSessions = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Active Sessions - Faculty</title>
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
        function copyCode(code) {
            navigator.clipboard.writeText(code);
            alert('Code copied: ' + code);
        }

        function endSession(sessionId) {
            if (confirm('Are you sure you want to end this session?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'end_session.php';
                
                const sessionInput = document.createElement('input');
                sessionInput.type = 'hidden';
                sessionInput.name = 'session_id';
                sessionInput.value = sessionId;
                
                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = 'csrf_token';
                csrfInput.value = '<?php echo generateCSRFToken(); ?>';
                
                form.appendChild(sessionInput);
                form.appendChild(csrfInput);
                document.body.appendChild(form);
                form.submit();
            }
        }

        setInterval(function() {
            location.reload();
        }, 60000);
    </script>
</head>
<body class="bg-gray-50 min-h-screen">
    <nav class="bg-ashesi-maroon text-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center">
                    <i class="fas fa-graduation-cap text-3xl mr-3"></i>
                    <div>
                        <h1 class="text-xl font-bold">Active Attendance Sessions</h1>
                        <p class="text-xs opacity-90">Faculty Portal</p>
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
                <i class="fas fa-broadcast-tower text-ashesi-maroon mr-3"></i>Start New Session
            </h2>
            <p class="text-gray-600">Create a new attendance session with a time-limited code</p>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <form action="create_session_handler.php" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                
                <div class="md:col-span-2">
                    <label class="block text-gray-700 font-semibold mb-2">Course *</label>
                    <select name="course_id" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-ashesi-maroon">
                        <option value="">Select a course</option>
                        <?php foreach ($courses as $course): ?>
                            <option value="<?php echo $course['course_id']; ?>">
                                <?php echo htmlspecialchars($course['course_code'] . ' - ' . $course['course_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-gray-700 font-semibold mb-2">Session Name *</label>
                    <input type="text" name="session_name" required 
                           placeholder="e.g., Week 5 Lecture"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-ashesi-maroon">
                </div>

                <div>
                    <label class="block text-gray-700 font-semibold mb-2">Code Duration (minutes) *</label>
                    <select name="code_duration" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-ashesi-maroon">
                        <option value="5">5 minutes</option>
                        <option value="10" selected>10 minutes</option>
                        <option value="15">15 minutes</option>
                        <option value="20">20 minutes</option>
                        <option value="30">30 minutes</option>
                    </select>
                </div>

                <div class="md:col-span-2 flex justify-end">
                    <button type="submit" class="bg-ashesi-maroon text-white px-8 py-3 rounded-lg hover:bg-red-900 transition font-semibold">
                        <i class="fas fa-play mr-2"></i>Start Session Now
                    </button>
                </div>
            </form>
        </div>

        <div class="mb-8">
            <h2 class="text-2xl font-bold text-gray-800 mb-4">
                <i class="fas fa-signal text-green-600 mr-3"></i>Live Sessions
                <span class="text-lg font-normal text-gray-600">(<?php echo count($activeSessions); ?> active)</span>
            </h2>
        </div>

        <?php if (empty($activeSessions)): ?>
            <div class="bg-white rounded-lg shadow-md p-12 text-center">
                <i class="fas fa-clock text-6xl text-gray-300 mb-4"></i>
                <p class="text-gray-500 text-lg">No active sessions. Start a new session above!</p>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <?php foreach ($activeSessions as $session): ?>
                    <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-green-500">
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <h3 class="text-xl font-bold text-gray-800"><?php echo htmlspecialchars($session['session_name']); ?></h3>
                                <p class="text-gray-600"><?php echo htmlspecialchars($session['course_code']); ?> - <?php echo htmlspecialchars($session['course_name']); ?></p>
                            </div>
                            <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm font-semibold">
                                <i class="fas fa-circle animate-pulse"></i> LIVE
                            </span>
                        </div>

                        <div class="bg-gray-50 rounded-lg p-4 mb-4">
                            <div class="flex justify-between items-center">
                                <div>
                                    <p class="text-sm text-gray-600 mb-1">Attendance Code</p>
                                    <p class="text-3xl font-bold text-ashesi-maroon tracking-wider"><?php echo htmlspecialchars($session['attendance_code']); ?></p>
                                </div>
                                <button onclick="copyCode('<?php echo htmlspecialchars($session['attendance_code']); ?>')" 
                                        class="bg-ashesi-maroon text-white px-4 py-2 rounded hover:bg-red-900 transition">
                                    <i class="fas fa-copy mr-2"></i>Copy
                                </button>
                            </div>
                        </div>

                        <div class="grid grid-cols-3 gap-4 mb-4">
                            <div class="text-center">
                                <p class="text-2xl font-bold text-gray-800"><?php echo $session['enrolled_count']; ?></p>
                                <p class="text-xs text-gray-600">Enrolled</p>
                            </div>
                            <div class="text-center">
                                <p class="text-2xl font-bold text-green-600"><?php echo $session['attendance_count']; ?></p>
                                <p class="text-xs text-gray-600">Marked</p>
                            </div>
                            <div class="text-center">
                                <p class="text-2xl font-bold <?php echo $session['minutes_remaining'] <= 5 ? 'text-red-600' : 'text-blue-600'; ?>">
                                    <?php echo max(0, $session['minutes_remaining']); ?>m
                                </p>
                                <p class="text-xs text-gray-600">Remaining</p>
                            </div>
                        </div>

                        <div class="flex gap-2">
                            <a href="view_attendance.php?session_id=<?php echo $session['session_id']; ?>" 
                               class="flex-1 bg-blue-600 text-white text-center px-4 py-2 rounded hover:bg-blue-700 transition">
                                <i class="fas fa-eye mr-2"></i>View
                            </a>
                            <button onclick="endSession(<?php echo $session['session_id']; ?>)" 
                                    class="flex-1 bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 transition">
                                <i class="fas fa-stop mr-2"></i>End
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($recentSessions)): ?>
            <div class="mb-8">
                <h2 class="text-2xl font-bold text-gray-800 mb-4">
                    <i class="fas fa-history text-gray-600 mr-3"></i>Today's Ended Sessions
                </h2>
            </div>

            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Session</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Course</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Time</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Attendance</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
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
                                    <span class="text-sm font-semibold"><?php echo $session['attendance_count']; ?> students</span>
                                </td>
                                <td class="px-6 py-4">
                                    <a href="view_attendance.php?session_id=<?php echo $session['session_id']; ?>" 
                                       class="text-blue-600 hover:text-blue-800">
                                        <i class="fas fa-eye mr-1"></i>View
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
