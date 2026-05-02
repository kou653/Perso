<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\Order\OrderService;
use App\Services\Payment\StripeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(
        protected OrderService $orderService,
        protected StripeService $stripeService,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $orders = $request->user()->orders()
            ->with(['items.customizationProject.product'])
            ->latest()
            ->get();

        return response()->json(['data' => $orders]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'project_ids' => ['required', 'array'],
            'project_ids.*' => ['integer', \Illuminate\Validation\Rule::exists('customization_projects', 'id')],
            'shipping_address' => ['required', 'array'],
            'shipping_address.name' => ['required', 'string'],
            'shipping_address.street' => ['required', 'string'],
            'shipping_address.city' => ['required', 'string'],
            'shipping_address.zip' => ['required', 'string'],
            'shipping_address.country' => ['required', 'string'],
        ]);

        try {
            $order = $this->orderService->createFromProjects(
                $request->user(),
                $validated['project_ids'],
                $validated['shipping_address']
            );

            // Create Payment Intent
            $clientSecret = $this->stripeService->createPaymentIntent(
                (float)$order->total_amount,
                'eur',
                ['order_id' => $order->id]
            );

            $order->update(['payment_intent_id' => $this->extractIntentId($clientSecret)]);

            return response()->json([
                'data' => [
                    'order' => $order->load('items'),
                    'client_secret' => $clientSecret,
                ],
            ], 201);

        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function show(Request $request, Order $order): JsonResponse
    {
        abort_if($order->user_id !== $request->user()->id, 403);

        $order->load(['items.customizationProject.product']);

        return response()->json(['data' => $order]);
    }

    protected function extractIntentId(string $clientSecret): string
    {
        return explode('_secret_', $clientSecret)[0];
    }
}
