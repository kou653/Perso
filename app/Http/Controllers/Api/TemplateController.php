<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProductTemplate;
use Illuminate\Http\JsonResponse;

class TemplateController extends Controller
{
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
}
