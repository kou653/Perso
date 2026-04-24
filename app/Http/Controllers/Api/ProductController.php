<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    public function index(): JsonResponse
    {
        $products = Product::query()
            ->where('is_active', true)
            ->with(['templates' => fn ($query) => $query->where('is_active', true)])
            ->orderBy('name')
            ->get();

        return response()->json([
            'data' => $products,
        ]);
    }

    public function show(Product $product): JsonResponse
    {
        $product->load(['templates' => fn ($query) => $query->where('is_active', true)]);

        return response()->json([
            'data' => $product,
        ]);
    }
}
