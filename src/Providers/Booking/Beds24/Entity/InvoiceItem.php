<?php

declare(strict_types=1);

namespace App\Providers\Booking\Beds24\Entity;

class InvoiceItem extends AbstractEntity
{
    public const CHARGE = 'charge';
    public const PAYMENT = 'payment';

    public function __construct(
        public int $amount,
        public string $type,
        public ?int $id = null,
        public ?int $bookingId = null,
        public ?int $invoiceId = null,
        public ?string $description = null,
        public ?string $status = null,
        public ?int $qty = null,
        public ?int $lineTotal = null,
        public ?int $vatRate = null,
        public ?int $createdBy = null,
        public ?string $createTime = null,
        public ?string $invoiceDate = null,
        ...$params,
    ) {
    }
}
