<?php
require_once 'config.php';
require_once 'helpers.php';

requireRole('faculty');

$courseId = $_GET['course_id'] ?? null;

if (!$courseId) {
    redirect('faculty_dashboard.php', 'Invalid course', 'error');
}

$pdo = getDatabaseConnection();

$stmt = $pdo->prepare("SELECT * FROM courses WHERE course_id = :course_id AND faculty_id = :faculty_id");
$stmt->execute(['course_id' => $courseId, 'faculty_id' => $_SESSION['user_id']]);
$course = $stmt->fetch();

if (!$course) {
    redirect('faculty_dashboard.php', 'Course not found or access denied', 'error');
}

$flashMessage = getFlashMessage();
$csrfToken = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Session - <?php echo htmlspecialchars($course['course_code']); ?></title>
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
                <a href="faculty_dashboard.php" class="hover:text-gray-200"><i class="fas fa-arrow-left mr-2"></i>Back</a>
                <h1 class="text-xl font-bold">Create Attendance Session</h1>
            </div>
            <span><i class="fas fa-user mr-2"></i><?php echo htmlspecialchars($_SESSION['name']); ?></span>
        </div>
    </nav>

    <div class="container mx-auto p-6 max-w-2xl">
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="mb-6">
                <h2 class="text-2xl font-bold text-gray-800"><?php echo htmlspecialchars($course['course_code']); ?></h2>
                <p class="text-gray-600"><?php echo htmlspecialchars($course['course_name']); ?></p>
            </div>

            <?php if ($flashMessage): ?>
                <div class="mb-6 p-4 rounded-lg <?php echo $flashMessage['type'] === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                    <i class="fas <?php echo $flashMessage['type'] === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?> mr-2"></i>
                    <?php echo htmlspecialchars($flashMessage['message']); ?>
                </div>
            <?php endif; ?>

            <form action="create_session_handler.php" method="POST" class="space-y-6">
                <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                <input type="hidden" name="course_id" value="<?php echo $courseId; ?>">

                <div>
                    <label for="session_name" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-heading text-ashesi-maroon mr-2"></i>Session Name
                    </label>
                    <input 
                        type="text" 
                        id="session_name" 
                        name="session_name" 
                        required 
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-ashesi-maroon"
                        placeholder="e.g., Lecture 1 - Introduction"
                    >
                </div>

                <div>
                    <label for="session_date" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-calendar text-ashesi-maroon mr-2"></i>Session Date
                    </label>
                    <input 
                        type="date" 
                        id="session_date" 
                        name="session_date" 
                        required 
                        value="<?php echo date('Y-m-d'); ?>"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-ashesi-maroon"
                    >
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="start_time" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-clock text-ashesi-maroon mr-2"></i>Start Time
                        </label>
                        <input 
                            type="time" 
                            id="start_time" 
                            name="start_time" 
                            required 
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-ashesi-maroon"
                        >
                    </div>

                    <div>
                        <label for="end_time" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-clock text-ashesi-maroon mr-2"></i>End Time
                        </label>
                        <input 
                            type="time" 
                            id="end_time" 
                            name="end_time" 
                            required 
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-ashesi-maroon"
                        >
                    </div>
                </div>

                <div>
                    <label for="code_duration" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-hourglass-half text-ashesi-maroon mr-2"></i>Attendance Code Valid For (minutes)
                    </label>
                    <select 
                        id="code_duration" 
                        name="code_duration" 
                        required 
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-ashesi-maroon"
                    >
                        <option value="5">5 minutes</option>
                        <option value="10" selected>10 minutes</option>
                        <option value="15">15 minutes</option>
                        <option value="20">20 minutes</option>
                        <option value="30">30 minutes</option>
                    </select>
                    <p class="text-sm text-gray-500 mt-1">Students can mark attendance within this time window</p>
                </div>

                <div class="flex space-x-4">
                    <button 
                        type="submit" 
                        class="flex-1 bg-ashesi-maroon text-white py-3 rounded-lg hover:bg-red-900 transition duration-200 font-semibold"
                    >
                        <i class="fas fa-plus-circle mr-2"></i>Create Session
                    </button>
                    <a 
                        href="faculty_dashboard.php" 
                        class="flex-1 bg-gray-500 text-white py-3 rounded-lg hover:bg-gray-600 transition duration-200 font-semibold text-center"
                    >
                        <i class="fas fa-times mr-2"></i>Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
