<?php

declare(strict_types=1);

namespace App\Providers\Booking\Beds24\Entity;

class Booking
{
    public function __construct(
        public readonly int $id,
        public readonly int $propertyId,
        public readonly int $apiSourceId,
        public readonly string $apiSource,
        public readonly string $subStatus,
        public readonly int $statusCode,
        public readonly ?string $stripeToken,
        public readonly int $offerId,
        public readonly string $referer,
        public readonly string $reference,
        public readonly string $apiReference,
        public readonly string $apiMessage,
        public readonly string $allowChannelUpdate,
        public readonly string $allowAutoAction,
        public readonly string $allowReview,
        public readonly string $bookingTime,
        public readonly string $modifiedTime,
        public readonly ?string $cancelTime,
        public readonly array $infoItems,
        public readonly ?int $masterId,
        public readonly int $roomId,
        public readonly int $unitId,
        public readonly int $roomQty,
        public readonly string $status,
        public readonly string $arrival,
        public readonly string $departure,
        public readonly int $numAdult,
        public readonly int $numChild,
        public readonly string $title,
        public readonly string $firstName,
        public readonly string $lastName,
        public readonly string $email,
        public readonly string $phone,
        public readonly string $mobile,
        public readonly string $fax,
        public readonly string $company,
        public readonly string $address,
        public readonly string $city,
        public readonly string $state,
        public readonly string $postcode,
        public readonly string $country,
        public readonly ?string $country2,
        public readonly string $arrivalTime,
        public readonly string $voucher,
        public readonly string $comments,
        public readonly string $notes,
        public readonly string $message,
        public readonly ?string $groupNote,
        public readonly string $custom1,
        public readonly string $custom2,
        public readonly string $custom3,
        public readonly string $custom4,
        public readonly string $custom5,
        public readonly string $custom6,
        public readonly string $custom7,
        public readonly string $custom8,
        public readonly string $custom9,
        public readonly string $custom10,
        public readonly string $flagColor,
        public readonly string $flagText,
        public readonly string $lang,
        public readonly float $price,
        public readonly float $deposit,
        public readonly float $tax,
        public readonly float $commission,
        public readonly string $refererEditable,
        public readonly string $rateDescription,
        public readonly ?int $invoiceeId,
        public readonly array $invoiceItems,
        public readonly ?array $guests = null,
        public readonly ?string $cancelUntil = null,
        ...$params,
    ) {
    }
}
