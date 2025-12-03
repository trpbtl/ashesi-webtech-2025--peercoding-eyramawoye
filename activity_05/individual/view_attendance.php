<?php
require_once 'config.php';
require_once 'helpers.php';

requireLogin();

$sessionId = $_GET['session_id'] ?? null;

if (!$sessionId) {
    redirect('dashboard.php', 'Invalid session', 'error');
}

$pdo = getDatabaseConnection();

$isFaculty = $_SESSION['role'] === 'faculty';

if ($isFaculty) {
    $stmt = $pdo->prepare("
        SELECT ats.*, c.course_code, c.course_name
        FROM attendance_sessions ats
        JOIN courses c ON ats.course_id = c.course_id
        WHERE ats.session_id = :session_id AND c.faculty_id = :faculty_id
    ");
    $stmt->execute(['session_id' => $sessionId, 'faculty_id' => $_SESSION['user_id']]);
} else {
    $stmt = $pdo->prepare("
        SELECT ats.*, c.course_code, c.course_name
        FROM attendance_sessions ats
        JOIN courses c ON ats.course_id = c.course_id
        JOIN enrollments e ON c.course_id = e.course_id
        WHERE ats.session_id = :session_id AND e.student_id = :student_id AND e.status = 'active'
    ");
    $stmt->execute(['session_id' => $sessionId, 'student_id' => $_SESSION['user_id']]);
}

$session = $stmt->fetch();

if (!$session) {
    redirect('dashboard.php', 'Session not found or access denied', 'error');
}

$stmt = $pdo->prepare("
    SELECT u.user_id, u.name, u.ashesi_id, ar.status, ar.marked_at, ar.marked_by_code
    FROM enrollments e
    JOIN users u ON e.student_id = u.user_id
    LEFT JOIN attendance_records ar ON e.student_id = ar.student_id AND ar.session_id = :session_id
    WHERE e.course_id = :course_id AND e.status = 'active'
    ORDER BY ar.status DESC, u.name
");
$stmt->execute(['session_id' => $sessionId, 'course_id' => $session['course_id']]);
$records = $stmt->fetchAll();

$presentCount = count(array_filter($records, fn($r) => $r['status'] === 'present'));
$lateCount = count(array_filter($records, fn($r) => $r['status'] === 'late'));
$absentCount = count(array_filter($records, fn($r) => !$r['status'] || $r['status'] === 'absent'));
$totalStudents = count($records);
$attendanceRate = $totalStudents > 0 ? round((($presentCount + $lateCount) / $totalStudents) * 100, 1) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Records - <?php echo htmlspecialchars($session['session_name']); ?></title>
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
                <a href="<?php echo $isFaculty ? 'view_sessions.php?course_id=' . $session['course_id'] : 'student_dashboard.php'; ?>" class="hover:text-gray-200">
                    <i class="fas fa-arrow-left mr-2"></i>Back
                </a>
                <h1 class="text-xl font-bold">Attendance Records</h1>
            </div>
            <span><i class="fas fa-user mr-2"></i><?php echo htmlspecialchars($_SESSION['name']); ?></span>
        </div>
    </nav>

    <div class="container mx-auto p-6 max-w-6xl">
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-2xl font-bold text-gray-800 mb-2"><?php echo htmlspecialchars($session['session_name']); ?></h2>
            <p class="text-gray-600 mb-4"><?php echo htmlspecialchars($session['course_code'] . ' - ' . $session['course_name']); ?></p>
            <div class="flex items-center space-x-6 text-sm text-gray-600">
                <span><i class="fas fa-calendar mr-2"></i><?php echo date('M d, Y', strtotime($session['session_date'])); ?></span>
                <span><i class="fas fa-clock mr-2"></i><?php echo date('g:i A', strtotime($session['start_time'])); ?></span>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white p-4 rounded-lg shadow-md">
                <div class="text-center">
                    <p class="text-gray-600 text-sm">Total Students</p>
                    <p class="text-3xl font-bold text-gray-800"><?php echo $totalStudents; ?></p>
                </div>
            </div>
            <div class="bg-green-50 p-4 rounded-lg shadow-md">
                <div class="text-center">
                    <p class="text-green-600 text-sm font-semibold">Present</p>
                    <p class="text-3xl font-bold text-green-700"><?php echo $presentCount; ?></p>
                </div>
            </div>
            <div class="bg-yellow-50 p-4 rounded-lg shadow-md">
                <div class="text-center">
                    <p class="text-yellow-600 text-sm font-semibold">Late</p>
                    <p class="text-3xl font-bold text-yellow-700"><?php echo $lateCount; ?></p>
                </div>
            </div>
            <div class="bg-red-50 p-4 rounded-lg shadow-md">
                <div class="text-center">
                    <p class="text-red-600 text-sm font-semibold">Absent</p>
                    <p class="text-3xl font-bold text-red-700"><?php echo $absentCount; ?></p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-4 mb-6">
            <div class="flex items-center justify-between">
                <span class="text-gray-700 font-semibold">Attendance Rate</span>
                <span class="text-2xl font-bold <?php echo $attendanceRate >= 75 ? 'text-green-600' : 'text-yellow-600'; ?>">
                    <?php echo $attendanceRate; ?>%
                </span>
            </div>
            <div class="mt-2 w-full bg-gray-200 rounded-full h-3">
                <div class="<?php echo $attendanceRate >= 75 ? 'bg-green-600' : 'bg-yellow-600'; ?> h-3 rounded-full" 
                     style="width: <?php echo $attendanceRate; ?>%"></div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ashesi ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Marked At</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Method</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($records as $record): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($record['name']); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900"><?php echo htmlspecialchars($record['ashesi_id']); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php if ($record['status'] === 'present'): ?>
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        <i class="fas fa-check-circle mr-1"></i>Present
                                    </span>
                                <?php elseif ($record['status'] === 'late'): ?>
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                        <i class="fas fa-exclamation-circle mr-1"></i>Late
                                    </span>
                                <?php else: ?>
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                        <i class="fas fa-times-circle mr-1"></i>Absent
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo $record['marked_at'] ? date('M d, g:i A', strtotime($record['marked_at'])) : '-'; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php if ($record['marked_at']): ?>
                                    <?php if ($record['marked_by_code']): ?>
                                        <span class="text-blue-600"><i class="fas fa-keyboard mr-1"></i>Code</span>
                                    <?php else: ?>
                                        <span class="text-purple-600"><i class="fas fa-user-check mr-1"></i>Manual</span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
