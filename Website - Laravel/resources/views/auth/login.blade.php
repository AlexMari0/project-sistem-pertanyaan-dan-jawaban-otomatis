<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

        :root {
            --primary-color: #667eea;
            --secondary-color: #764ba2;
            --accent-color: #f093fb;
            --success-color: #10b981;
            --error-color: #ef4444;
            --text-primary: #1f2937;
            --text-secondary: #6b7280;
            --bg-glass: rgba(255, 255, 255, 0.15);
            --shadow-soft: 0 8px 32px rgba(31, 38, 135, 0.37);
            --shadow-hover: 0 12px 40px rgba(31, 38, 135, 0.5);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(-45deg, #22c55e, #16a34a, #15803d, #166534);
            background-size: 400% 400%;
            animation: gradientShift 15s ease infinite;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        @keyframes gradientShift {
            0% {
                background-position: 0% 50%;
            }

            50% {
                background-position: 100% 50%;
            }

            100% {
                background-position: 0% 50%;
            }
        }

        /* Floating Elements */
        .floating-shapes {
            position: absolute;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: 0;
        }

        .shape {
            position: absolute;
            opacity: 0.1;
            animation: float 20s infinite linear;
        }

        .shape:nth-child(1) {
            top: 20%;
            left: 20%;
            width: 60px;
            height: 60px;
            background: white;
            border-radius: 50%;
            animation-delay: 0s;
        }

        .shape:nth-child(2) {
            top: 60%;
            right: 20%;
            width: 80px;
            height: 80px;
            background: white;
            clip-path: polygon(50% 0%, 0% 100%, 100% 100%);
            animation-delay: 5s;
        }

        .shape:nth-child(3) {
            bottom: 20%;
            left: 30%;
            width: 40px;
            height: 40px;
            background: white;
            transform: rotate(45deg);
            animation-delay: 10s;
        }

        @keyframes float {
            0% {
                transform: translateY(0px) rotate(0deg);
            }

            33% {
                transform: translateY(-30px) rotate(120deg);
            }

            66% {
                transform: translateY(15px) rotate(240deg);
            }

            100% {
                transform: translateY(0px) rotate(360deg);
            }
        }

        /* Glass Card */
        .login-container {
            position: relative;
            z-index: 10;
            max-width: 420px;
            width: 100%;
            margin: 0 20px;
        }

        .login-card {
            background: var(--bg-glass);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 20px;
            box-shadow: var(--shadow-soft);
            padding: 40px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .login-card:hover {
            box-shadow: var(--shadow-hover);
            transform: translateY(-5px);
        }

        .login-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
        }

        /* Header */
        .login-header {
            text-align: center;
            margin-bottom: 35px;
        }

        .login-title {
            font-size: 28px;
            font-weight: 700;
            color: white;
            margin-bottom: 8px;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .login-subtitle {
            color: rgba(255, 255, 255, 0.8);
            font-size: 14px;
            font-weight: 400;
        }

        /* Form Styles */
        .form-group {
            position: relative;
            margin-bottom: 24px;
        }

        .form-label {
            color: rgba(255, 255, 255, 0.9);
            font-weight: 500;
            font-size: 14px;
            margin-bottom: 8px;
            display: block;
        }

        .input-wrapper {
            position: relative;
        }

        .form-control {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            color: white;
            font-size: 15px;
            padding: 14px 50px 14px 16px;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }

        .form-control:focus {
            background: rgba(255, 255, 255, 0.15);
            border-color: rgba(255, 255, 255, 0.4);
            box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.1);
            color: white;
            outline: none;
        }

        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.5);
        }

        .input-icon {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: rgba(255, 255, 255, 0.6);
            font-size: 16px;
            transition: color 0.3s ease;
        }

        .form-control:focus+.input-icon {
            color: rgba(255, 255, 255, 0.9);
        }

        /* Password Toggle */
        .password-toggle {
            cursor: pointer;
            user-select: none;
        }

        .password-toggle:hover {
            color: white;
        }

        /* Button */
        .btn-login {
            background: linear-gradient(135deg, #22c55e, #16a34a);
            border: none;
            border-radius: 12px;
            color: white;
            font-weight: 600;
            font-size: 16px;
            padding: 14px;
            width: 100%;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            margin-top: 8px;
        }

        .btn-login::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }

        .btn-login:hover::before {
            left: 100%;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(34, 197, 94, 0.4);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        /* Loading State */
        .btn-login.loading {
            pointer-events: none;
        }

        .btn-login .spinner {
            display: none;
            width: 20px;
            height: 20px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 1s ease-in-out infinite;
            margin-right: 8px;
        }

        .btn-login.loading .spinner {
            display: inline-block;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        /* Footer */
        .login-footer {
            text-align: center;
            margin-top: 28px;
            padding-top: 24px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .register-link {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            font-size: 14px;
            transition: color 0.3s ease;
        }

        .register-link:hover {
            color: white;
            text-decoration: underline;
        }

        .register-link strong {
            color: #22c55e;
            font-weight: 600;
        }

        /* Error Messages */
        .error-message {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            border-radius: 8px;
            color: #fecaca;
            padding: 12px 16px;
            margin-bottom: 20px;
            font-size: 14px;
            backdrop-filter: blur(10px);
        }

        /* Success Messages */
        .success-message {
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid rgba(16, 185, 129, 0.3);
            border-radius: 8px;
            color: #a7f3d0;
            padding: 12px 16px;
            margin-bottom: 20px;
            font-size: 14px;
            backdrop-filter: blur(10px);
        }

        /* Responsive */
        @media (max-width: 480px) {
            .login-card {
                padding: 30px 25px;
                margin: 0 15px;
            }

            .login-title {
                font-size: 24px;
            }
        }

        /* Input Validation States */
        .form-control.is-valid {
            border-color: rgba(16, 185, 129, 0.5);
            background: rgba(16, 185, 129, 0.05);
        }

        .form-control.is-invalid {
            border-color: rgba(239, 68, 68, 0.5);
            background: rgba(239, 68, 68, 0.05);
        }

        .valid-feedback {
            color: #a7f3d0;
            font-size: 12px;
            margin-top: 4px;
        }

        .invalid-feedback {
            color: #fecaca;
            font-size: 12px;
            margin-top: 4px;
        }
    </style>
</head>

<body>
    <!-- Floating Background Elements -->
    <div class="floating-shapes">
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
    </div>

    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <h1 class="login-title">Welcome Back</h1>
                <p class="login-subtitle">Sign in to your account</p>
            </div>

            <!-- Example error message (remove/modify as needed) -->
            <!-- <div class="error-message">
                <i class="fas fa-exclamation-circle me-2"></i>
                Invalid username or password
            </div> -->

            <form action="{{ route('login') }}" method="POST" id="loginForm">
                @csrf
                <div class="form-group">
                    <label for="username" class="form-label">Username</label>
                    <div class="input-wrapper">
                        <input type="text" class="form-control" id="username" name="username"
                            placeholder="Enter your username" required>
                        <i class="fas fa-user input-icon"></i>
                    </div>
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-wrapper">
                        <input type="password" class="form-control" id="password" name="password"
                            placeholder="Enter your password" required>
                        <i class="fas fa-eye password-toggle input-icon" id="togglePassword"></i>
                    </div>
                </div>

                <button type="submit" class="btn-login" id="loginBtn">
                    <div class="spinner"></div>
                    <span class="btn-text">Sign In</span>
                </button>
            </form>

            <div class="login-footer">
                <p class="register-link">
                    Don't have an account?
                    <a href="{{ route('register.form') }}">
                        <strong>Create one</strong>
                    </a>
                </p>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('loginForm');
            const loginBtn = document.getElementById('loginBtn');
            const togglePassword = document.getElementById('togglePassword');
            const passwordInput = document.getElementById('password');
            const usernameInput = document.getElementById('username');

            // Password toggle functionality
            togglePassword.addEventListener('click', function() {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);

                // Toggle icon
                this.classList.toggle('fa-eye');
                this.classList.toggle('fa-eye-slash');
            });

            // Form submission with loading state
            form.addEventListener('submit', function(e) {
                loginBtn.classList.add('loading');
                loginBtn.querySelector('.btn-text').textContent = 'Signing In...';

                // Simulate loading (remove this in production)
                setTimeout(() => {
                    // Reset button state if form validation fails
                    // In real implementation, this would be handled by server response
                    loginBtn.classList.remove('loading');
                    loginBtn.querySelector('.btn-text').textContent = 'Sign In';
                }, 2000);
            });

            // Input validation feedback
            function validateInput(input, isValid) {
                input.classList.remove('is-valid', 'is-invalid');
                if (isValid) {
                    input.classList.add('is-valid');
                } else {
                    input.classList.add('is-invalid');
                }
            }

            // Real-time validation
            usernameInput.addEventListener('input', function() {
                const isValid = this.value.length >= 3;
                validateInput(this, isValid);
            });

            passwordInput.addEventListener('input', function() {
                const isValid = this.value.length >= 6;
                validateInput(this, isValid);
            });

            // Enhanced focus effects
            const inputs = document.querySelectorAll('.form-control');
            inputs.forEach(input => {
                input.addEventListener('focus', function() {
                    this.parentElement.parentElement.classList.add('focused');
                });

                input.addEventListener('blur', function() {
                    this.parentElement.parentElement.classList.remove('focused');
                });
            });
        });
    </script>
</body>

</html>