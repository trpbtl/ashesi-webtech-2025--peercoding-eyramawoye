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
    <title>Login - Course Management System</title>
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
<body class="min-h-screen flex items-center justify-center bg-ashesi">
    <div class="bg-white p-8 rounded-lg shadow-2xl w-full max-w-md relative z-10">
        <div class="text-center mb-8">
            <i class="fas fa-graduation-cap text-5xl text-ashesi-maroon mb-4"></i>
            <h1 class="text-3xl font-bold text-gray-800">Ashesi Course Management</h1>
            <p class="text-gray-600 mt-2">Sign in to your account</p>
        </div>

        <?php if ($flashMessage): ?>
            <div class="mb-6 p-4 rounded-lg <?php echo $flashMessage['type'] === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                <i class="fas <?php echo $flashMessage['type'] === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?> mr-2"></i>
                <?php echo htmlspecialchars($flashMessage['message']); ?>
            </div>
        <?php endif; ?>

        <form action="login_handler.php" method="POST" class="space-y-6">
            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
            
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-envelope text-ashesi-maroon mr-2"></i>Email Address
                </label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    required 
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-ashesi-maroon focus:border-transparent"
                    placeholder="your.email@ashesi.edu.gh"
                >
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-lock text-ashesi-maroon mr-2"></i>Password
                </label>
                <div class="relative">
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        required 
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-ashesi-maroon focus:border-transparent"
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

            <button 
                type="submit" 
                class="w-full bg-ashesi-maroon text-white py-3 rounded-lg hover:bg-red-900 transition duration-200 font-semibold shadow-lg"
            >
                <i class="fas fa-sign-in-alt mr-2"></i>Sign In
            </button>

            <div class="text-center mt-4">
                <p class="text-gray-600">
                    Don't have an account? 
                    <a href="register.php" class="text-ashesi-maroon hover:text-red-900 font-semibold">Register here</a>
                </p>
            </div>
        </form>
    </div>

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
    </script>
</body>
</html>
