<?php

declare(strict_types=1);

namespace App\Providers\Booking\Beds24\Entity;

class Guest extends AbstractEntity
{
    public function __construct(
        public int $id,
        public ?string $title,
        public ?string $firstName,
        public ?string $lastName,
        public ?string $email,
        public ?string $phone,
        public ?string $mobile,
        public ?string $company,
        public ?string $address,
        public ?string $city,
        public ?string $state,
        public ?string $postcode,
        public ?string $country,
        public ?string $country2,
        public ?string $flagText,
        public ?string $flagColor,
        public ?string $note,
        public ?string $custom1,
        public ?string $custom2,
        public ?string $custom3,
        public ?string $custom4,
        public ?string $custom5,
        public ?string $custom6,
        public ?string $custom7,
        public ?string $custom8,
        public ?string $custom9,
        public ?string $custom10,
    ) {
    }
}
