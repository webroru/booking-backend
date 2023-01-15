<?php

declare(strict_types=1);

namespace App\Providers\Payment;

interface PaymentInterface
{
    public function create(float $amount, int $bookingId, string $currency): string;
}
