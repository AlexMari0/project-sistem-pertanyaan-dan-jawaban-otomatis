<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuizAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'quiz_id',
        'user_id',
        'score',
        'raw_score',
        'max_score',
        'answers',
        'completed_at',
        'started_at',
        'scoring_method',
    ];

    protected $casts = [
        'answers' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'score' => 'decimal:1',
        'raw_score' => 'decimal:2',
        'max_score' => 'decimal:2',
    ];

    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    /**
     * Get the percentage score
     */
    public function getPercentageAttribute()
    {
        return $this->score;
    }

    /**
     * Get the raw percentage (raw_score / max_score * 100)
     */
    public function getRawPercentageAttribute()
    {
        return $this->max_score > 0 ? round(($this->raw_score / $this->max_score) * 100, 1) : 0;
    }

    /**
     * Check if this attempt uses cosine similarity scoring
     */
    public function usesCosineSimilarity()
    {
        return $this->scoring_method === 'cosine_similarity';
    }

    /**
     * Get performance level based on score
     */
    public function getPerformanceLevelAttribute()
    {
        $score = $this->score;

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
     * Get attempt statistics
     */
    public function getStatisticsAttribute()
    {
        $answers = $this->answers ?? [];
        $totalQuestions = count($answers);

        if ($totalQuestions === 0) {
            return null;
        }

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
            'average_similarity' => round($totalSimilarity / $totalQuestions, 3),
            'category_distribution' => [
                'excellent' => $excellentCount,
                'good' => $goodCount,
                'fair' => $fairCount,
                'poor' => $poorCount,
                'very_poor' => $veryPoorCount
            ],
            'performance_level' => $this->performance_level
        ];
    }
}