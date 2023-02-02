<?php

declare(strict_types=1);

namespace App\Providers\Booking\Beds24\Dto\Request;

class GetBookingsDto implements RequestDtoInterface
{
    public function __construct(
        public readonly ?string $filter = null,
        public readonly ?array $propertyId = null,
        public readonly ?array $roomId = null,
        public readonly ?array $id = null,
        public readonly ?array $masterId = null,
        public readonly ?string $arrival = null,
        public readonly ?string $arrivalFrom = null,
        public readonly ?string $arrivalTo = null,
        public readonly ?string $departure = null,
        public readonly ?string $departureFrom = null,
        public readonly ?string $departureTo = null,
        public readonly ?string $bookingTimeFrom = null,
        public readonly ?string $bookingTimeTo = null,
        public readonly ?string $modifiedFrom = null,
        public readonly ?string $modifiedTo = null,
        public readonly ?bool $includeInvoiceItems = null,
        public readonly ?bool $includeInfoItems = null,
        public readonly ?bool $includeInfoItemsConverted = null,
        public readonly ?bool $includeStripeCharges = null,
        public readonly ?array $status = null,
        public readonly ?string $searchString = null,
        public readonly ?int $page = null,
        public readonly ?string $search = null,
    ) {
    }

    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
