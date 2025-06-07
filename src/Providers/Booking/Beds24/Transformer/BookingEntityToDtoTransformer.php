<?php

declare(strict_types=1);

namespace App\Providers\Booking\Beds24\Transformer;

use App\Dto\BookingDto;
use App\Dto\InvoiceItemDto;
use App\Providers\Booking\Beds24\Entity;
use App\Providers\Booking\Beds24\Entity\InvoiceItem;
use App\Providers\Booking\Beds24\Entity\Property;
use App\Providers\Booking\Beds24\Service\GuestService;
use App\Providers\Booking\Beds24\Service\InfoItemService;

readonly class BookingEntityToDtoTransformer
{
    public function __construct(
        private GuestService $guestService,
        private InfoItemService $infoItemService,
    ) {
    }

    public function transform(Entity\Booking $booking, Property $property, array $groups): BookingDto
    {
        $roomType = $this->getRoomType($property->roomTypes, $booking->roomId);
        $roomName = ($roomType['name'] ?? '') . ' Room Number: ' . $this->getUnitName($roomType, $booking->unitId);

        $isRuleAccepted = $this->infoItemService->getInfoItemValue('isRuleAccepted', $booking->infoItems);
        $checkIn = $this->infoItemService->getInfoItemValue('checkIn', $booking->infoItems);
        $checkOut = $this->infoItemService->getInfoItemValue('checkOut', $booking->infoItems);

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
            passCode: $this->infoItemService->getInfoItemValue('CODELOCK', $booking->infoItems),
            debt: $this->getDebt($booking->invoiceItems),
            extraPerson: $this->getExtraPrice($property->roomTypes, $booking->roomId),
            capacity: $property->roomTypes ? $this->getMaxPeople($property->roomTypes, $booking->roomId) : 0,
            isRuleAccepted: $isRuleAccepted === 'true',
            checkIn: $checkIn === 'true',
            checkOut: $checkOut === 'true',
            paymentStatus: $this->infoItemService->getInfoItemValue('paymentStatus', $booking->infoItems),
            groupId: in_array($booking->id, $groups) ? $booking->id : $booking->masterId,
            invoiceItems: $this->getInvoiceItems($booking->invoiceItems),
            guests: $this->guestService->getGuests($booking),
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
