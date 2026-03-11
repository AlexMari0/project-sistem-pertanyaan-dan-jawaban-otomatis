@extends('layouts.app')

@section('content')
    <div class="quiz-edit-container">
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
                                <span class="breadcrumb-separator">/</span>
                                <a href="{{ route('quizzes.show', [$course->id, $quiz->id]) }}" class="breadcrumb-link">
                                    {{ $quiz->title }}
                                </a>
                            </div>
                            <h1 class="quiz-title">Edit Quiz</h1>
                            <p class="quiz-subtitle">Modify quiz details and questions</p>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="quiz-actions">
                            <a href="{{ route('quizzes.show', [$course->id, $quiz->id]) }}"
                                class="btn btn-secondary-custom">
                                <i class="fas fa-times me-2"></i>Cancel
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="container quiz-content">
            <form action="{{ route('quizzes.update', [$course->id, $quiz->id]) }}" method="POST" id="quiz-form">
                @csrf
                @method('PUT')

                <div class="row">
                    <!-- Quiz Settings -->
                    <div class="col-lg-4 mb-4">
                        <div class="settings-card">
                            <div class="card-header">
                                <h5 class="card-title">
                                    <i class="fas fa-cog me-2"></i>Quiz Settings
                                </h5>
                            </div>
                            <div class="card-body">
                                <!-- Quiz Title -->
                                <div class="form-group mb-4">
                                    <label for="title" class="form-label">Quiz Title</label>
                                    <input type="text" class="form-control @error('title') is-invalid @enderror"
                                        id="title" name="title" value="{{ old('title', $quiz->title) }}" required>
                                    @error('title')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Quiz Description -->
                                <div class="form-group mb-4">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description"
                                        rows="4" placeholder="Enter quiz description...">{{ old('description', $quiz->description) }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Time Limit -->
                                <div class="form-group mb-4">
                                    <label for="time_limit" class="form-label">Time Limit (minutes)</label>
                                    <input type="number" class="form-control @error('time_limit') is-invalid @enderror"
                                        id="time_limit" name="time_limit"
                                        value="{{ old('time_limit', $quiz->time_limit) }}" min="1"
                                        placeholder="Leave blank for unlimited">
                                    @error('time_limit')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">Leave blank for unlimited time</small>
                                </div>

                                <!-- Due Date -->
                                <div class="form-group mb-4">
                                    <label for="due_date" class="form-label">Due Date</label>
                                    <input type="datetime-local"
                                        class="form-control @error('due_date') is-invalid @enderror" id="due_date"
                                        name="due_date"
                                        value="{{ old('due_date', $quiz->due_date ? \Carbon\Carbon::parse($quiz->due_date)->format('Y-m-d\TH:i') : '') }}">
                                    @error('due_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">Leave blank for no deadline</small>
                                </div>

                                <!-- Quiz Status -->
                                <div class="form-group mb-4">
                                    <label class="form-label">Quiz Status</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active"
                                            value="1"
                                            {{ old('is_active', $quiz->is_published ?? true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_active">
                                            Active (students can take this quiz)
                                        </label>
                                    </div>
                                </div>

                                <!-- Save Button -->
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary-custom">
                                        <i class="fas fa-save me-2"></i>Save Changes
                                    </button>
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
                                    <button type="button" class="btn btn-success btn-sm" onclick="addQuestion()">
                                        <i class="fas fa-plus me-1"></i>Add Question
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <div id="questions-container">
                                    @if ($quiz->questions->count() > 0)
                                        @foreach ($quiz->questions as $index => $question)
                                            <div class="question-item" data-question-index="{{ $index }}">
                                                <div class="question-header">
                                                    <div class="question-number">
                                                        <span class="number">{{ $index + 1 }}</span>
                                                    </div>
                                                    <div class="question-actions">
                                                        <button type="button" class="action-icon move-up"
                                                            title="Move Up"
                                                            onclick="moveQuestion({{ $index }}, 'up')">
                                                            <i class="fas fa-arrow-up"></i>
                                                        </button>
                                                        <button type="button" class="action-icon move-down"
                                                            title="Move Down"
                                                            onclick="moveQuestion({{ $index }}, 'down')">
                                                            <i class="fas fa-arrow-down"></i>
                                                        </button>
                                                        <button type="button" class="action-icon delete-question"
                                                            title="Delete Question"
                                                            onclick="removeQuestion({{ $index }})">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </div>
                                                </div>

                                                <div class="question-content">
                                                    <input type="hidden" name="questions[{{ $index }}][id]"
                                                        value="{{ $question->id }}">

                                                    <!-- Question Text -->
                                                    <div class="form-group mb-3">
                                                        <label class="form-label">Question</label>
                                                        <textarea class="form-control" name="questions[{{ $index }}][question]" rows="3" required>{{ old("questions.{$index}.question", $question->question) }}</textarea>
                                                    </div>

                                                    <!-- Question Type -->
                                                    <div class="row mb-3">
                                                        <div class="col-md-6">
                                                            <label class="form-label">Question Type</label>
                                                            <select class="form-select"
                                                                name="questions[{{ $index }}][type]">
                                                                <option value="text"
                                                                    {{ ($question->type ?? 'text') == 'text' ? 'selected' : '' }}>
                                                                    Text Answer</option>
                                                                <option value="multiple_choice"
                                                                    {{ ($question->type ?? 'text') == 'multiple_choice' ? 'selected' : '' }}>
                                                                    Multiple Choice</option>
                                                                <option value="true_false"
                                                                    {{ ($question->type ?? 'text') == 'true_false' ? 'selected' : '' }}>
                                                                    True/False</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label">Points</label>
                                                            <input type="number" class="form-control"
                                                                name="questions[{{ $index }}][points]"
                                                                value="{{ old("questions.{$index}.points", $question->points) }}"
                                                                min="1" required>
                                                        </div>
                                                    </div>

                                                    <!-- Answer -->
                                                    <div class="form-group mb-3">
                                                        <label class="form-label">Correct Answer</label>
                                                        <textarea class="form-control" name="questions[{{ $index }}][answer]" rows="2" required>{{ old("questions.{$index}.answer", $question->answer) }}</textarea>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    @else
                                        <div class="empty-state" id="empty-questions">
                                            <div class="empty-icon">
                                                <i class="fas fa-question-circle"></i>
                                            </div>
                                            <h6 class="empty-title">No Questions Yet</h6>
                                            <p class="empty-description">Add questions to your quiz to get started.</p>
                                            <button type="button" class="btn btn-primary-custom"
                                                onclick="addQuestion()">
                                                <i class="fas fa-plus me-2"></i>Add First Question
                                            </button>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
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

            .quiz-edit-container {
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
            }

            .breadcrumb-separator {
                margin: 0 0.5rem;
                opacity: 0.6;
            }

            .quiz-title {
                font-size: 2.5rem;
                font-weight: 700;
                margin-bottom: 0.5rem;
            }

            .quiz-subtitle {
                opacity: 0.9;
                font-size: 1.1rem;
            }

            .quiz-actions {
                display: flex;
                gap: 1rem;
                justify-content: flex-end;
            }

            .btn-primary-custom {
                background: var(--gradient-primary);
                border: none;
                color: white;
                padding: 0.75rem 1.5rem;
                border-radius: 12px;
                font-weight: 600;
                transition: all 0.3s ease;
            }

            .btn-primary-custom:hover {
                transform: translateY(-2px);
                box-shadow: 0 8px 20px rgba(79, 70, 229, 0.3);
                color: white;
            }

            .btn-secondary-custom {
                background: rgba(255, 255, 255, 0.15);
                border: 2px solid rgba(255, 255, 255, 0.3);
                color: white;
                padding: 0.75rem 1.5rem;
                border-radius: 12px;
                font-weight: 600;
                transition: all 0.3s ease;
                backdrop-filter: blur(10px);
            }

            .btn-secondary-custom:hover {
                background: rgba(255, 255, 255, 0.25);
                color: white;
            }

            .quiz-content {
                margin-top: -1rem;
                position: relative;
                z-index: 3;
            }

            .settings-card,
            .questions-card {
                background: var(--card-bg);
                border-radius: 16px;
                box-shadow: var(--shadow-lg);
                border: 1px solid var(--border-color);
                overflow: hidden;
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

            .form-label {
                font-weight: 600;
                color: var(--text-primary);
                margin-bottom: 0.5rem;
                display: block;
            }

            .form-control,
            .form-select {
                border: 2px solid var(--border-color);
                border-radius: 10px;
                padding: 0.75rem;
                transition: all 0.3s ease;
                background: white;
            }

            .form-control:focus,
            .form-select:focus {
                border-color: var(--primary-color);
                box-shadow: 0 0 0 0.2rem rgba(79, 70, 229, 0.25);
            }

            .form-check-input:checked {
                background-color: var(--primary-color);
                border-color: var(--primary-color);
            }

            .question-item {
                background: var(--card-bg);
                border: 2px solid var(--border-color);
                border-radius: 12px;
                margin-bottom: 1.5rem;
                overflow: hidden;
                transition: all 0.3s ease;
            }

            .question-item:hover {
                border-color: var(--primary-color);
                box-shadow: var(--shadow-md);
            }

            .question-header {
                display: flex;
                align-items: center;
                justify-content: space-between;
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
            }

            .question-number .number {
                color: white;
                font-weight: 600;
                font-size: 1.1rem;
            }

            .question-actions {
                display: flex;
                gap: 0.5rem;
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

            .move-up,
            .move-down {
                background: var(--gradient-success);
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

            .is-invalid {
                border-color: var(--danger-color);
            }

            .invalid-feedback {
                color: var(--danger-color);
                font-size: 0.875rem;
                margin-top: 0.25rem;
            }

            /* Responsive Design */
            @media (max-width: 768px) {
                .quiz-title {
                    font-size: 1.8rem;
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
        </style>
    @endpush

    @push('scripts')
        <script>
            let questionIndex = {{ $quiz->questions->count() }};

            function addQuestion() {
                const container = document.getElementById('questions-container');
                const emptyState = document.getElementById('empty-questions');

                if (emptyState) {
                    emptyState.remove();
                }

                const questionHtml = `
                    <div class="question-item" data-question-index="${questionIndex}">
                        <div class="question-header">
                            <div class="question-number">
                                <span class="number">${questionIndex + 1}</span>
                            </div>
                            <div class="question-actions">
                                <button type="button" class="action-icon move-up" title="Move Up" onclick="moveQuestion(${questionIndex}, 'up')">
                                    <i class="fas fa-arrow-up"></i>
                                </button>
                                <button type="button" class="action-icon move-down" title="Move Down" onclick="moveQuestion(${questionIndex}, 'down')">
                                    <i class="fas fa-arrow-down"></i>
                                </button>
                                <button type="button" class="action-icon delete-question" title="Delete Question" onclick="removeQuestion(${questionIndex})">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                        <div class="question-content">
                            <input type="hidden" name="questions[${questionIndex}][id]" value="">
                            
                            <div class="form-group mb-3">
                                <label class="form-label">Question</label>
                                <textarea class="form-control" name="questions[${questionIndex}][question]" rows="3" required placeholder="Enter your question..."></textarea>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Question Type</label>
                                    <select class="form-select" name="questions[${questionIndex}][type]">
                                        <option value="text">Text Answer</option>
                                        <option value="multiple_choice">Multiple Choice</option>
                                        <option value="true_false">True/False</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Points</label>
                                    <input type="number" class="form-control" name="questions[${questionIndex}][points]" value="1" min="1" required>
                                </div>
                            </div>

                            <div class="form-group mb-3">
                                <label class="form-label">Correct Answer</label>
                                <textarea class="form-control" name="questions[${questionIndex}][answer]" rows="2" required placeholder="Enter the correct answer..."></textarea>
                            </div>
                        </div>
                    </div>
                `;

                container.insertAdjacentHTML('beforeend', questionHtml);
                questionIndex++;

                // Scroll to the new question
                const newQuestion = container.lastElementChild;
                newQuestion.scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                });
            }

            function removeQuestion(index) {
                if (confirm('Are you sure you want to delete this question?')) {
                    const questionItem = document.querySelector(`[data-question-index="${index}"]`);
                    questionItem.remove();
                    updateQuestionNumbers();

                    // Show empty state if no questions remain
                    if (document.querySelectorAll('.question-item').length === 0) {
                        showEmptyState();
                    }
                }
            }

            function moveQuestion(index, direction) {
                const questionItem = document.querySelector(`[data-question-index="${index}"]`);
                const container = document.getElementById('questions-container');

                if (direction === 'up' && questionItem.previousElementSibling) {
                    container.insertBefore(questionItem, questionItem.previousElementSibling);
                } else if (direction === 'down' && questionItem.nextElementSibling) {
                    container.insertBefore(questionItem.nextElementSibling, questionItem);
                }

                updateQuestionNumbers();
            }

            function updateQuestionNumbers() {
                const questions = document.querySelectorAll('.question-item');
                questions.forEach((question, index) => {
                    const numberSpan = question.querySelector('.question-number .number');
                    numberSpan.textContent = index + 1;
                });
            }

            function showEmptyState() {
                const container = document.getElementById('questions-container');
                const emptyStateHtml = `
                    <div class="empty-state" id="empty-questions">
                        <div class="empty-icon">
                            <i class="fas fa-question-circle"></i>
                        </div>
                        <h6 class="empty-title">No Questions Yet</h6>
                        <p class="empty-description">Add questions to your quiz to get started.</p>
                        <button type="button" class="btn btn-primary-custom" onclick="addQuestion()">
                            <i class="fas fa-plus me-2"></i>Add First Question
                        </button>
                    </div>
                `;
                container.innerHTML = emptyStateHtml;
            }

            // Form validation
            document.getElementById('quiz-form').addEventListener('submit', function(e) {
                const questions = document.querySelectorAll('.question-item');
                if (questions.length === 0) {
                    e.preventDefault();
                    alert('Please add at least one question to the quiz.');
                    return false;
                }
            });
        </script>
    @endpush
@endsection
