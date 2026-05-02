<?php

namespace App\Services\Payment;

use Stripe\StripeClient;
use Illuminate\Support\Facades\Log;

class StripeService
{
    protected StripeClient $stripe;

    public function __construct()
    {
        $this->stripe = new StripeClient(config('services.stripe.secret'));
    }

    public function createPaymentIntent(float $amount, string $currency = 'eur', array $metadata = []): string
    {
        try {
            $intent = $this->stripe->paymentIntents->create([
                'amount' => (int)($amount * 100), // Amount in cents
                'currency' => $currency,
                'metadata' => $metadata,
                'automatic_payment_methods' => ['enabled' => true],
            ]);

            return $intent->client_secret;
        } catch (\Exception $e) {
            Log::error('Stripe PaymentIntent Error: ' . $e->getMessage());
            throw $e;
        }
    }

    public function verifyWebhook(string $payload, string $sigHeader, string $secret): \Stripe\Event
    {
        try {
            return \Stripe\Webhook::constructEvent($payload, $sigHeader, $secret);
        } catch (\Exception $e) {
            Log::error('Stripe Webhook Error: ' . $e->getMessage());
            throw $e;
        }
    }
}
