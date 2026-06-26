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
        $systemPrompt = <<<'PROMPT'
You are a product template designer for a customization platform.
Generate a JSON structure for a new product template based on user description.

RESPONSE FORMAT (return ONLY valid JSON):
{
  "name": "Template Name (concise, 2-4 words)",
  "description": "Brief description of what this template offers",
  "layout": {
    "primaryColor": "oklch(0.55 0.2 250)",
    "style": "modern|minimal|elegant|playful"
  },
  "default_values": {
    "key1": "default text",
    "key2": "https://via.placeholder.com/300",
    "key3": "#000000"
  },
  "editable_areas": [
    {
      "key": "field_name",
      "type": "text|image|color",
      "label": "Human readable label",
      "default_value": "optional default"
    }
  ]
}

REQUIREMENTS:
- Generate 2-4 editable areas based on description
- Types can be: "text" (for strings), "image" (for URLs), "color" (for hex colors)
- Provide sensible defaults for each field
- Make labels clear and concise (5-10 chars)
- Name should be catchy but professional

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
                $errorBody = $response->body();
                Log::error('Gemini API Error: ' . $errorBody);
                throw new \Exception('Gemini API Error: ' . $response->status() . ' - ' . $errorBody);
            }

            $result = $response->json();
            Log::debug('Gemini Response: ' . json_encode($result));
            
            // Check if response has the expected structure
            if (!isset($result['candidates']) || !isset($result['candidates'][0])) {
                Log::error('Gemini Response missing candidates: ' . json_encode($result));
                throw new \Exception('Gemini API returned unexpected structure');
            }

            $jsonText = $result['candidates'][0]['content']['parts'][0]['text'] ?? null;
            
            if (!$jsonText) {
                Log::error('Gemini Response missing text content: ' . json_encode($result));
                throw new \Exception('Gemini API returned empty text');
            }

            Log::debug('Gemini JSON Text: ' . $jsonText);
            
            $decoded = json_decode($jsonText, true);
            if ($decoded === null) {
                Log::error('Failed to decode JSON from Gemini: ' . $jsonText);
                throw new \Exception('Failed to decode JSON from Gemini API');
            }
            
            return $decoded;
        } catch (\Exception $e) {
            Log::error('Gemini Customizer Exception: ' . $e->getMessage());
            throw $e;
        }
    }
}
