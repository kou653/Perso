<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CustomizationProject;
use App\Models\Product;
use App\Models\ProductTemplate;
use App\Services\Customization\TemplateRenderer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProjectController extends Controller
{
    public function __construct(
        protected TemplateRenderer $templateRenderer,
        protected \App\Services\Ai\GeminiCustomizer $geminiCustomizer,
        protected \App\Services\Customization\CustomizationValidator $customizationValidator,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $projects = $request->user()->customizationProjects()
            ->with(['product', 'template'])
            ->latest()
            ->get();

        return response()->json([
            'data' => $projects,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', Rule::exists('products', 'id')],
            'product_template_id' => ['required', 'integer', Rule::exists('product_templates', 'id')],
            'customer_name' => ['nullable', 'string', 'max:255'],
            'customer_email' => ['nullable', 'email', 'max:255'],
            'customization_data' => ['nullable', 'array'],
        ]);

        $product = Product::query()->findOrFail($validated['product_id']);

        $template = ProductTemplate::query()
            ->whereKey($validated['product_template_id'])
            ->where('product_id', $product->id)
            ->firstOrFail();

        $customizationData = $validated['customization_data'] ?? [];

        // Handle File Uploads
        foreach ($request->allFiles() as $key => $file) {
            $path = $file->store('customizations', 'public');
            $customizationData[$key] = \Illuminate\Support\Facades\Storage::url($path);
        }

        // Validate against template rules
        $this->customizationValidator->validate($template, $customizationData);

        $render = $this->templateRenderer->render(
            $template,
            $customizationData,
        );

        $project = CustomizationProject::query()->create([
            ...$validated,
            'user_id' => $request->user()->id,
            'customization_data' => $customizationData,
            'status' => 'configured',
            'latest_render' => $render,
        ]);

        $project->load(['product', 'template']);

        return response()->json([
            'data' => $project,
        ], 201);
    }

    public function show(Request $request, CustomizationProject $project): JsonResponse
    {
        abort_if($project->user_id !== $request->user()->id, 403);

        $project->load(['product', 'template']);

        return response()->json([
            'data' => $project,
        ]);
    }

    public function update(Request $request, CustomizationProject $project): JsonResponse
    {
        abort_if($project->user_id !== $request->user()->id, 403);

        $validated = $request->validate([
            'customer_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'customer_email' => ['sometimes', 'nullable', 'email', 'max:255'],
            'customization_data' => ['sometimes', 'array'],
        ]);

        $project->load('template');

        $customizationData = $validated['customization_data'] ?? ($project->customization_data ?? []);

        // Handle File Uploads
        foreach ($request->allFiles() as $key => $file) {
            $path = $file->store('customizations', 'public');
            $customizationData[$key] = \Illuminate\Support\Facades\Storage::url($path);
        }

        // Validate against template rules
        $this->customizationValidator->validate($project->template, $customizationData);

        $render = $this->templateRenderer->render($project->template, $customizationData);

        $project->update([
            ...$validated,
            'customization_data' => $customizationData,
            'status' => 'configured',
            'latest_render' => $render,
        ]);

        $project->load(['product', 'template']);

        return response()->json([
            'data' => $project,
        ]);
    }

    public function generateFromTemplate(Request $request, CustomizationProject $project): JsonResponse
    {
        abort_if($project->user_id !== $request->user()->id, 403);

        $validated = $request->validate([
            'customization_data' => ['nullable', 'array'],
        ]);

        $project->load('template');

        abort_if($project->template === null, 422, 'A product template is required to render a design.');

        $customizationData = $validated['customization_data'] ?? ($project->customization_data ?? []);
        $render = $this->templateRenderer->render($project->template, $customizationData);

        $project->update([
            'customization_data' => $customizationData,
            'status' => 'rendered',
            'latest_render' => $render,
        ]);

        $project->load(['product', 'template']);

        return response()->json([
            'data' => [
                'project' => $project,
                'render' => $render,
            ],
        ]);
    }

    public function aiCustomize(Request $request, CustomizationProject $project): JsonResponse
    {
        abort_if($project->user_id !== $request->user()->id, 403);

        $validated = $request->validate([
            'prompt' => ['required', 'string', 'max:1000'],
        ]);

        $project->load('template');

        abort_if($project->template === null, 422, 'A product template is required for AI customization.');

        try {
            $aiData = $this->geminiCustomizer->customize(
                $project->template,
                $validated['prompt'],
                $project->customization_data ?? []
            );

            $customizationData = array_merge($project->customization_data ?? [], $aiData);
            
            // Validate AI suggestions against template rules
            $this->customizationValidator->validate($project->template, $customizationData);

            $render = $this->templateRenderer->render($project->template, $customizationData);

            $project->update([
                'prompt' => $validated['prompt'],
                'customization_data' => $customizationData,
                'status' => 'ai_refined',
                'latest_render' => $render,
            ]);

            return response()->json([
                'data' => [
                    'project' => $project->load(['product', 'template']),
                    'ai_suggestions' => $aiData,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'AI Customization failed: ' . $e->getMessage(),
            ], 500);
        }
    }
}
