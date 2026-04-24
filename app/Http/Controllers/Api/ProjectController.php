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
    ) {
    }

    public function index(): JsonResponse
    {
        $projects = CustomizationProject::query()
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

        $render = $this->templateRenderer->render(
            $template,
            $validated['customization_data'] ?? [],
        );

        $project = CustomizationProject::query()->create([
            ...$validated,
            'status' => 'configured',
            'latest_render' => $render,
        ]);

        $project->load(['product', 'template']);

        return response()->json([
            'data' => $project,
        ], 201);
    }

    public function show(CustomizationProject $project): JsonResponse
    {
        $project->load(['product', 'template']);

        return response()->json([
            'data' => $project,
        ]);
    }

    public function update(Request $request, CustomizationProject $project): JsonResponse
    {
        $validated = $request->validate([
            'customer_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'customer_email' => ['sometimes', 'nullable', 'email', 'max:255'],
            'customization_data' => ['sometimes', 'array'],
        ]);

        $project->load('template');

        $customizationData = $validated['customization_data'] ?? ($project->customization_data ?? []);
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
}
