<?php

declare(strict_types=1);

namespace App\Dto;

readonly class GuestDto
{
    public function __construct(
        public ?int $id,
        public string $firstName,
        public string $lastName,
        public string $documentNumber,
        public string $documentType,
        public string $dateOfBirth,
        public string $nationality,
        public string $gender,
        public string $checkOutDate,
        public string $checkOutTime,
        public int $cityTaxExemption,
    ) {
    }
}
