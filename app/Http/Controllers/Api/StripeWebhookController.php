<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\Payment\StripeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class StripeWebhookController extends Controller
{
    public function __construct(
        protected StripeService $stripeService
    ) {
    }

    public function handle(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $webhookSecret = config('services.stripe.webhook_secret');

        try {
            $event = $this->stripeService->verifyWebhook($payload, $sigHeader, $webhookSecret);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Invalid payload'], 400);
        }

        switch ($event->type) {
            case 'payment_intent.succeeded':
                $paymentIntent = $event->data->object;
                $this->handlePaymentSucceeded($paymentIntent);
                break;
            
            case 'payment_intent.payment_failed':
                $paymentIntent = $event->data->object;
                $this->handlePaymentFailed($paymentIntent);
                break;
        }

        return response()->json(['status' => 'success']);
    }

    protected function handlePaymentSucceeded($paymentIntent)
    {
        $orderId = $paymentIntent->metadata->order_id ?? null;

        if ($orderId) {
            $order = Order::find($orderId);
            if ($order) {
                $order->update([
                    'status' => 'paid',
                    'payment_status' => 'paid',
                    'payment_method' => $paymentIntent->payment_method_types[0] ?? 'card',
                ]);
                Log::info("Order #{$orderId} paid successfully.");
            }
        }
    }

    protected function handlePaymentFailed($paymentIntent)
    {
        $orderId = $paymentIntent->metadata->order_id ?? null;

        if ($orderId) {
            $order = Order::find($orderId);
            if ($order) {
                $order->update([
                    'payment_status' => 'failed',
                ]);
                Log::warning("Payment failed for Order #{$orderId}.");
            }
        }
    }
}
