<?php

declare(strict_types=1);

namespace App\Providers\Booking\Beds24\Dto\Response;

use App\Providers\Booking\Beds24\Entity\Booking;
use App\Providers\Booking\Beds24\Entity\Guest;
use App\Providers\Booking\Beds24\Entity\InfoItem;
use App\Providers\Booking\Beds24\Entity\InvoiceItem;

class GetBookingsDto
{
    /** @var Booking[] */
    public array $bookings = [];

    public function __construct(
        public readonly bool $success,
        public readonly string $type,
        public readonly ?int $count = null,
        public readonly ?array $pages = null,
        ?array $data = null,
        ...$params,
    ) {
        foreach ($data as $item) {
            if (isset($item['infoItems'])) {
                $item['infoItems'] = array_map(
                    fn(array $props) => $this->createEntity(InfoItem::class, $props),
                    $item['infoItems']
                );
            }
            if (isset($item['invoiceItems'])) {
                $item['invoiceItems'] = array_map(
                    fn(array $props) => $this->createEntity(InvoiceItem::class, $props),
                    $item['invoiceItems']
                );
            }
            if (isset($item['guests'])) {
                $item['guests'] = array_map(
                    fn(array $props) => $this->createEntity(Guest::class, $props),
                    $item['guests']
                );
            }
            $this->bookings[] = new Booking(...$item);
        }
    }

    private function createEntity(string $className, array $props): object
    {
        return new $className(...$props);
    }
}
