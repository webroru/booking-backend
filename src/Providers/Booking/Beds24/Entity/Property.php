<?php

declare(strict_types=1);

namespace App\Providers\Booking\Beds24\Entity;

class Property extends AbstractEntity
{
    public function __construct(
        public int $id,
        public string $name,
        public string $propertyType,
        public array $account,
        public string $currency,
        public string $address,
        public string $city,
        public string $state,
        public string $country,
        public string $postcode,
        public float $latitude,
        public float $longitude,
        public string $checkInStart,
        public string $checkInEnd,
        public string $checkOutEnd,
        public string $offerType,
        public string $bookingPageMultiplier,
        public string $permit,
        public string $roomChargeDisplay,
        public array $templates,
        public array $bookingRules,
        public array $paymentCollection,
        public array $paymentGateways,
        public array $cardSettings,
        public array $groupKeywords,
        public array $oneTimeVouchers,
        public array $discountVouchers,
        public array $featureCodes,
        public ?array $roomTypes = null,
        public ?array $offers = null,
        public ?array $texts = null,
        public ?int $sellPriority = null,
        ...$params,
    ) {
    }
}
