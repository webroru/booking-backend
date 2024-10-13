<?php

declare(strict_types=1);

namespace App\Dto;

readonly class InvoiceItemDto
{
    public function __construct(
        public int $id,
        public string $type,
        public int $bookingId,
        public string $description,
        public string $status,
        public int $qty,
        public float $amount,
        public float $vatRate,
        public int $createdBy,
    ) {
    }
}
