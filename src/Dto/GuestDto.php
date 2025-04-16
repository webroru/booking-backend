<?php

declare(strict_types=1);

namespace App\Dto;

readonly class GuestDto
{
    public function __construct(
        public string $firstName,
        public string $lastName,
        public string $documentNumber,
        public string $documentType,
        public string $dateOfBirth,
        public string $nationality,
        public ?string $gender,
    ) {
    }
}
