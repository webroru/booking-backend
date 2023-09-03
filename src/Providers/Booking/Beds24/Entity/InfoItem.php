<?php

declare(strict_types=1);

namespace App\Providers\Booking\Beds24\Entity;

class InfoItem extends AbstractEntity
{
    public function __construct(
        public ?string $code = null,
        public ?string $text = null,
        public ?int $bookingId = null,
        public ?int $id = null,
        ...$params,
    ) {
    }
}
