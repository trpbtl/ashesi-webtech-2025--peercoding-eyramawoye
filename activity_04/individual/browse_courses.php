<?php
require_once 'config.php';
require_once 'helpers.php';

requireRole('student');

$flashMessage = getFlashMessage();
$studentId = $_SESSION['user_id'];
$userName = $_SESSION['name'];

$pdo = getDatabaseConnection();

// Get courses the student is NOT enrolled in and has NOT requested
$stmt = $pdo->prepare("
    SELECT c.course_id, c.course_code, c.course_name, c.semester, c.year, c.description,
           u.name as faculty_name
    FROM courses c
    LEFT JOIN users u ON c.faculty_id = u.user_id
    WHERE c.course_id NOT IN (
        SELECT course_id FROM enrollments WHERE student_id = :student_id1
    )
    AND c.course_id NOT IN (
        SELECT course_id FROM course_requests 
        WHERE student_id = :student_id2 AND status = 'pending'
    )
    ORDER BY c.course_code
");
$stmt->execute(['student_id1' => $studentId, 'student_id2' => $studentId]);
$availableCourses = $stmt->fetchAll();

// Get pending requests
$stmt = $pdo->prepare("
    SELECT cr.request_id, cr.requested_at, cr.status,
           c.course_code, c.course_name, c.semester, c.year,
           u.name as faculty_name
    FROM course_requests cr
    JOIN courses c ON cr.course_id = c.course_id
    LEFT JOIN users u ON c.faculty_id = u.user_id
    WHERE cr.student_id = :student_id AND cr.status = 'pending'
    ORDER BY cr.requested_at DESC
");
$stmt->execute(['student_id' => $studentId]);
$pendingRequests = $stmt->fetchAll();

// Get approved/rejected requests (recent 5)
$stmt = $pdo->prepare("
    SELECT cr.request_id, cr.requested_at, cr.reviewed_at, cr.status, cr.comments,
           c.course_code, c.course_name,
           u.name as faculty_name
    FROM course_requests cr
    JOIN courses c ON cr.course_id = c.course_id
    LEFT JOIN users u ON c.faculty_id = u.user_id
    WHERE cr.student_id = :student_id AND cr.status IN ('approved', 'rejected')
    ORDER BY cr.reviewed_at DESC
    LIMIT 5
");
$stmt->execute(['student_id' => $studentId]);
$processedRequests = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Courses - Student</title>
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
<body class="bg-gray-50 min-h-screen">
    <nav class="bg-ashesi-maroon text-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center">
                    <i class="fas fa-graduation-cap text-3xl mr-3"></i>
                    <div>
                        <h1 class="text-xl font-bold">Course Management</h1>
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
        <div class="mb-8">
            <h2 class="text-3xl font-bold text-gray-800">
                <i class="fas fa-search text-ashesi-maroon mr-3"></i>Browse Courses
            </h2>
            <p class="text-gray-600 mt-2">Request to join courses offered this semester</p>
        </div>

        <?php if ($flashMessage): ?>
            <div class="mb-6 p-4 rounded-lg <?php echo $flashMessage['type'] === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                <i class="fas <?php echo $flashMessage['type'] === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?> mr-2"></i>
                <?php echo htmlspecialchars($flashMessage['message']); ?>
            </div>
        <?php endif; ?>

        <!-- Pending Requests -->
        <?php if (!empty($pendingRequests)): ?>
            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-6 mb-8 rounded-lg">
                <h3 class="text-xl font-bold text-yellow-800 mb-4">
                    <i class="fas fa-clock mr-2"></i>Pending Requests (<?php echo count($pendingRequests); ?>)
                </h3>
                <div class="space-y-3">
                    <?php foreach ($pendingRequests as $request): ?>
                        <div class="bg-white p-4 rounded-lg shadow">
                            <div class="flex justify-between items-center">
                                <div>
                                    <h4 class="font-semibold text-gray-800"><?php echo htmlspecialchars($request['course_code']); ?> - <?php echo htmlspecialchars($request['course_name']); ?></h4>
                                    <p class="text-sm text-gray-600">Instructor: <?php echo htmlspecialchars($request['faculty_name']); ?></p>
                                    <p class="text-xs text-gray-500 mt-1">Requested: <?php echo formatDate($request['requested_at'], 'M d, Y g:i A'); ?></p>
                                </div>
                                <span class="px-4 py-2 bg-yellow-100 text-yellow-800 rounded-full text-sm font-semibold">
                                    <i class="fas fa-hourglass-half mr-1"></i>Awaiting Approval
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Available Courses -->
        <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
            <h3 class="text-2xl font-bold text-gray-800 mb-6">
                <i class="fas fa-book-open text-ashesi-maroon mr-2"></i>Available Courses
            </h3>

            <?php if (empty($availableCourses)): ?>
                <div class="text-center py-12">
                    <i class="fas fa-inbox text-6xl text-gray-300 mb-4"></i>
                    <p class="text-gray-600 text-lg">No available courses at the moment</p>
                    <p class="text-gray-500 text-sm mt-2">You're either enrolled in all courses or have pending requests for them</p>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach ($availableCourses as $course): ?>
                        <div class="border border-gray-200 rounded-lg p-6 hover:shadow-lg transition duration-200">
                            <div class="flex items-start justify-between mb-4">
                                <div>
                                    <h4 class="text-xl font-bold text-gray-800"><?php echo htmlspecialchars($course['course_code']); ?></h4>
                                    <p class="text-sm text-gray-500"><?php echo htmlspecialchars($course['semester'] . ' ' . $course['year']); ?></p>
                                </div>
                                <i class="fas fa-book text-3xl text-ashesi-maroon"></i>
                            </div>
                            
                            <h5 class="font-semibold text-gray-700 mb-2"><?php echo htmlspecialchars($course['course_name']); ?></h5>
                            
                            <?php if ($course['description']): ?>
                                <p class="text-sm text-gray-600 mb-4 line-clamp-3"><?php echo htmlspecialchars($course['description']); ?></p>
                            <?php endif; ?>
                            
                            <div class="mb-4 pb-4 border-b">
                                <p class="text-sm text-gray-600">
                                    <i class="fas fa-chalkboard-teacher text-ashesi-maroon mr-2"></i>
                                    <?php echo htmlspecialchars($course['faculty_name'] ?? 'TBA'); ?>
                                </p>
                            </div>
                            
                            <form action="request_join_handler.php" method="POST">
                                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                <input type="hidden" name="course_id" value="<?php echo $course['course_id']; ?>">
                                <button 
                                    type="submit"
                                    class="w-full bg-ashesi-maroon text-white py-2 rounded-lg hover:bg-red-900 transition duration-200 font-semibold"
                                >
                                    <i class="fas fa-paper-plane mr-2"></i>Request to Join
                                </button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Recent Request History -->
        <?php if (!empty($processedRequests)): ?>
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h3 class="text-2xl font-bold text-gray-800 mb-6">
                    <i class="fas fa-history text-gray-600 mr-2"></i>Recent Request History
                </h3>
                <div class="space-y-4">
                    <?php foreach ($processedRequests as $request): ?>
                        <div class="border-l-4 <?php echo $request['status'] === 'approved' ? 'border-green-500 bg-green-50' : 'border-red-500 bg-red-50'; ?> p-4 rounded">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h4 class="font-semibold text-gray-800"><?php echo htmlspecialchars($request['course_code']); ?> - <?php echo htmlspecialchars($request['course_name']); ?></h4>
                                    <p class="text-sm text-gray-600">Instructor: <?php echo htmlspecialchars($request['faculty_name']); ?></p>
                                    <p class="text-xs text-gray-500 mt-2">
                                        Reviewed: <?php echo formatDate($request['reviewed_at'], 'M d, Y g:i A'); ?>
                                    </p>
                                    <?php if ($request['comments']): ?>
                                        <p class="text-sm text-gray-700 mt-2 italic">"<?php echo htmlspecialchars($request['comments']); ?>"</p>
                                    <?php endif; ?>
                                </div>
                                <?php echo getStatusBadge($request['status']); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
