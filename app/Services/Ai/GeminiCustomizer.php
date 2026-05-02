<?php

namespace App\Services\Ai;

use App\Models\ProductTemplate;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiCustomizer
{
    /**
     * Customize a template based on a user prompt.
     *
     * @param ProductTemplate $template
     * @param string $prompt
     * @param array $currentData
     * @return array
     * @throws \Exception
     */
    public function customize(ProductTemplate $template, string $prompt, array $currentData = []): array
    {
        $editableAreas = collect($template->editable_areas ?? [])
            ->map(fn($area) => [
                'key' => $area['key'] ?? 'unknown',
                'label' => $area['label'] ?? '',
                'type' => $area['type'] ?? 'text',
            ])
            ->values()
            ->all();

        $areasJson = json_encode($editableAreas);
        $currentJson = json_encode($currentData);

        $systemPrompt = <<<PROMPT
You are an expert product customization assistant. Update customization data based on user request.
PRODUCT: {$template->name}
EDITABLE AREAS (JSON): {$areasJson}
CURRENT VALUES (JSON): {$currentJson}
Return ONLY valid JSON with updated keys/values.
PROMPT;

        return $this->callGemini($systemPrompt . "\n\nUSER REQUEST: " . $prompt);
    }

    /**
     * Generate a new template structure based on a description.
     */
    public function generateTemplate(string $description): array
    {
        $systemPrompt = <<<PROMPT
You are a product template designer. Generate a JSON structure for a new product template.
REQUIRED FIELDS:
- name: string
- description: string
- layout: object (visual layout description)
- default_values: object (key => value)
- editable_areas: array of objects {key, label, type}
Return ONLY valid JSON.
PROMPT;

        return $this->callGemini($systemPrompt . "\n\nDESCRIPTION: " . $description);
    }

    /**
     * Call Gemini API with a prompt.
     */
    protected function callGemini(string $fullPrompt): array
    {
        $apiKey = config('services.gemini.key');
        $model = config('services.gemini.model');

        if (!$apiKey) {
            throw new \Exception('Gemini API key is not configured.');
        }

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}", [
                'contents' => [
                    ['parts' => [['text' => $fullPrompt]]]
                ],
                'generationConfig' => [
                    'response_mime_type' => 'application/json',
                ]
            ]);

            if ($response->failed()) {
                Log::error('Gemini API Error: ' . $response->body());
                throw new \Exception('Gemini API Error: ' . $response->status());
            }

            $result = $response->json();
            $jsonText = $result['candidates'][0]['content']['parts'][0]['text'] ?? '{}';
            
            return json_decode($jsonText, true) ?? [];
        } catch (\Exception $e) {
            Log::error('Gemini Customizer Exception: ' . $e->getMessage());
            throw $e;
        }
    }
}
