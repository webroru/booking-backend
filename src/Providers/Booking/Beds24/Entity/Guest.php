<?php

declare(strict_types=1);

namespace App\Providers\Booking\Beds24\Entity;

class Guest extends AbstractEntity
{
    public function __construct(
        public ?int $id = null,
        public ?string $title = null,
        public ?string $firstName = null,
        public ?string $lastName = null,
        public ?string $email = null,
        public ?string $phone = null,
        public ?string $mobile = null,
        public ?string $company = null,
        public ?string $address = null,
        public ?string $city = null,
        public ?string $state = null,
        public ?string $postcode = null,
        public ?string $country = null,
        public ?string $country2 = null,
        public ?string $flagText = null,
        public ?string $flagColor = null,
        public ?string $note = null,
        public ?string $custom1 = null,
        public ?string $custom2 = null,
        public ?string $custom3 = null,
        public ?string $custom4 = null,
        public ?string $custom5 = null,
        public ?string $custom6 = null,
        public ?string $custom7 = null,
        public ?string $custom8 = null,
        public ?string $custom9 = null,
        public ?string $custom10 = null,
    ) {
    }
}
