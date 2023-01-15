<?php

declare(strict_types=1);

namespace App\Providers\Booking\Beds24\Entity;

class Invoice extends AbstractEntity
{
    public function __construct(
        int $id,
        string $type,
        int $bookingId,
        int $invoiceeId,
        string $description,
        bool $useDefaultDescription,
        bool $useDefaultStatus,
        string $status,
        int $qty,
        int $amount,
        int $lineTotal,
        int $vatRate,
        int $createdBy,
        string $createTime,
        string $invoiceDate,
        ...$params,
    ) {
    }
}
