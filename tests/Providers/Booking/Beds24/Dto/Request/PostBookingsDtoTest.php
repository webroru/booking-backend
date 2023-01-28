<?php

namespace App\Tests\Providers\Booking\Beds24\Dto\Request;

use App\Providers\Booking\Beds24\Dto\Request\PostBookingsDto;
use App\Providers\Booking\Beds24\Entity\Booking;
use App\Providers\Booking\Beds24\Entity\InfoItem;
use App\Providers\Booking\Beds24\Entity\InvoiceItem;
use PHPUnit\Framework\TestCase;

class PostBookingsDtoTest extends TestCase
{
    private PostBookingsDto $bookingsDto;

    public function setUp(): void
    {
        $invoiceItems = [new InvoiceItem(10, InvoiceItem::CHARGE, description: 'Test charging')];
        $infoItems = [new InfoItem('foo', '42')];
        $bookings = [new Booking(id: 1, invoiceItems: $invoiceItems, infoItems: $infoItems)];
        $this->bookingsDto = new PostBookingsDto($bookings);
        parent::setUp();
    }

    public function testToArray(): void
    {
        $data = $this->bookingsDto->toArray();
        $this->assertEquals(1, $data[0]['id']);
        $this->assertEquals(10, $data[0]['invoiceItems'][0]['amount']);
        $this->assertEquals('charge', $data[0]['invoiceItems'][0]['type']);
        $this->assertEquals('Test charging', $data[0]['invoiceItems'][0]['description']);
    }
}
