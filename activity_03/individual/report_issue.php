<?php
/**
 * ============================================
 * REPORT ATTENDANCE ISSUE PAGE - BEGINNER FRIENDLY
 * ============================================
 * 
 * WHAT THIS PAGE DOES:
 * Allows students to report problems with their attendance
 * For example:
 * - Marked absent but was present
 * - Marked late but was on time
 * - Missing attendance record
 * 
 * FLOW:
 * 1. Student fills out form with issue details
 * 2. Form submits to this same page (processes itself)
 * 3. Issue is saved to database
 * 4. Confirmation message shown
 */

// Include config file
require_once 'config.php';
require_once 'helpers.php';

// Must be logged in to report issues
requireLogin();

// Must be a student
if (!hasRole('student')) {
    header("Location: faculty_dashboard.php");
    exit();
}

// Get student info from session
$studentId = $_SESSION['user_id'];
$studentName = $_SESSION['name'];

// ============================================
// STEP 1: Get student's courses for dropdown
// ============================================
try {
    $courseQuery = $pdo->prepare("
        SELECT c.course_id, c.course_code, c.course_name
        FROM enrollments e
        JOIN courses c ON e.course_id = c.course_id
        WHERE e.student_id = :student_id AND e.status = 'active'
        ORDER BY c.course_code
    ");
    $courseQuery->bindParam(':student_id', $studentId, PDO::PARAM_INT);
    $courseQuery->execute();
    $courses = $courseQuery->fetchAll();
} catch (PDOException $e) {
    error_log("Error: " . $e->getMessage());
    $courses = [];
}

// ============================================
// STEP 2: If course is selected, get its sessions
// ============================================
$sessions = [];
$selectedCourse = isset($_GET['course_id']) ? (int)$_GET['course_id'] : null;

if ($selectedCourse) {
    try {
        $sessionQuery = $pdo->prepare("
            SELECT session_id, session_date, session_time, session_type
            FROM sessions
            WHERE course_id = :course_id
            ORDER BY session_date DESC
            LIMIT 20
        ");
        $sessionQuery->bindParam(':course_id', $selectedCourse, PDO::PARAM_INT);
        $sessionQuery->execute();
        $sessions = $sessionQuery->fetchAll();
    } catch (PDOException $e) {
        error_log("Error: " . $e->getMessage());
    }
}

// ============================================
// STEP 3: Process form submission
// ============================================
$success = false;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        $error = "Invalid security token";
    } else {
        // Get form data
        $courseId = isset($_POST['course_id']) ? (int)$_POST['course_id'] : 0;
        $sessionId = isset($_POST['session_id']) ? (int)$_POST['session_id'] : 0;
        $description = isset($_POST['description']) ? sanitizeInput($_POST['description']) : '';
        
        // Validate
        if (empty($courseId) || empty($sessionId) || empty($description)) {
            $error = "Please fill in all fields";
        } elseif (strlen($description) < 10) {
            $error = "Please provide a detailed description (at least 10 characters)";
        } else {
            // Insert issue into database
            try {
                $insertQuery = $pdo->prepare("
                    INSERT INTO issues (student_id, session_id, description, status, created_at)
                    VALUES (:student_id, :session_id, :description, 'pending', NOW())
                ");
                
                $insertQuery->bindParam(':student_id', $studentId, PDO::PARAM_INT);
                $insertQuery->bindParam(':session_id', $sessionId, PDO::PARAM_INT);
                $insertQuery->bindParam(':description', $description, PDO::PARAM_STR);
                
                $insertQuery->execute();
                
                $success = true;
                setFlashMessage('success', 'Issue reported successfully! Faculty will review it.');
                
            } catch (PDOException $e) {
                error_log("Error: " . $e->getMessage());
                $error = "Failed to submit issue. Please try again.";
            }
        }
    }
}

// Get flash message
$flashMessage = getFlashMessage();
$csrfToken = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report Issue - Ashesi Attendance</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50 min-h-screen">
    
    <!-- Navigation -->
    <nav class="bg-red-600 text-white shadow-lg">
        <div class="container mx-auto px-4 py-4">
            <div class="flex justify-between items-center">
                <div class="flex items-center space-x-4">
                    <i class="fas fa-exclamation-triangle text-3xl"></i>
                    <div>
                        <h1 class="text-xl font-bold">Report Attendance Issue</h1>
                        <p class="text-sm opacity-90">Submit attendance discrepancy</p>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="student_dashboard.php" class="bg-white text-red-600 px-4 py-2 rounded-lg hover:bg-red-50">
                        <i class="fas fa-arrow-left mr-2"></i>Back to Dashboard
                    </a>
                    <a href="logout.php" class="bg-red-700 px-4 py-2 rounded-lg hover:bg-red-800">
                        <i class="fas fa-sign-out-alt mr-2"></i>Logout
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container mx-auto px-4 py-8 max-w-3xl">
        
        <!-- Success Message -->
        <?php if ($success): ?>
            <div class="bg-green-100 border-l-4 border-green-500 text-green-800 p-6 rounded-lg mb-6">
                <div class="flex items-center mb-4">
                    <i class="fas fa-check-circle text-3xl mr-4"></i>
                    <div>
                        <h3 class="font-bold text-lg">Issue Reported Successfully!</h3>
                        <p>Your issue has been submitted and is pending review by faculty.</p>
                    </div>
                </div>
                <div class="mt-4">
                    <a href="student_dashboard.php" class="inline-block bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700">
                        Return to Dashboard
                    </a>
                    <a href="report_issue.php" class="inline-block bg-gray-200 text-gray-800 px-6 py-2 rounded-lg hover:bg-gray-300 ml-2">
                        Report Another Issue
                    </a>
                </div>
            </div>
        <?php endif; ?>

        <!-- Flash Message -->
        <?php if ($flashMessage && !$success): ?>
            <div class="mb-6 p-4 rounded-lg <?php echo $flashMessage['type'] === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                <i class="fas <?php echo $flashMessage['type'] === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?> mr-2"></i>
                <?php echo htmlspecialchars($flashMessage['message']); ?>
            </div>
        <?php endif; ?>

        <!-- Error Message -->
        <?php if ($error): ?>
            <div class="mb-6 p-4 rounded-lg bg-red-100 text-red-800">
                <i class="fas fa-exclamation-circle mr-2"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <!-- Information Box -->
        <?php if (!$success): ?>
            <div class="bg-blue-50 border-l-4 border-blue-500 p-6 rounded-lg mb-6">
                <h3 class="font-bold text-blue-900 mb-2">
                    <i class="fas fa-info-circle mr-2"></i>
                    When to Report an Issue:
                </h3>
                <ul class="text-blue-800 space-y-1 ml-6">
                    <li>• You were marked absent but you attended the session</li>
                    <li>• You were marked late but you arrived on time</li>
                    <li>• Your attendance record is missing</li>
                    <li>• There's an error in your attendance status</li>
                </ul>
            </div>

            <!-- Report Form -->
            <div class="bg-white rounded-lg shadow-md p-8">
                <h2 class="text-2xl font-bold text-gray-800 mb-6">Report Attendance Issue</h2>
                
                <form method="POST" class="space-y-6" id="issueForm">
                    <!-- CSRF Token -->
                    <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                    
                    <!-- Course Selection -->
                    <div>
                        <label for="course_id" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-book mr-2 text-red-600"></i>
                            Select Course *
                        </label>
                        <select 
                            id="course_id" 
                            name="course_id" 
                            required
                            onchange="window.location.href='report_issue.php?course_id=' + this.value"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500"
                        >
                            <option value="">-- Choose a course --</option>
                            <?php foreach ($courses as $course): ?>
                                <option value="<?php echo $course['course_id']; ?>" <?php echo ($selectedCourse == $course['course_id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($course['course_code'] . ' - ' . $course['course_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Session Selection (shows only when course is selected) -->
                    <?php if ($selectedCourse && !empty($sessions)): ?>
                        <div>
                            <label for="session_id" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-calendar-day mr-2 text-red-600"></i>
                                Select Session *
                            </label>
                            <select 
                                id="session_id" 
                                name="session_id" 
                                required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500"
                            >
                                <option value="">-- Choose a session --</option>
                                <?php foreach ($sessions as $session): ?>
                                    <option value="<?php echo $session['session_id']; ?>">
                                        <?php 
                                            echo date('M d, Y', strtotime($session['session_date']));
                                            if ($session['session_time']) {
                                                echo ' at ' . date('g:i A', strtotime($session['session_time']));
                                            }
                                            echo ' (' . ucfirst($session['session_type']) . ')';
                                        ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Issue Description -->
                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-comment-alt mr-2 text-red-600"></i>
                                Describe the Issue *
                            </label>
                            <textarea 
                                id="description" 
                                name="description" 
                                required
                                rows="6"
                                placeholder="Please provide detailed information about the attendance issue. Include any evidence or witnesses if applicable."
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500"
                            ></textarea>
                            <p class="text-sm text-gray-500 mt-2">
                                <i class="fas fa-lightbulb mr-1"></i>
                                Tip: Be specific and include details like time of arrival, witnesses, or any proof.
                            </p>
                        </div>

                        <!-- Submit Button -->
                        <div class="flex space-x-4">
                            <button 
                                type="submit" 
                                class="flex-1 bg-red-600 text-white py-3 px-6 rounded-lg hover:bg-red-700 font-semibold transition duration-200"
                            >
                                <i class="fas fa-paper-plane mr-2"></i>
                                Submit Issue Report
                            </button>
                            <a 
                                href="student_dashboard.php" 
                                class="flex-1 text-center bg-gray-200 text-gray-700 py-3 px-6 rounded-lg hover:bg-gray-300 font-semibold transition duration-200"
                            >
                                <i class="fas fa-times mr-2"></i>
                                Cancel
                            </a>
                        </div>
                    <?php elseif ($selectedCourse): ?>
                        <div class="text-center py-8 text-gray-500">
                            <i class="fas fa-calendar-times text-4xl mb-4"></i>
                            <p>No sessions found for this course.</p>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-8 text-gray-500">
                            <i class="fas fa-arrow-up text-4xl mb-4"></i>
                            <p>Please select a course above to see available sessions.</p>
                        </div>
                    <?php endif; ?>
                </form>
            </div>

            <!-- Tips Section -->
            <div class="mt-8 bg-yellow-50 border border-yellow-200 rounded-lg p-6">
                <h3 class="font-bold text-yellow-900 mb-3">
                    <i class="fas fa-clipboard-list mr-2"></i>
                    Tips for Reporting Issues:
                </h3>
                <ul class="text-yellow-800 space-y-2">
                    <li class="flex items-start">
                        <i class="fas fa-check text-yellow-600 mr-2 mt-1"></i>
                        <span><strong>Be specific:</strong> Include date, time, and exact details</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-check text-yellow-600 mr-2 mt-1"></i>
                        <span><strong>Provide evidence:</strong> Mention any witnesses or proof</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-check text-yellow-600 mr-2 mt-1"></i>
                        <span><strong>Be respectful:</strong> Use professional language</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-check text-yellow-600 mr-2 mt-1"></i>
                        <span><strong>Follow up:</strong> Check with your instructor if no response in 3 days</span>
                    </li>
                </ul>
            </div>
        <?php endif; ?>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white text-center py-6 mt-12">
        <p>&copy; 2025 Ashesi University - Attendance Management System</p>
    </footer>

    <script>
        // Form validation
        document.getElementById('issueForm')?.addEventListener('submit', function(e) {
            const description = document.getElementById('description').value;
            
            if (description.length < 10) {
                e.preventDefault();
                alert('Please provide a more detailed description (at least 10 characters)');
            }
        });
    </script>
</body>
</html>
