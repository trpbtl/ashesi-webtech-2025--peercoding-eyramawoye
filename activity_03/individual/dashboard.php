<?php
require_once 'config.php';
require_once 'helpers.php';

if (!isLoggedIn()) {
    header('Location: index.php?error=login_required');
    exit();
}

$userId = $_SESSION['user_id'];
$userRole = $_SESSION['role'];
$userName = $_SESSION['name'];
$userEmail = $_SESSION['email'];

$flashMessage = getFlashMessage();

$pdo = getDatabaseConnection();

$courses = [];
$attendanceStats = [];
$recentSessions = [];

if ($userRole === 'student') {


    $stmt = $pdo->prepare("
        SELECT c.course_id, c.course_code, c.course_name, c.semester, c.year,
               u.name as faculty_name
        FROM enrollments e
        JOIN courses c ON e.course_id = c.course_id
        LEFT JOIN users u ON c.faculty_id = u.user_id
        WHERE e.student_id = :student_id
        ORDER BY c.course_code
    ");
    $stmt->execute(['student_id' => $userId]);
    $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($courses as &$course) {
        $stmt = $pdo->prepare("
            SELECT 
                COUNT(*) as total_sessions,
                SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) as present_count,
                SUM(CASE WHEN a.status = 'absent' THEN 1 ELSE 0 END) as absent_count,
                SUM(CASE WHEN a.status = 'late' THEN 1 ELSE 0 END) as late_count
            FROM sessions s
            LEFT JOIN attendance a ON s.session_id = a.session_id AND a.student_id = :student_id
            WHERE s.course_id = :course_id
        ");
        $stmt->execute([
            'student_id' => $userId,
            'course_id' => $course['course_id']
        ]);
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);
        $course['stats'] = $stats;
        $course['attendance_percentage'] = $stats['total_sessions'] > 0 
            ? round(($stats['present_count'] / $stats['total_sessions']) * 100, 1) 
            : 0;
    }

    $stmt = $pdo->prepare("
        SELECT s.session_date, s.session_time, s.session_type, s.notes,
               c.course_code, c.course_name,
               a.status, a.marked_at
        FROM attendance a
        JOIN sessions s ON a.session_id = s.session_id
        JOIN courses c ON s.course_id = c.course_id
        WHERE a.student_id = :student_id
        ORDER BY s.session_date DESC, s.session_time DESC
        LIMIT 10
    ");
    $stmt->execute(['student_id' => $userId]);
    $recentSessions = $stmt->fetchAll(PDO::FETCH_ASSOC);
} elseif ($userRole === 'faculty') {


    $stmt = $pdo->prepare("
        SELECT course_id, course_code, course_name, semester, year, description
        FROM courses
        WHERE faculty_id = :faculty_id
        ORDER BY course_code
    ");
    $stmt->execute(['faculty_id' => $userId]);
    $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare("
        SELECT s.session_id, s.session_date, s.session_time, s.session_type, s.notes,
               c.course_code, c.course_name,
               COUNT(DISTINCT e.student_id) as total_students,
               COUNT(DISTINCT a.attendance_id) as marked_students
        FROM sessions s
        JOIN courses c ON s.course_id = c.course_id
        LEFT JOIN enrollments e ON c.course_id = e.course_id
        LEFT JOIN attendance a ON s.session_id = a.session_id
        WHERE c.faculty_id = :faculty_id
        GROUP BY s.session_id
        ORDER BY s.session_date DESC, s.session_time DESC
        LIMIT 10
    ");
    $stmt->execute(['faculty_id' => $userId]);
    $recentSessions = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getStatusBadge($status) {
    $badges = [
        'present' => '<span class="px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800"><i class="fas fa-check-circle mr-1"></i>Present</span>',
        'absent' => '<span class="px-3 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-800"><i class="fas fa-times-circle mr-1"></i>Absent</span>',
        'late' => '<span class="px-3 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800"><i class="fas fa-clock mr-1"></i>Late</span>'
    ];
    return $badges[$status] ?? '<span class="px-3 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-800">Unknown</span>';
}

function getSessionTypeBadge($type) {
    $badges = [
        'lecture' => '<span class="px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800"><i class="fas fa-book mr-1"></i>Lecture</span>',
        'lab' => '<span class="px-3 py-1 rounded-full text-xs font-semibold bg-orange-100 text-orange-800"><i class="fas fa-flask mr-1"></i>Lab</span>',
        'practical' => '<span class="px-3 py-1 rounded-full text-xs font-semibold bg-purple-100 text-purple-800"><i class="fas fa-laptop-code mr-1"></i>Practical</span>'
    ];
    return $badges[$type] ?? '<span class="px-3 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-800">Unknown</span>';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Ashesi Attendance Manager</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .card-hover {
            transition: all 0.3s ease;
        }
        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        .fade-in {
            animation: fadeIn 0.5s ease-in;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body class="bg-gradient-to-br from-red-50 to-gray-100 min-h-screen">
    <!-- Navigation Bar -->
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <!-- Logo and Title -->
                <div class="flex items-center">
                    <i class="fas fa-graduation-cap text-3xl text-red-600 mr-3"></i>
                    <div>
                        <h1 class="text-xl font-bold text-gray-800">Ashesi Attendance Manager</h1>
                        <p class="text-xs text-gray-600"><?php echo ucfirst($userRole); ?> Portal</p>
                    </div>
                </div>
                <!-- User Info and Logout -->
                <div class="flex items-center space-x-4">
                    <div class="text-right hidden sm:block">
                        <p class="text-sm font-semibold text-gray-800"><?php echo htmlspecialchars($userName); ?></p>
                        <p class="text-xs text-gray-600"><?php echo htmlspecialchars($userEmail); ?></p>
                    </div>
                    <a href="logout.php" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition duration-200">
                        <i class="fas fa-sign-out-alt mr-2"></i>Logout
                    </a>
                </div>
            </div>
        </div>
    </nav>
    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Flash Message -->
        <?php if ($flashMessage): ?>
            <div class="mb-6 p-4 rounded-lg fade-in <?php echo $flashMessage['type'] === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                <i class="fas <?php echo $flashMessage['type'] === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?> mr-2"></i>
                <?php echo htmlspecialchars($flashMessage['message']); ?>
            </div>
        <?php endif; ?>
        <!-- Welcome Section -->
        <div class="bg-white rounded-lg shadow-lg p-6 mb-8 fade-in">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-3xl font-bold text-gray-800">
                        Welcome back, <?php echo htmlspecialchars(explode(' ', $userName)[0]); ?>! 👋
                    </h2>
                    <p class="text-gray-600 mt-2">
                        <?php
                        if ($userRole === 'student') {
                            echo "Here's your attendance overview and recent activity.";
                        } elseif ($userRole === 'faculty') {
                            echo "Manage your courses and track student attendance.";
                        }
                        ?>
                    </p>
                </div>
                <i class="fas fa-user-circle text-6xl text-red-600 hidden md:block"></i>
            </div>
        </div>
        <?php if ($userRole === 'student'): ?>
            <!-- STUDENT DASHBOARD -->
            <!-- Course Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                <?php foreach ($courses as $course): ?>
                    <div class="bg-white rounded-lg shadow-lg p-6 card-hover fade-in">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-xl font-bold text-gray-800"><?php echo htmlspecialchars($course['course_code']); ?></h3>
                            <i class="fas fa-book text-3xl text-red-600"></i>
                        </div>
                        <p class="text-gray-600 mb-4"><?php echo htmlspecialchars($course['course_name']); ?></p>
                        <!-- Attendance Percentage -->
                        <div class="mb-4">
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-sm font-semibold text-gray-700">Attendance</span>
                                <span class="text-lg font-bold <?php echo $course['attendance_percentage'] >= 75 ? 'text-green-600' : 'text-red-600'; ?>">
                                    <?php echo $course['attendance_percentage']; ?>%
                                </span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="h-2 rounded-full <?php echo $course['attendance_percentage'] >= 75 ? 'bg-green-500' : 'bg-red-500'; ?>" 
                                     style="width: <?php echo $course['attendance_percentage']; ?>%"></div>
                            </div>
                        </div>
                        <!-- Stats -->
                        <div class="grid grid-cols-3 gap-2 text-center text-sm">
                            <div class="bg-green-50 rounded p-2">
                                <p class="font-bold text-green-700"><?php echo $course['stats']['present_count']; ?></p>
                                <p class="text-xs text-gray-600">Present</p>
                            </div>
                            <div class="bg-red-50 rounded p-2">
                                <p class="font-bold text-red-700"><?php echo $course['stats']['absent_count']; ?></p>
                                <p class="text-xs text-gray-600">Absent</p>
                            </div>
                            <div class="bg-yellow-50 rounded p-2">
                                <p class="font-bold text-yellow-700"><?php echo $course['stats']['late_count']; ?></p>
                                <p class="text-xs text-gray-600">Late</p>
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 mt-4">
                            <i class="fas fa-user mr-1"></i>
                            Instructor: <?php echo htmlspecialchars($course['faculty_name'] ?? 'TBA'); ?>
                        </p>
                    </div>
                <?php endforeach; ?>
                <?php if (empty($courses)): ?>
                    <div class="col-span-full bg-yellow-50 border border-yellow-200 rounded-lg p-6 text-center">
                        <i class="fas fa-info-circle text-4xl text-yellow-600 mb-3"></i>
                        <p class="text-gray-700">You are not enrolled in any courses yet.</p>
                    </div>
                <?php endif; ?>
            </div>
            <!-- Recent Attendance -->
            <div class="bg-white rounded-lg shadow-lg p-6 mb-8 fade-in">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-2xl font-bold text-gray-800">
                        <i class="fas fa-history text-red-600 mr-2"></i>
                        Recent Attendance
                    </h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Date</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Course</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Type</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Status</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Notes</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php foreach ($recentSessions as $session): ?>
                                <tr class="hover:bg-gray-50 transition duration-150">
                                    <td class="px-4 py-3 text-sm text-gray-800">
                                        <?php echo date('M d, Y', strtotime($session['session_date'])); ?>
                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        <p class="font-semibold text-gray-800"><?php echo htmlspecialchars($session['course_code']); ?></p>
                                        <p class="text-xs text-gray-600"><?php echo htmlspecialchars($session['course_name']); ?></p>
                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        <?php echo getSessionTypeBadge($session['session_type']); ?>
                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        <?php echo getStatusBadge($session['status']); ?>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-600">
                                        <?php echo $session['notes'] ? htmlspecialchars($session['notes']) : '-'; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($recentSessions)): ?>
                                <tr>
                                    <td colspan="5" class="px-4 py-8 text-center text-gray-500">
                                        <i class="fas fa-inbox text-4xl mb-3"></i>
                                        <p>No attendance records yet.</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <!-- Action Button -->
            <div class="text-center">
                <a href="report_issue.php" class="inline-block bg-red-600 text-white px-8 py-3 rounded-lg hover:bg-red-700 transition duration-200 font-semibold shadow-lg">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    Report Attendance Issue
                </a>
            </div>
        <?php elseif ($userRole === 'faculty'): ?>
            <!-- FACULTY DASHBOARD -->
            <!-- Course Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                <?php foreach ($courses as $course): ?>
                    <div class="bg-white rounded-lg shadow-lg p-6 card-hover fade-in">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-xl font-bold text-gray-800"><?php echo htmlspecialchars($course['course_code']); ?></h3>
                            <i class="fas fa-chalkboard-teacher text-3xl text-red-600"></i>
                        </div>
                        <p class="text-gray-600 mb-4"><?php echo htmlspecialchars($course['course_name']); ?></p>
                        <p class="text-sm text-gray-500">
                            <i class="fas fa-calendar mr-1"></i>
                            <?php echo htmlspecialchars($course['semester'] . ' ' . $course['year']); ?>
                        </p>
                        <div class="mt-4 pt-4 border-t">
                            <button class="w-full bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 transition duration-200">
                                <i class="fas fa-eye mr-2"></i>View Sessions
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
                <?php if (empty($courses)): ?>
                    <div class="col-span-full bg-yellow-50 border border-yellow-200 rounded-lg p-6 text-center">
                        <i class="fas fa-info-circle text-4xl text-yellow-600 mb-3"></i>
                        <p class="text-gray-700">No courses assigned yet.</p>
                    </div>
                <?php endif; ?>
            </div>
            <!-- Recent Sessions -->
            <div class="bg-white rounded-lg shadow-lg p-6 fade-in">
                <h3 class="text-2xl font-bold text-gray-800 mb-6">
                    <i class="fas fa-calendar-alt text-red-600 mr-2"></i>
                    Recent Sessions
                </h3>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Date</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Course</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Type</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Attendance</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Notes</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php foreach ($recentSessions as $session): ?>
                                <tr class="hover:bg-gray-50 transition duration-150">
                                    <td class="px-4 py-3 text-sm text-gray-800">
                                        <?php echo date('M d, Y', strtotime($session['session_date'])); ?>
                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        <p class="font-semibold text-gray-800"><?php echo htmlspecialchars($session['course_code']); ?></p>
                                        <p class="text-xs text-gray-600"><?php echo htmlspecialchars($session['course_name']); ?></p>
                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        <?php echo getSessionTypeBadge($session['session_type']); ?>
                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        <span class="font-semibold text-gray-800">
                                            <?php echo $session['marked_students']; ?> / <?php echo $session['total_students']; ?>
                                        </span>
                                        <span class="text-xs text-gray-600">marked</span>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-600">
                                        <?php echo $session['notes'] ? htmlspecialchars($session['notes']) : '-'; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($recentSessions)): ?>
                                <tr>
                                    <td colspan="5" class="px-4 py-8 text-center text-gray-500">
                                        <i class="fas fa-inbox text-4xl mb-3"></i>
                                        <p>No sessions yet.</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </main>
    <!-- Footer -->
    <footer class="bg-white shadow-lg mt-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="text-center text-gray-600">
                <p>&copy; <?php echo date('Y'); ?> Ashesi University. All rights reserved.</p>
                <p class="text-sm mt-1">Attendance Management System v1.0</p>
            </div>
        </div>
    </footer>
</body>
</html>