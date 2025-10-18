<?php
/**
 * ============================================
 * STUDENT DASHBOARD - BEGINNER FRIENDLY
 * ============================================
 * 
 * WHAT THIS PAGE DOES:
 * This is the main page students see after login.
 * It shows:
 * 1. Welcome message with student's name
 * 2. List of all courses they're enrolled in
 * 3. Their attendance records for each course
 * 4. Visual distinction between lectures and labs
 * 5. Attendance statistics (present/absent/late counts)
 * 
 * ONLY STUDENTS CAN ACCESS THIS PAGE
 */

// ============================================
// Include config file
// ============================================
require_once 'config.php';
require_once 'helpers.php';


// ============================================
// STEP 1: Check if user is logged in
// ============================================
// If not logged in, redirect to login page
requireLogin();

// ============================================
// STEP 2: Check if user is a student
// ============================================
// Only students should access this page
if (!hasRole('student')) {
    // If faculty tries to access, redirect to faculty dashboard
    if (hasRole('faculty')) {
        header("Location: faculty_dashboard.php");
        exit();
    }
    // Otherwise, redirect to login
    header("Location: index.php");
    exit();
}

// ============================================
// STEP 3: Get student information from session
// ============================================
// Remember: We stored this data in login_handler.php
$studentId = $_SESSION['user_id'];        // Student's database ID
$studentName = $_SESSION['name'];         // Student's full name
$studentEmail = $_SESSION['email'];       // Student's email
$ashesiId = $_SESSION['ashesi_id'];       // Student's Ashesi ID

// ============================================
// STEP 4: Get flash message (if any)
// ============================================
$flashMessage = getFlashMessage();

// ============================================
// STEP 5: Get all courses the student is enrolled in
// ============================================
try {
    // This SQL query joins multiple tables:
    // - enrollments: links students to courses
    // - courses: has course details
    // - users: has faculty information
    
    $courseQuery = $pdo->prepare("
        SELECT 
            c.course_id,
            c.course_code,
            c.course_name,
            c.semester,
            c.year,
            u.name as faculty_name
        FROM enrollments e
        INNER JOIN courses c ON e.course_id = c.course_id
        LEFT JOIN users u ON c.faculty_id = u.user_id
        WHERE e.student_id = :student_id 
        AND e.status = 'active'
        ORDER BY c.course_code
    ");
    
    // Bind the student ID
    $courseQuery->bindParam(':student_id', $studentId, PDO::PARAM_INT);
    
    // Execute the query
    $courseQuery->execute();
    
    // Fetch all courses as an array
    // Each item in the array is a course
    $courses = $courseQuery->fetchAll();
    
} catch (PDOException $e) {
    // If database error, log it
    error_log("Error fetching courses: " . $e->getMessage());
    $courses = []; // Empty array if error
}

// ============================================
// STEP 6: Get attendance records for each course
// ============================================
$attendanceData = []; // This will store all attendance records

foreach ($courses as $course) {
    // For each course, get all sessions and attendance
    try {
        $attendanceQuery = $pdo->prepare("
            SELECT 
                s.session_id,
                s.session_date,
                s.session_time,
                s.session_type,
                s.notes,
                a.status,
                a.marked_at
            FROM sessions s
            LEFT JOIN attendance a ON s.session_id = a.session_id 
                AND a.student_id = :student_id
            WHERE s.course_id = :course_id
            ORDER BY s.session_date DESC, s.session_time DESC
        ");
        
        $attendanceQuery->bindParam(':student_id', $studentId, PDO::PARAM_INT);
        $attendanceQuery->bindParam(':course_id', $course['course_id'], PDO::PARAM_INT);
        $attendanceQuery->execute();
        
        // Store attendance records for this course
        $attendanceData[$course['course_id']] = $attendanceQuery->fetchAll();
        
    } catch (PDOException $e) {
        error_log("Error fetching attendance: " . $e->getMessage());
        $attendanceData[$course['course_id']] = [];
    }
}

// ============================================
// STEP 7: Calculate attendance statistics for each course
// ============================================
$statistics = [];

foreach ($courses as $course) {
    $courseId = $course['course_id'];
    $records = $attendanceData[$courseId];
    
    // Count different attendance statuses
    $present = 0;
    $absent = 0;
    $late = 0;
    $total = count($records);
    
    foreach ($records as $record) {
        if ($record['status'] === 'present') {
            $present++;
        } elseif ($record['status'] === 'absent') {
            $absent++;
        } elseif ($record['status'] === 'late') {
            $late++;
        }
    }
    
    // Calculate percentage
    // Avoid division by zero
    $percentage = $total > 0 ? round(($present / $total) * 100, 1) : 0;
    
    // Store statistics
    $statistics[$courseId] = [
        'present' => $present,
        'absent' => $absent,
        'late' => $late,
        'total' => $total,
        'percentage' => $percentage
    ];
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - Ashesi Attendance</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Custom styles for better visuals */
        .lecture-badge {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
        }
        .lab-badge {
            background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
        }
        .status-present {
            background-color: #dcfce7;
            color: #166534;
        }
        .status-absent {
            background-color: #fee2e2;
            color: #991b1b;
        }
        .status-late {
            background-color: #fef3c7;
            color: #92400e;
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    
    <!-- ============================================ -->
    <!-- NAVIGATION BAR -->
    <!-- ============================================ -->
    <nav class="bg-red-600 text-white shadow-lg">
        <div class="container mx-auto px-4 py-4">
            <div class="flex justify-between items-center">
                <!-- Left side: Logo and title -->
                <div class="flex items-center space-x-4">
                    <i class="fas fa-graduation-cap text-3xl"></i>
                    <div>
                        <h1 class="text-xl font-bold">Ashesi Attendance</h1>
                        <p class="text-sm opacity-90">Student Dashboard</p>
                    </div>
                </div>
                
                <!-- Right side: User info and logout -->
                <div class="flex items-center space-x-4">
                    <div class="text-right">
                        <p class="font-semibold"><?php echo htmlspecialchars($studentName); ?></p>
                        <p class="text-sm opacity-90"><?php echo htmlspecialchars($ashesiId); ?></p>
                    </div>
                    <a href="logout.php" class="bg-white text-red-600 px-4 py-2 rounded-lg hover:bg-red-50 transition duration-200">
                        <i class="fas fa-sign-out-alt mr-2"></i>Logout
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- ============================================ -->
    <!-- MAIN CONTENT -->
    <!-- ============================================ -->
    <div class="container mx-auto px-4 py-8">
        
        <!-- Welcome message -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <h2 class="text-2xl font-bold text-gray-800 mb-2">
                Welcome back, <?php echo htmlspecialchars(explode(' ', $studentName)[0]); ?>! 👋
            </h2>
            <p class="text-gray-600">Here's your attendance overview for all courses</p>
        </div>

        <!-- Flash message -->
        <?php if ($flashMessage): ?>
            <div class="mb-6 p-4 rounded-lg <?php echo $flashMessage['type'] === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                <i class="fas <?php echo $flashMessage['type'] === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?> mr-2"></i>
                <?php echo htmlspecialchars($flashMessage['message']); ?>
            </div>
        <?php endif; ?>

        <!-- Report Issue Button -->
        <div class="mb-6">
            <a href="report_issue.php" class="inline-block bg-yellow-500 text-white px-6 py-3 rounded-lg hover:bg-yellow-600 transition duration-200">
                <i class="fas fa-exclamation-triangle mr-2"></i>
                Report Attendance Issue
            </a>
        </div>

        <!-- ============================================ -->
        <!-- COURSES AND ATTENDANCE RECORDS -->
        <!-- ============================================ -->
        
        <?php if (empty($courses)): ?>
            <!-- No courses enrolled -->
            <div class="bg-white rounded-lg shadow-md p-8 text-center">
                <i class="fas fa-book-open text-6xl text-gray-300 mb-4"></i>
                <h3 class="text-xl font-semibold text-gray-700 mb-2">No Courses Enrolled</h3>
                <p class="text-gray-500">You are not currently enrolled in any courses.</p>
            </div>
        <?php else: ?>
            <!-- Loop through each course -->
            <?php foreach ($courses as $course): ?>
                <?php 
                    $courseId = $course['course_id'];
                    $stats = $statistics[$courseId];
                    $records = $attendanceData[$courseId];
                ?>
                
                <div class="bg-white rounded-lg shadow-md mb-6 overflow-hidden">
                    <!-- Course header -->
                    <div class="bg-gradient-to-r from-red-600 to-red-700 text-white p-6">
                        <div class="flex justify-between items-start">
                            <div>
                                <h3 class="text-2xl font-bold mb-2">
                                    <?php echo htmlspecialchars($course['course_code']); ?>
                                </h3>
                                <p class="text-lg opacity-90"><?php echo htmlspecialchars($course['course_name']); ?></p>
                                <p class="text-sm opacity-75 mt-2">
                                    <i class="fas fa-chalkboard-teacher mr-2"></i>
                                    <?php echo htmlspecialchars($course['faculty_name'] ?? 'No instructor assigned'); ?>
                                </p>
                            </div>
                            
                            <!-- Attendance statistics -->
                            <div class="text-right">
                                <div class="text-4xl font-bold"><?php echo $stats['percentage']; ?>%</div>
                                <div class="text-sm opacity-90">Attendance Rate</div>
                            </div>
                        </div>
                        
                        <!-- Statistics cards -->
                        <div class="grid grid-cols-4 gap-4 mt-6">
                            <div class="bg-white bg-opacity-20 rounded-lg p-3 text-center">
                                <div class="text-2xl font-bold"><?php echo $stats['total']; ?></div>
                                <div class="text-xs opacity-90">Total Sessions</div>
                            </div>
                            <div class="bg-green-500 bg-opacity-30 rounded-lg p-3 text-center">
                                <div class="text-2xl font-bold"><?php echo $stats['present']; ?></div>
                                <div class="text-xs opacity-90">Present</div>
                            </div>
                            <div class="bg-yellow-500 bg-opacity-30 rounded-lg p-3 text-center">
                                <div class="text-2xl font-bold"><?php echo $stats['late']; ?></div>
                                <div class="text-xs opacity-90">Late</div>
                            </div>
                            <div class="bg-red-500 bg-opacity-30 rounded-lg p-3 text-center">
                                <div class="text-2xl font-bold"><?php echo $stats['absent']; ?></div>
                                <div class="text-xs opacity-90">Absent</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Attendance records table -->
                    <div class="p-6">
                        <?php if (empty($records)): ?>
                            <p class="text-gray-500 text-center py-4">No sessions recorded yet</p>
                        <?php else: ?>
                            <div class="overflow-x-auto">
                                <table class="w-full">
                                    <thead>
                                        <tr class="border-b-2 border-gray-200">
                                            <th class="text-left py-3 px-4 font-semibold text-gray-700">Date</th>
                                            <th class="text-left py-3 px-4 font-semibold text-gray-700">Time</th>
                                            <th class="text-left py-3 px-4 font-semibold text-gray-700">Type</th>
                                            <th class="text-left py-3 px-4 font-semibold text-gray-700">Status</th>
                                            <th class="text-left py-3 px-4 font-semibold text-gray-700">Notes</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($records as $record): ?>
                                            <tr class="border-b border-gray-100 hover:bg-gray-50">
                                                <!-- Date -->
                                                <td class="py-3 px-4">
                                                    <i class="fas fa-calendar mr-2 text-gray-400"></i>
                                                    <?php echo date('M d, Y', strtotime($record['session_date'])); ?>
                                                </td>
                                                
                                                <!-- Time -->
                                                <td class="py-3 px-4">
                                                    <i class="fas fa-clock mr-2 text-gray-400"></i>
                                                    <?php echo $record['session_time'] ? date('g:i A', strtotime($record['session_time'])) : 'N/A'; ?>
                                                </td>
                                                
                                                <!-- Session Type (Lecture/Lab/Practical) -->
                                                <td class="py-3 px-4">
                                                    <?php if ($record['session_type'] === 'lecture'): ?>
                                                        <span class="lecture-badge text-white px-3 py-1 rounded-full text-sm">
                                                            📚 Lecture
                                                        </span>
                                                    <?php elseif ($record['session_type'] === 'lab' || $record['session_type'] === 'practical'): ?>
                                                        <span class="lab-badge text-white px-3 py-1 rounded-full text-sm">
                                                            🔬 <?php echo ucfirst($record['session_type']); ?>
                                                        </span>
                                                    <?php endif; ?>
                                                </td>
                                                
                                                <!-- Attendance Status -->
                                                <td class="py-3 px-4">
                                                    <?php if ($record['status'] === 'present'): ?>
                                                        <span class="status-present px-3 py-1 rounded-full text-sm font-semibold">
                                                            ✓ Present
                                                        </span>
                                                    <?php elseif ($record['status'] === 'absent'): ?>
                                                        <span class="status-absent px-3 py-1 rounded-full text-sm font-semibold">
                                                            ✗ Absent
                                                        </span>
                                                    <?php elseif ($record['status'] === 'late'): ?>
                                                        <span class="status-late px-3 py-1 rounded-full text-sm font-semibold">
                                                            ⏰ Late
                                                        </
                                                    <?php else: ?>
                                                        <span class="bg-gray-200 text-gray-600 px-3 py-1 rounded-full text-sm">
                                                            Not Marked
                                                        </span>
                                                    <?php endif; ?>
                                                </td>
                                                
                                                <!-- Notes -->
                                                <td class="py-3 px-4 text-sm text-gray-600">
                                                    <?php echo $record['notes'] ? htmlspecialchars($record['notes']) : '-'; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white text-center py-6 mt-12">
        <p>&copy; 2025 Ashesi University - Attendance Management System</p>
    </footer>

</body>
</html>
