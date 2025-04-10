<?php

declare(strict_types=1);

namespace App\Providers\Booking\Beds24\Transformer;

use App\Dto\BookingDto;
use App\Dto\InvoiceItemDto;
use App\Entity\Photo;
use App\Providers\Booking\Beds24\Booking;
use App\Providers\Booking\Beds24\Entity;
use App\Providers\Booking\Beds24\Entity\InfoItem;
use App\Providers\Booking\Beds24\Entity\InvoiceItem;
use App\Providers\Booking\Beds24\Entity\Property;
use App\Repository\PhotoRepository;

class BookingTransformer
{
    public function __construct(
        private readonly PhotoRepository $photoRepository,
    ) {
    }

    public function toDto(Entity\Booking $booking, Property $property, array $groups): BookingDto
    {
        $roomType = $this->getRoomType($property->roomTypes, $booking->roomId);
        $roomName = ($roomType['name'] ?? '') . ' Room Number: ' . $this->getUnitName($roomType, $booking->unitId);

        $isRuleAccepted = $this->getInfoItemValue('isRuleAccepted', $booking->infoItems);
        $plusGuest = $this->getInfoItemValue('plusGuest', $booking->infoItems);
        $lessDocs = $this->getInfoItemValue('lessDocs', $booking->infoItems);
        $checkIn = $this->getInfoItemValue('checkIn', $booking->infoItems);
        $checkOut = $this->getInfoItemValue('checkOut', $booking->infoItems);

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
            checkOut: $checkOut === 'true',
            paymentStatus: $this->getInfoItemValue('paymentStatus', $booking->infoItems),
            plusGuest: $plusGuest === 'true',
            lessDocs: $lessDocs === 'true',
            photos: $this->getPhotos($booking->id),
            groupId: in_array($booking->id, $groups) ? $booking->id : $booking->masterId,
            invoiceItems: $this->getInvoiceItems($booking->invoiceItems),
            guests: $booking->guests,
        );
    }

    private function getMaxPeople(array $roomTypes, int $roomId): ?int
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
    private function getDebt(array $invoiceItems): float
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

    private function getRoomType(array $roomTypes, int $roomId): ?array
    {
        foreach ($roomTypes as $roomType) {
            if ($roomType['id'] === $roomId) {
                return $roomType;
            }
        }

        return null;
    }

    private function getUnitName(array $roomType, int $unitId): ?string
    {
        foreach ($roomType['units'] as $unit) {
            if ($unit['id'] === $unitId) {
                return $unit['name'];
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

    private function getPhotos(?int $id): array
    {
        return array_map(
            fn(Photo $photo) => ['id' => $photo->getId(), 'url' => $photo->getUrl()],
            $this->photoRepository->findBy(['bookingId' => $id])
        );
    }

    /**
     * @param InvoiceItem[] $invoiceItems
     * @return array
     */
    private function getInvoiceItems(array $invoiceItems): array
    {
        return array_map(
            fn(InvoiceItem $invoiceItem) => new InvoiceItemDto(
                id: $invoiceItem->id,
                type: $invoiceItem->type,
                bookingId: $invoiceItem->bookingId,
                description: $invoiceItem->description,
                status: $invoiceItem->status,
                qty: $invoiceItem->qty,
                amount: $invoiceItem->amount,
                lineTotal: $invoiceItem->lineTotal,
            ),
            $invoiceItems
        );
    }
}
