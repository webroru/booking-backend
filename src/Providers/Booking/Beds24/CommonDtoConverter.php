<?php

declare(strict_types=1);

namespace App\Providers\Booking\Beds24;

use App\Dto\Booking as BookingDto;
use App\Providers\Booking\Beds24\Entity\InfoItem;
use App\Providers\Booking\Beds24\Entity\InvoiceItem;
use App\Providers\Booking\Beds24\Entity\Property;

class CommonDtoConverter
{
    public function convert(Entity\Booking $booking, Property $property): BookingDto
    {
        $roomName = $property->roomTypes
            ? $this->getRoomName($property->roomTypes, $booking->roomId)
            . ' Room Number: ' . $this->getUnitName($property->roomTypes, $booking->unitId)
            : 'Unknown';

        $isRuleAccepted = $this->getInfoItemValue('isRuleAccepted', $booking->infoItems);
        $plusGuest = $this->getInfoItemValue('plusGuest', $booking->infoItems);
        $lessDocs = $this->getInfoItemValue('lessDocs', $booking->infoItems);
        $checkIn = $this->getInfoItemValue('checkIn', $booking->infoItems);

        return new BookingDto(
            firstName: $booking->firstName,
            lastName: $booking->lastName,
            checkInDate: $booking->arrival,
            checkOutDate: $booking->departure,
            phone: $booking->phone,
            orderId: $booking->id,
            propertyName: $property->name,
            room: $roomName,
            originalReferer: "$booking->referer, booking: $booking->apiReference",
            guestsAmount: $booking->numAdult + $booking->numChild,
            adults: (int) $this->getGuestAmount('adults', $booking->infoItems),
            children: (int) $this->getGuestAmount('children', $booking->infoItems),
            babies: (int) $this->getGuestAmount('babies', $booking->infoItems),
            sucklings: (int) $this->getGuestAmount('sucklings', $booking->infoItems),
            passCode: $this->getInfoItemValue('CODELOCK', $booking->infoItems),
            debt: $this->getDebt($booking->invoiceItems),
            extraPerson: $this->getExtraPrice($property->roomTypes, $booking->roomId),
            capacity: $property->roomTypes ? $this->getMaxPeople($property->roomTypes, $booking->roomId) : 0,
            overmax: (int) $this->getInfoItemValue('overmax', $booking->infoItems),
            isRuleAccepted: $isRuleAccepted === 'true',
            checkIn: $checkIn === 'true',
            paymentStatus: $this->getInfoItemValue('paymentStatus', $booking->infoItems),
            plusGuest: $plusGuest === 'true',
            lessDocs: $lessDocs === 'true',
        );
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

    /**
     * @param InvoiceItem[] $invoiceItems
     * @return float
     */
    public function getDebt(array $invoiceItems): float
    {
        $debt = array_reduce(
            $invoiceItems,
            fn (float $carry, InvoiceItem $item) => $carry + $item->lineTotal,
            0.0,
        );

        $debt = round($debt, 2);
        return $debt === -0.0 ? 0 : $debt;
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

    private function getGuestAmount(string $category, array $infoItems): int
    {
        if (!isset(Booking::GUESTS_AGE_CATEGORIES[$category])) {
            throw new \LogicException("Undefined guest category: $category");
        }
        return (int) $this->getInfoItemValue(Booking::GUESTS_AGE_CATEGORIES[$category], $infoItems);
    }

    private function getExtraPrice(array $roomTypes, int $roomId): float
    {
        $roomType = null;
        foreach ($roomTypes as $item) {
            if ($item['id'] !== $roomId) {
                continue;
            }
            $roomType = $item;
        }

        if (!$roomType) {
            throw new \LogicException("RoomType for Room Id $roomId not found");
        }

        if (!isset($roomType['priceRules'][0]['extraPerson'])) {
            throw new \LogicException("ExtraPerson price for room id $roomId not found");
        }

        return $roomType['priceRules'][0]['extraPerson'];
    }
}
