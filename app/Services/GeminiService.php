<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class GeminiService
{
    private $apiKey;
    private $baseUrl = 'https://generativelanguage.googleapis.com/v1/models/gemini-2.5-flash:generateContent';

    public function __construct()
    {
        $this->apiKey = config('services.gemini.api_key');
    }

    /**
     * Generate a suggestion for a wrong answer
     */
    public function generateWrongAnswerSuggestion($question, $wrongAnswer, $correctAnswer)
    {
        $fallback = "Take a moment to review the question: \"{$question}\". I advise you to study this specific topic more deeply to make sure you fully understand why \"{$correctAnswer}\" is the correct answer instead of \"{$wrongAnswer}\".";

        if (empty($this->apiKey)) {
            return $fallback;
        }

        try {
            $prompt = "A student answered a question incorrectly. 
            Question: \"{$question}\"
            Student's Wrong Answer: \"{$wrongAnswer}\"
            Correct Answer: \"{$correctAnswer}\"
            
            Provide a short, encouraging, and helpful suggestion (max 2 sentences) for the student. Explicitly advise them to study the specific topic or concept related to this question more deeply so they can fully understand it. Do not be mean. Focus on the learning aspect.";

            $response = Http::timeout(30)->post($this->baseUrl . '?key=' . $this->apiKey, [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'temperature' => 0.7,
                    'maxOutputTokens' => 1000
                ]
            ]);

            if ($response->successful()) {
                $responseData = $response->json();
                return $responseData['candidates'][0]['content']['parts'][0]['text'] ?? $fallback;
            } else {
                Log::error('Gemini API failed', ['status' => $response->status(), 'body' => $response->body()]);
            }
        } catch (Exception $e) {
            Log::error('Gemini suggestion error: ' . $e->getMessage());
        }

        return $fallback;
    }

    /**
     * Generate a behavioral summary
     */
    public function generateBehavioralSummary($performanceData)
    {
        if (empty($this->apiKey)) {
            return "The student shows a steady learning pattern based on their recent scores.";
        }

        try {
            $prompt = "Analyze this student performance data and provide a professional behavioral summary for a tutor.
            Data: " . json_encode($performanceData) . "
            
            The summary should be concise (3-4 sentences), focus on learning behaviors (e.g., consistency, improvement, common types of errors), and suggest a general direction for the tutor. Do not use markdown formatting in the response, just plain text.";

            $response = Http::timeout(30)->post($this->baseUrl . '?key=' . $this->apiKey, [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'temperature' => 0.7,
                    'maxOutputTokens' => 1000
                ]
            ]);

            if ($response->successful()) {
                $responseData = $response->json();
                return $responseData['candidates'][0]['content']['parts'][0]['text'] ?? "The student is making progress but could benefit from targeted practice in their weak areas.";
            }
        } catch (Exception $e) {
            Log::error('Gemini behavioral summary error: ' . $e->getMessage());
        }

        return "The student is making progress but could benefit from targeted practice in their weak areas.";
    }
}
