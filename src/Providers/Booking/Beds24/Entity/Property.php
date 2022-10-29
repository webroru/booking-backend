<?php

declare(strict_types=1);

namespace App\Providers\Booking\Beds24\Entity;

class Property
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $propertyType,
        public readonly int $ownerId,
        public readonly string $currency,
        public readonly string $address,
        public readonly string $city,
        public readonly string $state,
        public readonly string $country,
        public readonly string $postcode,
        public readonly float $latitude,
        public readonly float $longitude,
        public readonly string $checkInStart,
        public readonly string $checkInEnd,
        public readonly string $checkOutEnd,
        public readonly string $offerType,
        public readonly string $bookingPageMultiplier,
        public readonly string $permit,
        public readonly string $roomChargeDisplay,
        public readonly array $templates,
        public readonly array $bookingRules,
        public readonly array $paymentCollection,
        public readonly array $paymentGateways,
        public readonly array $cardSettings,
        public readonly array $groupKeywords,
        public readonly array $oneTimeVouchers,
        public readonly array $discountVoucherCodes,
        public readonly array $featureCodes,
        public readonly ?array $roomTypes = null,
        public readonly ?array $offers = null,
        public readonly ?array $texts = null,
        public readonly ?int $sellPriority = null,
    ) {
    }
}
