@extends('layouts.app')

@section('content')
    <div class="quiz-management-container">
        <!-- Header Section -->
        <div class="quiz-header-section rounded-4">
            <div class="container p-5">
                <div class="row align-items-center">
                    <div class="col-lg-8">
                        <div class="quiz-header-content">
                            <div class="breadcrumb-nav mb-3">
                                <a href="{{ route('courses.show', $course->id) }}" class="breadcrumb-link">
                                    <i class="fas fa-arrow-left me-2"></i>{{ $course->title }}
                                </a>
                            </div>
                            <h1 class="quiz-title">{{ $quiz->title }}</h1>
                            <div class="quiz-meta">
                                <span class="meta-item">
                                    <i class="fas fa-question-circle me-1"></i>
                                    {{ $quiz->questions->count() }} Questions
                                </span>
                                <span class="meta-item">
                                    <i class="fas fa-star me-1"></i>
                                    {{ $quiz->total_points }} Points
                                </span>
                                <span class="meta-item">
                                    <i class="fas fa-clock me-1"></i>
                                    {{ $quiz->time_limit ? $quiz->time_limit . ' minutes' : 'Unlimited' }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="quiz-actions">
                            <a href="{{ route('quizzes.edit', [$course->id, $quiz->id]) }}" class="btn btn-primary-custom">
                                <i class="fas fa-edit me-2"></i>Edit Quiz
                            </a>
                            <button type="button" class="btn btn-danger-custom" onclick="showDeleteModal()">
                                <i class="fas fa-trash me-2"></i>Delete
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="container quiz-content">
            <div class="row">
                <!-- Quiz Details Sidebar -->
                <div class="col-lg-4 mb-4">
                    <div class="details-card">
                        <div class="card-header">
                            <h5 class="card-title">
                                <i class="fas fa-info-circle me-2"></i>Quiz Overview
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="quiz-description mb-4">
                                <h6 class="section-title">Description</h6>
                                <p class="description-text">{{ $quiz->description ?: 'No description provided.' }}</p>
                            </div>

                            <div class="quiz-stats">
                                <div class="stat-item">
                                    <div class="stat-icon course-icon">
                                        <i class="fas fa-book"></i>
                                    </div>
                                    <div class="stat-content">
                                        <span class="stat-label">Course</span>
                                        <span class="stat-value">{{ $course->title }}</span>
                                    </div>
                                </div>

                                <div class="stat-item">
                                    <div class="stat-icon points-icon">
                                        <i class="fas fa-trophy"></i>
                                    </div>
                                    <div class="stat-content">
                                        <span class="stat-label">Total Points</span>
                                        <span class="stat-value">{{ $quiz->total_points }}</span>
                                    </div>
                                </div>

                                <div class="stat-item">
                                    <div class="stat-icon time-icon">
                                        <i class="fas fa-stopwatch"></i>
                                    </div>
                                    <div class="stat-content">
                                        <span class="stat-label">Time Limit</span>
                                        <span
                                            class="stat-value">{{ $quiz->time_limit ? $quiz->time_limit . ' min' : 'Unlimited' }}</span>
                                    </div>
                                </div>

                                <div class="stat-item">
                                    <div class="stat-icon date-icon">
                                        <i class="fas fa-calendar-alt"></i>
                                    </div>
                                    <div class="stat-content">
                                        <span class="stat-label">Due Date</span>
                                        <span class="stat-value">
                                            @if ($quiz->due_date)
                                                {{ \Carbon\Carbon::parse($quiz->due_date)->format('M j, Y g:i A') }}
                                            @else
                                                No deadline
                                            @endif
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Questions Section -->
                <div class="col-lg-8">
                    <div class="questions-card">
                        <div class="card-header">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="card-title">
                                    <i class="fas fa-list me-2"></i>Questions ({{ $quiz->questions->count() }})
                                </h5>
                                <div class="header-actions">
                                    <button class="btn btn-outline-primary btn-sm" onclick="toggleAnswers()">
                                        <i class="fas fa-eye me-1"></i>
                                        <span id="toggle-text">Show Answers</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            @if ($quiz->questions->count() > 0)
                                <div class="questions-container">
                                    @foreach ($quiz->questions as $index => $question)
                                        <div class="question-item" data-question-id="{{ $question->id }}">
                                            <div class="question-header">
                                                <div class="question-number">
                                                    <span class="number">{{ $index + 1 }}</span>
                                                </div>
                                                <div class="question-info">
                                                    <div class="question-meta">
                                                        <span class="points-badge">{{ $question->points }} pts</span>
                                                        <span
                                                            class="type-badge">{{ ucfirst($question->type ?? 'text') }}</span>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="question-content">
                                                <div class="question-text">{{ $question->question }}</div>

                                                <div class="answer-section" style="display: none;">
                                                    <div class="answer-header">
                                                        <i class="fas fa-check-circle me-2"></i>Correct Answer
                                                    </div>
                                                    <div class="answer-content">{{ $question->answer }}</div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="empty-state">
                                    <div class="empty-icon">
                                        <i class="fas fa-question-circle"></i>
                                    </div>
                                    <h6 class="empty-title">No Questions Yet</h6>
                                    <p class="empty-description">Start building your quiz by adding questions.</p>
                                    <button class="btn btn-primary-custom">
                                        <i class="fas fa-plus me-2"></i>Add First Question
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title" id="deleteModalLabel">
                        <i class="fas fa-exclamation-triangle text-danger me-2"></i>Delete Quiz
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-3">Are you sure you want to delete this quiz?</p>
                    <div class="alert alert-warning">
                        <i class="fas fa-info-circle me-2"></i>
                        This action cannot be undone. All questions and student attempts will be permanently deleted.
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form action="{{ route('quizzes.destroy', [$course->id, $quiz->id]) }}" method="POST"
                        class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash me-2"></i>Delete Quiz
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('styles')
        <style>
            :root {
                --primary-color: #4F46E5;
                --primary-dark: #4338CA;
                --secondary-color: #64748B;
                --success-color: #10B981;
                --danger-color: #EF4444;
                --warning-color: #F59E0B;
                --info-color: #3B82F6;
                --light-bg: #F8FAFC;
                --card-bg: #FFFFFF;
                --text-primary: #1E293B;
                --text-secondary: #64748B;
                --text-muted: #94A3B8;
                --border-color: #E2E8F0;
                --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
                --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
                --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
                --gradient-primary: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
                --gradient-success: linear-gradient(135deg, #10B981 0%, #059669 100%);
                --gradient-danger: linear-gradient(135deg, #EF4444 0%, #DC2626 100%);
            }

            .quiz-management-container {
                min-height: 100vh;
                background: var(--light-bg);
            }

            .quiz-header-section {
                background: var(--gradient-primary);
                color: white;
                padding: 2rem 0;
                position: relative;
                overflow: hidden;
            }

            .quiz-header-section::before {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="20" cy="20" r="2" fill="white" opacity="0.1"/><circle cx="80" cy="80" r="2" fill="white" opacity="0.1"/></svg>');
                opacity: 0.1;
            }

            .quiz-header-content {
                position: relative;
                z-index: 2;
            }

            .breadcrumb-link {
                color: rgba(255, 255, 255, 0.8);
                text-decoration: none;
                display: inline-flex;
                align-items: center;
                padding: 0.5rem 1rem;
                border-radius: 50px;
                background: rgba(255, 255, 255, 0.1);
                backdrop-filter: blur(10px);
                transition: all 0.3s ease;
            }

            .breadcrumb-link:hover {
                color: white;
                background: rgba(255, 255, 255, 0.2);
                transform: translateX(-5px);
            }

            .quiz-title {
                font-size: 2.5rem;
                font-weight: 700;
                margin-bottom: 1rem;
                line-height: 1.2;
            }

            .quiz-meta {
                display: flex;
                gap: 1.5rem;
                flex-wrap: wrap;
            }

            .meta-item {
                display: flex;
                align-items: center;
                opacity: 0.9;
                font-size: 0.95rem;
            }

            .quiz-actions {
                display: flex;
                gap: 1rem;
                justify-content: flex-end;
            }

            .btn-primary-custom {
                background: rgba(255, 255, 255, 0.15);
                border: 2px solid rgba(255, 255, 255, 0.3);
                color: white;
                padding: 0.75rem 1.5rem;
                border-radius: 12px;
                font-weight: 600;
                transition: all 0.3s ease;
                backdrop-filter: blur(10px);
            }

            .btn-primary-custom:hover {
                background: rgba(255, 255, 255, 0.25);
                border-color: rgba(255, 255, 255, 0.5);
                color: white;
                transform: translateY(-2px);
            }

            .btn-danger-custom {
                background: var(--gradient-danger);
                border: none;
                color: white;
                padding: 0.75rem 1.5rem;
                border-radius: 12px;
                font-weight: 600;
                transition: all 0.3s ease;
            }

            .btn-danger-custom:hover {
                transform: translateY(-2px);
                box-shadow: 0 8px 20px rgba(239, 68, 68, 0.3);
            }

            .quiz-content {
                margin-top: -1rem;
                position: relative;
                z-index: 3;
            }

            .details-card,
            .questions-card {
                background: var(--card-bg);
                border-radius: 16px;
                box-shadow: var(--shadow-lg);
                border: 1px solid var(--border-color);
                overflow: hidden;
                transition: all 0.3s ease;
            }

            .details-card:hover,
            .questions-card:hover {
                transform: translateY(-2px);
                box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
            }

            .card-header {
                background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
                padding: 1.5rem;
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
                padding: 1.5rem;
            }

            .section-title {
                font-size: 0.9rem;
                font-weight: 600;
                color: var(--text-secondary);
                text-transform: uppercase;
                letter-spacing: 0.5px;
                margin-bottom: 0.5rem;
            }

            .description-text {
                color: var(--text-primary);
                line-height: 1.6;
                font-size: 0.95rem;
            }

            .quiz-stats {
                display: flex;
                flex-direction: column;
                gap: 1rem;
            }

            .stat-item {
                display: flex;
                align-items: center;
                padding: 1rem;
                background: linear-gradient(135deg, #f8fafc 0%, #ffffff 100%);
                border-radius: 12px;
                border: 1px solid var(--border-color);
                transition: all 0.3s ease;
            }

            .stat-item:hover {
                transform: translateX(5px);
                box-shadow: var(--shadow-md);
            }

            .stat-icon {
                width: 40px;
                height: 40px;
                border-radius: 10px;
                display: flex;
                align-items: center;
                justify-content: center;
                margin-right: 1rem;
                flex-shrink: 0;
            }

            .course-icon {
                background: var(--gradient-primary);
            }

            .points-icon {
                background: var(--gradient-success);
            }

            .time-icon {
                background: linear-gradient(135deg, #F59E0B 0%, #D97706 100%);
            }

            .date-icon {
                background: linear-gradient(135deg, #3B82F6 0%, #2563EB 100%);
            }

            .stat-icon i {
                color: white;
                font-size: 1rem;
            }

            .stat-content {
                display: flex;
                flex-direction: column;
            }

            .stat-label {
                font-size: 0.8rem;
                color: var(--text-secondary);
                font-weight: 500;
            }

            .stat-value {
                font-size: 1rem;
                font-weight: 600;
                color: var(--text-primary);
                margin-top: 0.25rem;
            }

            .action-buttons {
                display: flex;
                flex-direction: column;
                gap: 0.75rem;
            }

            .action-btn {
                display: flex;
                align-items: center;
                padding: 0.75rem 1rem;
                border-radius: 10px;
                text-decoration: none;
                font-weight: 500;
                font-size: 0.9rem;
                transition: all 0.3s ease;
                border: none;
                cursor: pointer;
                width: 100%;
                justify-content: flex-start;
            }

            .preview-btn {
                background: linear-gradient(135deg, #3B82F6 0%, #2563EB 100%);
                color: white;
            }

            .analytics-btn {
                background: linear-gradient(135deg, #10B981 0%, #059669 100%);
                color: white;
            }

            .duplicate-btn {
                background: linear-gradient(135deg, #F59E0B 0%, #D97706 100%);
                color: white;
            }

            .action-btn:hover {
                transform: translateY(-2px);
                box-shadow: var(--shadow-md);
            }

            .questions-container {
                display: flex;
                flex-direction: column;
                gap: 1.5rem;
            }

            .question-item {
                background: var(--card-bg);
                border: 1px solid var(--border-color);
                border-radius: 12px;
                overflow: hidden;
                transition: all 0.3s ease;
            }

            .question-item:hover {
                box-shadow: var(--shadow-md);
                transform: translateY(-2px);
            }

            .question-header {
                display: flex;
                align-items: center;
                padding: 1rem;
                background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
                border-bottom: 1px solid var(--border-color);
            }

            .question-number {
                width: 40px;
                height: 40px;
                background: var(--gradient-primary);
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                margin-right: 1rem;
                flex-shrink: 0;
            }

            .question-number .number {
                color: white;
                font-weight: 600;
                font-size: 1.1rem;
            }

            .question-info {
                flex: 1;
            }

            .question-meta {
                display: flex;
                gap: 0.5rem;
            }

            .points-badge,
            .type-badge {
                padding: 0.25rem 0.75rem;
                border-radius: 20px;
                font-size: 0.8rem;
                font-weight: 500;
            }

            .points-badge {
                background: var(--gradient-success);
                color: white;
            }

            .type-badge {
                background: linear-gradient(135deg, #64748B 0%, #475569 100%);
                color: white;
            }

            .action-icon {
                width: 36px;
                height: 36px;
                border-radius: 8px;
                border: none;
                display: flex;
                align-items: center;
                justify-content: center;
                cursor: pointer;
                transition: all 0.3s ease;
            }

            .edit-question {
                background: var(--gradient-primary);
                color: white;
            }

            .delete-question {
                background: var(--gradient-danger);
                color: white;
            }

            .action-icon:hover {
                transform: scale(1.1);
            }

            .question-content {
                padding: 1.5rem;
            }

            .question-text {
                font-size: 1.05rem;
                line-height: 1.6;
                color: var(--text-primary);
                margin-bottom: 1rem;
            }

            .answer-section {
                margin-top: 1rem;
                padding: 1rem;
                background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%);
                border-radius: 10px;
                border: 1px solid #86efac;
            }

            .answer-header {
                display: flex;
                align-items: center;
                font-weight: 600;
                color: #166534;
                margin-bottom: 0.5rem;
            }

            .answer-content {
                color: #166534;
                font-size: 0.95rem;
                line-height: 1.5;
            }

            .empty-state {
                text-align: center;
                padding: 3rem 2rem;
            }

            .empty-icon {
                width: 80px;
                height: 80px;
                background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                margin: 0 auto 1.5rem;
            }

            .empty-icon i {
                font-size: 2rem;
                color: var(--text-muted);
            }

            .empty-title {
                font-weight: 600;
                color: var(--text-primary);
                margin-bottom: 0.5rem;
            }

            .empty-description {
                color: var(--text-secondary);
                margin-bottom: 1.5rem;
            }

            .header-actions {
                display: flex;
                gap: 0.5rem;
            }

            /* Modal Enhancements */
            .modal-content {
                border-radius: 16px;
                border: none;
                box-shadow: var(--shadow-lg);
            }

            .modal-header {
                padding: 1.5rem;
            }

            .modal-body {
                padding: 0 1.5rem 1.5rem;
            }

            .modal-footer {
                padding: 1.5rem;
            }

            /* Responsive Design */
            @media (max-width: 768px) {
                .quiz-title {
                    font-size: 1.8rem;
                }

                .quiz-meta {
                    flex-direction: column;
                    gap: 0.5rem;
                }

                .quiz-actions {
                    flex-direction: column;
                    gap: 0.5rem;
                }

                .question-header {
                    flex-wrap: wrap;
                    gap: 1rem;
                }
            }

            /* Animation */
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

            .details-card,
            .questions-card {
                animation: fadeInUp 0.6s ease-out forwards;
            }

            .questions-card {
                animation-delay: 0.4s;
            }
        </style>
    @endpush

    @push('scripts')
        <script>
            function showDeleteModal() {
                const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
                modal.show();
            }

            function toggleAnswers() {
                const answerSections = document.querySelectorAll('.answer-section');
                const toggleText = document.getElementById('toggle-text');
                const isVisible = answerSections[0]?.style.display !== 'none';

                answerSections.forEach(section => {
                    section.style.display = isVisible ? 'none' : 'block';
                });

                toggleText.textContent = isVisible ? 'Show Answers' : 'Hide Answers';
            }

            // Add smooth scrolling for better UX
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function(e) {
                    e.preventDefault();
                    document.querySelector(this.getAttribute('href')).scrollIntoView({
                        behavior: 'smooth'
                    });
                });
            });
        </script>
    @endpush
@endsection
