<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReadingMaterial extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'title',
        'content',
        'reading_time',
        'attachments',
        'created_by'
    ];

    protected $casts = [
        'reading_time' => 'integer',
    ];

    /**
     * Get the course that owns the reading material.
     */
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Get the user who created this reading material.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'user_id');
    }

    /**
     * Get attachments as array.
     */
    public function getAttachmentsArrayAttribute()
    {
        return $this->attachments ? json_decode($this->attachments, true) : [];
    }
}