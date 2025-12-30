<?php

namespace App\Services;

use Gemini\Laravel\Facades\Gemini;
use Gemini\Data\GenerationConfig;
use Gemini\Enums\ResponseMimeType;
use Illuminate\Support\Facades\Log;

class BlogAIService
{
   public function generateBatchBlogs(string $topic): ?array
{
    try {
           $prompt = <<<PROMPT
            You are a professional security content strategist.
            Base Topic: {$topic}

            Generate 5 unique blog posts. For each post, assign it ONE relevant category name (e.g., 'Cybersecurity', 'Physical Security', 'Risk Management', 'Event Safety', or 'Corporate Governance').

            Return ONLY a JSON array of 5 objects with these keys:
            "title", "summary", "content", "conclusion", "category"
            PROMPT;

        $response = Gemini::generativeModel('gemini-2.5-flash')
            ->withGenerationConfig(new GenerationConfig(
                responseMimeType: ResponseMimeType::APPLICATION_JSON,
                maxOutputTokens: 8192,
            ))
            ->generateContent($prompt);

        return json_decode($response->text(), true);
    } catch (\Exception $e) {
        Log::error('Batch Blog AI Error: ' . $e->getMessage());
        return null;
    }
}
}