<?php
require_once 'config.php';
require_once 'helpers.php';

if (isLoggedIn()) {
    redirectToDashboard();
}

$flashMessage = getFlashMessage();
$csrfToken = generateCSRFToken();

$formData = [
    'name' => $_GET['name'] ?? '',
    'email' => $_GET['email'] ?? '',
    'role' => $_GET['role'] ?? 'student',
    'ashesi_id' => $_GET['ashesi_id'] ?? ''
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Course Management System</title>
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
        .bg-ashesi {
            background-image: url('ashesi_admissions_banner.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            position: relative;
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center bg-ashesi py-8">
    <div class="bg-white p-8 rounded-lg shadow-2xl w-full max-w-md relative z-10">
        <div class="text-center mb-8">
            <i class="fas fa-user-plus text-5xl text-ashesi-maroon mb-4"></i>
            <h1 class="text-3xl font-bold text-gray-800">Create Account</h1>
            <p class="text-gray-600 mt-2">Join Ashesi Course Management</p>
        </div>

        <?php if ($flashMessage): ?>
            <div class="mb-6 p-4 rounded-lg <?php echo $flashMessage['type'] === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                <i class="fas <?php echo $flashMessage['type'] === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?> mr-2"></i>
                <?php echo htmlspecialchars($flashMessage['message']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
            <div class="mb-6 p-4 rounded-lg bg-red-100 text-red-800">
                <i class="fas fa-exclamation-circle mr-2"></i>
                <?php
                    $errors = [
                        'empty_fields' => 'Please fill in all required fields.',
                        'invalid_email' => 'Please enter a valid email address.',
                        'email_exists' => 'This email is already registered.',
                        'ashesi_id_exists' => 'This Ashesi ID is already registered.',
                        'weak_password' => 'Password must be at least 8 characters with uppercase, lowercase, and numbers.',
                        'passwords_mismatch' => 'Passwords do not match.',
                        'csrf_invalid' => 'Invalid security token. Please try again.'
                    ];
                    echo $errors[$_GET['error']] ?? 'An error occurred.';
                ?>
            </div>
        <?php endif; ?>

        <form action="register_handler.php" method="POST" class="space-y-6" id="registerForm">
            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
            
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-user mr-2 text-ashesi-maroon"></i>Full Name
                </label>
                <input 
                    type="text" 
                    id="name" 
                    name="name" 
                    required
                    value="<?php echo htmlspecialchars($formData['name']); ?>"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-ashesi-maroon"
                    placeholder="John Doe"
                >
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-envelope mr-2 text-ashesi-maroon"></i>Email Address
                </label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    required
                    value="<?php echo htmlspecialchars($formData['email']); ?>"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-ashesi-maroon"
                    placeholder="student@ashesi.edu.gh"
                >
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-lock mr-2 text-ashesi-maroon"></i>Password
                </label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-ashesi-maroon"
                    placeholder="At least 8 characters"
                >
                <p class="text-xs text-gray-500 mt-1">Must include uppercase, lowercase, and numbers</p>
            </div>

            <div>
                <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-lock mr-2 text-ashesi-maroon"></i>Confirm Password
                </label>
                <input 
                    type="password" 
                    id="confirm_password" 
                    name="confirm_password" 
                    required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-ashesi-maroon"
                    placeholder="Re-enter password"
                >
            </div>

            <div>
                <label for="role" class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-user-tag mr-2 text-ashesi-maroon"></i>Role
                </label>
                <select 
                    id="role" 
                    name="role" 
                    required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-ashesi-maroon"
                >
                    <option value="student" <?php echo $formData['role'] === 'student' ? 'selected' : ''; ?>>Student</option>
                    <option value="faculty" <?php echo $formData['role'] === 'faculty' ? 'selected' : ''; ?>>Faculty</option>
                </select>
            </div>

            <div>
                <label for="ashesi_id" class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-id-card mr-2 text-ashesi-maroon"></i>Ashesi ID
                </label>
                <input 
                    type="text" 
                    id="ashesi_id" 
                    name="ashesi_id" 
                    required
                    value="<?php echo htmlspecialchars($formData['ashesi_id']); ?>"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-ashesi-maroon"
                    placeholder="e.g., 2026001 or FAC001"
                >
            </div>

            <button 
                type="submit" 
                class="w-full bg-ashesi-maroon text-white py-3 rounded-lg hover:bg-red-900 transition duration-200 font-semibold"
            >
                <i class="fas fa-user-plus mr-2"></i>Create Account
            </button>

            <div class="text-center mt-6">
                <p class="text-gray-600">
                    Already have an account? 
                    <a href="index.php" class="text-ashesi-maroon hover:text-red-900 font-semibold">Sign in here</a>
                </p>
            </div>
        </form>
    </div>

    <script>
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match!');
                return false;
            }

            if (password.length < 8) {
                e.preventDefault();
                alert('Password must be at least 8 characters long!');
                return false;
            }

            if (!/[A-Z]/.test(password) || !/[a-z]/.test(password) || !/\d/.test(password)) {
                e.preventDefault();
                alert('Password must contain uppercase, lowercase, and numbers!');
                return false;
            }
        });
    </script>
</body>
</html>
