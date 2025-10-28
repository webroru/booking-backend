<?php

declare(strict_types=1);

namespace App\Providers\Booking\Beds24\Service;

use App\Providers\Booking\Beds24\Entity\Booking;
use App\Providers\Booking\Beds24\Entity\InvoiceItem;

class InvoiceItemService
{
    public function getDebt(Booking $booking): float
    {
        $debt = array_reduce(
            $booking->invoiceItems,
            fn (float $carry, InvoiceItem $item) => $carry + ($item->lineTotal ?? 0),
            0.0,
        );

        $debt = round($debt, 2);
        return $debt === -0.0 ? 0 : $debt;
    }

    public function extractPayment(Booking $booking): float
    {
        $totalPayment = 0.0;
        foreach ($booking->invoiceItems as $item) {
            if (isset($item->type) && $item->type === InvoiceItem::PAYMENT) {
                $totalPayment += $item->amount;
            }
        }
        $this->removePayments($booking->invoiceItems);

        return $totalPayment;
    }

    public function addPayment(Booking $booking, float $amount, string $description): void
    {
        $booking->invoiceItems[] = new InvoiceItem(
            amount: $amount,
            type: InvoiceItem::PAYMENT,
            description: $description,
        );
    }

    /**
     * @param InvoiceItem[] $invoiceItems
     * @return void
     */
    private function removePayments(array $invoiceItems): void
    {
        foreach ($invoiceItems as $item) {
            if (isset($item->type) && $item->type === 'payment') {
                foreach ($item as $prop => $value) {
                    if ($prop !== 'id') {
                        unset($item->$prop);
                    }
                }
            }
        }
    }
}
