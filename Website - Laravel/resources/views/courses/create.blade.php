@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-10 col-lg-8 col-xl-7">
                <!-- Header Section -->
                <div class="form-header text-center mb-5">
                    <div class="icon-wrapper mb-3">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    <h1 class="display-6 fw-bold text-primary mb-2">Buat Course Baru</h1>
                    <p class="text-muted lead">Mulai perjalanan pembelajaran dengan membuat course yang menarik</p>
                </div>

                <!-- Error Alert with Enhanced Design -->
                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0" role="alert">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <div class="flex-grow-1">
                                <strong>Terdapat kesalahan:</strong>
                                <ul class="mb-0 mt-2">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <!-- Main Form Card -->
                <div class="form-card">
                    <form action="{{ route('courses.store') }}" method="POST" class="needs-validation" novalidate>
                        @csrf

                        <!-- Course Title Section -->
                        <div class="form-section">
                            <div class="section-header">
                                <i class="fas fa-bookmark section-icon"></i>
                                <h5 class="section-title">Informasi Course</h5>
                            </div>

                            <div class="form-floating mb-4">
                                <input type="text" name="title" class="form-control custom-input" id="title"
                                    placeholder="Masukkan judul course" value="{{ old('title') }}" required>
                                <label for="title">
                                    <i class="fas fa-pencil-alt me-2"></i>Judul Course
                                </label>
                                <div class="invalid-feedback">
                                    Judul course harus diisi.
                                </div>
                            </div>

                            <div class="form-floating mb-4">
                                <textarea name="description" class="form-control custom-textarea" id="description"
                                    placeholder="Deskripsikan course Anda" style="height: 120px" required>{{ old('description') }}</textarea>
                                <label for="description">
                                    <i class="fas fa-align-left me-2"></i>Deskripsi Course
                                </label>
                                <div class="invalid-feedback">
                                    Deskripsi course harus diisi.
                                </div>
                            </div>
                        </div>

                        <!-- Security Section -->
                        <div class="form-section">
                            <div class="section-header">
                                <i class="fas fa-shield-alt section-icon"></i>
                                <h5 class="section-title">Keamanan Course</h5>
                            </div>

                            <div class="form-floating mb-4">
                                <input type="password" name="join_password" class="form-control custom-input"
                                    id="join_password" placeholder="Masukkan password" required>
                                <label for="join_password">
                                    <i class="fas fa-lock me-2"></i>Password untuk Bergabung
                                </label>
                                <div class="invalid-feedback">
                                    Password harus diisi.
                                </div>
                                <div class="form-text">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Password akan digunakan siswa untuk bergabung ke course ini
                                </div>
                            </div>

                            <!-- Password Toggle -->
                            <div class="form-check mb-4">
                                <input class="form-check-input" type="checkbox" id="showPassword">
                                <label class="form-check-label" for="showPassword">
                                    Tampilkan password
                                </label>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="form-actions">
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="{{ route('courses.index') }}" class="btn btn-outline-secondary btn-lg me-md-2">
                                    <i class="fas fa-arrow-left me-2"></i>Kembali
                                </a>
                                <button type="submit" class="btn btn-primary btn-lg custom-btn">
                                    <i class="fas fa-save me-2"></i>Buat Course
                                    <span class="btn-ripple"></span>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Enhanced Styles -->
    <style>
        :root {
            --primary-color: #1cb2a2;
            --primary-dark: #159688;
            --primary-light: #e0f2f1;
            --text-primary: #1D3341;
            --text-secondary: #6c757d;
            --border-radius: 16px;
            --shadow-sm: 0 2px 8px rgba(0, 0, 0, 0.08);
            --shadow-md: 0 4px 20px rgba(0, 0, 0, 0.12);
            --shadow-lg: 0 8px 32px rgba(0, 0, 0, 0.16);
        }

        body {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            min-height: 100vh;
        }

        .container-fluid {
            padding: 2rem 1rem;
        }

        /* Form Header */
        .form-header {
            margin-bottom: 3rem;
        }

        .icon-wrapper {
            width: 80px;
            height: 80px;
            margin: 0 auto;
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: var(--shadow-md);
        }

        .icon-wrapper i {
            font-size: 2rem;
            color: white;
        }

        .text-primary {
            color: var(--text-primary) !important;
        }

        /* Form Card */
        .form-card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-md);
            padding: 2.5rem;
            border: 1px solid rgba(0, 0, 0, 0.05);
            position: relative;
            overflow: hidden;
        }

        .form-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-color), var(--primary-dark));
        }

        /* Form Sections */
        .form-section {
            margin-bottom: 2.5rem;
            padding-bottom: 2rem;
            border-bottom: 1px solid #e9ecef;
        }

        .form-section:last-of-type {
            border-bottom: none;
            padding-bottom: 0;
        }

        .section-header {
            display: flex;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 0.75rem;
            border-bottom: 2px solid var(--primary-light);
        }

        .section-icon {
            color: var(--primary-color);
            font-size: 1.25rem;
            margin-right: 0.75rem;
        }

        .section-title {
            color: var(--text-primary);
            font-weight: 600;
            margin: 0;
            font-size: 1.1rem;
        }

        /* Enhanced Form Controls */
        .form-floating {
            position: relative;
        }

        .custom-input,
        .custom-textarea {
            border: 2px solid #e9ecef;
            border-radius: 12px;
            padding: 1rem 1rem 1rem 3rem;
            font-size: 1rem;
            transition: all 0.3s ease;
            background-color: #fafafa;
        }

        .custom-input:focus,
        .custom-textarea:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(28, 178, 162, 0.1);
            background-color: white;
            outline: none;
        }

        .form-floating>label {
            color: var(--text-secondary);
            font-weight: 500;
            padding-left: 3rem;
        }

        .form-floating>.form-control:focus~label,
        .form-floating>.form-control:not(:placeholder-shown)~label {
            color: var(--primary-color);
            transform: scale(0.85) translateY(-0.5rem) translateX(-0.25rem);
        }

        /* Custom Buttons */
        .custom-btn {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            border: none;
            border-radius: 12px;
            font-weight: 600;
            padding: 0.875rem 2rem;
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
            text-transform: none;
            letter-spacing: 0.025em;
        }

        .custom-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(28, 178, 162, 0.3);
            background: linear-gradient(135deg, var(--primary-dark), var(--primary-color));
        }

        .custom-btn:active {
            transform: translateY(0);
        }

        .btn-outline-secondary {
            border: 2px solid #6c757d;
            color: #6c757d;
            border-radius: 12px;
            font-weight: 500;
            padding: 0.875rem 2rem;
            transition: all 0.3s ease;
        }

        .btn-outline-secondary:hover {
            background-color: #6c757d;
            border-color: #6c757d;
            transform: translateY(-1px);
        }

        /* Form Actions */
        .form-actions {
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid #e9ecef;
        }

        /* Alert Enhancements */
        .alert-danger {
            background: linear-gradient(135deg, #fff5f5, #fed7d7);
            border: 1px solid #feb2b2;
            color: #c53030;
            border-radius: 12px;
        }

        /* Form Validation */
        .was-validated .form-control:valid,
        .form-control.is-valid {
            border-color: #28a745;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 8 8'%3e%3cpath fill='%2328a745' d='m2.3 6.73.72-.72L4.77 7.77 8 4.54l-.72-.72L4.77 6.33z'/%3e%3c/svg%3e");
        }

        .was-validated .form-control:invalid,
        .form-control.is-invalid {
            border-color: #dc3545;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23dc3545'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath d='m5.8 4.6 1.4 1.4m0-1.4L5.8 6'/%3e%3c/svg%3e");
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .container-fluid {
                padding: 1rem 0.5rem;
            }

            .form-card {
                padding: 1.5rem;
            }

            .form-header {
                margin-bottom: 2rem;
            }

            .icon-wrapper {
                width: 60px;
                height: 60px;
            }

            .icon-wrapper i {
                font-size: 1.5rem;
            }
        }

        /* Loading Animation */
        .btn-loading {
            position: relative;
            color: transparent;
        }

        .btn-loading::after {
            content: '';
            position: absolute;
            width: 16px;
            height: 16px;
            top: 50%;
            left: 50%;
            margin-left: -8px;
            margin-top: -8px;
            border: 2px solid #ffffff;
            border-radius: 50%;
            border-top-color: transparent;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }
    </style>

    <!-- Enhanced JavaScript -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Form validation
            const form = document.querySelector('.needs-validation');
            const inputs = form.querySelectorAll('input, textarea');

            // Real-time validation
            inputs.forEach(input => {
                input.addEventListener('blur', function() {
                    if (this.checkValidity()) {
                        this.classList.add('is-valid');
                        this.classList.remove('is-invalid');
                    } else {
                        this.classList.add('is-invalid');
                        this.classList.remove('is-valid');
                    }
                });

                input.addEventListener('input', function() {
                    if (this.classList.contains('is-invalid') && this.checkValidity()) {
                        this.classList.remove('is-invalid');
                        this.classList.add('is-valid');
                    }
                });
            });

            // Password toggle
            const showPasswordCheckbox = document.getElementById('showPassword');
            const passwordInput = document.getElementById('join_password');

            showPasswordCheckbox.addEventListener('change', function() {
                passwordInput.type = this.checked ? 'text' : 'password';
            });

            // Form submission with loading state
            form.addEventListener('submit', function(e) {
                if (!form.checkValidity()) {
                    e.preventDefault();
                    e.stopPropagation();
                } else {
                    const submitBtn = form.querySelector('button[type="submit"]');
                    submitBtn.classList.add('btn-loading');
                    submitBtn.disabled = true;
                }
                form.classList.add('was-validated');
            });

            // Auto-resize textarea
            const textarea = document.getElementById('description');
            textarea.addEventListener('input', function() {
                this.style.height = 'auto';
                this.style.height = Math.min(this.scrollHeight, 200) + 'px';
            });
        });
    </script>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
@endsection
