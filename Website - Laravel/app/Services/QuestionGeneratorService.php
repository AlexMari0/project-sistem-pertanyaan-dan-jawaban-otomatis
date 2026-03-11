<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class QuestionGeneratorService
{
    private $baseUrl;
    private $timeout;
    private $apiPrefix;

    public function __construct()
    {
        $this->baseUrl = config('services.flask_api.url');
        $this->timeout = config('services.flask_api.timeout', 600);
        $this->apiPrefix = '/api/question-generator';
    }

    /**
     * Check if Flask API is healthy
     */
    public function healthCheck(): array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->get($this->baseUrl . $this->apiPrefix . '/health');

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'data' => $data,
                    'status' => $data['status'] ?? 'unknown',
                    'models_loaded' => $data['models_loaded'] ?? [],
                    'device' => $data['device'] ?? 'unknown'
                ];
            }

            return [
                'success' => false,
                'error' => 'Flask API not responding properly',
                'status_code' => $response->status()
            ];
        } catch (Exception $e) {
            Log::error('Flask API health check failed: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Generate QA pairs from text
     */
    public function generateQuestions(string $text, string $model = 'bert2bert'): array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->post($this->baseUrl . $this->apiPrefix . '/generate', [
                    'text' => $text,
                    'model' => $model
                ]);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'data' => $data,
                    'model_used' => $data['model_used'] ?? $model,
                    'qa_pairs' => $data['qa_pairs'] ?? [],
                    'count' => $data['count'] ?? 0,
                    'total_generated' => $data['total_generated'] ?? 0
                ];
            }

            $errorData = $response->json();
            return [
                'success' => false,
                'error' => $errorData['error'] ?? 'Unknown error',
                'status_code' => $response->status()
            ];
        } catch (Exception $e) {
            Log::error('Question generation failed: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Generate QA pairs using weighted averaging pipeline
     */
    public function generateQuestionsWeightedAveraging(string $text): array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->post($this->baseUrl . $this->apiPrefix . '/weighted-averaging', [
                    'text' => $text
                ]);

            if ($response->successful()) {
                $data = $response->json();

                return [
                    'success' => true,
                    'data' => $data,
                    'qa_pairs' => $data['qa_pairs'] ?? [],
                    'count' => $data['count'] ?? 0,
                    'total_generated' => $data['total_generated'] ?? 0,
                    'method' => 'weighted_averaging',
                    'model_used' => $data['model_used'] ?? 'weighted_averaging',
                    'model_weights' => $data['model_weights'] ?? [],
                    'pipeline_stats' => $data['pipeline_stats'] ?? [
                        'bertscore_results' => 0,
                        'models_used' => []
                    ]
                ];
            }

            $errorData = $response->json();
            return [
                'success' => false,
                'error' => $errorData['error'] ?? 'Unknown error',
                'status_code' => $response->status()
            ];
        } catch (Exception $e) {
            Log::error('Weighted averaging question generation failed: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get available models
     */
    public function getAvailableModels(): array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->get($this->baseUrl . $this->apiPrefix . '/models');

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'data' => $data,
                    'available_models' => $data['available_models'] ?? [],
                    'models' => $data['models'] ?? [],
                    'model_configs' => $data['model_configs'] ?? []
                ];
            }

            return [
                'success' => false,
                'error' => 'Failed to get models',
                'status_code' => $response->status()
            ];
        } catch (Exception $e) {
            Log::error('Get models failed: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get available model names only
     */
    public function getModelNames(): array
    {
        $result = $this->getAvailableModels();

        if ($result['success']) {
            return [
                'success' => true,
                'models' => $result['available_models'] ?? []
            ];
        }

        return $result;
    }

    /**
     * Check if a specific model is available
     */
    public function isModelAvailable(string $model): bool
    {
        $result = $this->getAvailableModels();

        if ($result['success']) {
            return in_array($model, $result['available_models'] ?? []);
        }

        return false;
    }

    /**
     * Get service status with detailed information
     */
    public function getServiceStatus(): array
    {
        $healthCheck = $this->healthCheck();
        $modelsCheck = $this->getAvailableModels();

        return [
            'service_healthy' => $healthCheck['success'],
            'models_available' => $modelsCheck['success'],
            'health_data' => $healthCheck,
            'models_data' => $modelsCheck,
            'timestamp' => now()->toISOString()
        ];
    }
}
