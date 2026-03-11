<?php

namespace App\Http\Controllers;

use App\Services\ChatGPTQAService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ChatGPTGeneratorController extends Controller
{
    protected ChatGPTQAService $qaService;

    public function __construct(ChatGPTQAService $qaService)
    {
        $this->qaService = $qaService;
    }

    /**
     * Generate questions using ChatGPT API
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function generate_chatgpt(Request $request)
    {
        try {
            // Validate the incoming request
            $request->validate([
                'text' => 'required|string|min:10|max:10000'
            ], [
                'text.required' => 'Text is required for question generation.',
                'text.min' => 'Text must be at least 10 characters long.',
                'text.max' => 'Text must not exceed 10,000 characters.'
            ]);

            $text = $request->input('text');

            // Log the generation attempt
            Log::info('ChatGPT question generation started', [
                'text_length' => strlen($text),
                'user_id' => auth()->id(),
                'timestamp' => now()
            ]);

            // Generate QA pairs using the service
            $qaPairs = $this->qaService->generateQAPairs($text);

            // Check if generation was successful
            if (empty($qaPairs)) {
                Log::warning('ChatGPT generated empty QA pairs', [
                    'text_preview' => substr($text, 0, 100),
                    'user_id' => auth()->id()
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Failed to generate questions from the provided text. Please try with different content or check if the text contains meaningful information.',
                    'qa_pairs' => []
                ], 422);
            }

            // Log successful generation
            Log::info('ChatGPT question generation completed successfully', [
                'generated_pairs' => count($qaPairs),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Questions generated successfully using ChatGPT AI.',
                'qa_pairs' => $qaPairs,
                'count' => count($qaPairs)
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Handle validation errors
            Log::warning('ChatGPT validation failed', [
                'errors' => $e->errors(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Validation failed: ' . implode(' ', $e->validator->errors()->all()),
                'qa_pairs' => []
            ], 422);
        } catch (\Exception $e) {
            // Handle any other exceptions
            Log::error('ChatGPT generation failed with exception', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred while generating questions. Please try again later.',
                'qa_pairs' => []
            ], 500);
        }
    }

    /**
     * Get ChatGPT service health status
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function chatgpt_health()
    {
        try {
            // You can add a simple health check here
            $apiKey = config('services.openai.api_key');

            if (empty($apiKey)) {
                return response()->json([
                    'success' => false,
                    'message' => 'OpenAI API key not configured.',
                    'status' => 'error'
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'ChatGPT service is available.',
                'status' => 'healthy'
            ]);
        } catch (\Exception $e) {
            Log::error('ChatGPT health check failed', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'ChatGPT service health check failed.',
                'status' => 'error'
            ]);
        }
    }
}