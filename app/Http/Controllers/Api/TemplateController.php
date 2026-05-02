<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProductTemplate;
use Illuminate\Http\JsonResponse;

class TemplateController extends Controller
{
    public function __construct(
        protected \App\Services\Ai\GeminiCustomizer $geminiCustomizer,
    ) {
    }

    public function index(): JsonResponse
    {
        $templates = ProductTemplate::query()
            ->where('is_active', true)
            ->with('product')
            ->orderBy('name')
            ->get();

        return response()->json([
            'data' => $templates,
        ]);
    }

    public function show(ProductTemplate $template): JsonResponse
    {
        $template->load('product');

        return response()->json([
            'data' => [
                ...$template->toArray(),
                'structure' => [
                    'layout' => $template->layout ?? [],
                    'default_values' => $template->default_values ?? [],
                    'editable_areas' => $template->editable_areas ?? [],
                ],
            ],
        ]);
    }

    public function generateFromAi(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'description' => ['required', 'string', 'max:1000'],
            'product_id' => ['required', 'integer', \Illuminate\Validation\Rule::exists('products', 'id')],
        ]);

        try {
            $templateData = $this->geminiCustomizer->generateTemplate($validated['description']);

            $template = ProductTemplate::query()->create([
                'product_id' => $validated['product_id'],
                'name' => $templateData['name'] ?? 'Nouveau Modèle IA',
                'slug' => \Illuminate\Support\Str::slug($templateData['name'] ?? 'ai-template-' . time()),
                'description' => $templateData['description'] ?? $validated['description'],
                'layout' => $templateData['layout'] ?? [],
                'default_values' => $templateData['default_values'] ?? [],
                'editable_areas' => $templateData['editable_areas'] ?? [],
                'is_active' => true,
            ]);

            return response()->json([
                'data' => $template,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Template generation failed: ' . $e->getMessage(),
            ], 500);
        }
    }
}
