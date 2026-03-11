@extends('layouts.app')

@section('content')
    <div class="quiz-container">
        <!-- Hero Section with Gradient Background -->
        <div class="quiz-hero">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-lg-8">
                        <div class="quiz-header-content">
                            <div class="breadcrumb-nav mb-4">
                                <a href="{{ route('courses.show', $course->id) }}" class="breadcrumb-link">
                                    <i class="bi bi-arrow-left me-2"></i>{{ $course->title }}
                                </a>
                            </div>
                            <h1 class="quiz-title">{{ $quiz->title }}</h1>
                            <p class="quiz-subtitle">{{ $quiz->description }}</p>
                        </div>
                    </div>
                    <div class="col-lg-4 text-center">
                        <div class="quiz-illustration">
                            <div class="quiz-icon-wrapper">
                                <i class="bi bi-clipboard-check quiz-icon"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="container quiz-content">
            <!-- Flash Messages -->
            @if (session('success'))
                <div class="alert alert-success-custom mb-5" role="alert">
                    <div class="alert-icon">
                        <i class="bi bi-check-circle-fill"></i>
                    </div>
                    <div class="alert-content">
                        <h6 class="alert-title mb-2">Success!</h6>
                        <p class="alert-text mb-0">{{ session('success') }}</p>
                    </div>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger-custom mb-5" role="alert">
                    <div class="alert-icon">
                        <i class="bi bi-x-circle-fill"></i>
                    </div>
                    <div class="alert-content">
                        <h6 class="alert-title mb-2">Notice</h6>
                        <p class="alert-text mb-0">{{ session('error') }}</p>
                    </div>
                </div>
            @endif

            @if (session('warning'))
                <div class="alert alert-warning-custom mb-5" role="alert">
                    <div class="alert-icon">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                    </div>
                    <div class="alert-content">
                        <h6 class="alert-title mb-2">Warning</h6>
                        <p class="alert-text mb-0">{{ session('warning') }}</p>
                    </div>
                </div>
            @endif

            <!-- Quiz Status Alert -->
            @if ($quiz->due_date && now()->gt($quiz->due_date))
                <div class="alert alert-warning-custom mb-5" role="alert">
                    <div class="alert-icon">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                    </div>
                    <div class="alert-content">
                        <h6 class="alert-title mb-2">Quiz Expired</h6>
                        <p class="alert-text mb-0">This quiz is past its due date and can no longer be taken.</p>
                    </div>
                </div>
            @endif

            <!-- Check if user has already attempted -->
            @php
                $hasAttempted = $quiz
                    ->attempts()
                    ->where('user_id', auth()->id())
                    ->exists();
            @endphp

            @if ($hasAttempted)
                <div class="alert alert-info-custom mb-5" role="alert">
                    <div class="alert-icon">
                        <i class="bi bi-info-circle-fill"></i>
                    </div>
                    <div class="alert-content">
                        <h6 class="alert-title mb-2">Quiz Completed</h6>
                        <p class="alert-text mb-3">You have already completed this quiz. You can view your results below.
                        </p>
                        <a href="{{ route('quizzes.results', [$course->id, $quiz->id]) }}"
                            class="btn btn-sm btn-outline-info">
                            <i class="bi bi-eye me-1"></i>View Results
                        </a>
                    </div>
                </div>
            @endif

            <div class="row">
                <!-- Quiz Details Card -->
                <div class="col-lg-8 mb-5">
                    <div class="quiz-details-card">
                        <div class="card-header">
                            <h5 class="card-title">
                                <i class="bi bi-info-circle me-2"></i>Quiz Information
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="quiz-stats-grid">
                                <div class="stat-item">
                                    <div class="stat-icon">
                                        <i class="bi bi-trophy"></i>
                                    </div>
                                    <div class="stat-content">
                                        <span class="stat-label">Total Points</span>
                                        <span class="stat-value">{{ $quiz->total_points }}</span>
                                    </div>
                                </div>

                                <div class="stat-item">
                                    <div class="stat-icon">
                                        <i class="bi bi-clock"></i>
                                    </div>
                                    <div class="stat-content">
                                        <span class="stat-label">Time Limit</span>
                                        <span
                                            class="stat-value">{{ $quiz->time_limit ? $quiz->time_limit . ' min' : 'Unlimited' }}</span>
                                    </div>
                                </div>

                                <div class="stat-item">
                                    <div class="stat-icon">
                                        <i class="bi bi-calendar-event"></i>
                                    </div>
                                    <div class="stat-content">
                                        <span class="stat-label">Due Date</span>
                                        <span
                                            class="stat-value">{{ $quiz->due_date ? $quiz->due_date->format('M j, Y') : 'No deadline' }}</span>
                                    </div>
                                </div>

                                <div class="stat-item">
                                    <div class="stat-icon">
                                        <i class="bi bi-book"></i>
                                    </div>
                                    <div class="stat-content">
                                        <span class="stat-label">Course</span>
                                        <span class="stat-value">{{ $course->title }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Card -->
                <div class="col-lg-4 mb-5">
                    <div class="action-card">
                        @if ($hasAttempted)
                            <!-- Already attempted -->
                            <div class="action-content text-center">
                                <div class="action-icon mb-4 completed">
                                    <i class="bi bi-check-circle-fill"></i>
                                </div>
                                <h6 class="action-title mb-3">Quiz Completed</h6>
                                <p class="action-description mb-4">You have successfully completed this quiz.</p>
                                <a href="{{ route('quizzes.results', [$course->id, $quiz->id]) }}"
                                    class="btn btn-success-custom btn-lg w-100 mb-3">
                                    <i class="bi bi-eye me-2"></i>View Results
                                </a>
                                <small class="text-muted">Note: You can only take this quiz once.</small>
                            </div>
                        @elseif ($quiz->due_date && now()->gt($quiz->due_date))
                            <!-- Quiz expired -->
                            <div class="action-content text-center">
                                <div class="action-icon mb-4 text-muted">
                                    <i class="bi bi-lock-fill"></i>
                                </div>
                                <h6 class="action-title text-muted mb-3">Quiz Unavailable</h6>
                                <p class="action-description text-muted mb-0">This quiz has passed its due date.</p>
                            </div>
                        @else
                            <!-- Available to take -->
                            <div class="action-content text-center">
                                <div class="action-icon mb-4">
                                    <i class="bi bi-play-circle-fill"></i>
                                </div>
                                <h6 class="action-title mb-3">Ready to Begin?</h6>
                                <p class="action-description mb-4">Click the button below to start your quiz attempt.</p>
                                <a href="{{ route('quiz.attempt.create', [$course->id, $quiz->id]) }}"
                                    class="btn btn-primary-custom btn-lg w-100">
                                    <i class="bi bi-rocket-takeoff me-2"></i>Start Quiz
                                </a>
                            </div>
                        @endif
                    </div>

                    <!-- Quick Tips Card -->
                    <div class="tips-card mt-4">
                        <div class="card-header">
                            <h6 class="card-title">
                                <i class="bi bi-lightbulb me-2"></i>Quick Tips
                            </h6>
                        </div>
                        <div class="card-body">
                            <ul class="tips-list">
                                <li>Read all questions carefully before answering</li>
                                <li>Manage your time effectively</li>
                                <li>Review your answers before submitting</li>
                                <li>Stay calm and focused throughout</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('styles')
        <style>
            :root {
                --primary-color: #1CB2A2;
                --primary-dark: #17a092;
                --secondary-color: #1D3341;
                --accent-color: #FF6B6B;
                --success-color: #4ECDC4;
                --warning-color: #FFE66D;
                --info-color: #3182CE;
                --danger-color: #E53E3E;
                --light-bg: #F8FAFB;
                --card-bg: #FFFFFF;
                --text-primary: #2D3748;
                --text-secondary: #718096;
                --border-color: #E2E8F0;
                --shadow-sm: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
                --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
                --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            }

            .quiz-container {
                min-height: 100vh;
                background: linear-gradient(135deg, var(--light-bg) 0%, #ffffff 100%);
                border-radius: 1rem;
                padding: 2rem;
                box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.1);
                margin: 2rem auto;
                max-width: 1200px;
            }

            .quiz-hero {
                background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
                color: white;
                padding: 5rem 5rem 4rem;
                position: relative;
                overflow: hidden;
                border-radius: 1rem;
                box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.1);
            }

            .quiz-hero::before {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="white" opacity="0.1"/><circle cx="75" cy="75" r="1" fill="white" opacity="0.1"/><circle cx="50" cy="10" r="0.5" fill="white" opacity="0.05"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
                opacity: 0.1;
            }

            .quiz-header-content {
                position: relative;
                z-index: 2;
            }

            .breadcrumb-nav {
                font-size: 0.9rem;
            }

            .breadcrumb-link {
                color: rgba(255, 255, 255, 0.8);
                text-decoration: none;
                display: inline-flex;
                align-items: center;
                transition: all 0.3s ease;
                padding: 0.75rem 1.25rem;
                border-radius: 50px;
                background: rgba(255, 255, 255, 0.1);
                backdrop-filter: blur(10px);
            }

            .breadcrumb-link:hover {
                color: white;
                background: rgba(255, 255, 255, 0.2);
                transform: translateX(-5px);
            }

            .quiz-title {
                font-size: 3rem;
                font-weight: 700;
                margin-bottom: 1.5rem;
                line-height: 1.2;
            }

            .quiz-subtitle {
                font-size: 1.1rem;
                opacity: 0.9;
                line-height: 1.6;
                max-width: 600px;
                margin-bottom: 0;
            }

            .quiz-illustration {
                position: relative;
            }

            .quiz-icon-wrapper {
                width: 120px;
                height: 120px;
                background: rgba(255, 255, 255, 0.15);
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                margin: 0 auto;
                backdrop-filter: blur(10px);
                border: 2px solid rgba(255, 255, 255, 0.2);
            }

            .quiz-icon {
                font-size: 3rem;
                color: white;
            }

            .quiz-content {
                margin-top: -3rem;
                position: relative;
                z-index: 3;
                padding-bottom: 3rem;
            }

            .quiz-details-card,
            .action-card,
            .tips-card {
                background: var(--card-bg);
                border-radius: 16px;
                box-shadow: var(--shadow-lg);
                border: 1px solid var(--border-color);
                overflow: hidden;
                transition: all 0.3s ease;
            }

            .quiz-details-card:hover,
            .action-card:hover,
            .tips-card:hover {
                transform: translateY(-2px);
                box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            }

            .card-header {
                background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
                padding: 1.75rem 2rem;
                border-bottom: 1px solid var(--border-color);
            }

            .card-title {
                margin: 0;
                font-weight: 600;
                color: var(--text-primary);
                display: flex;
                align-items: center;
            }

            .card-body {
                padding: 2.5rem 2rem;
            }

            .quiz-stats-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
                gap: 1.5rem;
            }

            .stat-item {
                display: flex;
                align-items: center;
                padding: 1.5rem;
                background: linear-gradient(135deg, #f8fafc 0%, #ffffff 100%);
                border-radius: 12px;
                border: 1px solid var(--border-color);
                transition: all 0.3s ease;
            }

            .stat-item:hover {
                transform: translateY(-2px);
                box-shadow: var(--shadow-md);
                border-color: var(--primary-color);
            }

            .stat-icon {
                width: 52px;
                height: 52px;
                background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
                border-radius: 12px;
                display: flex;
                align-items: center;
                justify-content: center;
                margin-right: 1.25rem;
                flex-shrink: 0;
            }

            .stat-icon i {
                color: white;
                font-size: 1.3rem;
            }

            .stat-content {
                display: flex;
                flex-direction: column;
            }

            .stat-label {
                font-size: 0.85rem;
                color: var(--text-secondary);
                font-weight: 500;
                margin-bottom: 0.25rem;
            }

            .stat-value {
                font-size: 1.1rem;
                font-weight: 600;
                color: var(--text-primary);
            }

            .action-card {
                position: sticky;
                top: 2rem;
            }

            .action-content {
                padding: 2.5rem;
            }

            .action-icon {
                width: 80px;
                height: 80px;
                background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                margin: 0 auto;
            }

            .action-icon.completed {
                background: linear-gradient(135deg, var(--success-color), #20b2aa);
            }

            .action-icon i {
                font-size: 2rem;
                color: white;
            }

            .action-title {
                font-weight: 600;
                color: var(--text-primary);
                font-size: 1.1rem;
            }

            .action-description {
                color: var(--text-secondary);
                font-size: 0.95rem;
                line-height: 1.6;
            }

            .btn-primary-custom {
                background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
                border: none;
                border-radius: 12px;
                padding: 1.25rem 2rem;
                font-weight: 600;
                font-size: 1.05rem;
                transition: all 0.3s ease;
                box-shadow: var(--shadow-md);
                position: relative;
                overflow: hidden;
            }

            .btn-success-custom {
                background: linear-gradient(135deg, var(--success-color), #20b2aa);
                border: none;
                border-radius: 12px;
                padding: 1.25rem 2rem;
                font-weight: 600;
                font-size: 1.05rem;
                transition: all 0.3s ease;
                box-shadow: var(--shadow-md);
                color: white;
                text-decoration: none;
                display: inline-block;
                text-align: center;
            }

            .btn-primary-custom::before,
            .btn-success-custom::before {
                content: '';
                position: absolute;
                top: 0;
                left: -100%;
                width: 100%;
                height: 100%;
                background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
                transition: left 0.5s;
            }

            .btn-primary-custom:hover::before,
            .btn-success-custom:hover::before {
                left: 100%;
            }

            .btn-primary-custom:hover {
                transform: translateY(-2px);
                box-shadow: 0 10px 20px rgba(28, 178, 162, 0.3);
            }

            .btn-success-custom:hover {
                transform: translateY(-2px);
                box-shadow: 0 10px 20px rgba(78, 205, 196, 0.3);
                color: white;
                text-decoration: none;
            }

            .tips-card .card-body {
                padding: 1.75rem 2rem;
            }

            .tips-list {
                list-style: none;
                padding: 0;
                margin: 0;
            }

            .tips-list li {
                padding: 1rem 0;
                border-bottom: 1px solid var(--border-color);
                position: relative;
                padding-left: 2.5rem;
                color: var(--text-secondary);
                font-size: 0.95rem;
                line-height: 1.5;
                transition: color 0.3s ease;
            }

            .tips-list li:last-child {
                border-bottom: none;
                padding-bottom: 0;
            }

            .tips-list li:first-child {
                padding-top: 0;
            }

            .tips-list li::before {
                content: '✓';
                position: absolute;
                left: 0;
                top: 1rem;
                width: 22px;
                height: 22px;
                background: var(--success-color);
                color: white;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 0.75rem;
                font-weight: bold;
            }

            .tips-list li:hover {
                color: var(--text-primary);
            }

            /* Alert Styles */
            .alert-success-custom,
            .alert-danger-custom,
            .alert-warning-custom,
            .alert-info-custom {
                border-radius: 12px;
                padding: 2rem;
                display: flex;
                align-items: flex-start;
                box-shadow: var(--shadow-sm);
                animation: slideInDown 0.5s ease-out;
            }

            .alert-success-custom {
                background: linear-gradient(135deg, #f0fff4 0%, #c6f6d5 100%);
                border: 1px solid #9ae6b4;
            }

            .alert-danger-custom {
                background: linear-gradient(135deg, #fff5f5 0%, #fed7d7 100%);
                border: 1px solid #fc8181;
            }

            .alert-warning-custom {
                background: linear-gradient(135deg, #fff3e0 0%, #ffe0b2 100%);
                border: 1px solid #ffcc80;
            }

            .alert-info-custom {
                background: linear-gradient(135deg, #ebf8ff 0%, #bee3f8 100%);
                border: 1px solid #90cdf4;
            }

            .alert-icon {
                width: 50px;
                height: 50px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                margin-right: 1.5rem;
                flex-shrink: 0;
                margin-top: 0.25rem;
            }

            .alert-success-custom .alert-icon {
                background: var(--success-color);
            }

            .alert-danger-custom .alert-icon {
                background: var(--danger-color);
            }

            .alert-warning-custom .alert-icon {
                background: var(--warning-color);
            }

            .alert-info-custom .alert-icon {
                background: var(--info-color);
            }

            .alert-success-custom .alert-icon i {
                color: white;
                font-size: 1.3rem;
            }

            .alert-danger-custom .alert-icon i {
                color: white;
                font-size: 1.3rem;
            }

            .alert-warning-custom .alert-icon i {
                color: #b8860b;
                font-size: 1.3rem;
            }

            .alert-info-custom .alert-icon i {
                color: white;
                font-size: 1.3rem;
            }

            .alert-content {
                flex: 1;
            }

            .alert-title {
                font-weight: 600;
                font-size: 1.1rem;
                margin-bottom: 0.5rem;
            }

            .alert-success-custom .alert-title {
                color: #22543d;
            }

            .alert-danger-custom .alert-title {
                color: #742a2a;
            }

            .alert-warning-custom .alert-title {
                color: #b8860b;
            }

            .alert-info-custom .alert-title {
                color: #2a4365;
            }

            .alert-text {
                font-size: 0.95rem;
                line-height: 1.5;
                margin-bottom: 0;
            }

            .alert-success-custom .alert-text {
                color: #2f855a;
            }

            .alert-danger-custom .alert-text {
                color: #9b2c2c;
            }

            .alert-warning-custom .alert-text {
                color: #8d6e00;
            }

            .alert-info-custom .alert-text {
                color: #3182ce;
            }

            .btn-outline-info {
                border: 1px solid var(--info-color);
                color: var(--info-color);
                background: transparent;
                border-radius: 8px;
                padding: 0.5rem 1rem;
                font-size: 0.9rem;
                transition: all 0.3s ease;
                text-decoration: none;
            }

            .btn-outline-info:hover {
                background: var(--info-color);
                color: white;
                transform: translateY(-1px);
            }

            /* Responsive Design */
            @media (max-width: 768px) {
                .quiz-title {
                    font-size: 2.25rem;
                    margin-bottom: 1.25rem;
                }

                .quiz-hero {
                    padding: 3rem 0 2.5rem;
                }

                .quiz-content {
                    margin-top: -2rem;
                    padding-bottom: 2rem;
                }

                .quiz-stats-grid {
                    grid-template-columns: 1fr;
                    gap: 1.25rem;
                }

                .card-header {
                    padding: 1.5rem;
                }

                .card-body {
                    padding: 2rem 1.5rem;
                }

                .action-content {
                    padding: 2rem;
                }

                .tips-card .card-body {
                    padding: 1.5rem;
                }

                .stat-item {
                    padding: 1.25rem;
                }

                .alert-success-custom,
                .alert-danger-custom,
                .alert-warning-custom,
                .alert-info-custom {
                    padding: 1.5rem;
                    flex-direction: column;
                    text-align: center;
                }

                .alert-icon {
                    margin: 0 auto 1rem auto;
                }

                .breadcrumb-link {
                    padding: 0.5rem 1rem;
                }
            }

            @media (max-width: 576px) {
                .quiz-title {
                    font-size: 1.75rem;
                }

                .quiz-hero {
                    padding: 2.5rem 0 2rem;
                }

                .action-content {
                    padding: 1.5rem;
                }

                .card-body {
                    padding: 1.5rem;
                }

                .tips-card .card-body {
                    padding: 1.25rem;
                }
            }

            /* Animations */
            @keyframes fadeInUp {
                from {
                    opacity: 0;
                    transform: translateY(30px);
                }

                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            @keyframes slideInDown {
                from {
                    opacity: 0;
                    transform: translateY(-20px);
                }

                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            .quiz-details-card,
            .action-card,
            .tips-card {
                animation: fadeInUp 0.6s ease-out forwards;
            }

            .action-card {
                animation-delay: 0.2s;
            }

            .tips-card {
                animation-delay: 0.4s;
            }
        </style>
    @endpush
@endsection