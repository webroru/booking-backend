<?php

declare(strict_types=1);

namespace App\Dto;

class Booking
{
    public function __construct(
        public readonly string $fullName,
        public readonly string $checkInDate,
        public readonly string $checkOutDate,
        public readonly string $phone,
        public readonly int $orderId,
        public readonly string $propertyName,
        public readonly string $room,
        public readonly string $originalReferer,
        public readonly int $guestsAmount,
        public readonly int $adults,
        public readonly int $children,
        public readonly int $babies,
        public readonly int $sucklings,
        public readonly ?string $passCode,
        public readonly float $debt,
        public readonly int $extraPerson,
        public readonly int $capacity,
        public readonly ?int $overmax,
        public readonly ?int $extraGuests,
        public readonly ?bool $isRuleAccepted,
        public readonly ?bool $checkIn,
        public readonly ?string $status,
        public readonly ?bool $plusGuest,
        public readonly ?bool $lessDocs,
    ) {
    }
}
