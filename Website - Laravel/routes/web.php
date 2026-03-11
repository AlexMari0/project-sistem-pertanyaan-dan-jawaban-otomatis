<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\ChatGPTGeneratorController;
use App\Http\Controllers\DeepseekGeneratorController;
use App\Http\Controllers\GeminiGeneratorController;
use App\Http\Controllers\QuizController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\QuizAttemptController;
use App\Http\Controllers\ReadingMaterialController;

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/

Route::get('/', [AuthController::class, 'showLoginForm'])->name('home');

Route::prefix('login')->group(function () {
    Route::get('/', [AuthController::class, 'showLoginForm'])->name('login.form');
    Route::post('/', [AuthController::class, 'login'])->name('login');
});

Route::prefix('register')->group(function () {
    Route::get('/', [AuthController::class, 'showRegisterForm'])->name('register.form');
    Route::post('/', [AuthController::class, 'register'])->name('register');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

/*
|--------------------------------------------------------------------------
| Dashboard Route
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->get('/dashboard', [DashboardController::class, 'dashboard'])->name('dashboard');

/*
|--------------------------------------------------------------------------
| Course Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->prefix('courses')->name('courses.')->group(function () {
    Route::get('/', [CourseController::class, 'index'])->name('index');
    Route::get('/create', [CourseController::class, 'create'])->name('create');
    Route::post('/', [CourseController::class, 'store'])->name('store');
    Route::get('/{id}', [CourseController::class, 'show'])->name('show');
    Route::delete('/{course}', [CourseController::class, 'destroy'])->name('destroy');

    // Join course routes
    Route::get('/{course}/join', [CourseController::class, 'showJoinForm'])->name('join.form');
    Route::post('/join', [CourseController::class, 'joinCourse'])->name('join');
});


/*
|--------------------------------------------------------------------------
| Quiz Routes (with Nested Course Prefix)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->prefix('courses/{course}')->group(function () {
    // Existing quiz routes
    Route::get('/quizzes', [QuizController::class, 'index'])->name('quizzes.index');
    Route::get('/quizzes/create', [QuizController::class, 'create'])->name('quizzes.create');
    Route::post('/quizzes', [QuizController::class, 'store'])->name('quizzes.store');
    Route::get('/quizzes/{quiz}', [QuizController::class, 'show'])->name('quizzes.show');
    Route::get('/quizzes/{quiz}/edit', [QuizController::class, 'edit'])->name('quizzes.edit');
    Route::put('/quizzes/{quiz}', [QuizController::class, 'update'])->name('quizzes.update');
    Route::delete('/quizzes/{quiz}', [QuizController::class, 'destroy'])->name('quizzes.destroy');

    // Quiz attempt routes
    Route::get('/quizzes/{quiz}/attempt', [QuizAttemptController::class, 'create'])->name('quiz.attempt.create');
    Route::post('/quizzes/{quiz}/attempt', [QuizAttemptController::class, 'store'])->name('quiz.attempt.store');
    Route::get('/quizzes/{quiz}/results', [QuizAttemptController::class, 'results'])->name('quizzes.results');

    // AI Question Generation Routes
    Route::prefix('/quizzes')->group(function () {
        Route::post('/generate-questions', [QuizController::class, 'generateQuestions'])
            ->name('quizzes.generate-questions');

        Route::post('/generate-chatgpt', [ChatGPTGeneratorController::class, 'generate_chatgpt'])
            ->name('quizzes.generate-chatgpt');

        Route::post('/generate-deepseek', [DeepseekGeneratorController::class, 'generate_deepseek'])
            ->name('quizzes.generate-deepseek');

        Route::post('/generate-gemini', [GeminiGeneratorController::class, 'generate_gemini'])
            ->name('quizzes.generate-gemini');

        Route::post('/generate-questions-weighted', [QuizController::class, 'generateQuestionsWeightedAveraging'])
            ->name('quizzes.generate-questions-weighted');
    });
});

/*
|--------------------------------------------------------------------------
| Reading Materials Routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->prefix('reading-materials')->name('reading-materials.')->group(function () {
    Route::post('/', [ReadingMaterialController::class, 'store'])->name('store');
    Route::delete('/{id}', [ReadingMaterialController::class, 'destroy'])->name('destroy');
    Route::get('/{id}/edit', [ReadingMaterialController::class, 'edit'])->name('edit');
    Route::get('/{material}', [ReadingMaterialController::class, 'show'])->name('show');
    Route::post('/{material}/mark-read', [ReadingMaterialController::class, 'markAsRead'])->name('mark-read');
    Route::put('/{material}', [ReadingMaterialController::class, 'update'])->name('update');
});

Route::delete('/reading-materials/{material}/attachments/{index}', [ReadingMaterialController::class, 'deleteAttachment'])
    ->name('reading-materials.delete-attachment')
    ->middleware(['auth', 'role:teacher']);
