@extends('layouts.app')

@section('content')
    <div class="results-container">
        @php
            $scorePercentage = $attempt->score;
            $totalQuestions = count($quiz->questions);
            $usesCosineSimilarity = $attempt->usesCosineSimilarity();
            $stats = $attempt->statistics;
            $performanceLevel = $attempt->performance_level;
        @endphp

        <!-- Results Header -->
        <div class="results-header">
            <div class="results-celebration">
                <div class="celebration-icon {{ strtolower(str_replace(' ', '-', $performanceLevel['level'])) }}">
                    <span style="font-size: 35px;">{{ $performanceLevel['icon'] }}</span>
                </div>
                <div class="celebration-text">
                    <h1 class="celebration-title">{{ $performanceLevel['level'] }}!</h1>
                    <p class="celebration-subtitle">
                        @if ($scorePercentage >= 90)
                            Outstanding performance! You've mastered this material.
                        @elseif ($scorePercentage >= 80)
                            Excellent work! You have a strong understanding of the material.
                        @elseif ($scorePercentage >= 70)
                            Good job! You're on the right track with solid understanding.
                        @elseif ($scorePercentage >= 60)
                            Fair performance. Review the feedback to strengthen your knowledge.
                        @elseif ($scorePercentage >= 50)
                            Keep working! Use this feedback to improve your understanding.
                        @else
                            Don't give up! Every attempt is a learning opportunity.
                        @endif
                    </p>
                </div>
            </div>

            <div class="quiz-info">
                <h2 class="quiz-title">{{ $quiz->title }}</h2>
                <p class="completion-time">
                    <i class="fas fa-clock"></i>
                    Completed on {{ $attempt->completed_at->format('M d, Y \a\t g:i A') }}
                </p>
            </div>
        </div>

        <!-- Score Dashboard -->
        <div class="score-dashboard">
            <div class="score-card main-score">
                <div class="score-circle">
                    <svg class="score-ring" viewBox="0 0 120 120">
                        <circle class="score-ring-background" cx="60" cy="60" r="50" />
                        <circle class="score-ring-progress" cx="60" cy="60" r="50"
                            style="--percentage: {{ $scorePercentage }}" />
                    </svg>
                    <div class="score-content">
                        <div class="score-number">{{ round($scorePercentage) }}%</div>
                        <div class="score-label">Final Score</div>
                    </div>
                </div>
                <div class="score-details">
                    @if ($usesCosineSimilarity)
                        <div class="score-fraction">{{ round($attempt->raw_score, 1) }}/{{ $attempt->max_score }} points
                        </div>
                        @if ($stats)
                            <div class="similarity-info">
                                <small>Avg. Similarity: {{ round($stats['average_similarity'] * 100, 1) }}%</small>
                            </div>
                        @endif
                    @else
                        <div class="score-fraction">{{ $attempt->score }}/{{ $quiz->total_points }} points</div>
                    @endif
                </div>
            </div>

            <div class="stats-grid">
                @if ($usesCosineSimilarity && $stats)
                    <!-- Similarity-based stats -->
                    @foreach (['excellent' => 'fa-star', 'good' => 'fa-thumbs-up', 'fair' => 'fa-balance-scale'] as $label => $icon)
                        <div class="stat-card">
                            <div class="stat-icon {{ $label }}">
                                <i class="fas {{ $icon }}"></i>
                            </div>
                            <div class="stat-content">
                                <div class="stat-number">{{ $stats['category_distribution'][$label] }}</div>
                                <div class="stat-label">{{ ucfirst($label) }}
                                    ({{ $label === 'excellent' ? '90%+' : ($label === 'good' ? '70-89%' : '50-69%') }})
                                </div>
                            </div>
                        </div>
                    @endforeach

                    <div class="stat-card">
                        <div class="stat-icon needs-work">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-number">
                                {{ $stats['category_distribution']['poor'] + $stats['category_distribution']['very_poor'] }}
                            </div>
                            <div class="stat-label">Needs Work (&lt;50%)</div>
                        </div>
                    </div>
                @else
                    @php
                        $correctAnswers = collect($attempt->answers)->where('is_correct', true)->count();
                    @endphp

                    <div class="stat-card">
                        <div class="stat-icon correct">
                            <i class="fas fa-check"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-number">{{ $correctAnswers }}</div>
                            <div class="stat-label">Correct</div>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon incorrect">
                            <i class="fas fa-times"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-number">{{ $totalQuestions - $correctAnswers }}</div>
                            <div class="stat-label">Incorrect</div>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon total">
                            <i class="fas fa-list"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-number">{{ $totalQuestions }}</div>
                            <div class="stat-label">Total Questions</div>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon grade">
                            <i class="fas fa-graduation-cap"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-number">
                                @if ($scorePercentage >= 90)
                                    A
                                @elseif ($scorePercentage >= 80)
                                    B
                                @elseif ($scorePercentage >= 70)
                                    C
                                @elseif ($scorePercentage >= 60)
                                    D
                                @else
                                    F
                                @endif
                            </div>
                            <div class="stat-label">Grade</div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Question Review -->
        <div class="questions-review">
            <div class="review-header">
                <h3 class="review-title">
                    <i class="fas fa-clipboard-list"></i>
                    Detailed Review
                </h3>
                <p class="review-subtitle">
                    {{ $usesCosineSimilarity ? 'Review your answers with AI-powered similarity analysis' : 'Review your answers and learn from the feedback' }}
                </p>
            </div>

            <div class="questions-list">
                @foreach ($quiz->questions as $index => $question)
                    @php
                        $answerData = $attempt->answers[$question->id];
                        if ($usesCosineSimilarity) {
                            $similarity = $answerData['similarity_score'] ?? 0;
                            $pointsEarned = $answerData['points_earned'] ?? 0;
                            $maxPoints = $answerData['max_points'] ?? $question->points;
                            $similarityCategory = is_array($answerData['similarity_category'] ?? null)
                                ? $answerData['similarity_category']['category'] ?? 'Unknown'
                                : $answerData['similarity_category'] ?? 'Unknown';
                            $percentage = $answerData['percentage'] ?? 0;
                        } else {
                            $isCorrect = $answerData['is_correct'] ?? false;
                            $pointsEarned = $isCorrect ? $question->points : 0;
                            $maxPoints = $question->points;
                        }
                    @endphp

                    <div
                        class="question-review-card {{ $usesCosineSimilarity ? 'similarity-scoring' : ($isCorrect ? 'correct' : 'incorrect') }}">
                        <div class="question-header">
                            <div
                                class="question-number {{ $usesCosineSimilarity ? 'similarity' : ($isCorrect ? 'correct' : 'incorrect') }}">
                                <span class="number">{{ $index + 1 }}</span>
                                @if ($usesCosineSimilarity)
                                    <div class="similarity-badge">{{ round($similarity * 100) }}%</div>
                                @else
                                    <div class="status-icon">
                                        <i class="fas {{ $isCorrect ? 'fa-check' : 'fa-times' }}"></i>
                                    </div>
                                @endif
                            </div>
                            <div class="question-info">
                                <h4 class="question-title">Question {{ $index + 1 }}</h4>
                                <div class="question-meta">
                                    @if ($usesCosineSimilarity)
                                        <span class="points-earned similarity-points">
                                            <i class="fas fa-star"></i>
                                            {{ round($pointsEarned, 1) }}/{{ $maxPoints }} points
                                            ({{ $percentage }}%)
                                        </span>
                                        <span
                                            class="similarity-category {{ strtolower(str_replace(' ', '-', $similarityCategory)) }}">
                                            {{ $similarityCategory }} Match
                                        </span>
                                    @else
                                        <span class="points-earned {{ $isCorrect ? 'full-points' : 'no-points' }}">
                                            <i class="fas fa-star"></i>
                                            {{ $pointsEarned }}/{{ $maxPoints }} points
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="question-content">
                            <div class="question-text">
                                <p>{{ $question->question }}</p>
                            </div>

                            <div class="answer-review">
                                <div class="user-answer">
                                    <div class="answer-label">
                                        <i class="fas fa-user"></i>
                                        Your Answer
                                    </div>
                                    <div class="answer-content user-response">
                                        {{ $answerData['user_answer'] ?? 'No answer provided' }}
                                    </div>
                                </div>

                                <div class="correct-answer-section">
                                    <div class="answer-label">
                                        <i class="fas fa-lightbulb"></i>
                                        Reference Answer
                                    </div>
                                    <div class="answer-content correct-reference">
                                        {{ $answerData['correct_answer'] ?? $question->answer }}
                                    </div>
                                </div>
                            </div>

                            @if ($usesCosineSimilarity)
                                <div class="similarity-analysis">
                                    <div class="similarity-bar-container">
                                        <div class="similarity-label">Similarity Score</div>
                                        <div class="similarity-bar">
                                            <div class="similarity-fill" style="width: {{ $similarity * 100 }}%"></div>
                                            <span class="similarity-text">{{ round($similarity * 100, 1) }}%</span>
                                        </div>
                                    </div>
                                    <div class="feedback-section similarity-feedback">
                                        @if ($similarity >= 0.9)
                                            <i class="fas fa-star"></i><span>Excellent! Your answer closely matches the
                                                reference answer.</span>
                                        @elseif ($similarity >= 0.7)
                                            <i class="fas fa-thumbs-up"></i><span>Good answer! You captured most of the key
                                                concepts.</span>
                                        @elseif ($similarity >= 0.5)
                                            <i class="fas fa-info-circle"></i><span>Fair answer. Consider including more
                                                details from the reference answer.</span>
                                        @elseif ($similarity >= 0.2)
                                            <i class="fas fa-exclamation-triangle"></i><span>Your answer shows some
                                                understanding, but needs significant improvement.</span>
                                        @else
                                            <i class="fas fa-redo"></i><span>Review the reference answer to understand the
                                                expected response better.</span>
                                        @endif
                                    </div>
                                </div>
                            @else
                                <div class="feedback-section {{ $isCorrect ? 'positive' : 'negative' }}">
                                    <i class="fas {{ $isCorrect ? 'fa-thumbs-up' : 'fa-info-circle' }}"></i>
                                    <span>{{ $isCorrect ? 'Excellent! You got this one right.' : 'Review the correct answer above to improve your understanding.' }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Action Buttons -->
            <div class="results-actions">
                <a href="{{ route('courses.show', $course->id) }}" class="btn-action secondary">
                    <i class="fas fa-arrow-left"></i>
                    <span>Back to Course</span>
                </a>
                <button onclick="window.print()" class="btn-action outline">
                    <i class="fas fa-print"></i>
                    <span>Print Results</span>
                </button>
                <button onclick="shareResults()" class="btn-action primary">
                    <i class="fas fa-share"></i>
                    <span>Share Achievement</span>
                </button>
            </div>
        </div>

        <!-- Analysis Link for Teachers -->
        @if (auth()->user()->isTeacher() ?? false)
            <div class="teacher-analysis">
                <a href="{{ route('quizzes.analysis', [$course->id, $quiz->id, $attempt->id]) }}"
                    class="btn-action analysis">
                    <i class="fas fa-chart-bar"></i>
                    <span>View Detailed Analysis</span>
                </a>
            </div>
        @endif
    </div>

    @push('styles')
        <style>
            .results-container {
                max-width: 1000px;
                margin: 0 auto;
                padding: 20px;
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            }

            /* Results Header */
            .results-header {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                border-radius: 20px;
                padding: 40px;
                margin-bottom: 30px;
                color: white;
                text-align: center;
                position: relative;
                overflow: hidden;
            }

            .results-header::before {
                content: '';
                position: absolute;
                top: -50%;
                left: -50%;
                width: 200%;
                height: 200%;
                background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Ccircle cx='30' cy='30' r='2'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E") repeat;
                animation: float 20s linear infinite;
            }

            @keyframes float {
                0% {
                    transform: translate(-50%, -50%) rotate(0deg);
                }

                100% {
                    transform: translate(-50%, -50%) rotate(360deg);
                }
            }

            .results-celebration {
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 25px;
                margin-bottom: 20px;
                position: relative;
                z-index: 1;
            }

            .celebration-icon {
                width: 80px;
                height: 80px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                animation: pulse 2s infinite;
            }

            .celebration-icon.outstanding {
                background: linear-gradient(135deg, #f1c40f, #f39c12);
            }

            .celebration-icon.excellent {
                background: linear-gradient(135deg, #3498db, #2980b9);
            }

            .celebration-icon.good {
                background: linear-gradient(135deg, #27ae60, #2ecc71);
            }

            .celebration-icon.fair {
                background: linear-gradient(135deg, #e67e22, #d35400);
            }

            .celebration-icon.needs-improvement,
            .celebration-icon.poor {
                background: linear-gradient(135deg, #e74c3c, #c0392b);
            }

            .celebration-text {
                text-align: left;
            }

            .celebration-title {
                font-size: 32px;
                font-weight: 700;
                margin: 0;
                text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            }

            .celebration-subtitle {
                font-size: 16px;
                margin: 8px 0 0 0;
                opacity: 0.9;
            }

            .quiz-info {
                position: relative;
                z-index: 1;
            }

            .quiz-title {
                font-size: 24px;
                font-weight: 600;
                margin: 0 0 10px 0;
            }

            .completion-time {
                font-size: 14px;
                opacity: 0.8;
                margin: 0 0 8px 0;
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 8px;
            }

            .scoring-method {
                font-size: 12px;
                opacity: 0.7;
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 6px;
                font-style: italic;
            }

            /* Score Dashboard */
            .score-dashboard {
                display: grid;
                grid-template-columns: 1fr 2fr;
                gap: 30px;
                margin-bottom: 40px;
            }

            .score-card {
                background: white;
                border-radius: 20px;
                padding: 30px;
                box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
                text-align: center;
            }

            .score-circle {
                position: relative;
                display: inline-block;
                margin-bottom: 20px;
            }

            .score-ring {
                width: 120px;
                height: 120px;
                transform: rotate(-90deg);
            }

            .score-ring-background {
                fill: none;
                stroke: #f1f3f4;
                stroke-width: 8;
            }

            .score-ring-progress {
                fill: none;
                stroke: url(#scoreGradient);
                stroke-width: 8;
                stroke-linecap: round;
                stroke-dasharray: 314;
                stroke-dashoffset: calc(314 - (314 * var(--percentage) / 100));
                transition: stroke-dashoffset 2s ease-in-out;
            }

            .score-content {
                position: absolute;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                text-align: center;
            }

            .score-number {
                font-size: 24px;
                font-weight: 700;
                color: #2c3e50;
                line-height: 1;
            }

            .score-label {
                font-size: 12px;
                color: #7f8c8d;
                font-weight: 500;
            }

            .score-details {
                color: #7f8c8d;
                font-size: 16px;
                font-weight: 500;
            }

            .similarity-info {
                font-size: 12px;
                margin-top: 5px;
                opacity: 0.8;
            }

            .stats-grid {
                display: grid;
                grid-template-columns: repeat(2, 1fr);
                gap: 20px;
            }

            .stat-card {
                background: white;
                border-radius: 16px;
                padding: 25px;
                box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
                display: flex;
                align-items: center;
                gap: 15px;
                transition: transform 0.2s ease;
            }

            .stat-card:hover {
                transform: translateY(-2px);
            }

            .stat-icon {
                width: 50px;
                height: 50px;
                border-radius: 12px;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 18px;
                color: white;
            }

            .stat-icon.excellent {
                background: linear-gradient(135deg, #f1c40f, #f39c12);
            }

            .stat-icon.good {
                background: linear-gradient(135deg, #27ae60, #2ecc71);
            }

            .stat-icon.fair {
                background: linear-gradient(135deg, #3498db, #2980b9);
            }

            .stat-icon.needs-work {
                background: linear-gradient(135deg, #e74c3c, #c0392b);
            }

            .stat-icon.correct {
                background: linear-gradient(135deg, #27ae60, #2ecc71);
            }

            .stat-icon.incorrect {
                background: linear-gradient(135deg, #e74c3c, #c0392b);
            }

            .stat-icon.total {
                background: linear-gradient(135deg, #3498db, #2980b9);
            }

            .stat-icon.grade {
                background: linear-gradient(135deg, #9b59b6, #8e44ad);
            }

            .stat-number {
                font-size: 24px;
                font-weight: 700;
                color: #2c3e50;
                line-height: 1;
            }

            .stat-label {
                font-size: 14px;
                color: #7f8c8d;
                font-weight: 500;
            }

            /* Questions Review */
            .questions-review {
                margin-bottom: 40px;
            }

            .review-header {
                text-align: center;
                margin-bottom: 30px;
            }

            .review-title {
                font-size: 28px;
                font-weight: 700;
                color: #2c3e50;
                margin: 0 0 10px 0;
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 12px;
            }

            .review-subtitle {
                color: #7f8c8d;
                font-size: 16px;
                margin: 0;
            }

            .questions-list {
                display: flex;
                flex-direction: column;
                gap: 20px;
            }

            .question-review-card {
                background: white;
                border-radius: 16px;
                overflow: hidden;
                box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
                border-left: 5px solid;
                transition: transform 0.2s ease;
            }

            .question-review-card:hover {
                transform: translateY(-2px);
                box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
            }

            .question-review-card.correct {
                border-left-color: #27ae60;
            }

            .question-review-card.incorrect {
                border-left-color: #e74c3c;
            }

            .question-review-card.similarity-scoring {
                border-left-color: #3498db;
            }

            .question-header {
                display: flex;
                align-items: center;
                gap: 20px;
                padding: 25px 30px 20px;
                background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            }

            .question-number {
                position: relative;
                width: 60px;
                height: 60px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                font-weight: 700;
                font-size: 18px;
                color: white;
            }

            .question-number.correct {
                background: linear-gradient(135deg, #27ae60, #2ecc71);
            }

            .question-number.incorrect {
                background: linear-gradient(135deg, #e74c3c, #c0392b);
            }

            .question-number.similarity {
                background: linear-gradient(135deg, #3498db, #2980b9);
            }

            .similarity-badge {
                position: absolute;
                bottom: -8px;
                right: -8px;
                background: white;
                color: #3498db;
                font-size: 10px;
                font-weight: 600;
                padding: 2px 6px;
                border-radius: 10px;
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
            }

            .status-icon {
                position: absolute;
                bottom: -5px;
                right: -5px;
                width: 24px;
                height: 24px;
                background: white;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 10px;
                color: inherit;
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
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

            .question-meta {
                margin-top: 8px;
                display: flex;
                gap: 12px;
                flex-wrap: wrap;
            }

            .points-earned {
                display: inline-flex;
                align-items: center;
                gap: 6px;
                font-size: 14px;
                font-weight: 500;
                padding: 4px 12px;
                border-radius: 20px;
            }

            .points-earned.similarity-points {
                background: rgba(52, 152, 219, 0.1);
                color: #2980b9;
            }

            .points-earned.full-points {
                background: rgba(39, 174, 96, 0.1);
                color: #27ae60;
            }

            .points-earned.no-points {
                background: rgba(231, 76, 60, 0.1);
                color: #e74c3c;
            }

            .similarity-category {
                font-size: 12px;
                font-weight: 500;
                padding: 4px 10px;
                border-radius: 15px;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }

            .similarity-category.excellent {
                background: rgba(241, 196, 15, 0.1);
                color: #f39c12;
            }

            .similarity-category.good {
                background: rgba(39, 174, 96, 0.1);
                color: #27ae60;
            }

            .similarity-category.fair {
                background: rgba(52, 152, 219, 0.1);
                color: #2980b9;
            }

            .similarity-category.poor,
            .similarity-category.very-poor {
                background: rgba(231, 76, 60, 0.1);
                color: #e74c3c;
            }

            .question-content {
                padding: 20px 30px 30px;
            }

            .question-text {
                margin-bottom: 25px;
            }

            .question-text p {
                font-size: 16px;
                line-height: 1.6;
                color: #2c3e50;
                margin: 0;
            }

            .answer-review {
                display: flex;
                flex-direction: column;
                gap: 20px;
                margin-bottom: 20px;
            }

            .user-answer,
            .correct-answer-section {
                border-radius: 12px;
                padding: 20px;
            }

            .user-answer {
                background: rgba(52, 152, 219, 0.05);
                border: 2px solid rgba(52, 152, 219, 0.1);
            }

            .correct-answer-section {
                background: rgba(39, 174, 96, 0.05);
                border: 2px solid rgba(39, 174, 96, 0.1);
            }

            .answer-label {
                display: flex;
                align-items: center;
                gap: 8px;
                font-weight: 600;
                color: #2c3e50;
                margin-bottom: 10px;
                font-size: 14px;
            }

            .answer-content {
                font-size: 15px;
                line-height: 1.5;
                padding: 12px 16px;
                border-radius: 8px;
                font-weight: 500;
                background: white;
                border: 1px solid #e9ecef;
            }

            .user-response {
                color: #2c3e50;
            }

            .correct-reference {
                background: rgba(39, 174, 96, 0.1);
                color: #1e8449;
                border-color: rgba(39, 174, 96, 0.2);
            }

            /* Similarity Analysis */
            .similarity-analysis {
                background: rgba(52, 152, 219, 0.05);
                border: 1px solid rgba(52, 152, 219, 0.1);
                border-radius: 12px;
                padding: 20px;
                margin-top: 15px;
            }

            .similarity-bar-container {
                margin-bottom: 15px;
            }

            .similarity-label {
                font-size: 14px;
                font-weight: 600;
                color: #2c3e50;
                margin-bottom: 8px;
            }

            .similarity-bar {
                position: relative;
                height: 20px;
                background: #ecf0f1;
                border-radius: 10px;
                overflow: hidden;
            }

            .similarity-fill {
                height: 100%;
                background: linear-gradient(90deg, #e74c3c 0%, #f39c12 50%, #27ae60 100%);
                transition: width 1.5s ease-in-out;
                border-radius: 10px;
            }

            .similarity-text {
                position: absolute;
                top: 50%;
                right: 10px;
                transform: translateY(-50%);
                font-size: 12px;
                font-weight: 600;
                color: #2c3e50;
            }

            .feedback-section {
                display: flex;
                align-items: center;
                gap: 10px;
                padding: 12px 16px;
                border-radius: 8px;
                font-size: 14px;
                font-weight: 500;
            }

            .feedback-section.positive {
                background: rgba(39, 174, 96, 0.1);
                color: #27ae60;
            }

            .feedback-section.negative {
                background: rgba(52, 152, 219, 0.1);
                color: #2980b9;
            }

            .feedback-section.similarity-feedback {
                background: rgba(52, 152, 219, 0.08);
                color: #2c3e50;
                border: 1px solid rgba(52, 152, 219, 0.15);
            }

            /* Teacher Analysis */
            .teacher-analysis {
                text-align: center;
                margin-bottom: 30px;
            }

            /* Action Buttons */
            .results-actions {
                display: flex;
                justify-content: center;
                gap: 20px;
                flex-wrap: wrap;
                padding-top: 30px;
            }

            .btn-action {
                display: flex;
                align-items: center;
                gap: 10px;
                padding: 15px 25px;
                border-radius: 12px;
                font-size: 16px;
                font-weight: 600;
                text-decoration: none;
                border: 2px solid;
                cursor: pointer;
                transition: all 0.3s ease;
            }

            .btn-action.primary {
                background: linear-gradient(135deg, #3498db, #2980b9);
                border-color: #3498db;
                color: white;
            }

            .btn-action.primary:hover {
                transform: translateY(-2px);
                box-shadow: 0 8px 25px rgba(52, 152, 219, 0.3);
            }

            .btn-action.secondary {
                background: linear-gradient(135deg, #95a5a6, #7f8c8d);
                border-color: #95a5a6;
                color: white;
            }

            .btn-action.secondary:hover {
                transform: translateY(-2px);
                box-shadow: 0 8px 25px rgba(149, 165, 166, 0.3);
            }

            .btn-action.outline {
                background: transparent;
                border-color: #bdc3c7;
                color: #2c3e50;
            }

            .btn-action.outline:hover {
                background: #f8f9fa;
                border-color: #95a5a6;
            }

            .btn-action.analysis {
                background: linear-gradient(135deg, #9b59b6, #8e44ad);
                border-color: #9b59b6;
                color: white;
            }

            .btn-action.analysis:hover {
                transform: translateY(-2px);
                box-shadow: 0 8px 25px rgba(155, 89, 182, 0.3);
            }

            /* Responsive Design */
            @media (max-width: 768px) {
                .results-container {
                    padding: 15px;
                }

                .results-header {
                    padding: 30px 20px;
                }

                .results-celebration {
                    flex-direction: column;
                    text-align: center;
                    gap: 20px;
                }

                .celebration-text {
                    text-align: center;
                }

                .celebration-title {
                    font-size: 24px;
                }

                .score-dashboard {
                    grid-template-columns: 1fr;
                    gap: 20px;
                }

                .stats-grid {
                    grid-template-columns: 1fr;
                }

                .question-header {
                    flex-direction: column;
                    text-align: center;
                    gap: 15px;
                }

                .question-content {
                    padding: 20px;
                }

                .results-actions {
                    flex-direction: column;
                    align-items: center;
                }

                .btn-action {
                    width: 100%;
                    max-width: 300px;
                    justify-content: center;
                }

                .question-meta {
                    justify-content: center;
                }
            }

            /* Print Styles */
            @media print {

                .results-actions,
                .teacher-analysis {
                    display: none;
                }

                .results-header::before {
                    display: none;
                }

                .question-review-card {
                    break-inside: avoid;
                    margin-bottom: 20px;
                }

                .similarity-analysis {
                    break-inside: avoid;
                }
            }

            /* Animation for pulse effect */
            @keyframes pulse {
                0% {
                    transform: scale(1);
                }

                50% {
                    transform: scale(1.05);
                }

                100% {
                    transform: scale(1);
                }
            }
        </style>

        <!-- SVG Gradients -->
        <svg width="0" height="0" style="position: absolute;">
            <defs>
                <linearGradient id="scoreGradient" x1="0%" y1="0%" x2="100%" y2="0%">
                    @if ($scorePercentage >= 90)
                        <stop offset="0%" style="stop-color:#f1c40f;stop-opacity:1" />
                        <stop offset="100%" style="stop-color:#f39c12;stop-opacity:1" />
                    @elseif($scorePercentage >= 80)
                        <stop offset="0%" style="stop-color:#3498db;stop-opacity:1" />
                        <stop offset="100%" style="stop-color:#2980b9;stop-opacity:1" />
                    @elseif($scorePercentage >= 70)
                        <stop offset="0%" style="stop-color:#27ae60;stop-opacity:1" />
                        <stop offset="100%" style="stop-color:#2ecc71;stop-opacity:1" />
                    @elseif($scorePercentage >= 60)
                        <stop offset="0%" style="stop-color:#e67e22;stop-opacity:1" />
                        <stop offset="100%" style="stop-color:#d35400;stop-opacity:1" />
                    @else
                        <stop offset="0%" style="stop-color:#e74c3c;stop-opacity:1" />
                        <stop offset="100%" style="stop-color:#c0392b;stop-opacity:1" />
                    @endif
                </linearGradient>
            </defs>
        </svg>
    @endpush

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Animate score ring on load
                setTimeout(() => {
                    const ring = document.querySelector('.score-ring-progress');
                    if (ring) {
                        ring.style.strokeDashoffset = `calc(314 - (314 * {{ $scorePercentage }} / 100))`;
                    }
                }, 500);

                // Animate similarity bars
                setTimeout(() => {
                    document.querySelectorAll('.similarity-fill').forEach(bar => {
                        const width = bar.style.width;
                        bar.style.width = '0%';
                        setTimeout(() => {
                            bar.style.width = width;
                        }, 100);
                    });
                }, 1000);

                // Animate stats on scroll
                const observerOptions = {
                    threshold: 0.1,
                    rootMargin: '0px 0px -50px 0px'
                };

                const observer = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            entry.target.style.animation = 'slideInUp 0.6s ease forwards';
                        }
                    });
                }, observerOptions);

                document.querySelectorAll('.question-review-card').forEach(card => {
                    observer.observe(card);
                });
            });

            function shareResults() {
                const score = {{ round($scorePercentage) }};
                const quizTitle = "{{ $quiz->title }}";
                const usesAI = {{ $usesCosineSimilarity ? 'true' : 'false' }};
                const aiText = usesAI ? ' using AI-powered similarity analysis' : '';
                const text = `I just completed "${quizTitle}" and scored ${score}%${aiText}! 🎉`;

                if (navigator.share) {
                    navigator.share({
                        title: 'Quiz Results',
                        text: text,
                        url: window.location.href
                    });
                } else {
                    // Fallback - copy to clipboard
                    navigator.clipboard.writeText(text + ' ' + window.location.href).then(() => {
                        alert('Results copied to clipboard!');
                    });
                }
            }
        </script>

        <style>
            @keyframes slideInUp {
                from {
                    opacity: 0;
                    transform: translateY(30px);
                }

                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
        </style>
    @endpush
@endsection
