<?php

declare(strict_types=1);

namespace App\Providers\Booking\Beds24\Entity;

class Booking extends AbstractEntity
{
    public function __construct(
        public ?int $id,
        public ?int $propertyId,
        public ?int $apiSourceId,
        public ?string $apiSource,
        public ?string $subStatus,
        public ?int $statusCode,
        public ?string $stripeToken,
        public ?int $offerId,
        public ?string $referer,
        public ?string $reference,
        public ?string $apiReference,
        public ?string $apiMessage,
        public ?string $allowChannelUpdate,
        public ?string $allowAutoAction,
        public ?string $allowReview,
        public ?string $bookingTime,
        public ?string $modifiedTime,
        public ?string $cancelTime,
        public ?int $masterId,
        public ?int $roomId,
        public ?int $unitId,
        public ?int $roomQty,
        public ?string $status,
        public ?string $arrival,
        public ?string $departure,
        public ?int $numAdult,
        public ?int $numChild,
        public ?string $title,
        public ?string $firstName,
        public ?string $lastName,
        public ?string $email,
        public ?string $phone,
        public ?string $mobile,
        public ?string $fax,
        public ?string $company,
        public ?string $address,
        public ?string $city,
        public ?string $state,
        public ?string $postcode,
        public ?string $country,
        public ?string $country2,
        public ?string $arrivalTime,
        public ?string $voucher,
        public ?string $comments,
        public ?string $notes,
        public ?string $message,
        public ?string $groupNote,
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
        public ?string $flagColor,
        public ?string $flagText,
        public ?string $lang,
        public ?float $price,
        public ?float $deposit,
        public ?float $tax,
        public ?float $commission,
        public ?string $refererEditable,
        public ?string $rateDescription,
        public ?int $invoiceeId,
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
