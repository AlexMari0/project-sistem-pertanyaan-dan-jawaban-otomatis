<?php

namespace App\Http\Controllers;

use App\Models\QuizAttempt;
use App\Models\Course;
use App\Models\Quiz;
use App\Services\CosineSimilarityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class QuizAttemptController extends Controller
{
    protected $similarityService;

    public function __construct(CosineSimilarityService $similarityService)
    {
        $this->similarityService = $similarityService;
    }

    public function create(Course $course, Quiz $quiz)
    {
        // Authorization checks
        if ($quiz->course_id !== $course->id) {
            abort(404);
        }

        // Check if already attempted
        if ($quiz->attempts()->where('user_id', auth()->id())->exists()) {
            return redirect()->route('quizzes.show', [$course->id, $quiz->id])
                ->with('error', 'You have already attempted this quiz.');
        }

        // Check due date
        if ($quiz->due_date && now()->gt($quiz->due_date)) {
            return back()->with('error', 'This quiz is past its due date.');
        }

        // Start timer for timed quizzes
        if ($quiz->time_limit) {
            session()->put('quiz_timer', [
                'start' => now()->timestamp,
                'end' => now()->addMinutes($quiz->time_limit)->timestamp,
                'quiz_id' => $quiz->id
            ]);
        }

        return view('quizzes.attempt', compact('course', 'quiz'));
    }

    public function store(Request $request, Course $course, Quiz $quiz)
    {
        // Validate submission
        $request->validate([
            'answers' => 'required|array',
            'answers.*' => 'required|string|max:2000'
        ]);

        // Check if already submitted
        if ($quiz->attempts()->where('user_id', auth()->id())->exists()) {
            return redirect()->route('quizzes.show', [$course->id, $quiz->id])
                ->with('error', 'You have already submitted this quiz.');
        }

        // Calculate score using cosine similarity
        $totalScore = 0;
        $maxPossibleScore = 0;
        $answers = [];

        foreach ($quiz->questions as $question) {
            $userAnswer = $request->answers[$question->id] ?? '';
            $referenceAnswer = $question->answer;
            $maxPoints = $question->points;

            // Calculate similarity
            $similarity = $this->similarityService->calculateSimilarity($referenceAnswer, $userAnswer);

            // Calculate score for this question
            $questionScore = $this->similarityService->calculateQuizScore($similarity, $maxPoints);

            // Get similarity category for feedback
            $similarityCategory = $this->similarityService->getSimilarityCategory($similarity);

            $totalScore += $questionScore;
            $maxPossibleScore += $maxPoints;

            $answers[$question->id] = [
                'question' => $question->question,
                'correct_answer' => $referenceAnswer,
                'user_answer' => $userAnswer,
                'similarity_score' => round($similarity, 3),
                'points_earned' => $questionScore,
                'max_points' => $maxPoints,
                'similarity_category' => $similarityCategory,
                'percentage' => round(($questionScore / $maxPoints) * 100, 1)
            ];

            // Log similarity calculation for debugging
            Log::info('Quiz similarity calculation', [
                'quiz_id' => $quiz->id,
                'question_id' => $question->id,
                'similarity' => $similarity,
                'score' => $questionScore,
                'max_points' => $maxPoints
            ]);
        }

        // Convert total score to percentage (1-100 scale)
        $finalScore = $maxPossibleScore > 0 ? round(($totalScore / $maxPossibleScore) * 100, 1) : 0;

        // Ensure final score is at least 1 if any answer was provided
        if ($finalScore < 1 && $totalScore > 0) {
            $finalScore = 1;
        }

        // Store attempt
        QuizAttempt::create([
            'quiz_id' => $quiz->id,
            'user_id' => auth()->id(),
            'score' => $finalScore,
            'raw_score' => $totalScore,
            'max_score' => $maxPossibleScore,
            'answers' => $answers,
            'completed_at' => now(),
            'scoring_method' => 'cosine_similarity'
        ]);

        // Clear timer
        session()->forget('quiz_timer');

        return redirect()->route('quizzes.results', [$course->id, $quiz->id])
            ->with('success', 'Quiz submitted successfully! Your score: ' . $finalScore . '%');
    }

    /**
     * Legacy method for backwards compatibility - now uses cosine similarity
     */
    protected function checkAnswer($question, $userAnswer)
    {
        $similarity = $this->similarityService->calculateSimilarity($question->answer, $userAnswer);

        // Consider answer "correct" if similarity is above 70%
        return $similarity >= 0.7;
    }

    public function results(Course $course, Quiz $quiz)
    {
        $attempt = $quiz->attempts()
            ->where('user_id', auth()->id())
            ->firstOrFail();

        // Calculate additional statistics
        $stats = $this->calculateAttemptStatistics($attempt);

        return view('quizzes.results', compact('course', 'quiz', 'attempt', 'stats'));
    }

    /**
     * Calculate detailed statistics for the quiz attempt
     */
    private function calculateAttemptStatistics($attempt): array
    {
        $answers = $attempt->answers;
        $totalQuestions = count($answers);

        $excellentCount = 0;
        $goodCount = 0;
        $fairCount = 0;
        $poorCount = 0;
        $veryPoorCount = 0;

        $totalSimilarity = 0;

        foreach ($answers as $answer) {
            $similarity = $answer['similarity_score'] ?? 0;
            $totalSimilarity += $similarity;

            if ($similarity >= 0.9) {
                $excellentCount++;
            } elseif ($similarity >= 0.7) {
                $goodCount++;
            } elseif ($similarity >= 0.5) {
                $fairCount++;
            } elseif ($similarity >= 0.2) {
                $poorCount++;
            } else {
                $veryPoorCount++;
            }
        }

        return [
            'total_questions' => $totalQuestions,
            'average_similarity' => $totalQuestions > 0 ? round($totalSimilarity / $totalQuestions, 3) : 0,
            'category_distribution' => [
                'excellent' => $excellentCount,
                'good' => $goodCount,
                'fair' => $fairCount,
                'poor' => $poorCount,
                'very_poor' => $veryPoorCount
            ],
            'performance_level' => $this->getPerformanceLevel($attempt->score)
        ];
    }

    /**
     * Get performance level based on final score
     */
    private function getPerformanceLevel($score): array
    {
        if ($score >= 90) {
            return ['level' => 'Outstanding', 'color' => 'green', 'icon' => '🌟'];
        } elseif ($score >= 80) {
            return ['level' => 'Excellent', 'color' => 'blue', 'icon' => '🎯'];
        } elseif ($score >= 70) {
            return ['level' => 'Good', 'color' => 'teal', 'icon' => '👍'];
        } elseif ($score >= 60) {
            return ['level' => 'Fair', 'color' => 'yellow', 'icon' => '📝'];
        } elseif ($score >= 50) {
            return ['level' => 'Needs Improvement', 'color' => 'orange', 'icon' => '📚'];
        } else {
            return ['level' => 'Poor', 'color' => 'red', 'icon' => '💪'];
        }
    }

    /**
     * Show detailed analysis for teachers
     */
    public function showAnalysis(Course $course, Quiz $quiz, QuizAttempt $attempt)
    {
        // Check if user is authorized to view this analysis
        if (!auth()->user()->isTeacher() && $attempt->user_id !== auth()->id()) {
            abort(403);
        }

        $detailedAnalysis = [];

        foreach ($attempt->answers as $questionId => $answer) {
            $question = $quiz->questions->find($questionId);

            if ($question) {
                $detailedAnalysis[] = [
                    'question' => $question,
                    'answer_data' => $answer,
                    'word_analysis' => $this->analyzeWords($answer['correct_answer'], $answer['user_answer'])
                ];
            }
        }

        return view('quizzes.analysis', compact('course', 'quiz', 'attempt', 'detailedAnalysis'));
    }

    /**
     * Analyze word-level similarities for detailed feedback
     */
    private function analyzeWords($referenceAnswer, $userAnswer): array
    {
        $referenceWords = str_word_count(strtolower($referenceAnswer), 1, 'àáäâèéëêìíïîòóöôùúüûñç');
        $userWords = str_word_count(strtolower($userAnswer), 1, 'àáäâèéëêìíïîòóöôùúüûñç');

        $commonWords = array_intersect($referenceWords, $userWords);
        $uniqueToReference = array_diff($referenceWords, $userWords);
        $uniqueToUser = array_diff($userWords, $referenceWords);

        return [
            'common_words' => array_values($commonWords),
            'missing_keywords' => array_values($uniqueToReference),
            'additional_words' => array_values($uniqueToUser),
            'word_overlap_ratio' => count($referenceWords) > 0 ? round(count($commonWords) / count($referenceWords), 3) : 0
        ];
    }
}
