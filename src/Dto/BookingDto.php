<?php

declare(strict_types=1);

namespace App\Dto;

readonly class BookingDto
{
    public function __construct(
        public string $firstName,
        public string $lastName,
        public string $checkInDate,
        public string $checkOutDate,
        public string $phone,
        public int $orderId,
        public string $propertyName,
        public string $room,
        public string $originalReferer,
        public int $guestsAmount,
        public int $adults,
        public int $children,
        public int $babies,
        public int $sucklings,
        public ?string $passCode,
        public float $debt,
        public float $extraPerson,
        public int $capacity,
        public ?int $overmax,
        public ?bool $isRuleAccepted,
        public ?bool $checkIn,
        public bool $checkOut,
        public ?string $paymentStatus,
        public ?bool $plusGuest,
        public ?bool $lessDocs,
        public ?array $photos,
        public ?int $groupId,
        public array $invoiceItems,
        public array $guests,
    ) {
    }
}
