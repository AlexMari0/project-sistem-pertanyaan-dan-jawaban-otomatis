<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\QuizQuestion;

class Quiz extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'due_date',
        'time_limit',
        'is_published',
        'course_id',
        'teacher_id',
        'total_points'
    ];

    protected $casts = [
        'due_date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }
    public function questions()
    {
        return $this->hasMany(QuizQuestion::class);
    }
    public function quizQuestions()
    {
        return $this->hasMany(QuizQuestion::class);
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id', 'user_id');
    }

    public function attempts()
    {
        return $this->hasMany(QuizAttempt::class);
    }
}
