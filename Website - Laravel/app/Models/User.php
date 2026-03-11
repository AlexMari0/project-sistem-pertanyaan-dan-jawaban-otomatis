<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\Course;

class User extends Authenticatable
{
    use Notifiable;

    protected $primaryKey = 'user_id';

    protected $fillable = [
        'username',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
    ];

    public function jawaban()
    {
        return $this->hasMany(Jawaban::class, 'user_id', 'user_id');
    }


    public function courses()
    {
        return $this->belongsToMany(Course::class, 'course_user', 'user_id', 'course_id')
            ->withPivot('last_accessed_at')
            ->withTimestamps();
    }

    public function getAuthIdentifier()
    {
        return $this->user_id;
    }

    public function getAuthIdentifierName()
    {
        return 'user_id';
    }

    public function isTeacher()
    {
        return $this->role === 'teacher';
    }

    public function isStudent()
    {
        return $this->role === 'student';
    }

    public function quizAttempts()
    {
        return $this->hasMany(QuizAttempt::class);
    }

    public function readingProgress()
    {
        return $this->hasMany(ReadingProgress::class, 'user_id');
    }


    public function hasCompletedReading($materialId)
    {
        return $this->readingProgress()
            ->where('reading_material_id', $materialId)
            ->where('is_completed', true)
            ->exists();
    }
}
