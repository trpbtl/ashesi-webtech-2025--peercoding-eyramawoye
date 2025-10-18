<?php
/**
 * Login Page (Landing Page)
 * 
 * This is the main entry point of the application.
 * Users enter their credentials here to access the system.
 * 
 * Flow:
 * 1. Display login form
 * 2. User submits credentials
 * 3. login_handler.php validates credentials
 * 4. Redirect to appropriate dashboard based on role
 */

// Include configuration and helper functions
require_once 'config.php';
require_once 'helpers.php';


// If user is already logged in, redirect to their dashboard
if (isLoggedIn()) {
    redirectToDashboard();
}

// Get flash message if any (success/error messages)
$flashMessage = getFlashMessage();

// Generate CSRF token for form security
$csrfToken = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Ashesi Attendance Manager</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Custom styles for better UX */
        .input-group {
            transition: all 0.3s ease;
        }
        .input-group:focus-within {
            transform: translateY(-2px);
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center bg-gradient-to-br from-red-50 to-gray-100">
    <div class="bg-white p-8 rounded-lg shadow-2xl w-full max-w-md">
        
        <!-- Header Section -->
        <div class="text-center mb-8">
            <i class="fas fa-graduation-cap text-5xl text-red-600 mb-4"></i>
            <h1 class="text-3xl font-bold text-gray-800">Ashesi Attendance Manager</h1>
            <p class="text-gray-600 mt-2">Sign in to your account</p>
        </div>

        <!-- Display Flash Messages (success/error notifications) -->
        <?php if ($flashMessage): ?>
            <div class="mb-6 p-4 rounded-lg <?php echo $flashMessage['type'] === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                <i class="fas <?php echo $flashMessage['type'] === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?> mr-2"></i>
                <?php echo htmlspecialchars($flashMessage['message']); ?>
            </div>
        <?php endif; ?>

        <!-- Display URL parameter errors (e.g., ?error=invalid_credentials) -->
        <?php if (isset($_GET['error'])): ?>
            <div class="mb-6 p-4 rounded-lg bg-red-100 text-red-800">
                <i class="fas fa-exclamation-circle mr-2"></i>
                <?php
                    // Map error codes to user-friendly messages
                    $errorMessages = [
                        'invalid_credentials' => 'Invalid email or password. Please try again.',
                        'login_required' => 'Please log in to access that page.',
                        'csrf_invalid' => 'Invalid security token. Please try again.',
                        'empty_fields' => 'Please fill in all fields.'
                    ];
                    echo isset($errorMessages[$_GET['error']]) ? $errorMessages[$_GET['error']] : 'An error occurred. Please try again.';
                ?>
            </div>
        <?php endif; ?>

        <!-- Login Form -->
        <form action="login_handler.php" method="POST" class="space-y-6">
            
            <!-- CSRF Token (hidden field for security) -->
            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
            
            <!-- Email Input -->
            <div class="input-group">
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
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent transition duration-200"
                    value="<?php echo isset($_GET['email']) ? htmlspecialchars($_GET['email']) : ''; ?>"
                >
            </div>

            <!-- Password Input with Toggle Visibility -->
            <div class="input-group">
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
                        placeholder="Enter your password"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent transition duration-200 pr-12"
                    >
                    <!-- Toggle password visibility button -->
                    <button 
                        type="button" 
                        id="togglePassword"
                        class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700"
                    >
                        <i class="fas fa-eye-slash"></i>
                    </button>
                </div>
            </div>

            <!-- Remember Me Checkbox (Optional) -->
            <div class="flex items-center justify-between">
                <label class="flex items-center">
                    <input type="checkbox" name="remember" class="form-checkbox h-4 w-4 text-red-600 rounded">
                    <span class="ml-2 text-sm text-gray-600">Remember me</span>
                </label>
                <!-- Optional: Forgot Password Link -->
                <!-- <a href="forgot_password.php" class="text-sm text-red-600 hover:text-red-800">Forgot password?</a> -->
            </div>

            <!-- Submit Button -->
            <button 
                type="submit" 
                class="w-full bg-red-600 text-white py-3 px-4 rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition duration-200 font-semibold"
            >
                <i class="fas fa-sign-in-alt mr-2"></i>
                Sign In
            </button>

            <!-- Registration Link -->
            <div class="text-center mt-6">
                <p class="text-gray-600">
                    Don't have an account? 
                    <a href="register.php" class="text-red-600 hover:text-red-800 font-semibold">
                        Register here
                    </a>
                </p>
            </div>
        </form>

        <!-- Test Credentials Information (Remove in production) -->
        <div class="mt-8 p-4 bg-gray-100 rounded-lg text-sm">
            <p class="font-semibold text-gray-700 mb-2">Test Credentials:</p>
            <p class="text-gray-600"><strong>Student:</strong> john.doe@ashesi.edu.gh</p>
            <p class="text-gray-600"><strong>Faculty:</strong> jane.smith@ashesi.edu.gh</p>
            <p class="text-gray-600"><strong>Password:</strong> Password123!</p>
        </div>
    </div>

    <!-- JavaScript for password toggle and form validation -->
    <script>
        // Toggle password visibility
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const icon = this.querySelector('i');
            
            if (passwordInput.type === 'password') {
                // Show password
                passwordInput.type = 'text';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            } else {
                // Hide password
                passwordInput.type = 'password';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            }
        });

        // Form validation before submission
        document.querySelector('form').addEventListener('submit', function(e) {
            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value;

            // Check if fields are empty
            if (email === '' || password === '') {
                e.preventDefault();
                alert('Please fill in all fields');
                return false;
            }

            // Basic email validation
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                e.preventDefault();
                alert('Please enter a valid email address');
                return false;
            }
        });
    </script>
</body>
</html>
