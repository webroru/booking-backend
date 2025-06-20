<?php

declare(strict_types=1);

namespace App\Providers\Booking\Beds24\Service;

use App\Providers\Booking\Beds24\Entity\InvoiceItem;

class InvoiceItemService
{
    /**
     * @param InvoiceItem[] $invoiceItems
     * @return float
     */
    public function getDebt(array $invoiceItems): float
    {
        $debt = array_reduce(
            $invoiceItems,
            fn (float $carry, InvoiceItem $item) => $carry + $item->lineTotal,
            0.0,
        );

        $debt = round($debt, 2);
        return $debt === -0.0 ? 0 : $debt;
    }
}
