<?php
require_once 'config.php';
require_once 'helpers.php';

requireRole('faculty');

$sessionId = $_GET['session_id'] ?? null;

if (!$sessionId) {
    redirect('faculty_dashboard.php', 'Invalid session', 'error');
}

$pdo = getDatabaseConnection();

$stmt = $pdo->prepare("
    SELECT ats.*, c.course_code, c.course_name
    FROM attendance_sessions ats
    JOIN courses c ON ats.course_id = c.course_id
    WHERE ats.session_id = :session_id AND c.faculty_id = :faculty_id
");
$stmt->execute(['session_id' => $sessionId, 'faculty_id' => $_SESSION['user_id']]);
$session = $stmt->fetch();

if (!$session) {
    redirect('faculty_dashboard.php', 'Session not found or access denied', 'error');
}

$stmt = $pdo->prepare("
    SELECT u.user_id, u.name, u.ashesi_id, ar.record_id, ar.status, ar.marked_at
    FROM enrollments e
    JOIN users u ON e.student_id = u.user_id
    LEFT JOIN attendance_records ar ON e.student_id = ar.student_id AND ar.session_id = :session_id
    WHERE e.course_id = :course_id AND e.status = 'active'
    ORDER BY u.name
");
$stmt->execute(['session_id' => $sessionId, 'course_id' => $session['course_id']]);
$students = $stmt->fetchAll();

$flashMessage = getFlashMessage();
$csrfToken = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mark Attendance - <?php echo htmlspecialchars($session['session_name']); ?></title>
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
                <a href="view_sessions.php?course_id=<?php echo $session['course_id']; ?>" class="hover:text-gray-200">
                    <i class="fas fa-arrow-left mr-2"></i>Back
                </a>
                <h1 class="text-xl font-bold">Mark Attendance</h1>
            </div>
            <span><i class="fas fa-user mr-2"></i><?php echo htmlspecialchars($_SESSION['name']); ?></span>
        </div>
    </nav>

    <div class="container mx-auto p-6 max-w-4xl">
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-2xl font-bold text-gray-800 mb-2"><?php echo htmlspecialchars($session['session_name']); ?></h2>
            <p class="text-gray-600 mb-4"><?php echo htmlspecialchars($session['course_code'] . ' - ' . $session['course_name']); ?></p>
            <div class="flex items-center space-x-6 text-sm text-gray-600">
                <span><i class="fas fa-calendar mr-2"></i><?php echo date('M d, Y', strtotime($session['session_date'])); ?></span>
                <span><i class="fas fa-clock mr-2"></i><?php echo date('g:i A', strtotime($session['start_time'])); ?></span>
                <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded font-semibold">
                    Code: <?php echo htmlspecialchars($session['attendance_code']); ?>
                </span>
            </div>
        </div>

        <?php if ($flashMessage): ?>
            <div class="mb-6 p-4 rounded-lg <?php echo $flashMessage['type'] === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                <i class="fas <?php echo $flashMessage['type'] === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?> mr-2"></i>
                <?php echo htmlspecialchars($flashMessage['message']); ?>
            </div>
        <?php endif; ?>

        <form action="mark_attendance_save.php" method="POST" class="bg-white rounded-lg shadow-md p-6">
            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
            <input type="hidden" name="session_id" value="<?php echo $sessionId; ?>">

            <div class="mb-4 flex justify-between items-center">
                <h3 class="text-lg font-bold">Students (<?php echo count($students); ?>)</h3>
                <div class="space-x-2">
                    <button type="button" onclick="markAll('present')" class="bg-green-600 text-white px-3 py-1 rounded text-sm hover:bg-green-700">
                        <i class="fas fa-check-circle mr-1"></i>All Present
                    </button>
                    <button type="button" onclick="markAll('absent')" class="bg-red-600 text-white px-3 py-1 rounded text-sm hover:bg-red-700">
                        <i class="fas fa-times-circle mr-1"></i>All Absent
                    </button>
                </div>
            </div>

            <div class="space-y-2 max-h-96 overflow-y-auto">
                <?php foreach ($students as $student): ?>
                    <div class="border border-gray-200 rounded p-3 flex items-center justify-between hover:bg-gray-50">
                        <div class="flex-1">
                            <p class="font-semibold text-gray-800"><?php echo htmlspecialchars($student['name']); ?></p>
                            <p class="text-sm text-gray-500"><?php echo htmlspecialchars($student['ashesi_id']); ?></p>
                        </div>
                        <div class="flex space-x-2">
                            <label class="inline-flex items-center cursor-pointer">
                                <input 
                                    type="radio" 
                                    name="attendance[<?php echo $student['user_id']; ?>]" 
                                    value="present" 
                                    <?php echo $student['status'] === 'present' ? 'checked' : ''; ?>
                                    class="form-radio text-green-600 h-5 w-5"
                                >
                                <span class="ml-2 text-sm text-gray-700">Present</span>
                            </label>
                            <label class="inline-flex items-center cursor-pointer">
                                <input 
                                    type="radio" 
                                    name="attendance[<?php echo $student['user_id']; ?>]" 
                                    value="late" 
                                    <?php echo $student['status'] === 'late' ? 'checked' : ''; ?>
                                    class="form-radio text-yellow-600 h-5 w-5"
                                >
                                <span class="ml-2 text-sm text-gray-700">Late</span>
                            </label>
                            <label class="inline-flex items-center cursor-pointer">
                                <input 
                                    type="radio" 
                                    name="attendance[<?php echo $student['user_id']; ?>]" 
                                    value="absent" 
                                    <?php echo (!$student['status'] || $student['status'] === 'absent') ? 'checked' : ''; ?>
                                    class="form-radio text-red-600 h-5 w-5"
                                >
                                <span class="ml-2 text-sm text-gray-700">Absent</span>
                            </label>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="mt-6 flex space-x-4">
                <button 
                    type="submit" 
                    class="flex-1 bg-ashesi-maroon text-white py-3 rounded-lg hover:bg-red-900 transition duration-200 font-semibold"
                >
                    <i class="fas fa-save mr-2"></i>Save Attendance
                </button>
                <a 
                    href="view_sessions.php?course_id=<?php echo $session['course_id']; ?>" 
                    class="flex-1 bg-gray-500 text-white py-3 rounded-lg hover:bg-gray-600 transition duration-200 font-semibold text-center"
                >
                    <i class="fas fa-times mr-2"></i>Cancel
                </a>
            </div>
        </form>
    </div>

    <script>
        function markAll(status) {
            const radios = document.querySelectorAll(`input[type="radio"][value="${status}"]`);
            radios.forEach(radio => radio.checked = true);
        }
    </script>
</body>
</html>
