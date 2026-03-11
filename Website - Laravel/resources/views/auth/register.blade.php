<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Join Our Learning Platform</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary-color: #10b981;
            --secondary-color: #047857;
            --accent-color: #34d399;
            --success-color: #4ade80;
            --warning-color: #fbbf24;
            --error-color: #ef4444;
            --text-primary: #1e293b;
            --text-secondary: #64748b;
            --glass-bg: rgba(255, 255, 255, 0.25);
            --glass-border: rgba(255, 255, 255, 0.18);
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow-x: hidden;
        }

        /* Animated background elements */
        .bg-shapes {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: 0;
        }

        .shape {
            position: absolute;
            border-radius: 50%;
            background: linear-gradient(45deg, rgba(255, 255, 255, 0.1), rgba(255, 255, 255, 0.05));
            animation: float 6s ease-in-out infinite;
        }

        .shape:nth-child(1) {
            width: 80px;
            height: 80px;
            top: 10%;
            left: 20%;
            animation-delay: 0s;
        }

        .shape:nth-child(2) {
            width: 120px;
            height: 120px;
            top: 70%;
            left: 80%;
            animation-delay: 2s;
        }

        .shape:nth-child(3) {
            width: 60px;
            height: 60px;
            top: 20%;
            right: 10%;
            animation-delay: 4s;
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0px) rotate(0deg);
                opacity: 0.7;
            }

            50% {
                transform: translateY(-20px) rotate(180deg);
                opacity: 0.3;
            }
        }

        /* Glass morphism card */
        .register-container {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 480px;
            margin: 20px;
        }

        .register-card {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 24px;
            padding: 40px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            animation: slideUp 0.6s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .register-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
        }

        /* Header styling */
        .header-section {
            text-align: center;
            margin-bottom: 32px;
        }

        .welcome-icon {
            width: 64px;
            height: 64px;
            background: linear-gradient(135deg, var(--accent-color), var(--primary-color));
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.05);
            }
        }

        .welcome-icon i {
            color: white;
            font-size: 28px;
        }

        .header-title {
            font-size: 28px;
            font-weight: 700;
            color: white;
            margin-bottom: 8px;
        }

        .header-subtitle {
            color: rgba(255, 255, 255, 0.8);
            font-size: 16px;
            font-weight: 400;
        }

        /* Form styling */
        .form-group {
            margin-bottom: 24px;
            position: relative;
        }

        .form-label {
            color: white;
            font-weight: 500;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            font-size: 14px;
        }

        .form-label i {
            margin-right: 8px;
            width: 16px;
        }

        .form-control {
            background: rgba(255, 255, 255, 0.1);
            border: 2px solid rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            padding: 16px;
            color: white;
            font-size: 16px;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }

        .form-control:focus {
            background: rgba(255, 255, 255, 0.15);
            border-color: var(--accent-color);
            box-shadow: 0 0 0 3px rgba(52, 211, 153, 0.1);
            outline: none;
            transform: translateY(-2px);
        }

        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.6);
        }

        /* Custom select styling */
        .form-select {
            background: rgba(255, 255, 255, 0.1);
            border: 2px solid rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            padding: 16px;
            color: white;
            font-size: 16px;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
            cursor: pointer;
        }

        .form-select:focus {
            background: rgba(255, 255, 255, 0.15);
            border-color: var(--accent-color);
            box-shadow: 0 0 0 3px rgba(52, 211, 153, 0.1);
            outline: none;
        }

        .form-select option {
            background: var(--text-primary);
            color: white;
        }

        /* Button styling */
        .btn-register {
            background: linear-gradient(135deg, var(--accent-color), var(--primary-color));
            border: none;
            border-radius: 12px;
            padding: 16px;
            font-size: 16px;
            font-weight: 600;
            color: white;
            width: 100%;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .btn-register::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }

        .btn-register:hover::before {
            left: 100%;
        }

        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(52, 211, 153, 0.3);
        }

        .btn-register:active {
            transform: translateY(0);
        }

        /* Loading state */
        .btn-register.loading {
            pointer-events: none;
        }

        .btn-register.loading .btn-text {
            opacity: 0;
        }

        .btn-register .spinner {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .btn-register.loading .spinner {
            opacity: 1;
        }

        /* Footer links */
        .footer-links {
            text-align: center;
            margin-top: 24px;
            padding-top: 24px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .footer-links p {
            color: rgba(255, 255, 255, 0.8);
            margin-bottom: 0;
        }

        .footer-links a {
            color: var(--accent-color);
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .footer-links a:hover {
            color: white;
            text-decoration: underline;
        }

        /* Validation styles */
        .form-control.is-invalid {
            border-color: var(--error-color);
            background: rgba(239, 68, 68, 0.1);
        }

        .form-control.is-valid {
            border-color: var(--success-color);
            background: rgba(74, 222, 128, 0.1);
        }

        .invalid-feedback {
            color: #fecaca;
            font-size: 14px;
            margin-top: 4px;
        }

        .valid-feedback {
            color: #bbf7d0;
            font-size: 14px;
            margin-top: 4px;
        }

        /* Responsive design */
        @media (max-width: 576px) {
            .register-card {
                padding: 24px;
                margin: 16px;
                border-radius: 20px;
            }

            .header-title {
                font-size: 24px;
            }

            .welcome-icon {
                width: 56px;
                height: 56px;
            }

            .welcome-icon i {
                font-size: 24px;
            }
        }

        /* Password strength indicator */
        .password-strength {
            height: 4px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 2px;
            margin-top: 8px;
            overflow: hidden;
        }

        .password-strength-fill {
            height: 100%;
            width: 0%;
            transition: all 0.3s ease;
        }

        .strength-weak {
            background: var(--error-color);
        }

        .strength-medium {
            background: var(--warning-color);
        }

        .strength-strong {
            background: var(--success-color);
        }
    </style>
</head>

<body>
    <div class="bg-shapes">
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
    </div>

    <div class="register-container">
        <div class="register-card">
            <div class="header-section">
                <div class="welcome-icon">
                    <i class="fas fa-graduation-cap"></i>
                </div>
                <h1 class="header-title">Join Our Platform</h1>
                <p class="header-subtitle">Create your account to start learning</p>
            </div>

            <form id="registerForm" action="{{ route('register') }}" method="POST" novalidate>
                @csrf
                <div class="form-group">
                    <label for="username" class="form-label">
                        <i class="fas fa-user"></i>
                        Username
                    </label>
                    <input type="text" class="form-control" id="username" name="username"
                        placeholder="Enter your username" required autocomplete="username">
                    <div class="invalid-feedback"></div>
                    <div class="valid-feedback">Username looks good!</div>
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">
                        <i class="fas fa-lock"></i>
                        Password
                    </label>
                    <input type="password" class="form-control" id="password" name="password"
                        placeholder="Create a strong password" required autocomplete="new-password">
                    <div class="password-strength">
                        <div class="password-strength-fill"></div>
                    </div>
                    <div class="invalid-feedback"></div>
                    <div class="valid-feedback">Password strength looks good!</div>
                </div>

                <div class="form-group">
                    <label for="role" class="form-label">
                        <i class="fas fa-user-tag"></i>
                        Role
                    </label>
                    <select class="form-select" id="role" name="role" required>
                        <option value="">Select your role</option>
                        <option value="teacher">👨‍🏫 Teacher</option>
                        <option value="student">👨‍🎓 Student</option>
                    </select>
                    <div class="invalid-feedback"></div>
                    <div class="valid-feedback">Role selected!</div>
                </div>

                <button type="submit" class="btn-register" id="registerBtn">
                    <span class="btn-text">
                        <i class="fas fa-user-plus me-2"></i>
                        Create Account
                    </span>
                    <div class="spinner">
                        <i class="fas fa-spinner fa-spin"></i>
                    </div>
                </button>
            </form>

            <div class="footer-links">
                <p>Already have an account? <a href="{{ route('login.form') }}">Sign in here</a></p>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('registerForm');
            const usernameInput = document.getElementById('username');
            const passwordInput = document.getElementById('password');
            const roleSelect = document.getElementById('role');
            const registerBtn = document.getElementById('registerBtn');
            const strengthFill = document.querySelector('.password-strength-fill');

            // Real-time validation
            function validateField(field, validationFn) {
                const isValid = validationFn(field.value);
                const feedback = field.parentNode.querySelector('.invalid-feedback');

                if (isValid.valid) {
                    field.classList.remove('is-invalid');
                    field.classList.add('is-valid');
                    feedback.textContent = '';
                } else {
                    field.classList.remove('is-valid');
                    field.classList.add('is-invalid');
                    feedback.textContent = isValid.message;
                }

                return isValid.valid;
            }

            // Username validation
            function validateUsername(username) {
                if (!username) {
                    return {
                        valid: false,
                        message: 'Username is required'
                    };
                }
                if (username.length < 3) {
                    return {
                        valid: false,
                        message: 'Username must be at least 3 characters'
                    };
                }
                if (!/^[a-zA-Z0-9_]+$/.test(username)) {
                    return {
                        valid: false,
                        message: 'Username can only contain letters, numbers, and underscores'
                    };
                }
                return {
                    valid: true
                };
            }

            // Password validation and strength
            function validatePassword(password) {
                if (!password) {
                    return {
                        valid: false,
                        message: 'Password is required',
                        strength: 0
                    };
                }

                let strength = 0;
                let message = '';

                if (password.length >= 8) strength += 25;
                else message = 'Password must be at least 8 characters';

                if (/[a-z]/.test(password)) strength += 25;
                if (/[A-Z]/.test(password)) strength += 25;
                if (/[0-9]/.test(password)) strength += 15;
                if (/[^a-zA-Z0-9]/.test(password)) strength += 10;

                // Update strength indicator
                strengthFill.style.width = strength + '%';

                if (strength < 50) {
                    strengthFill.className = 'password-strength-fill strength-weak';
                    if (!message) message = 'Password is too weak';
                } else if (strength < 85) {
                    strengthFill.className = 'password-strength-fill strength-medium';
                } else {
                    strengthFill.className = 'password-strength-fill strength-strong';
                }

                return {
                    valid: strength >= 50,
                    message: message,
                    strength: strength
                };
            }

            // Role validation
            function validateRole(role) {
                if (!role) {
                    return {
                        valid: false,
                        message: 'Please select a role'
                    };
                }
                return {
                    valid: true
                };
            }

            // Event listeners
            usernameInput.addEventListener('input', function() {
                validateField(this, validateUsername);
            });

            passwordInput.addEventListener('input', function() {
                validateField(this, validatePassword);
            });

            roleSelect.addEventListener('change', function() {
                validateField(this, validateRole);
            });

            // Form submission
            form.addEventListener('submit', function(e) {
                e.preventDefault();

                const isUsernameValid = validateField(usernameInput, validateUsername);
                const isPasswordValid = validateField(passwordInput, validatePassword);
                const isRoleValid = validateField(roleSelect, validateRole);

                if (isUsernameValid && isPasswordValid && isRoleValid) {
                    // Show loading state
                    registerBtn.classList.add('loading');
                    registerBtn.disabled = true;

                    // Simulate form submission delay
                    setTimeout(() => {
                        // In real implementation, submit the form
                        // form.submit();

                        // For demo purposes, show success
                        alert('Registration successful! (Demo mode)');

                        // Reset loading state
                        registerBtn.classList.remove('loading');
                        registerBtn.disabled = false;
                    }, 2000);
                }
            });

            // Add floating animation to shapes
            const shapes = document.querySelectorAll('.shape');
            shapes.forEach((shape, index) => {
                shape.style.animationDelay = `${index * 2}s`;
            });
        });
    </script>
</body>

</html>
