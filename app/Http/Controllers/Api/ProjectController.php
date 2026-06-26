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
            'ai_refinement_prompt' => ['nullable', 'string', 'max:1000'],
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

        // Apply AI refinement if prompt provided
        $aiSuggestions = null;
        $projectStatus = 'configured';
        
        if (!empty($validated['ai_refinement_prompt'])) {
            try {
                $aiSuggestions = $this->geminiCustomizer->customize(
                    $template,
                    $validated['ai_refinement_prompt'],
                    $customizationData
                );
                
                // Merge AI suggestions with user data
                $customizationData = array_merge($customizationData, $aiSuggestions);
                
                // Validate AI suggestions
                $this->customizationValidator->validate($template, $customizationData);
                
                $projectStatus = 'ai_refined';
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('AI Refinement Error: ' . $e->getMessage());
                // Continue without AI refinement if it fails
            }
        }

        $render = $this->templateRenderer->render(
            $template,
            $customizationData,
        );

        $project = CustomizationProject::query()->create([
            'product_id' => $validated['product_id'],
            'product_template_id' => $validated['product_template_id'],
            'customer_name' => $validated['customer_name'] ?? null,
            'customer_email' => $validated['customer_email'] ?? null,
            'user_id' => $request->user()->id,
            'customization_data' => $customizationData,
            'prompt' => $validated['ai_refinement_prompt'] ?? null,
            'status' => $projectStatus,
            'latest_render' => $render,
        ]);

        $project->load(['product', 'template']);

        return response()->json([
            'data' => $project,
            'ai_suggestions' => $aiSuggestions,
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
            'ai_refinement_prompt' => ['nullable', 'string', 'max:1000'],
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

        // Apply AI refinement if prompt provided
        $aiSuggestions = null;
        $projectStatus = $project->status; // Keep existing status if no AI refinement
        
        if (!empty($validated['ai_refinement_prompt'])) {
            try {
                $aiSuggestions = $this->geminiCustomizer->customize(
                    $project->template,
                    $validated['ai_refinement_prompt'],
                    $customizationData
                );
                
                // Merge AI suggestions with user data
                $customizationData = array_merge($customizationData, $aiSuggestions);
                
                // Validate AI suggestions
                $this->customizationValidator->validate($project->template, $customizationData);
                
                $projectStatus = 'ai_refined';
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('AI Refinement Error: ' . $e->getMessage());
                // Continue without AI refinement if it fails
            }
        }

        $render = $this->templateRenderer->render($project->template, $customizationData);

        $updateData = [
            'customization_data' => $customizationData,
            'status' => $projectStatus,
            'latest_render' => $render,
        ];
        
        // Only update customer info if provided
        if (isset($validated['customer_name'])) {
            $updateData['customer_name'] = $validated['customer_name'];
        }
        if (isset($validated['customer_email'])) {
            $updateData['customer_email'] = $validated['customer_email'];
        }
        if (!empty($validated['ai_refinement_prompt'])) {
            $updateData['prompt'] = $validated['ai_refinement_prompt'];
        }

        $project->update($updateData);

        $project->load(['product', 'template']);

        return response()->json([
            'data' => $project,
            'ai_suggestions' => $aiSuggestions,
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
