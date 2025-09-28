<?php

declare(strict_types=1);

namespace App\Providers\Booking\Beds24\Service;

use App\Providers\Booking\Beds24\Entity\Booking;
use App\Providers\Booking\Beds24\Entity\InfoItem;

class InfoItemService
{
    public function updateInfoItem(Booking $booking, InfoItem $infoItem): void
    {
        $existedInfoItem = $this->findInfoItemByCode($booking->infoItems, $infoItem->code);
        if ($infoItem->text === null) {
            if ($existedInfoItem) {
                unset($existedInfoItem->text);
                unset($existedInfoItem->code);
                unset($existedInfoItem->bookingId);
            }
            return;
        }

        if ($existedInfoItem) {
            $existedInfoItem->text = $infoItem->text;
        } else {
            $booking->infoItems[] = $infoItem;
        }
    }

    /**
     * @param string $name
     * @param InfoItem[] $infoItems
     * @return ?string
     */
    public function getInfoItemValue(string $name, array $infoItems): ?string
    {
        foreach ($infoItems as $infoItem) {
            if ($infoItem->code === $name) {
                return $infoItem->text;
            }
        }

        return null;
    }

    /**
     * @param InfoItem[] $infoItems
     */
    private function findInfoItemByCode(array $infoItems, string $code): ?InfoItem
    {
        foreach ($infoItems as $infoItem) {
            if (isset($infoItem->code) && $infoItem->code === $code) {
                return $infoItem;
            }
        }

        return null;
    }
}
