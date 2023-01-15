<?php

declare(strict_types=1);

namespace App\Providers\Booking\Beds24\Entity;

class Booking extends AbstractEntity
{
    public function __construct(
        public ?int $id = null,
        public ?int $propertyId = null,
        public ?int $apiSourceId = null,
        public ?string $apiSource = null,
        public ?string $subStatus = null,
        public ?int $statusCode = null,
        public ?string $stripeToken = null,
        public ?int $offerId = null,
        public ?string $referer = null,
        public ?string $reference = null,
        public ?string $apiReference = null,
        public ?string $apiMessage = null,
        public ?string $allowChannelUpdate = null,
        public ?string $allowAutoAction = null,
        public ?string $allowReview = null,
        public ?string $bookingTime = null,
        public ?string $modifiedTime = null,
        public ?string $cancelTime = null,
        public ?int $masterId = null,
        public ?int $roomId = null,
        public ?int $unitId = null,
        public ?int $roomQty = null,
        public ?string $status = null,
        public ?string $arrival = null,
        public ?string $departure = null,
        public ?int $numAdult = null,
        public ?int $numChild = null,
        public ?string $title = null,
        public ?string $firstName = null,
        public ?string $lastName = null,
        public ?string $email = null,
        public ?string $phone = null,
        public ?string $mobile = null,
        public ?string $fax = null,
        public ?string $company = null,
        public ?string $address = null,
        public ?string $city = null,
        public ?string $state = null,
        public ?string $postcode = null,
        public ?string $country = null,
        public ?string $country2 = null,
        public ?string $arrivalTime = null,
        public ?string $voucher = null,
        public ?string $comments = null,
        public ?string $notes = null,
        public ?string $message = null,
        public ?string $groupNote = null,
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
        public ?string $flagColor = null,
        public ?string $flagText = null,
        public ?string $lang = null,
        public ?float $price = null,
        public ?float $deposit = null,
        public ?float $tax = null,
        public ?float $commission = null,
        public ?string $refererEditable = null,
        public ?string $rateDescription = null,
        public ?int $invoiceeId = null,
        public ?array $invoiceItems = [],
        public ?array $guests = null,
        public ?string $cancelUntil = null,
        public ?array $infoItems = [],
        ...$params,
    ) {
        $infoItems = [];
        foreach ($this->infoItems as $infoItem) {
            $infoItems[] = new InfoItem(...$infoItem);
        }
        $this->infoItems = $infoItems;
    }
}
