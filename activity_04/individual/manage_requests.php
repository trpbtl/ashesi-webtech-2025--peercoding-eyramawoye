<?php
require_once 'config.php';
require_once 'helpers.php';

requireRole('faculty');

$flashMessage = getFlashMessage();
$facultyId = $_SESSION['user_id'];
$userName = $_SESSION['name'];

$pdo = getDatabaseConnection();

// Get all pending requests for faculty's courses
$stmt = $pdo->prepare("
    SELECT cr.request_id, cr.requested_at, cr.status,
           c.course_id, c.course_code, c.course_name,
           u.user_id as student_id, u.name as student_name, u.email as student_email, u.ashesi_id
    FROM course_requests cr
    JOIN courses c ON cr.course_id = c.course_id
    JOIN users u ON cr.student_id = u.user_id
    WHERE c.faculty_id = :faculty_id AND cr.status = 'pending'
    ORDER BY cr.requested_at ASC
");
$stmt->execute(['faculty_id' => $facultyId]);
$pendingRequests = $stmt->fetchAll();

// Group requests by course
$requestsByCourse = [];
foreach ($pendingRequests as $request) {
    $courseKey = $request['course_id'];
    if (!isset($requestsByCourse[$courseKey])) {
        $requestsByCourse[$courseKey] = [
            'course_code' => $request['course_code'],
            'course_name' => $request['course_name'],
            'requests' => []
        ];
    }
    $requestsByCourse[$courseKey]['requests'][] = $request;
}

// Get recently processed requests (last 10)
$stmt = $pdo->prepare("
    SELECT cr.request_id, cr.requested_at, cr.reviewed_at, cr.status, cr.comments,
           c.course_code, c.course_name,
           u.name as student_name, u.ashesi_id
    FROM course_requests cr
    JOIN courses c ON cr.course_id = c.course_id
    JOIN users u ON cr.student_id = u.user_id
    WHERE c.faculty_id = :faculty_id AND cr.status IN ('approved', 'rejected')
    ORDER BY cr.reviewed_at DESC
    LIMIT 10
");
$stmt->execute(['faculty_id' => $facultyId]);
$processedRequests = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Requests - Faculty</title>
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
        <div class="mb-8">
            <h2 class="text-3xl font-bold text-gray-800">
                <i class="fas fa-tasks text-ashesi-maroon mr-3"></i>Manage Course Requests
            </h2>
            <p class="text-gray-600 mt-2">Review and approve student requests to join your courses</p>
        </div>

        <?php if ($flashMessage): ?>
            <div class="mb-6 p-4 rounded-lg <?php echo $flashMessage['type'] === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                <i class="fas <?php echo $flashMessage['type'] === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?> mr-2"></i>
                <?php echo htmlspecialchars($flashMessage['message']); ?>
            </div>
        <?php endif; ?>

        <!-- Pending Requests -->
        <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-2xl font-bold text-gray-800">
                    <i class="fas fa-clock text-yellow-500 mr-2"></i>Pending Requests
                    <span class="ml-2 bg-yellow-100 text-yellow-800 text-sm font-semibold px-3 py-1 rounded-full">
                        <?php echo count($pendingRequests); ?>
                    </span>
                </h3>
            </div>

            <?php if (empty($pendingRequests)): ?>
                <div class="text-center py-12">
                    <i class="fas fa-check-circle text-6xl text-green-300 mb-4"></i>
                    <p class="text-gray-600 text-lg">No pending requests</p>
                    <p class="text-gray-500 text-sm mt-2">You're all caught up!</p>
                </div>
            <?php else: ?>
                <?php foreach ($requestsByCourse as $courseData): ?>
                    <div class="mb-8 border border-gray-200 rounded-lg p-6">
                        <h4 class="text-xl font-bold text-ashesi-maroon mb-4">
                            <?php echo htmlspecialchars($courseData['course_code']); ?> - <?php echo htmlspecialchars($courseData['course_name']); ?>
                            <span class="ml-2 text-sm bg-red-100 text-red-800 px-3 py-1 rounded-full">
                                <?php echo count($courseData['requests']); ?> request(s)
                            </span>
                        </h4>

                        <div class="space-y-4">
                            <?php foreach ($courseData['requests'] as $request): ?>
                                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                                    <div class="flex items-start justify-between">
                                        <div class="flex-1">
                                            <div class="flex items-center mb-2">
                                                <i class="fas fa-user-circle text-2xl text-gray-400 mr-3"></i>
                                                <div>
                                                    <h5 class="font-semibold text-gray-800"><?php echo htmlspecialchars($request['student_name']); ?></h5>
                                                    <p class="text-sm text-gray-600"><?php echo htmlspecialchars($request['student_email']); ?></p>
                                                    <p class="text-xs text-gray-500">ID: <?php echo htmlspecialchars($request['ashesi_id']); ?></p>
                                                </div>
                                            </div>
                                            <p class="text-xs text-gray-500 ml-11">
                                                <i class="fas fa-calendar mr-1"></i>
                                                Requested: <?php echo formatDate($request['requested_at'], 'M d, Y g:i A'); ?>
                                            </p>
                                        </div>

                                        <div class="flex space-x-2 ml-4">
                                            <form action="process_request.php" method="POST" class="inline">
                                                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                                <input type="hidden" name="request_id" value="<?php echo $request['request_id']; ?>">
                                                <input type="hidden" name="action" value="approve">
                                                <button 
                                                    type="submit"
                                                    class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition duration-200 text-sm font-semibold"
                                                    onclick="return confirm('Approve this student for <?php echo htmlspecialchars($courseData['course_code']); ?>?')"
                                                >
                                                    <i class="fas fa-check mr-1"></i>Approve
                                                </button>
                                            </form>

                                            <button 
                                                type="button"
                                                onclick="showRejectModal(<?php echo $request['request_id']; ?>, '<?php echo htmlspecialchars($request['student_name']); ?>', '<?php echo htmlspecialchars($courseData['course_code']); ?>')"
                                                class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition duration-200 text-sm font-semibold"
                                            >
                                                <i class="fas fa-times mr-1"></i>Reject
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Recently Processed Requests -->
        <?php if (!empty($processedRequests)): ?>
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h3 class="text-2xl font-bold text-gray-800 mb-6">
                    <i class="fas fa-history text-gray-600 mr-2"></i>Recent Actions
                </h3>
                <div class="space-y-3">
                    <?php foreach ($processedRequests as $request): ?>
                        <div class="border-l-4 <?php echo $request['status'] === 'approved' ? 'border-green-500 bg-green-50' : 'border-red-500 bg-red-50'; ?> p-4 rounded">
                            <div class="flex justify-between items-start">
                                <div>
                                    <p class="font-semibold text-gray-800"><?php echo htmlspecialchars($request['student_name']); ?> (<?php echo htmlspecialchars($request['ashesi_id']); ?>)</p>
                                    <p class="text-sm text-gray-600"><?php echo htmlspecialchars($request['course_code']); ?> - <?php echo htmlspecialchars($request['course_name']); ?></p>
                                    <p class="text-xs text-gray-500 mt-1">
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

    <!-- Reject Modal -->
    <div id="rejectModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-lg bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-bold text-gray-900 mb-4">
                    <i class="fas fa-times-circle text-red-600 mr-2"></i>Reject Request
                </h3>
                <form id="rejectForm" action="process_request.php" method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <input type="hidden" name="request_id" id="reject_request_id">
                    <input type="hidden" name="action" value="reject">
                    
                    <p class="text-sm text-gray-600 mb-4">
                        Student: <strong id="reject_student_name"></strong><br>
                        Course: <strong id="reject_course_code"></strong>
                    </p>

                    <label for="comments" class="block text-sm font-medium text-gray-700 mb-2">
                        Reason for Rejection (Optional)
                    </label>
                    <textarea 
                        name="comments" 
                        id="comments"
                        rows="3"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500"
                        placeholder="Provide a reason for rejection..."
                    ></textarea>

                    <div class="flex justify-end space-x-3 mt-6">
                        <button 
                            type="button"
                            onclick="closeRejectModal()"
                            class="px-4 py-2 bg-gray-300 text-gray-800 rounded-lg hover:bg-gray-400"
                        >
                            Cancel
                        </button>
                        <button 
                            type="submit"
                            class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700"
                        >
                            <i class="fas fa-times mr-1"></i>Reject Request
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function showRejectModal(requestId, studentName, courseCode) {
            document.getElementById('reject_request_id').value = requestId;
            document.getElementById('reject_student_name').textContent = studentName;
            document.getElementById('reject_course_code').textContent = courseCode;
            document.getElementById('rejectModal').classList.remove('hidden');
        }

        function closeRejectModal() {
            document.getElementById('rejectModal').classList.add('hidden');
            document.getElementById('comments').value = '';
        }
    </script>
</body>
</html>
