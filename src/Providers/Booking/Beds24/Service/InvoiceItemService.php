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
            fn (float $carry, InvoiceItem $item) => $carry + $item->lineTotal,
            0.0,
        );

        $debt = round($debt, 2);
        return $debt === -0.0 ? 0 : $debt;
    }

    public function extractCityTaxPayment(Booking $booking): float
    {
        $totalPayment = 0.0;
        foreach ($booking->invoiceItems as $item) {
            if ($item->type === InvoiceItem::PAYMENT) {
                $totalPayment += $item->amount;
            }
        }

        $roomCharge = 0.0;
        foreach ($booking->invoiceItems as $item) {
            if ($item->type === InvoiceItem::CHARGE && !str_contains(mb_strtolower($item->description), 'city tax')) {
                $roomCharge += $item->amount * $item->qty;
            }
        }

        $cityTaxPayment = $totalPayment - $roomCharge;

        if ($cityTaxPayment > 0) {
            $this->removePayments($booking->invoiceItems);
            $booking->invoiceItems[] = new InvoiceItem(
                amount: $totalPayment - $cityTaxPayment,
                type: InvoiceItem::PAYMENT,
                description: 'Payment (excl. city tax)',
            );
        }

        return $cityTaxPayment > 0 ? $cityTaxPayment : 0.0;
    }

    /**
     * @param InvoiceItem[] $invoiceItems
     * @return void
     */
    private function removePayments(array $invoiceItems): void
    {
        foreach ($invoiceItems as $item) {
            if ($item->type === 'payment') {
                foreach ($item as $prop => $value) {
                    if ($prop !== 'id') {
                        unset($item->$prop);
                    }
                }
            }
        }
    }
}
