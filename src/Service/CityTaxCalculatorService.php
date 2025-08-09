<?php

declare(strict_types=1);

namespace App\Service;

class CityTaxCalculatorService
{
    public function calculateTax(int $age): float
    {
        if ($age < 7) {
            return 0.0;
        } elseif ($age < 18) {
            return 1.57;
        } else {
            return 3.12;
        }
    }
}
