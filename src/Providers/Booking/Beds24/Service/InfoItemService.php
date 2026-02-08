<?php

declare(strict_types=1);

namespace App\Providers\Booking\Beds24\Service;

use App\Providers\Booking\Beds24\Entity\Booking;
use App\Providers\Booking\Beds24\Entity\InfoItem;

class InfoItemService
{
    public const CHECK_IN = 'CHECK_IN';
    public const PAYMENT_STATUS = 'PAYMENT_STATUS';
    public const IS_RULE_ACCEPTED = 'IS_RULE_ACCEPTED';
    public const CHECK_OUT = 'CHECK_OUT';
    public const OVERMAX = 'OVERMAX';
    public const PLUS_GUEST = 'PLUS_GUEST';
    public const LESS_DOCS = 'LESS_DOCS';
    public const CODELOCK = 'CODELOCK';

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
