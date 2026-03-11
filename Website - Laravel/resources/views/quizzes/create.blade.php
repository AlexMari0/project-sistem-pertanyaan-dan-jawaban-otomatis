@extends('layouts.app')

@section('content')
    <div class="container">
        <h2 class="mb-4">Create New Quiz for {{ $course->title }}</h2>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if (session('ai_generated'))
            @php
                // Gabungkan pertanyaan lama dengan pertanyaan baru yang dihasilkan AI
                $oldQuestions = old('questions', []);
                $aiQuestions = session('questions', []);

                // Format pertanyaan AI untuk match dengan struktur form
                $formattedAiQuestions = array_map(function ($q) {
                    return [
                        'text' => $q['question'],
                        'answer' => $q['answer'],
                        'points' => 1,
                    ];
                }, $aiQuestions);

                $questions = array_merge($oldQuestions, $formattedAiQuestions);
            @endphp
        @else
            @php
                $questions = old('questions', [['text' => '', 'answer' => '', 'points' => 1]]);
            @endphp
        @endif

        <!-- Quiz Creation Form -->
        <form action="{{ route('quizzes.store', $course->id) }}" method="POST">
            @csrf

            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Quiz Information</h5>
                </div>
                <div class="card-body">
                    <div class="form-group mb-3">
                        <label for="title" class="form-label">Quiz Title</label>
                        <input type="text" class="form-control" id="title" name="title" value="{{ old('title') }}"
                            required>
                    </div>

                    <div class="form-group mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3">{{ old('description') }}</textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="due_date" class="form-label">Due Date</label>
                                <input type="datetime-local" class="form-control" id="due_date" name="due_date"
                                    value="{{ old('due_date') }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="time_limit" class="form-label">Time Limit (minutes)</label>
                                <input type="number" class="form-control" id="time_limit" name="time_limit" min="1"
                                    value="{{ old('time_limit', 30) }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Questions</h5>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#aiGenerateModal">
                        <i class="fas fa-magic"></i> Generate Questions with AI
                    </button>
                </div>
                <div class="card-body">
                    <!-- Loading Indicator for Questions -->
                    <div id="questions-loading" class="text-center py-4" style="display: none;">
                        <div class="spinner-border text-primary mb-3" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <div class="mb-2">
                            <h5 class="text-primary">
                                <i class="fas fa-robot me-2"></i>AI is generating questions...
                            </h5>
                            <p class="text-muted mb-0">This may take a few moments. Please wait.</p>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary"
                                role="progressbar" style="width: 100%"></div>
                        </div>
                    </div>

                    <div id="questions-container">
                        @foreach ($questions as $index => $question)
                            <div class="question-item card mb-3">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h5 class="mb-0">Question {{ $index + 1 }}</h5>
                                        @if ($index > 0 || count($questions) > 1)
                                            <button type="button" class="btn btn-sm btn-outline-danger"
                                                onclick="removeQuestion(this)">
                                                <i class="fas fa-trash"></i> Remove
                                            </button>
                                        @endif
                                    </div>

                                    <div class="form-group mb-3">
                                        <label class="form-label">Question Text</label>
                                        <textarea class="form-control question-text" rows="3" name="questions[{{ $index }}][text]" required>{{ $question['text'] }}</textarea>
                                    </div>

                                    <div class="form-group mb-3">
                                        <label class="form-label">Answer</label>
                                        <textarea class="form-control question-answer" rows="2" name="questions[{{ $index }}][answer]" required>{{ $question['answer'] }}</textarea>
                                    </div>

                                    <div class="form-group mb-3">
                                        <label class="form-label">Points</label>
                                        <input type="number" class="form-control question-points" min="1"
                                            value="{{ $question['points'] ?? 1 }}"
                                            name="questions[{{ $index }}][points]" required>
                                    </div>

                                    @if (session('ai_generated') && $index >= count($oldQuestions))
                                        <div class="badge bg-success mb-2">
                                            <i class="fas fa-robot"></i> AI Generated
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <button type="button" class="btn btn-secondary" onclick="addQuestion()">
                        <i class="fas fa-plus"></i> Add Question Manually
                    </button>
                </div>
            </div>

            <div class="d-flex justify-content-between">
                <a href="{{ route('courses.show', $course->id) }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Cancel
                </a>
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save"></i> Save Quiz
                </button>
            </div>
        </form>
    </div>

    <!-- Unified AI Generation Modal -->
    <div class="modal fade" id="aiGenerateModal" tabindex="-1" aria-labelledby="aiGenerateModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content shadow-lg border-0">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title d-flex align-items-center" id="aiGenerateModalLabel">
                        <i class="fas fa-magic me-2"></i> AI Question Generator
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>

                <div class="modal-body p-4">
                    <!-- Error Messages -->
                    <div id="ai-error" class="alert alert-danger" style="display: none;">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <span id="ai-error-message"></span>
                    </div>

                    <!-- Success Messages -->
                    <div id="ai-success" class="alert alert-success" style="display: none;">
                        <i class="fas fa-check-circle me-2"></i>
                        <span id="ai-success-message"></span>
                    </div>

                    <!-- Loading Indicator -->
                    <div id="ai-loading" class="text-center py-5" style="display: none;">
                        <div class="mb-4">
                            <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                        <div class="mb-3">
                            <h4 class="text-primary mb-2">
                                <i class="fas fa-cog fa-spin me-2"></i><span id="loading-title">Processing your
                                    request...</span>
                            </h4>
                            <p class="text-muted mb-3" id="loading-message">Analyzing your text and generating
                                questions...</p>
                            <div class="progress mx-auto" style="height: 10px; max-width: 400px;">
                                <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary"
                                    role="progressbar" style="width: 100%"></div>
                            </div>
                        </div>
                        <small class="text-muted">
                            <i class="fas fa-info-circle me-1"></i>
                            <span id="loading-subtitle">This process may take 30-60 seconds depending on the complexity of
                                your text.</span>
                        </small>
                    </div>

                    <!-- Form Content -->
                    <div id="ai-content">
                        <div class="card border-0 bg-light">
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-4">
                                    <i class="fas fa-info-circle text-primary me-2"></i>
                                    <small class="text-muted">
                                        Generate questions using various AI models including traditional models, weighted
                                        averaging, and DeepSeek AI
                                    </small>
                                </div>

                                <form id="ai-generation-form">
                                    <div class="mb-4">
                                        <label for="ai-text" class="form-label fw-bold">
                                            <i class="fas fa-file-text me-2"></i>Input Text
                                        </label>
                                        <textarea class="form-control shadow-sm rounded" id="ai-text" name="text" rows="8" required
                                            placeholder="Enter context or paragraph for question generation..."></textarea>
                                        <div class="form-text">
                                            <i class="fas fa-lightbulb me-1"></i>
                                            Tip: Provide detailed text for better question quality
                                        </div>
                                    </div>

                                    <div class="mb-4">
                                        <label for="ai-model" class="form-label fw-bold">
                                            <i class="fas fa-robot me-2"></i>Select AI Model
                                        </label>
                                        <select class="form-select shadow-sm rounded" id="ai-model" name="model"
                                            required>
                                            <option value="">Choose an AI model...</option>
                                            <option value="weighted">Weighted Averaging (Multiple Models)</option>
                                            <option value="deepseek">DeepSeek AI (Advanced Analysis)</option>
                                            <option value="chatgpt">ChatGPT (OpenAI GPT-4)</option>
                                            <option value="gemini">Gemini AI (Google's Advanced Model)</option>
                                        </select>
                                        <div class="form-text">
                                            <i class="fas fa-info-circle me-1"></i>
                                            <span id="model-description">Select a model to see its description</span>
                                        </div>
                                    </div>

                                    <div class="d-flex align-items-center justify-content-between">
                                        <button type="submit" class="btn btn-primary btn-lg px-4" id="ai-submit">
                                            <i class="fas fa-magic me-2"></i>Generate Questions
                                        </button>
                                        <div class="text-end">
                                            <small class="text-muted d-block" id="processing-info">
                                                <i class="fas fa-clock me-1"></i>Processing time varies by model
                                            </small>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Results Preview -->
                        <div id="ai-results" class="mt-4" style="display: none;">
                            <div class="card border-primary">
                                <div class="card-header bg-primary text-white">
                                    <h6 class="mb-0">
                                        <i class="fas fa-list me-2"></i>Generated Questions Preview
                                    </h6>
                                </div>
                                <div class="card-body" id="ai-results-content">
                                    <!-- Results will be populated here -->
                                </div>
                                <div class="card-footer">
                                    <div class="d-flex justify-content-between">
                                        <small class="text-muted">
                                            <i class="fas fa-info-circle me-1"></i>
                                            Review questions before adding to quiz
                                        </small>
                                        <button type="button" class="btn btn-success btn-sm" id="add-all-questions">
                                            <i class="fas fa-plus me-1"></i>Add All to Quiz
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal" id="ai-close-btn">
                        <i class="fas fa-times me-1"></i> Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    <style>
        .qa-pair {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border: 1px solid #007bff;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            transition: all 0.3s ease;
        }

        .qa-pair:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 123, 255, 0.15);
        }

        .qa-pair .question {
            font-weight: 600;
            color: #007bff;
            margin-bottom: 8px;
        }

        .qa-pair .answer {
            color: #495057;
            padding-left: 15px;
            border-left: 3px solid #007bff;
        }

        .model-badge {
            font-size: 0.75rem;
            padding: 4px 8px;
        }

        /* Loading Animation Styles */
        .spinner-border {
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        .progress-bar-animated {
            animation: progress-bar-stripes 1s linear infinite;
        }

        @keyframes progress-bar-stripes {
            0% {
                background-position: 1rem 0;
            }

            100% {
                background-position: 0 0;
            }
        }

        /* Model specific styling */
        .deepseek-qa {
            border-color: #17a2b8;
        }

        .deepseek-qa .question {
            color: #17a2b8;
        }

        .deepseek-qa .answer {
            border-left-color: #17a2b8;
        }

        .chatgpt-qa {
            border-color: #ffc107;
        }

        .chatgpt-qa .question {
            color: #ffc107;
        }

        .chatgpt-qa .answer {
            border-left-color: #ffc107;
        }

        .weighted-qa {
            border-color: #28a745;
        }

        .weighted-qa .question {
            color: #28a745;
        }

        .weighted-qa .answer {
            border-left-color: #28a745;
        }

        /* Form enhancements */
        .form-select:focus,
        .form-control:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }

        .btn-primary {
            background: linear-gradient(45deg, #007bff, #0056b3);
            border: none;
        }

        .btn-primary:hover {
            background: linear-gradient(45deg, #0056b3, #004085);
            transform: translateY(-1px);
        }
    </style>

    <script>
        // Global variables
        let currentQAPairs = [];
        let selectedModel = '';

        // Model descriptions
        const modelDescriptions = {
            'weighted': 'Uses multiple models with weighted averaging for enhanced question quality',
            'deepseek': 'Advanced AI with step-by-step analysis and reasoning capabilities',
            'chatgpt': 'ChatGPT (GPT-4) - Advanced conversational AI with excellent question generation capabilities',
            'gemini': 'Google Gemini AI - Advanced multimodal AI with superior reasoning and analysis'
        };

        // Utility functions
        function addQuestion() {
            const container = document.getElementById('questions-container');
            const questionCount = document.querySelectorAll('.question-item').length;

            const newQuestionHTML = `
                <div class="question-item card mb-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0">Question ${questionCount + 1}</h5>
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeQuestion(this)">
                                <i class="fas fa-trash"></i> Remove
                            </button>
                        </div>
                        <div class="form-group mb-3">
                            <label class="form-label">Question Text</label>
                            <textarea class="form-control question-text" rows="3" name="questions[${questionCount}][text]" required></textarea>
                        </div>
                        <div class="form-group mb-3">
                            <label class="form-label">Answer</label>
                            <textarea class="form-control question-answer" rows="2" name="questions[${questionCount}][answer]" required></textarea>
                        </div>
                        <div class="form-group mb-3">
                            <label class="form-label">Points</label>
                            <input type="number" class="form-control question-points" min="1" value="1" name="questions[${questionCount}][points]" required>
                        </div>
                    </div>
                </div>
            `;

            container.insertAdjacentHTML('beforeend', newQuestionHTML);
        }

        function removeQuestion(button) {
            const questionItem = button.closest('.question-item');
            const totalQuestions = document.querySelectorAll('.question-item').length;

            if (totalQuestions > 1) {
                if (confirm('Are you sure you want to remove this question?')) {
                    questionItem.remove();
                    updateQuestionNumbers();
                }
            } else {
                alert('You need at least one question');
            }
        }

        function updateQuestionNumbers() {
            document.querySelectorAll('.question-item').forEach((item, index) => {
                const questionHeader = item.querySelector('h5');
                if (questionHeader) {
                    questionHeader.textContent = `Question ${index + 1}`;
                }

                const textArea = item.querySelector('.question-text');
                const answerArea = item.querySelector('.question-answer');
                const pointsInput = item.querySelector('.question-points');

                if (textArea) textArea.name = `questions[${index}][text]`;
                if (answerArea) answerArea.name = `questions[${index}][answer]`;
                if (pointsInput) pointsInput.name = `questions[${index}][points]`;
            });
        }

        function addGeneratedQuestion(question, answer, source = 'AI') {
            const container = document.getElementById('questions-container');
            const questionCount = document.querySelectorAll('.question-item').length;

            const unescapedQuestion = question.replace(/\\'/g, "'").replace(/\\"/g, '"');
            const unescapedAnswer = answer.replace(/\\'/g, "'").replace(/\\"/g, '"');

            let badgeClass, badgeIcon;
            if (source === 'DeepSeek') {
                badgeClass = 'bg-info';
                badgeIcon = 'fa-brain';
            } else if (source === 'ChatGPT') {
                badgeClass = 'bg-warning';
                badgeIcon = 'fa-comments';
            } else if (source === 'Gemini') {
                badgeClass = 'bg-warning';
                badgeIcon = 'fa-gem';
            } else if (source === 'Weighted') {
                badgeClass = 'bg-success';
                badgeIcon = 'fa-layer-group';
            } else {
                badgeClass = 'bg-primary';
                badgeIcon = 'fa-robot';
            }

            const newQuestionHTML = `
                <div class="question-item card mb-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0">Question ${questionCount + 1}</h5>
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeQuestion(this)">
                                <i class="fas fa-trash"></i> Remove
                            </button>
                        </div>
                        <div class="form-group mb-3">
                            <label class="form-label">Question Text</label>
                            <textarea class="form-control question-text" rows="3" name="questions[${questionCount}][text]" required>${unescapedQuestion}</textarea>
                        </div>
                        <div class="form-group mb-3">
                            <label class="form-label">Answer</label>
                            <textarea class="form-control question-answer" rows="2" name="questions[${questionCount}][answer]" required>${unescapedAnswer}</textarea>
                        </div>
                        <div class="form-group mb-3">
                            <label class="form-label">Points</label>
                            <input type="number" class="form-control question-points" min="1" value="1" name="questions[${questionCount}][points]" required>
                        </div>
                        <div class="badge ${badgeClass} mb-2">
                            <i class="fas ${badgeIcon}"></i> ${source} Generated
                        </div>
                    </div>
                </div>
            `;

            container.insertAdjacentHTML('beforeend', newQuestionHTML);

            const newQuestion = container.lastElementChild;
            newQuestion.scrollIntoView({
                behavior: 'smooth'
            });
        }

        // Modal functions
        function showAILoading(model) {
            document.getElementById('ai-loading').style.display = 'block';
            document.getElementById('ai-content').style.display = 'none';
            document.getElementById('ai-close-btn').disabled = true;
            document.getElementById('ai-error').style.display = 'none';
            document.getElementById('ai-success').style.display = 'none';

            // Update loading messages based on model
            updateLoadingMessages(model);
        }

        function hideAILoading() {
            document.getElementById('ai-loading').style.display = 'none';
            document.getElementById('ai-content').style.display = 'block';
            document.getElementById('ai-close-btn').disabled = false;
        }

        function updateLoadingMessages(model) {
            const loadingTitle = document.getElementById('loading-title');
            const loadingMessage = document.getElementById('loading-message');
            const loadingSubtitle = document.getElementById('loading-subtitle');

            if (model === 'deepseek') {
                loadingTitle.textContent = 'DeepSeek is analyzing your text...';
                loadingMessage.textContent = 'Advanced AI processing with step-by-step analysis';
                loadingSubtitle.textContent = 'DeepSeek AI provides detailed reasoning - this may take 30-60 seconds';
            } else if (model === 'chatgpt') {
                loadingTitle.textContent = 'ChatGPT is analyzing your text...';
                loadingMessage.textContent = 'OpenAI GPT-4 processing with advanced language understanding';
                loadingSubtitle.textContent = 'ChatGPT provides high-quality questions - this may take 30-45 seconds';
            } else if (model === 'gemini') {
                loadingTitle.textContent = 'Gemini is processing your text...';
                loadingMessage.textContent = 'Google\'s advanced AI analyzing and generating questions';
                loadingSubtitle.textContent = 'Gemini AI uses multimodal processing - this may take 30-45 seconds';
            } else if (model === 'weighted') {
                loadingTitle.textContent = 'Processing with multiple models...';
                loadingMessage.textContent = 'Using weighted averaging across multiple AI models';
                loadingSubtitle.textContent = 'Multiple model processing takes longer but provides better quality';
            } else {
                loadingTitle.textContent = `Processing with ${model.toUpperCase()} model...`;
                loadingMessage.textContent = 'Analyzing your text and generating questions';
                loadingSubtitle.textContent = 'This process may take 30-60 seconds depending on complexity';
            }
        }

        function showAIError(message) {
            hideAILoading();
            document.getElementById('ai-error-message').textContent = message;
            document.getElementById('ai-error').style.display = 'block';
            document.getElementById('ai-success').style.display = 'none';
        }

        function showAISuccess(message) {
            document.getElementById('ai-success-message').textContent = message;
            document.getElementById('ai-success').style.display = 'block';
            document.getElementById('ai-error').style.display = 'none';
        }

        function displayAIResults(qaPairs, model) {
            const resultsContainer = document.getElementById('ai-results-content');
            let resultsHTML = '';

            let modelClass, modelBadge, badgeClass;
            if (model === 'deepseek') {
                modelClass = 'deepseek-qa';
                modelBadge = 'DeepSeek';
                badgeClass = 'bg-info';
            } else if (model === 'chatgpt') {
                modelClass = 'chatgpt-qa';
                modelBadge = 'ChatGPT';
                badgeClass = 'bg-warning';
            } else if (model === 'gemini') {
                modelClass = 'gemini-qa';
                modelBadge = 'Gemini';
                badgeClass = 'bg-warning';
            } else if (model === 'weighted') {
                modelClass = 'weighted-qa';
                modelBadge = 'Weighted';
                badgeClass = 'bg-success';
            } else {
                modelClass = '';
                modelBadge = model.toUpperCase();
                badgeClass = 'bg-primary';
            }

            qaPairs.forEach((pair, index) => {
                resultsHTML += `
                    <div class="qa-pair ${modelClass}" data-index="${index}">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div class="question">
                                <i class="fas fa-question-circle me-2"></i>
                                ${pair.question.trim()}
                            </div>
                            <div class="d-flex gap-2">
                                <span class="badge ${badgeClass} model-badge">${modelBadge}</span>
                                <button type="button" class="btn btn-sm btn-outline-success" onclick="addSingleQuestion(${index})">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="answer">
                            <i class="fas fa-check-circle me-2"></i>
                            ${pair.answer.trim()}
                        </div>
                    </div>
                `;
            });

            resultsContainer.innerHTML = resultsHTML;
            document.getElementById('ai-results').style.display = 'block';

            currentQAPairs = qaPairs;
        }

        function addSingleQuestion(index) {
            if (currentQAPairs && currentQAPairs[index]) {
                const pair = currentQAPairs[index];
                let source;
                if (selectedModel === 'deepseek') {
                    source = 'DeepSeek';
                } else if (selectedModel === 'chatgpt') {
                    source = 'ChatGPT';
                } else if (selectedModel === 'gemini') {
                    source = 'Gemini';
                } else if (selectedModel === 'weighted') {
                    source = 'Weighted';
                } else {
                    source = 'AI';
                }

                addGeneratedQuestion(pair.question, pair.answer, source);

                const qaItem = document.querySelector(`[data-index="${index}"]`);
                if (qaItem) {
                    qaItem.style.transition = 'opacity 0.3s ease';
                    qaItem.style.opacity = '0.5';
                    const button = qaItem.querySelector('button');
                    button.innerHTML = '<i class="fas fa-check"></i> Added';
                    button.disabled = true;
                    button.classList.remove('btn-outline-success');
                    button.classList.add('btn-success');
                }

                showAISuccess('Question added to quiz successfully!');
                setTimeout(() => {
                    document.getElementById('ai-success').style.display = 'none';
                }, 3000);
            }
        }

        function addAllQuestions() {
            if (currentQAPairs && currentQAPairs.length > 0) {
                let addedCount = 0;
                let source;
                if (selectedModel === 'deepseek') {
                    source = 'DeepSeek';
                } else if (selectedModel === 'chatgpt') {
                    source = 'ChatGPT';
                } else if (selectedModel === 'gemini') {
                    source = 'Gemini';
                } else if (selectedModel === 'weighted') {
                    source = 'Weighted';
                } else {
                    source = 'AI';
                }

                currentQAPairs.forEach((pair, index) => {
                    const qaItem = document.querySelector(`[data-index="${index}"]`);
                    const button = qaItem ? qaItem.querySelector('button') : null;

                    if (button && !button.disabled) {
                        addGeneratedQuestion(pair.question, pair.answer, source);
                        addedCount++;

                        button.innerHTML = '<i class="fas fa-check"></i> Added';
                        button.disabled = true;
                        button.classList.remove('btn-outline-success');
                        button.classList.add('btn-success');
                        qaItem.style.opacity = '0.5';
                    }
                });

                if (addedCount > 0) {
                    showAISuccess(`${addedCount} questions added to quiz successfully!`);
                    setTimeout(() => {
                        document.getElementById('ai-success').style.display = 'none';
                    }, 5000);
                } else {
                    showAIError('All questions have already been added to the quiz.');
                }
            }
        }

        // API calls
        function generateWithDeepSeek(text) {
            return fetch('{{ route('quizzes.generate-deepseek', ['course' => $course->id]) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    text: text
                })
            });
        }

        function generateWithChatGPT(text) {
            return fetch('{{ route('quizzes.generate-chatgpt', ['course' => $course->id]) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    text: text
                })
            });
        }

        function generateWithGemini(text) {
            return fetch('{{ route('quizzes.generate-gemini', ['course' => $course->id]) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    text: text
                })
            });
        }

        function generateWithWeighted(text) {
            return fetch('{{ route('quizzes.generate-questions-weighted', ['course' => $course->id]) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    text: text
                })
            });
        }

        function generateWithSingleModel(text, model) {
            const formData = new FormData();
            formData.append('text', text);
            formData.append('model', model);
            formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

            return fetch('{{ route('quizzes.generate-questions', ['course' => $course->id]) }}', {
                method: 'POST',
                body: formData
            });
        }

        // Event listeners
        document.addEventListener('DOMContentLoaded', function() {
            // Model select change handler
            const modelSelect = document.getElementById('ai-model');
            const modelDescription = document.getElementById('model-description');
            const processingInfo = document.getElementById('processing-info');

            if (modelSelect) {
                modelSelect.addEventListener('change', function() {
                    const selectedValue = this.value;
                    selectedModel = selectedValue;

                    if (selectedValue && modelDescriptions[selectedValue]) {
                        modelDescription.textContent = modelDescriptions[selectedValue];
                        modelDescription.className = 'text-info';
                    } else {
                        modelDescription.textContent = 'Select a model to see its description';
                        modelDescription.className = 'text-muted';
                    }

                    // Update processing info
                    if (selectedValue === 'deepseek') {
                        processingInfo.innerHTML =
                            '<i class="fas fa-brain me-1"></i>DeepSeek AI - Advanced Analysis (30-60s)';
                    } else if (selectedValue === 'chatgpt') {
                        processingInfo.innerHTML =
                            '<i class="fas fa-comments me-1"></i>ChatGPT GPT-4 - Conversational AI (30-45s)';
                    } else if (selectedValue === 'gemini') {
                        processingInfo.innerHTML =
                            '<i class="fas fa-gem me-1"></i>Gemini AI - Multimodal Processing (30-45s)';
                    } else if (selectedValue === 'weighted') {
                        processingInfo.innerHTML =
                            '<i class="fas fa-layer-group me-1"></i>Multiple Models - Enhanced Quality (60-90s)';
                    } else if (selectedValue) {
                        processingInfo.innerHTML =
                            '<i class="fas fa-robot me-1"></i>Single Model Processing (30-45s)';
                    } else {
                        processingInfo.innerHTML =
                            '<i class="fas fa-clock me-1"></i>Processing time varies by model';
                    }
                });
            }

            // Form submission handler
            const aiForm = document.getElementById('ai-generation-form');
            if (aiForm) {
                aiForm.addEventListener('submit', function(e) {
                    e.preventDefault();

                    const textInput = document.getElementById('ai-text');
                    const modelSelect = document.getElementById('ai-model');
                    const text = textInput.value.trim();
                    const model = modelSelect.value;

                    if (!text) {
                        showAIError('Please enter some text to generate questions from.');
                        return;
                    }

                    if (!model) {
                        showAIError('Please select an AI model.');
                        return;
                    }

                    showAILoading(model);

                    let apiCall;

                    // Choose API call based on model
                    if (model === 'deepseek') {
                        apiCall = generateWithDeepSeek(text);
                    } else if (model === 'chatgpt') {
                        apiCall = generateWithChatGPT(text);
                    } else if (model === 'gemini') {
                        apiCall = generateWithGemini(text);
                    } else if (model === 'weighted') {
                        apiCall = generateWithWeighted(text);
                    } else {
                        apiCall = generateWithSingleModel(text, model);
                    }

                    apiCall
                        .then(response => {
                            if (model === 'deepseek' || model === 'chatgpt' || model === 'gemini') {
                                return response.json();
                            } else {
                                // For traditional models, handle form submission redirect
                                if (response.redirected) {
                                    window.location.href = response.url;
                                    return;
                                }
                                return response.json();
                            }
                        })
                        .then(data => {
                            hideAILoading();

                            if (data && data.success) {
                                if (data.qa_pairs && data.qa_pairs.length > 0) {
                                    displayAIResults(data.qa_pairs, model);
                                    let modelName;
                                    if (model === 'deepseek') {
                                        modelName = 'DeepSeek AI';
                                    } else if (model === 'chatgpt') {
                                        modelName = 'ChatGPT GPT-4';
                                    } else if (model === 'gemini') {
                                        modelName = 'Gemini AI';
                                    } else if (model === 'weighted') {
                                        modelName = 'Weighted Averaging';
                                    } else {
                                        modelName = model.toUpperCase();
                                    }
                                    showAISuccess(
                                        `Successfully generated ${data.qa_pairs.length} question(s) using ${modelName}!`
                                    );
                                } else if (data.questions && data.questions.length > 0) {
                                    // Handle traditional model response format
                                    const formattedPairs = data.questions.map(q => ({
                                        question: q.question,
                                        answer: q.answer
                                    }));
                                    displayAIResults(formattedPairs, model);
                                    showAISuccess(
                                        `Successfully generated ${formattedPairs.length} question(s) using ${model.toUpperCase()}!`
                                    );
                                }
                            } else {
                                showAIError(data.message ||
                                    'Failed to generate questions. Please try again with different text.'
                                );
                            }
                        })
                        .catch(error => {
                            console.error('AI Generation Error:', error);
                            hideAILoading();
                            showAIError(
                                'An error occurred while generating questions. Please try again.');
                        });
                });
            }

            // Add All button handler
            const addAllButton = document.getElementById('add-all-questions');
            if (addAllButton) {
                addAllButton.addEventListener('click', addAllQuestions);
            }

            // Modal event handlers
            const aiModal = document.getElementById('aiGenerateModal');
            if (aiModal) {
                aiModal.addEventListener('hidden.bs.modal', function() {
                    hideAILoading();
                    // Reset form and results when modal is closed
                    document.getElementById('ai-text').value = '';
                    document.getElementById('ai-model').value = '';
                    document.getElementById('ai-results').style.display = 'none';
                    document.getElementById('ai-error').style.display = 'none';
                    document.getElementById('ai-success').style.display = 'none';
                    document.getElementById('model-description').textContent =
                        'Select a model to see its description';
                    document.getElementById('model-description').className = 'text-muted';
                    document.getElementById('processing-info').innerHTML =
                        '<i class="fas fa-clock me-1"></i>Processing time varies by model';
                    currentQAPairs = [];
                    selectedModel = '';
                });
            }
        });

        // Add CSRF token to meta tag if not already present
        if (!document.querySelector('meta[name="csrf-token"]')) {
            const csrfMeta = document.createElement('meta');
            csrfMeta.name = 'csrf-token';
            csrfMeta.content = '{{ csrf_token() }}';
            document.getElementsByTagName('head')[0].appendChild(csrfMeta);
        }
    </script>
@endsection
