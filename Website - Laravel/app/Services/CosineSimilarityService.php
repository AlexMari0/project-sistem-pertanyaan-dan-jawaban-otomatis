<?php

namespace App\Services;

class CosineSimilarityService
{
    private $stopWords = [];

    public function __construct()
    {
        $this->loadStopWords();
    }

    /**
     * Load stop words from file
     */
    private function loadStopWords(): void
    {
        $stopWordsFile = storage_path('app/stopwords-id.txt');
        
        if (file_exists($stopWordsFile)) {
            $content = file_get_contents($stopWordsFile);
            $this->stopWords = array_filter(
                array_map('trim', explode("\n", $content)),
                function($word) {
                    return !empty($word);
                }
            );
        } else {
            // Fallback to basic Indonesian stopwords if file not found
            $this->stopWords = [
                'dan', 'atau', 'yang', 'di', 'ke', 'dari', 'untuk', 'pada', 
                'dengan', 'dalam', 'adalah', 'akan', 'dapat', 'tidak', 'ada',
                'ini', 'itu', 'juga', 'saya', 'kamu', 'dia', 'kita', 'mereka',
                'sudah', 'belum'
            ];
        }
    }

    /**
     * Calculate cosine similarity between two texts
     * 
     * @param string $text1 Reference answer (teacher's answer)
     * @param string $text2 Student's answer
     * @return float Similarity score between 0 and 1
     */
    public function calculateSimilarity(string $text1, string $text2): float
    {
        // Handle empty inputs
        if (empty(trim($text1)) || empty(trim($text2))) {
            return 0.0;
        }

        // Preprocess texts
        $text1 = $this->preprocessText($text1);
        $text2 = $this->preprocessText($text2);

        // Create word frequency vectors
        $vector1 = $this->createWordVector($text1);
        $vector2 = $this->createWordVector($text2);

        // Get all unique words from both texts
        $allWords = array_unique(array_merge(array_keys($vector1), array_keys($vector2)));

        // Create normalized vectors
        $normalizedVector1 = [];
        $normalizedVector2 = [];

        foreach ($allWords as $word) {
            $normalizedVector1[] = $vector1[$word] ?? 0;
            $normalizedVector2[] = $vector2[$word] ?? 0;
        }

        // Calculate cosine similarity
        return $this->cosineSimilarity($normalizedVector1, $normalizedVector2);
    }

    /**
     * Preprocess text: lowercase, remove punctuation, tokenize
     * 
     * @param string $text
     * @return array
     */
    private function preprocessText(string $text): array
    {
        // Convert to lowercase
        $text = strtolower($text);

        // Remove punctuation and special characters
        $text = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $text);

        // Split into words and remove empty strings
        $words = array_filter(explode(' ', $text), function ($word) {
            return !empty(trim($word)) && strlen(trim($word)) > 1;
        });

        // Remove stop words using loaded stopwords
        return array_filter($words, function ($word) {
            return !in_array(trim($word), $this->stopWords);
        });
    }

    /**
     * Create word frequency vector
     * 
     * @param array $words
     * @return array
     */
    private function createWordVector(array $words): array
    {
        $vector = [];
        foreach ($words as $word) {
            $vector[$word] = ($vector[$word] ?? 0) + 1;
        }
        return $vector;
    }

    /**
     * Calculate cosine similarity between two vectors
     * 
     * @param array $vector1
     * @param array $vector2
     * @return float
     */
    private function cosineSimilarity(array $vector1, array $vector2): float
    {
        if (count($vector1) !== count($vector2)) {
            return 0.0;
        }

        $dotProduct = 0;
        $magnitude1 = 0;
        $magnitude2 = 0;

        for ($i = 0; $i < count($vector1); $i++) {
            $dotProduct += $vector1[$i] * $vector2[$i];
            $magnitude1 += $vector1[$i] * $vector1[$i];
            $magnitude2 += $vector2[$i] * $vector2[$i];
        }

        $magnitude1 = sqrt($magnitude1);
        $magnitude2 = sqrt($magnitude2);

        if ($magnitude1 == 0 || $magnitude2 == 0) {
            return 0.0;
        }

        return $dotProduct / ($magnitude1 * $magnitude2);
    }

    /**
     * Calculate quiz score based on cosine similarity
     * Convert similarity (0-1) to score (1-100)
     * 
     * @param float $similarity Cosine similarity score (0-1)
     * @param int $maxPoints Maximum points for the question
     * @return int Score between 1 and maxPoints
     */
    public function calculateQuizScore(float $similarity, int $maxPoints): int
    {
        // Ensure similarity is between 0 and 1
        $similarity = max(0, min(1, $similarity));

        // Apply a more generous scoring curve
        // Using square root to make it easier to get higher scores
        $adjustedSimilarity = sqrt($similarity);

        // Convert to score range (1 to maxPoints)
        // Minimum score is 1 if there's any similarity > 0.1
        if ($similarity < 0.1) {
            return 1;
        }

        $score = 1 + ($adjustedSimilarity * ($maxPoints - 1));

        return (int) round($score);
    }

    /**
     * Get similarity category for better user feedback
     * 
     * @param float $similarity
     * @return array
     */
    public function getSimilarityCategory(float $similarity): array
    {
        if ($similarity >= 0.9) {
            return ['category' => 'Excellent', 'color' => 'green', 'description' => 'Jawaban sangat mirip dengan kunci jawaban'];
        } elseif ($similarity >= 0.7) {
            return ['category' => 'Good', 'color' => 'blue', 'description' => 'Jawaban cukup mirip dengan kunci jawaban'];
        } elseif ($similarity >= 0.5) {
            return ['category' => 'Fair', 'color' => 'yellow', 'description' => 'Jawaban memiliki kemiripan sedang dengan kunci jawaban'];
        } elseif ($similarity >= 0.2) {
            return ['category' => 'Poor', 'color' => 'orange', 'description' => 'Jawaban kurang mirip dengan kunci jawaban'];
        } else {
            return ['category' => 'Very Poor', 'color' => 'red', 'description' => 'Jawaban sangat berbeda dengan kunci jawaban'];
        }
    }

    /**
     * Get loaded stop words (for debugging)
     * 
     * @return array
     */
    public function getStopWords(): array
    {
        return $this->stopWords;
    }
}