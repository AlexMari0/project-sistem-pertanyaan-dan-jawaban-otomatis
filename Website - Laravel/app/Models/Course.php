<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'description', 'join_password', 'teacher_id'];

    public function students()
    {
        return $this->belongsToMany(User::class, 'course_user', 'course_id', 'user_id')
            ->withPivot('last_accessed_at')
            ->withTimestamps();
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function quizzes()
    {
        return $this->hasMany(Quiz::class);
    }

    public function reading_materials()
    {
        return $this->hasMany(ReadingMaterial::class);
    }
}
