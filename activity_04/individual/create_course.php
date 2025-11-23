<?php
require_once 'config.php';
require_once 'helpers.php';

requireRole('faculty');

$flashMessage = getFlashMessage();
$csrfToken = generateCSRFToken();
$userName = $_SESSION['name'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Course - Faculty</title>
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

    <div class="max-w-4xl mx-auto px-4 py-8">
        <div class="bg-white rounded-lg shadow-lg p-8">
            <div class="mb-6">
                <h2 class="text-3xl font-bold text-gray-800">
                    <i class="fas fa-plus-circle text-ashesi-maroon mr-3"></i>Create New Course
                </h2>
                <p class="text-gray-600 mt-2">Fill in the course details below</p>
            </div>

            <?php if ($flashMessage): ?>
                <div class="mb-6 p-4 rounded-lg <?php echo $flashMessage['type'] === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                    <i class="fas <?php echo $flashMessage['type'] === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?> mr-2"></i>
                    <?php echo htmlspecialchars($flashMessage['message']); ?>
                </div>
            <?php endif; ?>

            <form action="create_course_handler.php" method="POST" class="space-y-6">
                <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="course_code" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-code text-ashesi-maroon mr-2"></i>Course Code *
                        </label>
                        <input 
                            type="text" 
                            id="course_code" 
                            name="course_code" 
                            required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-ashesi-maroon"
                            placeholder="e.g., CS301"
                            pattern="[A-Z]{2,4}[0-9]{3}"
                            title="Format: 2-4 uppercase letters followed by 3 digits (e.g., CS101)"
                        >
                        <p class="text-xs text-gray-500 mt-1">Format: CS101, MATH201, etc.</p>
                    </div>

                    <div>
                        <label for="course_name" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-book text-ashesi-maroon mr-2"></i>Course Name *
                        </label>
                        <input 
                            type="text" 
                            id="course_name" 
                            name="course_name" 
                            required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-ashesi-maroon"
                            placeholder="e.g., Web Technologies"
                        >
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="semester" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-calendar-alt text-ashesi-maroon mr-2"></i>Semester *
                        </label>
                        <select 
                            id="semester" 
                            name="semester" 
                            required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-ashesi-maroon"
                        >
                            <option value="">Select Semester</option>
                            <option value="Fall">Fall</option>
                            <option value="Spring">Spring</option>
                            <option value="Summer">Summer</option>
                        </select>
                    </div>

                    <div>
                        <label for="year" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-calendar text-ashesi-maroon mr-2"></i>Year *
                        </label>
                        <input 
                            type="number" 
                            id="year" 
                            name="year" 
                            required
                            min="2024"
                            max="2030"
                            value="2025"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-ashesi-maroon"
                        >
                    </div>
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-align-left text-ashesi-maroon mr-2"></i>Course Description
                    </label>
                    <textarea 
                        id="description" 
                        name="description" 
                        rows="4"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-ashesi-maroon"
                        placeholder="Provide a brief description of the course content and objectives..."
                    ></textarea>
                </div>

                <div class="flex items-center justify-between pt-6 border-t">
                    <a href="dashboard.php" class="text-gray-600 hover:text-gray-800">
                        <i class="fas fa-arrow-left mr-2"></i>Back to Dashboard
                    </a>
                    <button 
                        type="submit" 
                        class="bg-ashesi-maroon text-white px-8 py-3 rounded-lg hover:bg-red-900 transition duration-200 font-semibold"
                    >
                        <i class="fas fa-save mr-2"></i>Create Course
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
