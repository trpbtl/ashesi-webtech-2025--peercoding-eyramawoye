<?php

require_once 'config.php';
require_once 'helpers.php';

if (isLoggedIn()) {
    redirectToDashboard();
}

$flashMessage = getFlashMessage();

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
</head>
<body class="min-h-screen flex items-center justify-center bg-gradient-to-br from-red-50 to-gray-100">
    <div class="bg-white p-8 rounded-lg shadow-2xl w-full max-w-md">
        <!-- Header -->
        <div class="text-center mb-8">
            <i class="fas fa-graduation-cap text-4xl text-red-600 mb-4"></i>
            <h1 class="text-2xl font-bold text-gray-800">Ashesi Attendance Manager</h1>
            <p class="text-gray-600 mt-2">Sign in to your account</p>
        </div>
        <!-- Flash Messages -->
        <?php if ($flashMessage): ?>
            <div class="mb-6 p-4 rounded-lg <?php echo $flashMessage['type'] === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                <i class="fas <?php echo $flashMessage['type'] === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?> mr-2"></i>
                <?php echo htmlspecialchars($flashMessage['message']); ?>
            </div>
        <?php endif; ?>
        <!-- Login Form -->
        <form action="login_handler.php" method="POST" class="space-y-6">
            <!-- CSRF Token -->
            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
            <!-- Email Field -->
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-envelope text-red-600 mr-2"></i>Email Address
                </label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    required 
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent transition duration-200"
                    placeholder="your.email@ashesi.edu.gh"
                >
            </div>
            <!-- Password Field -->
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-lock text-red-600 mr-2"></i>Password
                </label>
                <div class="relative">
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        required 
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent transition duration-200"
                        placeholder="Enter your password"
                    >
                    <button 
                        type="button" 
                        id="togglePassword" 
                        class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700"
                    >
                        <i class="fas fa-eye-slash"></i>
                    </button>
                </div>
            </div>
            <!-- Remember Me & Forgot Password -->
            <div class="flex items-center justify-between">
                <label class="flex items-center">
                    <input type="checkbox" name="remember" class="rounded text-red-600 focus:ring-red-500">
                    <span class="ml-2 text-sm text-gray-600">Remember me</span>
                </label>
                <a href="#" class="text-sm text-red-600 hover:text-red-700">Forgot password?</a>
            </div>
            <!-- Submit Button -->
            <button 
                type="submit" 
                class="w-full bg-red-600 text-white py-3 rounded-lg hover:bg-red-700 transition duration-200 font-semibold shadow-lg"
            >
                <i class="fas fa-sign-in-alt mr-2"></i>Sign In
            </button>
            <!-- Register Link -->
            <div class="text-center mt-4">
                <p class="text-gray-600">
                    Don't have an account? 
                    <a href="register.php" class="text-red-600 hover:text-red-700 font-semibold">Register here</a>
                </p>
            </div>
        </form>
    </div>
    <!-- JavaScript -->
    <script>

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

        document.querySelector('form').addEventListener('submit', function(e) {
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            if (!email || !password) {
                e.preventDefault();
                alert('Please fill in all fields');
                return false;
            }

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