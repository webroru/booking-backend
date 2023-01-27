<?php

declare(strict_types=1);

namespace App\Providers\Payment\Stripe;

use App\Providers\Payment\PaymentInterface;
use Stripe\StripeClient;

class Payment implements PaymentInterface
{
    public function __construct(private StripeClient $client)
    {
    }

    public function create(float $amount, int $bookingId, string $client, string $currency = 'eur'): string
    {
        $intent = $this->client->paymentIntents->create(
            [
                'amount' => $amount,
                'currency' => $currency,
                'automatic_payment_methods' => ['enabled' => true],
                'metadata' => [
                    'bookingId' => $bookingId,
                    'client' => $client,
                ],
            ]
        );

        return $intent->client_secret;
    }
}
