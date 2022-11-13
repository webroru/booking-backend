<?php

declare(strict_types=1);

namespace App\Providers\Booking\Beds24;

use App\Dto\Booking;
use App\Providers\Booking\Beds24\Entity\InfoItem;
use App\Providers\Booking\Beds24\Entity\Property;

class CommonDtoConverter
{
    public function convert(Entity\Booking $booking, Property $property): Booking
    {
        $roomName = $property->roomTypes
            ? $this->getRoomName($property->roomTypes, $booking->roomId)
            . ' Room Number: ' . $this->getUnitName($property->roomTypes, $booking->unitId)
            : 'Unknown';

        $isRuleAccepted = $this->getInfoItemValue('isRuleAccepted', $booking->infoItems);

        return new Booking(
            fullName: "$booking->firstName $booking->lastName",
            checkInDate: $booking->arrival,
            checkOutDate: $booking->departure,
            phone: $booking->phone,
            orderId: $booking->id,
            propertyName: $property->name,
            room: $roomName,
            originalReferer: "$booking->referer, booking: $booking->apiReference",
            guestsAmount: $booking->numAdult + $booking->numChild,
            adults: (int) $this->getInfoItemValue('adults', $booking->infoItems),
            children: (int) $this->getInfoItemValue('children', $booking->infoItems),
            babies: (int) $this->getInfoItemValue('babies', $booking->infoItems),
            sucklings: (int) $this->getInfoItemValue('sucklings', $booking->infoItems),
            passCode: $this->getInfoItemValue('CODELOCK', $booking->infoItems),
            debt: $this->getDebt($booking->invoiceItems),
            extraPerson: 0,
            capacity: $property->roomTypes ? $this->getMaxPeople($property->roomTypes, $booking->roomId) : 0,
            overmax: $this->getInfoItemValue('overmax', $booking->infoItems),
            extraGuests: $this->getInfoItemValue('extraGuests', $booking->infoItems),
            isRuleAccepted: $isRuleAccepted === '1',
            checkIn: $this->getInfoItemValue('checkIn', $booking->infoItems),
            status: $this->getInfoItemValue('status', $booking->infoItems),
        );
    }

    /**
     * @param string $name
     * @param InfoItem[] $infoItems
     * @return ?string
     */
    private function getInfoItemValue(string $name, array $infoItems): ?string
    {
        foreach ($infoItems as $infoItem) {
            if ($infoItem->code === $name) {
                return $infoItem->text;
            }
        }

        return null;
    }

    private function getUnitName(array $roomTypes, int $unitId): ?string
    {
        foreach ($roomTypes as $roomType) {
            foreach ($roomType['units'] as $unit) {
                if ($unit['id'] === $unitId) {
                    return $unit['name'];
                }
            }
        }

        return null;
    }

    private function getRoomName(array $roomTypes, int $roomId): ?string
    {
        foreach ($roomTypes as $roomType) {
            if ($roomType['id'] === $roomId) {
                return $roomType['name'];
            }
        }

        return null;
    }

    public function getMaxPeople(array $roomTypes, int $roomId): ?int
    {
        foreach ($roomTypes as $roomType) {
            if ($roomType['id'] === $roomId) {
                return $roomType['maxPeople'];
            }
        }

        return null;
    }

    public function getDebt(array $invoiceItems): float
    {
        $debt = array_reduce(
            $invoiceItems,
            fn (float $carry, array $item) => $carry + $item['lineTotal'],
            0.0,
        );

        $debt = round($debt, 2);
        return $debt === -0.0 ? 0 : $debt;
    }
}
