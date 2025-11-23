<?php
require_once 'config.php';
require_once 'helpers.php';

requireLogin();

$userId = $_SESSION['user_id'];
$userRole = $_SESSION['role'];
$userName = $_SESSION['name'];
$userEmail = $_SESSION['email'];
$flashMessage = getFlashMessage();

$pdo = getDatabaseConnection();

if ($userRole === 'student') {
    $stmt = $pdo->prepare("
        SELECT c.course_id, c.course_code, c.course_name, c.semester, c.year,
               u.name as faculty_name
        FROM enrollments e
        JOIN courses c ON e.course_id = c.course_id
        LEFT JOIN users u ON c.faculty_id = u.user_id
        WHERE e.student_id = :student_id AND e.status = 'active'
        ORDER BY c.course_code
    ");
    $stmt->execute(['student_id' => $userId]);
    $enrolledCourses = $stmt->fetchAll();
    
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM course_requests WHERE student_id = :student_id AND status = 'pending'");
    $stmt->execute(['student_id' => $userId]);
    $pendingRequestsCount = $stmt->fetchColumn();
    
} elseif ($userRole === 'faculty') {
    $stmt = $pdo->prepare("
        SELECT c.course_id, c.course_code, c.course_name, c.semester, c.year, c.description,
               COUNT(DISTINCT e.student_id) as enrolled_count
        FROM courses c
        LEFT JOIN enrollments e ON c.course_id = e.course_id AND e.status = 'active'
        WHERE c.faculty_id = :faculty_id
        GROUP BY c.course_id
        ORDER BY c.course_code
    ");
    $stmt->execute(['faculty_id' => $userId]);
    $facultyCourses = $stmt->fetchAll();
    
    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM course_requests cr
        JOIN courses c ON cr.course_id = c.course_id
        WHERE c.faculty_id = :faculty_id AND cr.status = 'pending'
    ");
    $stmt->execute(['faculty_id' => $userId]);
    $pendingRequestsCount = $stmt->fetchColumn();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Course Management</title>
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
    <style>
        .card-hover {
            transition: all 0.3s ease;
        }
        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    
    <!-- Navigation -->
    <nav class="bg-ashesi-maroon text-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center">
                    <i class="fas fa-graduation-cap text-3xl mr-3"></i>
                    <div>
                        <h1 class="text-xl font-bold">Course Management System</h1>
                        <p class="text-xs opacity-90"><?php echo ucfirst($userRole); ?> Portal</p>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="text-right hidden sm:block">
                        <p class="text-sm font-semibold"><?php echo htmlspecialchars($userName); ?></p>
                        <p class="text-xs opacity-90"><?php echo htmlspecialchars($userEmail); ?></p>
                    </div>
                    <a href="logout.php" class="bg-red-600 px-4 py-2 rounded-lg hover:bg-red-700 transition">
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
            <div class="mb-6 p-4 rounded-lg <?php echo $flashMessage['type'] === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                <i class="fas <?php echo $flashMessage['type'] === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?> mr-2"></i>
                <?php echo htmlspecialchars($flashMessage['message']); ?>
            </div>
        <?php endif; ?>

        <!-- Welcome Section -->
        <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-3xl font-bold text-gray-800">
                        Welcome back, <?php echo htmlspecialchars(explode(' ', $userName)[0]); ?>! 👋
                    </h2>
                    <p class="text-gray-600 mt-2">
                        <?php
                        if ($userRole === 'student') {
                            echo "Manage your courses and browse new opportunities";
                        } else {
                            echo "Manage your courses and review student requests";
                        }
                        ?>
                    </p>
                </div>
                <i class="fas fa-user-circle text-6xl text-ashesi-maroon hidden md:block"></i>
            </div>
        </div>

        <?php if ($userRole === 'student'): ?>
            <!-- STUDENT DASHBOARD -->
            
            <!-- Action Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <a href="browse_courses.php" class="card-hover bg-gradient-to-br from-ashesi-maroon to-red-900 text-white rounded-lg shadow-lg p-6">
                    <i class="fas fa-search text-4xl mb-4"></i>
                    <h3 class="text-xl font-bold mb-2">Browse Courses</h3>
                    <p class="text-sm opacity-90">Explore and request to join new courses</p>
                </a>

                <div class="card-hover bg-gradient-to-br from-green-500 to-green-600 text-white rounded-lg shadow-lg p-6">
                    <i class="fas fa-book text-4xl mb-4"></i>
                    <h3 class="text-xl font-bold mb-2">My Courses</h3>
                    <p class="text-2xl font-bold"><?php echo count($enrolledCourses); ?></p>
                    <p class="text-sm opacity-90">Currently enrolled</p>
                </div>

                <div class="card-hover bg-gradient-to-br from-yellow-500 to-yellow-600 text-white rounded-lg shadow-lg p-6">
                    <i class="fas fa-clock text-4xl mb-4"></i>
                    <h3 class="text-xl font-bold mb-2">Pending Requests</h3>
                    <p class="text-2xl font-bold"><?php echo $pendingRequestsCount; ?></p>
                    <p class="text-sm opacity-90">Awaiting approval</p>
                </div>
            </div>

            <!-- Enrolled Courses -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h3 class="text-2xl font-bold text-gray-800 mb-6">
                    <i class="fas fa-graduation-cap text-ashesi-maroon mr-2"></i>My Enrolled Courses
                </h3>

                <?php if (empty($enrolledCourses)): ?>
                    <div class="text-center py-12">
                        <i class="fas fa-book-open text-6xl text-gray-300 mb-4"></i>
                        <p class="text-gray-600 text-lg mb-4">You're not enrolled in any courses yet</p>
                        <a href="browse_courses.php" class="inline-block bg-ashesi-maroon text-white px-6 py-3 rounded-lg hover:bg-red-900">
                            <i class="fas fa-search mr-2"></i>Browse Available Courses
                        </a>
                    </div>
                <?php else: ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <?php foreach ($enrolledCourses as $course): ?>
                            <div class="border border-gray-200 rounded-lg p-6 card-hover">
                                <div class="flex items-start justify-between mb-4">
                                    <div>
                                        <h4 class="text-xl font-bold text-gray-800"><?php echo htmlspecialchars($course['course_code']); ?></h4>
                                        <p class="text-sm text-gray-500"><?php echo htmlspecialchars($course['semester'] . ' ' . $course['year']); ?></p>
                                    </div>
                                    <i class="fas fa-book text-3xl text-ashesi-maroon"></i>
                                </div>
                                <h5 class="font-semibold text-gray-700 mb-3"><?php echo htmlspecialchars($course['course_name']); ?></h5>
                                <p class="text-sm text-gray-600">
                                    <i class="fas fa-chalkboard-teacher text-ashesi-maroon mr-2"></i>
                                    <?php echo htmlspecialchars($course['faculty_name'] ?? 'TBA'); ?>
                                </p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

        <?php elseif ($userRole === 'faculty'): ?>
            <!-- FACULTY DASHBOARD -->
            
            <!-- Action Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <a href="create_course.php" class="card-hover bg-gradient-to-br from-ashesi-maroon to-red-900 text-white rounded-lg shadow-lg p-6">
                    <i class="fas fa-plus-circle text-4xl mb-4"></i>
                    <h3 class="text-xl font-bold mb-2">Create Course</h3>
                    <p class="text-sm opacity-90">Add a new course to the system</p>
                </a>

                <a href="manage_requests.php" class="card-hover bg-gradient-to-br from-yellow-500 to-yellow-600 text-white rounded-lg shadow-lg p-6">
                    <i class="fas fa-tasks text-4xl mb-4"></i>
                    <h3 class="text-xl font-bold mb-2">Pending Requests</h3>
                    <p class="text-2xl font-bold"><?php echo $pendingRequestsCount; ?></p>
                    <p class="text-sm opacity-90">Requests to review</p>
                </a>

                <div class="card-hover bg-gradient-to-br from-green-500 to-green-600 text-white rounded-lg shadow-lg p-6">
                    <i class="fas fa-chalkboard text-4xl mb-4"></i>
                    <h3 class="text-xl font-bold mb-2">My Courses</h3>
                    <p class="text-2xl font-bold"><?php echo count($facultyCourses); ?></p>
                    <p class="text-sm opacity-90">Courses teaching</p>
                </div>
            </div>

            <!-- Faculty Courses -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-2xl font-bold text-gray-800">
                        <i class="fas fa-chalkboard-teacher text-ashesi-maroon mr-2"></i>My Courses
                    </h3>
                    <a href="create_course.php" class="bg-ashesi-maroon text-white px-4 py-2 rounded-lg hover:bg-red-900">
                        <i class="fas fa-plus mr-2"></i>Create New Course
                    </a>
                </div>

                <?php if (empty($facultyCourses)): ?>
                    <div class="text-center py-12">
                        <i class="fas fa-chalkboard text-6xl text-gray-300 mb-4"></i>
                        <p class="text-gray-600 text-lg mb-4">You haven't created any courses yet</p>
                        <a href="create_course.php" class="inline-block bg-ashesi-maroon text-white px-6 py-3 rounded-lg hover:bg-red-900">
                            <i class="fas fa-plus mr-2"></i>Create Your First Course
                        </a>
                    </div>
                <?php else: ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <?php foreach ($facultyCourses as $course): ?>
                            <div class="border border-gray-200 rounded-lg p-6 card-hover">
                                <div class="flex items-start justify-between mb-4">
                                    <div>
                                        <h4 class="text-xl font-bold text-gray-800"><?php echo htmlspecialchars($course['course_code']); ?></h4>
                                        <p class="text-sm text-gray-500"><?php echo htmlspecialchars($course['semester'] . ' ' . $course['year']); ?></p>
                                    </div>
                                    <i class="fas fa-chalkboard text-3xl text-ashesi-maroon"></i>
                                </div>
                                
                                <h5 class="font-semibold text-gray-700 mb-3"><?php echo htmlspecialchars($course['course_name']); ?></h5>
                                
                                <?php if ($course['description']): ?>
                                    <p class="text-sm text-gray-600 mb-4"><?php echo htmlspecialchars(substr($course['description'], 0, 100)) . (strlen($course['description']) > 100 ? '...' : ''); ?></p>
                                <?php endif; ?>
                                
                                <div class="pt-4 border-t">
                                    <p class="text-sm text-gray-600">
                                        <i class="fas fa-users text-ashesi-maroon mr-2"></i>
                                        <span class="font-semibold"><?php echo $course['enrolled_count']; ?></span> student(s) enrolled
                                    </p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

        <?php endif; ?>

    </main>

    <!-- Footer -->
    <footer class="bg-white shadow-lg mt-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="text-center text-gray-600">
                <p>&copy; <?php echo date('Y'); ?> Course Management System. All rights reserved.</p>
                <p class="text-sm mt-1">Activity 04 - Ashesi University</p>
            </div>
        </div>
    </footer>

</body>
</html>
