<?php

declare(strict_types=1);

namespace App\Providers\Payment;

interface PaymentInterface
{
    public function create(float $amount, string $currency): string;
}
