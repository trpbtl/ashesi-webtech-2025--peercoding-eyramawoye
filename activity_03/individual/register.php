<?php
/**
 * Registration Page
 * 
 * Allows new users to create an account.
 * Collects: name, email, password, role, and Ashesi ID
 * 
 * Flow:
 * 1. Display registration form
 * 2. User fills in details
 * 3. Form submits to register_handler.php
 * 4. Handler validates and creates account
 * 5. User redirected to login page
 */

require_once 'config.php';

// If already logged in, redirect to dashboard
if (isLoggedIn()) {
    redirectToDashboard();
}

// Get flash messages and CSRF token
$flashMessage = getFlashMessage();
$csrfToken = generateCSRFToken();

// Preserve form data if validation fails (from URL parameters)
$formData = [
    'name' => isset($_GET['name']) ? htmlspecialchars($_GET['name']) : '',
    'email' => isset($_GET['email']) ? htmlspecialchars($_GET['email']) : '',
    'role' => isset($_GET['role']) ? htmlspecialchars($_GET['role']) : 'student',
    'ashesi_id' => isset($_GET['ashesi_id']) ? htmlspecialchars($_GET['ashesi_id']) : ''
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Ashesi Attendance Manager</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="min-h-screen flex items-center justify-center bg-gradient-to-br from-red-50 to-gray-100 py-8">
    <div class="bg-white p-8 rounded-lg shadow-2xl w-full max-w-md">
        
        <!-- Header -->
        <div class="text-center mb-8">
            <i class="fas fa-user-plus text-5xl text-red-600 mb-4"></i>
            <h1 class="text-3xl font-bold text-gray-800">Create Account</h1>
            <p class="text-gray-600 mt-2">Join Ashesi Attendance Manager</p>
        </div>

        <!-- Flash Messages -->
        <?php if ($flashMessage): ?>
            <div class="mb-6 p-4 rounded-lg <?php echo $flashMessage['type'] === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                <i class="fas <?php echo $flashMessage['type'] === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?> mr-2"></i>
                <?php echo htmlspecialchars($flashMessage['message']); ?>
            </div>
        <?php endif; ?>

        <!-- Error Messages from URL -->
        <?php if (isset($_GET['error'])): ?>
            <div class="mb-6 p-4 rounded-lg bg-red-100 text-red-800">
                <i class="fas fa-exclamation-circle mr-2"></i>
                <?php
                    $errorMessages = [
                        'empty_fields' => 'Please fill in all required fields.',
                        'invalid_email' => 'Please enter a valid email address.',
                        'email_exists' => 'This email is already registered.',
                        'ashesi_id_exists' => 'This Ashesi ID is already registered.',
                        'weak_password' => 'Password must be at least 8 characters with uppercase, lowercase, and numbers.',
                        'csrf_invalid' => 'Invalid security token. Please try again.'
                    ];
                    echo isset($errorMessages[$_GET['error']]) ? $errorMessages[$_GET['error']] : 'An error occurred.';
                ?>
            </div>
        <?php endif; ?>

        <!-- Registration Form -->
        <form action="register_handler.php" method="POST" class="space-y-6" id="registerForm">
            
            <!-- CSRF Token -->
            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
            
            <!-- Full Name -->
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-user mr-2 text-red-600"></i>
                    Full Name
                </label>
                <input 
                    type="text" 
                    id="name" 
                    name="name" 
                    required
                    placeholder="John Doe"
                    value="<?php echo $formData['name']; ?>"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent"
                >
            </div>

            <!-- Email -->
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-envelope mr-2 text-red-600"></i>
                    Email Address
                </label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    required
                    placeholder="student@ashesi.edu.gh"
                    value="<?php echo $formData['email']; ?>"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent"
                >
                <p class="text-xs text-gray-500 mt-1">Use your Ashesi email address</p>
            </div>

            <!-- Password -->
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-lock mr-2 text-red-600"></i>
                    Password
                </label>
                <div class="relative">
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        required
                        placeholder="Create a strong password"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent pr-12"
                    >
                    <button 
                        type="button" 
                        id="togglePassword"
                        class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700"
                    >
                        <i class="fas fa-eye-slash"></i>
                    </button>
                </div>
                <p class="text-xs text-gray-500 mt-1">At least 8 characters with uppercase, lowercase, and numbers</p>
            </div>

            <!-- Confirm Password -->
            <div>
                <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-lock mr-2 text-red-600"></i>
                    Confirm Password
                </label>
                <input 
                    type="password" 
                    id="confirm_password" 
                    name="confirm_password" 
                    required
                    placeholder="Re-enter your password"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent"
                >
            </div>

            <!-- Role Selection -->
            <div>
                <label for="role" class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-user-tag mr-2 text-red-600"></i>
                    Role
                </label>
                <select 
                    id="role" 
                    name="role" 
                    required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent"
                >
                    <option value="student" <?php echo $formData['role'] === 'student' ? 'selected' : ''; ?>>Student</option>
                    <option value="faculty" <?php echo $formData['role'] === 'faculty' ? 'selected' : ''; ?>>Faculty</option>
                </select>
            </div>

            <!-- Ashesi ID -->
            <div>
                <label for="ashesi_id" class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-id-card mr-2 text-red-600"></i>
                    Ashesi ID
                </label>
                <input 
                    type="text" 
                    id="ashesi_id" 
                    name="ashesi_id" 
                    required
                    placeholder="e.g., 2026001 or FAC001"
                    value="<?php echo $formData['ashesi_id']; ?>"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent"
                >
            </div>

            <!-- Submit Button -->
            <button 
                type="submit" 
                class="w-full bg-red-600 text-white py-3 px-4 rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition duration-200 font-semibold"
            >
                <i class="fas fa-user-plus mr-2"></i>
                Create Account
            </button>

            <!-- Login Link -->
            <div class="text-center mt-6">
                <p class="text-gray-600">
                    Already have an account? 
                    <a href="index.php" class="text-red-600 hover:text-red-800 font-semibold">
                        Sign in here
                    </a>
                </p>
            </div>
        </form>
    </div>

    <!-- JavaScript -->
    <script>
        // Toggle password visibility
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const icon = this.querySelector('i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            }
        });

        // Form validation
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            // Check if passwords match
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match!');
                return false;
            }

            // Password strength validation
            if (password.length < 8) {
                e.preventDefault();
                alert('Password must be at least 8 characters long!');
                return false;
            }

            // Check for uppercase, lowercase, and numbers
            const hasUpperCase = /[A-Z]/.test(password);
            const hasLowerCase = /[a-z]/.test(password);
            const hasNumbers = /\d/.test(password);
            
            if (!hasUpperCase || !hasLowerCase || !hasNumbers) {
                e.preventDefault();
                alert('Password must contain uppercase, lowercase, and numbers!');
                return false;
            }
        });
    </script>
</body>
</html>
