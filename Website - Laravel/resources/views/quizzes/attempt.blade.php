@extends('layouts.app')

@section('content')
    <div class="quiz-container">
        <!-- Quiz Header -->
        <div class="quiz-header">
            <div class="quiz-header-content">
                <div class="quiz-title-section">
                    <div class="quiz-icon">
                        <i class="fas fa-clipboard-check"></i>
                    </div>
                    <div>
                        <h1 class="quiz-title">{{ $quiz->title }}</h1>
                        <p class="quiz-subtitle">{{ count($quiz->questions) }} Questions •
                            {{ $quiz->questions->sum('points') }} Total Points</p>
                    </div>
                </div>

                <div class="quiz-info-badges">
                    @if ($quiz->time_limit)
                        <div class="quiz-badge quiz-badge-time">
                            <i class="fas fa-clock"></i>
                            <span>{{ $quiz->time_limit }} minutes</span>
                        </div>
                        <div class="quiz-timer" id="timer-display">
                            <i class="fas fa-hourglass-half"></i>
                            <span id="timer-text">Loading...</span>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Progress Bar -->
            <div class="quiz-progress-container">
                <div class="quiz-progress-bar">
                    <div class="quiz-progress-fill" id="progress-fill"></div>
                </div>
                <span class="quiz-progress-text" id="progress-text">0% Complete</span>
            </div>
        </div>

        <!-- Quiz Form -->
        <form action="{{ route('quiz.attempt.store', [$course->id, $quiz->id]) }}" method="POST" id="quiz-form">
            @csrf

            <div class="quiz-questions">
                @foreach ($quiz->questions as $index => $question)
                    <div class="question-card" data-question="{{ $index + 1 }}">
                        <div class="question-header">
                            <div class="question-number">
                                <span>{{ $index + 1 }}</span>
                            </div>
                            <div class="question-info">
                                <h3 class="question-title">Question {{ $index + 1 }}</h3>
                                <div class="question-points">
                                    <i class="fas fa-star"></i>
                                    {{ $question->points }} {{ $question->points == 1 ? 'point' : 'points' }}
                                </div>
                            </div>
                        </div>

                        <div class="question-content">
                            <p class="question-text">{{ $question->question }}</p>

                            <div class="answer-section">
                                <label for="answer_{{ $question->id }}" class="answer-label">
                                    <i class="fas fa-pen"></i>
                                    Your Answer
                                </label>
                                <div class="answer-input-container">
                                    <textarea class="answer-input" id="answer_{{ $question->id }}" name="answers[{{ $question->id }}]" rows="4"
                                        placeholder="Type your answer here..." required data-question-index="{{ $index }}"></textarea>
                                    <div class="answer-status" id="status_{{ $question->id }}">
                                        <i class="fas fa-circle"></i>
                                        <span>Not answered</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Submit Section -->
            <div class="quiz-submit-section">
                <div class="quiz-summary">
                    <div class="summary-stats">
                        <div class="stat-item">
                            <i class="fas fa-check-circle"></i>
                            <span>Answered: <strong id="answered-count">0</strong>/{{ count($quiz->questions) }}</span>
                        </div>
                        <div class="stat-item">
                            <i class="fas fa-clock"></i>
                            <span>Time Spent: <strong id="time-spent">0:00</strong></span>
                        </div>
                    </div>

                    <button type="submit" class="quiz-submit-btn" id="submit-btn">
                        <i class="fas fa-paper-plane"></i>
                        <span>Submit Quiz</span>
                        <div class="btn-loader" id="btn-loader">
                            <i class="fas fa-spinner fa-spin"></i>
                        </div>
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Confirmation Modal -->
    <div class="modal-overlay" id="submit-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Submit Quiz?</h3>
                <button type="button" class="modal-close" id="modal-close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to submit your quiz? You won't be able to make changes after submission.</p>
                <div class="modal-stats">
                    <div class="modal-stat">
                        <strong id="modal-answered">0</strong>
                        <span>Answered</span>
                    </div>
                    <div class="modal-stat">
                        <strong id="modal-unanswered">{{ count($quiz->questions) }}</strong>
                        <span>Unanswered</span>
                    </div>
                </div>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn-secondary" id="modal-cancel">Review Answers</button>
                <button type="button" class="btn-primary" id="modal-confirm">Submit Quiz</button>
            </div>
        </div>
    </div>

    @push('styles')
        <style>
            .quiz-container {
                max-width: 900px;
                margin: 0 auto;
                padding: 20px;
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            }

            .quiz-header {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                border-radius: 20px;
                padding: 30px;
                margin-bottom: 30px;
                color: white;
                box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
            }

            .quiz-header-content {
                display: flex;
                justify-content: space-between;
                align-items: flex-start;
                margin-bottom: 20px;
            }

            .quiz-title-section {
                display: flex;
                align-items: center;
                gap: 20px;
            }

            .quiz-icon {
                background: rgba(255, 255, 255, 0.2);
                border-radius: 15px;
                padding: 15px;
                font-size: 24px;
            }

            .quiz-title {
                font-size: 28px;
                font-weight: 700;
                margin: 0;
                line-height: 1.2;
            }

            .quiz-subtitle {
                font-size: 16px;
                margin: 5px 0 0 0;
                opacity: 0.9;
            }

            .quiz-info-badges {
                display: flex;
                flex-direction: column;
                gap: 10px;
                align-items: flex-end;
            }

            .quiz-badge {
                display: flex;
                align-items: center;
                gap: 8px;
                background: rgba(255, 255, 255, 0.2);
                padding: 8px 16px;
                border-radius: 20px;
                font-size: 14px;
                font-weight: 500;
            }

            .quiz-timer {
                display: flex;
                align-items: center;
                gap: 8px;
                background: rgba(255, 255, 255, 0.9);
                color: #667eea;
                padding: 10px 18px;
                border-radius: 20px;
                font-weight: 600;
                font-size: 15px;
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

            .quiz-progress-container {
                display: flex;
                align-items: center;
                gap: 15px;
            }

            .quiz-progress-bar {
                flex: 1;
                height: 8px;
                background: rgba(255, 255, 255, 0.3);
                border-radius: 10px;
                overflow: hidden;
            }

            .quiz-progress-fill {
                height: 100%;
                background: linear-gradient(90deg, #4CAF50, #45a049);
                border-radius: 10px;
                width: 0%;
                transition: width 0.3s ease;
            }

            .quiz-progress-text {
                font-size: 14px;
                font-weight: 600;
                white-space: nowrap;
            }

            .quiz-questions {
                display: flex;
                flex-direction: column;
                gap: 25px;
            }

            .question-card {
                background: white;
                border-radius: 16px;
                box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
                border: 2px solid transparent;
                transition: all 0.3s ease;
                overflow: hidden;
            }

            .question-card:hover {
                transform: translateY(-2px);
                box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
            }

            .question-card.answered {
                border-color: #4CAF50;
            }

            .question-header {
                display: flex;
                align-items: center;
                gap: 20px;
                padding: 25px 30px 20px;
                background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            }

            .question-number {
                width: 50px;
                height: 50px;
                background: linear-gradient(135deg, #667eea, #764ba2);
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                color: white;
                font-weight: 700;
                font-size: 18px;
            }

            .question-info {
                flex: 1;
            }

            .question-title {
                font-size: 20px;
                font-weight: 600;
                margin: 0;
                color: #2c3e50;
            }

            .question-points {
                display: flex;
                align-items: center;
                gap: 6px;
                color: #f39c12;
                font-size: 14px;
                font-weight: 500;
                margin-top: 4px;
            }

            .question-content {
                padding: 20px 30px 30px;
            }

            .question-text {
                font-size: 16px;
                line-height: 1.6;
                color: #34495e;
                margin-bottom: 25px;
            }

            .answer-section {
                margin-top: 20px;
            }

            .answer-label {
                display: flex;
                align-items: center;
                gap: 8px;
                font-weight: 600;
                color: #2c3e50;
                margin-bottom: 12px;
                font-size: 15px;
            }

            .answer-input-container {
                position: relative;
            }

            .answer-input {
                width: 100%;
                border: 2px solid #e1e8ed;
                border-radius: 12px;
                padding: 15px 20px;
                font-size: 15px;
                line-height: 1.5;
                transition: all 0.3s ease;
                resize: vertical;
                min-height: 100px;
            }

            .answer-input:focus {
                outline: none;
                border-color: #667eea;
                box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            }

            .answer-status {
                position: absolute;
                top: 15px;
                right: 20px;
                display: flex;
                align-items: center;
                gap: 6px;
                font-size: 12px;
                color: #95a5a6;
                transition: all 0.3s ease;
            }

            .answer-status.answered {
                color: #4CAF50;
            }

            .quiz-submit-section {
                margin-top: 40px;
                padding: 30px;
                background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
                border-radius: 16px;
            }

            .quiz-summary {
                display: flex;
                justify-content: space-between;
                align-items: center;
                gap: 20px;
                flex-wrap: wrap;
            }

            .summary-stats {
                display: flex;
                gap: 30px;
                flex-wrap: wrap;
            }

            .stat-item {
                display: flex;
                align-items: center;
                gap: 8px;
                color: #2c3e50;
                font-size: 15px;
            }

            .quiz-submit-btn {
                position: relative;
                background: linear-gradient(135deg, #4CAF50, #45a049);
                color: white;
                border: none;
                padding: 15px 30px;
                border-radius: 12px;
                font-size: 16px;
                font-weight: 600;
                cursor: pointer;
                transition: all 0.3s ease;
                display: flex;
                align-items: center;
                gap: 12px;
                min-width: 160px;
                justify-content: center;
            }

            .quiz-submit-btn:hover {
                transform: translateY(-2px);
                box-shadow: 0 8px 25px rgba(76, 175, 80, 0.3);
            }

            .quiz-submit-btn:disabled {
                opacity: 0.7;
                cursor: not-allowed;
                transform: none;
            }

            .btn-loader {
                display: none;
            }

            .quiz-submit-btn.loading .btn-loader {
                display: block;
            }

            .quiz-submit-btn.loading span {
                display: none;
            }

            /* Modal Styles */
            .modal-overlay {
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(0, 0, 0, 0.5);
                display: none;
                align-items: center;
                justify-content: center;
                z-index: 1000;
                backdrop-filter: blur(5px);
            }

            .modal-overlay.show {
                display: flex;
            }

            .modal-content {
                background: white;
                border-radius: 20px;
                padding: 0;
                max-width: 500px;
                width: 90%;
                max-height: 90vh;
                overflow-y: auto;
                animation: modalSlideIn 0.3s ease;
            }

            @keyframes modalSlideIn {
                from {
                    opacity: 0;
                    transform: translateY(-50px);
                }

                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            .modal-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 25px 30px;
                border-bottom: 1px solid #e1e8ed;
            }

            .modal-header h3 {
                margin: 0;
                font-size: 20px;
                color: #2c3e50;
            }

            .modal-close {
                background: none;
                border: none;
                font-size: 20px;
                color: #95a5a6;
                cursor: pointer;
                padding: 5px;
                border-radius: 50%;
                transition: all 0.2s ease;
            }

            .modal-close:hover {
                background: #f1f2f6;
                color: #2c3e50;
            }

            .modal-body {
                padding: 30px;
            }

            .modal-body p {
                margin-bottom: 20px;
                color: #2c3e50;
                line-height: 1.6;
            }

            .modal-stats {
                display: flex;
                gap: 30px;
                justify-content: center;
            }

            .modal-stat {
                text-align: center;
            }

            .modal-stat strong {
                display: block;
                font-size: 24px;
                color: #2c3e50;
                margin-bottom: 5px;
            }

            .modal-stat span {
                color: #95a5a6;
                font-size: 14px;
            }

            .modal-actions {
                display: flex;
                gap: 15px;
                padding: 20px 30px 30px;
                justify-content: flex-end;
            }

            .btn-secondary {
                background: #e1e8ed;
                color: #2c3e50;
                border: none;
                padding: 12px 24px;
                border-radius: 8px;
                font-weight: 500;
                cursor: pointer;
                transition: all 0.2s ease;
            }

            .btn-secondary:hover {
                background: #d1d9e0;
            }

            .btn-primary {
                background: linear-gradient(135deg, #4CAF50, #45a049);
                color: white;
                border: none;
                padding: 12px 24px;
                border-radius: 8px;
                font-weight: 500;
                cursor: pointer;
                transition: all 0.2s ease;
            }

            .btn-primary:hover {
                transform: translateY(-1px);
                box-shadow: 0 4px 15px rgba(76, 175, 80, 0.3);
            }

            /* Responsive Design */
            @media (max-width: 768px) {
                .quiz-container {
                    padding: 15px;
                }

                .quiz-header {
                    padding: 20px;
                }

                .quiz-header-content {
                    flex-direction: column;
                    gap: 20px;
                }

                .quiz-info-badges {
                    align-items: flex-start;
                }

                .quiz-title {
                    font-size: 24px;
                }

                .question-header {
                    padding: 20px;
                    flex-direction: column;
                    text-align: center;
                    gap: 15px;
                }

                .question-content {
                    padding: 20px;
                }

                .quiz-summary {
                    flex-direction: column;
                    text-align: center;
                    gap: 20px;
                }

                .summary-stats {
                    justify-content: center;
                }

                .modal-content {
                    width: 95%;
                    margin: 20px;
                }
            }
        </style>
    @endpush

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Timer functionality
                @if ($quiz->time_limit)
                    const endTime = {{ session('quiz_timer.end') }};
                    const timerElement = document.getElementById('timer-text');
                    const startTime = Date.now();

                    function updateTimer() {
                        const now = Math.floor(Date.now() / 1000);
                        const diff = endTime - now;

                        if (diff <= 0) {
                            timerElement.textContent = 'Time Expired!';
                            timerElement.parentElement.style.background = 'linear-gradient(135deg, #e74c3c, #c0392b)';
                            document.getElementById('quiz-form').submit();
                            return;
                        }

                        const minutes = Math.floor(diff / 60);
                        const seconds = diff % 60;
                        timerElement.textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;

                        // Change color when time is running low
                        if (diff <= 300) { // 5 minutes
                            timerElement.parentElement.style.background = 'linear-gradient(135deg, #f39c12, #e67e22)';
                        }
                        if (diff <= 60) { // 1 minute
                            timerElement.parentElement.style.background = 'linear-gradient(135deg, #e74c3c, #c0392b)';
                        }
                    }

                    updateTimer();
                    setInterval(updateTimer, 1000);
                @endif

                // Progress tracking
                const textareas = document.querySelectorAll('.answer-input');
                const progressFill = document.getElementById('progress-fill');
                const progressText = document.getElementById('progress-text');
                const answeredCount = document.getElementById('answered-count');
                const totalQuestions = {{ count($quiz->questions) }};

                let answered = 0;
                let timeSpent = 0;
                const timeSpentElement = document.getElementById('time-spent');

                // Time tracking
                setInterval(() => {
                    timeSpent++;
                    const minutes = Math.floor(timeSpent / 60);
                    const seconds = timeSpent % 60;
                    timeSpentElement.textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;
                }, 1000);

                function updateProgress() {
                    const percentage = (answered / totalQuestions) * 100;
                    progressFill.style.width = percentage + '%';
                    progressText.textContent = Math.round(percentage) + '% Complete';
                    answeredCount.textContent = answered;
                }

                textareas.forEach((textarea, index) => {
                    const statusElement = document.getElementById(`status_${textarea.name.match(/\d+/)[0]}`);
                    const questionCard = textarea.closest('.question-card');

                    textarea.addEventListener('input', function() {
                        const wasAnswered = this.dataset.answered === 'true';
                        const isAnswered = this.value.trim().length > 0;

                        if (isAnswered && !wasAnswered) {
                            answered++;
                            this.dataset.answered = 'true';
                            statusElement.innerHTML =
                                '<i class="fas fa-check-circle"></i><span>Answered</span>';
                            statusElement.classList.add('answered');
                            questionCard.classList.add('answered');
                        } else if (!isAnswered && wasAnswered) {
                            answered--;
                            this.dataset.answered = 'false';
                            statusElement.innerHTML =
                                '<i class="fas fa-circle"></i><span>Not answered</span>';
                            statusElement.classList.remove('answered');
                            questionCard.classList.remove('answered');
                        }

                        updateProgress();
                    });
                });

                // Form submission with confirmation
                const form = document.getElementById('quiz-form');
                const submitBtn = document.getElementById('submit-btn');
                const modal = document.getElementById('submit-modal');
                const modalClose = document.getElementById('modal-close');
                const modalCancel = document.getElementById('modal-cancel');
                const modalConfirm = document.getElementById('modal-confirm');
                const modalAnswered = document.getElementById('modal-answered');
                const modalUnanswered = document.getElementById('modal-unanswered');

                form.addEventListener('submit', function(e) {
                    e.preventDefault();

                    modalAnswered.textContent = answered;
                    modalUnanswered.textContent = totalQuestions - answered;
                    modal.classList.add('show');
                });

                modalClose.addEventListener('click', () => modal.classList.remove('show'));
                modalCancel.addEventListener('click', () => modal.classList.remove('show'));

                modalConfirm.addEventListener('click', function() {
                    modal.classList.remove('show');
                    submitBtn.classList.add('loading');
                    submitBtn.disabled = true;

                    setTimeout(() => {
                        form.submit();
                    }, 500);
                });

                // Close modal on outside click
                modal.addEventListener('click', function(e) {
                    if (e.target === modal) {
                        modal.classList.remove('show');
                    }
                });

                // Prevent accidental page leave
                window.addEventListener('beforeunload', function(e) {
                    if (answered > 0) {
                        e.preventDefault();
                        e.returnValue = '';
                    }
                });
            });
        </script>
    @endpush
@endsection