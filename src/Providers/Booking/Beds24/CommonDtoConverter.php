<?php

declare(strict_types=1);

namespace App\Providers\Booking\Beds24;

use App\Dto\Booking;

class CommonDtoConverter
{
    public function convert(Entity\Booking $booking): Booking
    {
        return new Booking(
            fullName: "$booking->firstName $booking->lastName",
            checkInDate: $booking->arrival,
            checkOutDate: $booking->departure,
            phone: $booking->phone,
            orderId: $booking->id,
            propertyName: "TODO fix it",
            room: "TODO fix it",
            originalReferrer: "TODO fix it, referer: $booking->referer, reference: $booking->reference",
            guestsAmount: $booking->numAdult + $booking->numChild,
            adults: (int) $this->getInfoItemValue('adults', $booking->infoItems),
            children: (int) $this->getInfoItemValue('children', $booking->infoItems),
            babies: (int) $this->getInfoItemValue('babies', $booking->infoItems),
            sucklings: (int) $this->getInfoItemValue('sucklings', $booking->infoItems),
            passCode: $this->getInfoItemValue('code', $booking->infoItems),
            debt: 0, // TODO fix it,
            extraPerson: 1, //"TODO check it"
            capacity: 1, // TODO fix it properties?id=156424&includeTexts=&roomId=345716
            overmax: $this->getInfoItemValue('overmax', $booking->infoItems),
            extraGuests: $this->getInfoItemValue('extraGuests', $booking->infoItems),
            isRuleAccepted: $this->getInfoItemValue('isRuleAccepted', $booking->infoItems),
            checkIn: $this->getInfoItemValue('checkIn', $booking->infoItems),
            status: $this->getInfoItemValue('status', $booking->infoItems),
        );
    }

    private function getInfoItemValue(string $name, array $infoItems): mixed
    {
        foreach ($infoItems as $infoItem) {
            if ($infoItem['code'] === $name) {
                return $infoItem['text'];
            }
        }

        return null;
    }
}
