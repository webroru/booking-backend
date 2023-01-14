<?php

declare(strict_types=1);

namespace App\Providers\Booking\Beds24\Dto\Response;

use App\Providers\Booking\Beds24\Entity\Booking;

class GetBookingsDto
{
    /** @var Booking[] */
    public array $bookings = [];

    public function __construct(
        public readonly bool $success,
        public readonly string $type,
        public readonly ?int $count = null,
        public readonly ?array $pages = null,
        ?array $data = null,
        ...$params,
    ) {
        foreach ($data as $item) {
            $this->bookings[] = new Booking(...$item);
        }
    }
}
