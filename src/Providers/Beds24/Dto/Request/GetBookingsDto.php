<?php

declare(strict_types=1);

namespace App\Providers\Beds24\Dto\Request;

use App\Providers\Beds24\Exception\ModifyDtoException;

class GetBookingsDto implements \ArrayAccess
{
    public function __construct(
        public readonly string $filter,
        public readonly array $propertyId,
        public readonly array $roomId,
        public readonly array $id,
        public readonly array $masterId,
        public readonly string $arrival,
        public readonly string $arrivalFrom,
        public readonly string $arrivalTo,
        public readonly string $departure,
        public readonly string $departureFrom,
        public readonly string $departureTo,
        public readonly string $bookingTimeFrom,
        public readonly string $bookingTimeTo,
        public readonly string $modifiedFrom,
        public readonly string $modifiedTo,
        public readonly bool $includeInvoiceItems,
        public readonly bool $includeInfoItems,
        public readonly bool $includeInfoItemsConverted,
        public readonly bool $includeStripeCharges,
        public readonly array $status,
        public readonly string $searchString,
        public readonly int $page,
    ) {}

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->toArray()[$offset]);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->toArray()[$offset] ?? null;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new ModifyDtoException("Can't modify existed Dto");
    }

    public function offsetUnset(mixed $offset): void
    {
        throw new ModifyDtoException("Can't modify existed Dto");
    }

    private function toArray(): array
    {
        return get_object_vars($this);
    }
}
