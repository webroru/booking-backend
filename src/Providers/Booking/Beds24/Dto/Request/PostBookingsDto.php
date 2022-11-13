<?php

declare(strict_types=1);

namespace App\Providers\Booking\Beds24\Dto\Request;

use App\Providers\Booking\Beds24\Entity\Booking;

class PostBookingsDto extends AbstractRequestDto
{
    /**
     * @param Booking[] $bookings
     */
    public function __construct(
        public readonly array $bookings,
    ) {
    }

    public function toArray(): array
    {
        $result = [];
        foreach ($this->bookings as $booking) {
            $result[] = $this->entityToArray($booking);
        }

        return $result;
    }
}
