<?php

declare(strict_types=1);

namespace App\Providers\Booking\Beds24\Service;

use App\Dto\BookingDto;
use App\Dto\GuestDto;
use App\Providers\Booking\Beds24\Entity\Booking;
use App\Providers\Booking\Beds24\Entity\InfoItem;

class GuestService
{
    private const CODE = 'guest';

    public function overwriteGuests(Booking $booking, BookingDto $bookingDto): void
    {
        $this->removeGuests($booking);

        /** @var GuestDto $guest */
        foreach ($bookingDto->guests as $guest) {
            $booking->infoItems[] = (new InfoItem(self::CODE, json_encode($guest)));
        }
    }

    /**
     * @param Booking $booking
     * @return GuestDto[]
     */
    public function getGuests(Booking $booking): array
    {
        $guests = [];
        foreach ($booking->infoItems as $infoItem) {
            if ($infoItem->code === self::CODE) {
                $guests[] = new GuestDto(...json_decode($infoItem->text, true));
            }
        }

        return $guests;
    }

    private function removeGuests(Booking $booking): void
    {
        foreach ($booking->infoItems as $infoItem) {
            if (isset($infoItem->code) && $infoItem->code === self::CODE) {
                unset($infoItem->text);
                unset($infoItem->code);
                unset($infoItem->bookingId);
            }
        }
    }
}
