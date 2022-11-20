<?php

declare(strict_types=1);

namespace App\Providers\Booking\Beds24\Entity;

class InfoItem extends AbstractEntity
{
    public function __construct(
        public ?string $code,
        public ?string $text,
        public ?int $bookingId = null,
        public ?int $id = null,
        ...$params,
    ) {
    }
}
