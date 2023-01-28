<?php

declare(strict_types=1);

namespace App\Tests\Providers\Booking\Beds24;

use App\Providers\Booking\Beds24\CommonDtoConverter;
use App\Providers\Booking\Beds24\Entity\Booking;
use App\Providers\Booking\Beds24\Entity\InfoItem;
use App\Providers\Booking\Beds24\Entity\InvoiceItem;
use App\Providers\Booking\Beds24\Entity\Property;
use PHPUnit\Framework\TestCase;

class CommonDtoConverterTest extends TestCase
{
    private Booking $booking;
    private Property $property;

    public function setUp(): void
    {
        $invoiceItems = [new InvoiceItem(10, InvoiceItem::CHARGE, description: 'Test charging')];
        $infoItems = [new InfoItem('foo', '42')];
        $this->booking = new Booking(
            id: 1,
            referer: 'referer',
            arrival: '2000-01-01',
            departure: '2000-01-02',
            firstName: 'Test',
            lastName: 'Test',
            phone: '777',
            invoiceItems: $invoiceItems,
            infoItems: $infoItems,
        );
        $this->property = new Property(1, 'Test', 'Test', [], 'Test', 'Test', 'Test', 'Test', 'Test', 'Test', 1.1, 1.1, 'Test', 'Test', 'Test', 'Test', 'Test', 'Test', 'Test', [], [], [], [], [], [], [], [], []);
        parent::setUp();
    }

    public function testConvert(): void
    {
        $converter = new CommonDtoConverter();
        $bookingDTO = $converter->convert($this->booking, $this->property);
        $this->assertInstanceOf(\App\Dto\Booking::class, $bookingDTO);
    }
}
