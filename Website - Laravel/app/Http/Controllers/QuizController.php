<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Models\Course;
use Illuminate\Http\Request;
use App\Services\QuestionGeneratorService;
use Illuminate\Support\Facades\Log;

class QuizController extends Controller
{
    protected $questionService;

    public function __construct(QuestionGeneratorService $questionService)
    {
        $this->questionService = $questionService;
    }

    public function index(Course $course)
    {
        $quizzes = $course->quizzes()
            ->withCount('questions')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('quizzes.index', compact('course', 'quizzes'));
    }

    public function create(Course $course)
    {
        $serviceHealth = $this->questionService->healthCheck();
        $availableModels = $this->questionService->getAvailableModels();

        return view('quizzes.create', [
            'course' => $course,
            'serviceHealth' => $serviceHealth,
            'availableModels' => $availableModels
        ]);
    }

    public function store(Request $request, Course $course)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date|after:now',
            'time_limit' => 'nullable|integer|min:1',
            'is_active' => 'boolean',
            'questions' => 'required|array|min:1',
            'questions.*.text' => 'required|string',
            'questions.*.answer' => 'required|string',
            'questions.*.points' => 'required|integer|min:1|max:100',
            'questions.*.is_ai_generated' => 'sometimes|boolean'
        ]);

        $quiz = Quiz::create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'due_date' => $validated['due_date'] ?? null,
            'time_limit' => $validated['time_limit'] ?? null,
            'is_published' => $validated['is_active'] ?? false,
            'course_id' => $course->id,
            'teacher_id' => auth()->id(),
            'total_points' => 0,
        ]);

        $totalPoints = 0;
        $order = 1;

        foreach ($validated['questions'] as $questionData) {
            QuizQuestion::create([
                'quiz_id' => $quiz->id,
                'question' => $questionData['text'],
                'answer' => $questionData['answer'],
                'points' => $questionData['points'],
                'order' => $order++,
                'explanation' => null,
                'is_ai_generated' => $questionData['is_ai_generated'] ?? false,
            ]);

            $totalPoints += $questionData['points'];
        }

        $quiz->update(['total_points' => $totalPoints]);

        return redirect()->route('courses.show', $course->id)
            ->with('success', 'Quiz created successfully!');
    }

    public function show(Course $course, Quiz $quiz)
    {
        if ($quiz->course_id !== $course->id) {
            abort(404, 'Quiz not found in this course.');
        }

        $quiz->load(['questions' => function ($query) {
            $query->orderBy('order', 'asc');
        }, 'teacher']);
        $user = auth()->user();

        if ($user->isTeacher()) {
            return view('quizzes.show-teacher', compact('course', 'quiz', 'user'));
        }

        return view('quizzes.show-student', compact('course', 'quiz', 'user'));
    }

    public function edit(Course $course, Quiz $quiz)
    {
        $quiz->load(['questions' => function ($query) {
            $query->orderBy('order', 'asc');
        }]);

        $serviceHealth = $this->questionService->healthCheck();
        $availableModels = $this->questionService->getAvailableModels();

        return view('quizzes.edit', compact('course', 'quiz', 'serviceHealth', 'availableModels'));
    }

    public function update(Request $request, Course $course, Quiz $quiz)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date|after:now', // Tambahkan after:now seperti di store
            'time_limit' => 'nullable|integer|min:1',
            'is_active' => 'boolean',
            'questions' => 'required|array|min:1',
            'questions.*.id' => 'nullable|integer', // Sederhanakan validasi ID
            'questions.*.text' => 'required|string',
            'questions.*.answer' => 'required|string',
            'questions.*.points' => 'required|integer|min:1|max:100',
            'questions.*.is_ai_generated' => 'sometimes|boolean'
        ]);

        // Update quiz data
        $quiz->update([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'due_date' => $validated['due_date'] ?? null,
            'time_limit' => $validated['time_limit'] ?? null,
            'is_published' => $validated['is_active'] ?? false,
        ]);

        $totalPoints = 0;
        $existingQuestionIds = $quiz->questions->pluck('id')->toArray();
        $updatedQuestionIds = [];
        $order = 1;

        foreach ($validated['questions'] as $questionData) {
            if (isset($questionData['id']) && !empty($questionData['id'])) {
                // Update existing question
                $question = QuizQuestion::where('id', $questionData['id'])
                    ->where('quiz_id', $quiz->id)
                    ->first();

                if ($question) {
                    $question->update([
                        'question' => $questionData['text'],
                        'answer' => $questionData['answer'],
                        'points' => $questionData['points'],
                        'order' => $order,
                        'is_ai_generated' => $questionData['is_ai_generated'] ?? false,
                    ]);
                    $updatedQuestionIds[] = $question->id;
                }
            } else {
                // Create new question
                $question = QuizQuestion::create([
                    'quiz_id' => $quiz->id,
                    'question' => $questionData['text'],
                    'answer' => $questionData['answer'],
                    'points' => $questionData['points'],
                    'order' => $order,
                    'explanation' => null,
                    'is_ai_generated' => $questionData['is_ai_generated'] ?? false,
                ]);
                $updatedQuestionIds[] = $question->id;
            }

            $totalPoints += $questionData['points'];
            $order++;
        }

        // Delete questions that are no longer present
        $questionsToDelete = array_diff($existingQuestionIds, $updatedQuestionIds);
        if (!empty($questionsToDelete)) {
            QuizQuestion::whereIn('id', $questionsToDelete)->delete();
        }

        // Update total points
        $quiz->update(['total_points' => $totalPoints]);

        return redirect()->route('quizzes.show', [$course->id, $quiz->id])
            ->with('success', 'Quiz updated successfully!');
    }


    public function destroy(Course $course, Quiz $quiz)
    {
        $quiz->delete();

        return redirect()->route('courses.show', $course->id)
            ->with('success', 'Quiz deleted successfully!');
    }

    /**
     * Handle AI question generation using single model
     */
    public function generateQuestions(Request $request, Course $course)
    {
        $request->validate([
            'text' => 'required|string|min:10|max:10000',
            'model' => 'nullable|string|in:bert2bert,pegasus,t5,gpt4o'
        ]);

        try {
            $result = $this->questionService->generateQuestions(
                $request->text,
                $request->input('model', 'bert2bert')
            );

            if (!$result['success']) {
                return back()->withErrors(['ai_generation' => $result['error'] ?? 'Failed to generate questions']);
            }

            return back()->with([
                'ai_generated' => true,
                'questions' => $result['qa_pairs'],
                'model_used' => $result['model_used'],
                'count' => $result['count'],
                'generation_type' => 'single'
            ]);
        } catch (\Exception $e) {
            Log::error('Question generation failed: ' . $e->getMessage());
            return back()->withErrors(['ai_generation' => 'Question generation failed']);
        }
    }

    /**
     * Handle AI question generation using weighted averaging
     */
    public function generateQuestionsWeightedAveraging(Request $request, Course $course)
    {
        $request->validate([
            'text' => 'required|string|min:10|max:10000'
        ]);

        try {
            $result = $this->questionService->generateQuestionsWeightedAveraging($request->text);

            if (!$result['success']) {
                // Return JSON error instead of redirect
                return response()->json([
                    'success' => false,
                    'message' => $result['error'] ?? 'Failed to generate questions using weighted averaging'
                ], 400);
            }

            // Extract pipeline statistics for better user feedback
            $pipelineStats = $result['pipeline_stats'] ?? [];
            $modelsUsed = $pipelineStats['models_used'] ?? [];
            $bertscoreResults = $pipelineStats['bertscore_results'] ?? 0;

            // Create method description with model information
            $methodDescription = 'Weighted Averaging';
            if (!empty($modelsUsed)) {
                $methodDescription .= ' (' . implode(', ', $modelsUsed) . ')';
            }

            // Return JSON response instead of redirect
            return response()->json([
                'success' => true,
                'qa_pairs' => $result['qa_pairs'], // This is what JavaScript expects
                'count' => $result['count'],
                'total_generated' => $result['total_generated'],
                'generation_type' => 'weighted_averaging',
                'method' => $methodDescription,
                'model_used' => $result['model_used'],
                'model_weights' => $result['model_weights'],
                'pipeline_info' => [
                    'models_used' => $modelsUsed,
                    'bertscore_evaluations' => $bertscoreResults,
                    'ranking_applied' => true
                ],
                'message' => "Questions generated using weighted averaging with " . count($modelsUsed) . " models and " . $bertscoreResults . " quality evaluations"
            ]);
        } catch (\Exception $e) {
            Log::error('Weighted averaging question generation failed: ' . $e->getMessage(), [
                'course_id' => $course->id,
                'text_length' => strlen($request->text),
                'error' => $e->getMessage()
            ]);

            // Return JSON error instead of redirect
            return response()->json([
                'success' => false,
                'message' => 'Weighted averaging question generation failed'
            ], 500);
        }
    }
}
