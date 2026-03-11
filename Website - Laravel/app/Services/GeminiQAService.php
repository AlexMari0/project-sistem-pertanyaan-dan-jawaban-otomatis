<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiQAService
{
    protected string $apiKey;
    protected string $apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash-latest:generateContent';

    public function __construct()
    {
        $this->apiKey = config('services.gemini.api_key');
    }

    /**
     * Generate QA pairs from text using Gemini API
     */
    public function generateQAPairs(string $text): array
    {
        $prompt = $this->createQAPrompt($text);

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post($this->apiUrl . '?key=' . $this->apiKey, [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'temperature' => 0.7,
                    'topK' => 40,
                    'topP' => 0.95,
                    'maxOutputTokens' => 8192,
                ]
            ]);

            if ($response->successful()) {
                $responseData = $response->json();
                $qaResponse = $responseData['candidates'][0]['content']['parts'][0]['text'] ?? '';
                return $this->parseQAResponse($qaResponse);
            }

            Log::error('Gemini API request failed', [
                'status' => $response->status(),
                'response' => $response->body()
            ]);
        } catch (\Exception $e) {
            Log::error('Gemini API exception', ['error' => $e->getMessage()]);
        }

        return [];
    }

    /**
     * Create QA prompt from text
     */
    protected function createQAPrompt(string $text): string
    {
        return <<<PROMPT
Saya ingin kamu membantu membuat pasangan pertanyaan dan jawaban (QA pairs) dari teks berikut yang terdiri dari beberapa kalimat.

TEKS:
"""$text"""

Mari kita berpikir langkah demi langkah:

Langkah 1: Pisahkan setiap kalimat dan identifikasi informasi penting dalam masing-masing kalimat.
- Apa fakta utama, tokoh, tempat, waktu, atau konsep penting yang disebutkan?
- Apakah kalimat ini mengandung informasi sebab-akibat, definisi, data numerik, atau peristiwa?

Langkah 2: Tentukan jenis pertanyaan yang paling sesuai untuk masing-masing kalimat.
- Apakah ini cocok untuk pertanyaan: "Apa", "Siapa", "Kapan", "Di mana", "Mengapa", atau "Bagaimana"?

Langkah 3: Rumuskan pertanyaan yang spesifik dan relevan untuk setiap kalimat.
- Gunakan bagian dari konteks sebagai petunjuk dalam pertanyaan.
- Pastikan pertanyaan mudah dipahami dan tidak ambigu.

Langkah 4: Buat jawaban berdasarkan informasi dalam setiap kalimat.
- Jawaban harus jelas, ringkas, dan berdasarkan informasi yang tersedia.
- Hindari menambahkan informasi yang tidak ada dalam teks.

Format output HARUS seperti ini (satu blok per kalimat):
PERTANYAAN: [tuliskan pertanyaan 1]
JAWABAN: [tuliskan jawaban 1]

PERTANYAAN: [tuliskan pertanyaan 2]
JAWABAN: [tuliskan jawaban 2]

... dan seterusnya untuk setiap kalimat dalam teks di atas.
PROMPT;
    }

    /**
     * Parse QA pairs from API response
     */
    protected function parseQAResponse(string $qaResponse): array
    {
        $lines = explode("\n", trim($qaResponse));
        $qaPairs = [];
        $currentQ = '';
        $currentA = '';

        foreach ($lines as $line) {
            $line = trim($line);

            if (str_starts_with($line, 'PERTANYAAN:')) {
                if ($currentQ && $currentA) {
                    $qaPairs[] = [
                        'question' => $currentQ,
                        'answer' => $currentA
                    ];
                }
                $currentQ = str_replace('PERTANYAAN:', '', $line);
                $currentA = '';
            } elseif (str_starts_with($line, 'JAWABAN:')) {
                $currentA = str_replace('JAWABAN:', '', $line);
            }
        }

        // Add the last pair if exists
        if ($currentQ && $currentA) {
            $qaPairs[] = [
                'question' => $currentQ,
                'answer' => $currentA
            ];
        }

        if (empty($qaPairs)) {
            Log::error('Failed to parse QA response', ['response' => $qaResponse]);
        }

        return $qaPairs;
    }
}
