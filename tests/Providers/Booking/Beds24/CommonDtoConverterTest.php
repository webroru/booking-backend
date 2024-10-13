<?php

declare(strict_types=1);

namespace App\Tests\Providers\Booking\Beds24;

use App\Dto\BookingDto;
use App\Providers\Booking\Beds24\CommonDtoConverter;
use App\Providers\Booking\Beds24\Entity\Booking;
use App\Providers\Booking\Beds24\Entity\InfoItem;
use App\Providers\Booking\Beds24\Entity\InvoiceItem;
use App\Providers\Booking\Beds24\Entity\Property;
use App\Repository\PhotoRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CommonDtoConverterTest extends TestCase
{
    private Booking $booking;
    private Property $property;
    private PhotoRepository|MockObject $photoRepositoryMock;

    public function testConvert(): void
    {
        $converter = new CommonDtoConverter($this->photoRepositoryMock);
        $bookingDTO = $converter->convert($this->booking, $this->property, []);
        $this->assertInstanceOf(BookingDto::class, $bookingDTO);
    }

    protected function setUp(): void
    {
        $this->photoRepositoryMock = $this->getMockBuilder(PhotoRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->photoRepositoryMock->expects($this->once())
            ->method('findBy')
            ->willReturn([]);

        $invoiceItems = [new InvoiceItem(10, InvoiceItem::CHARGE, description: 'Test charging')];
        $infoItems = [new InfoItem('foo', '42')];
        $this->booking = new Booking(
            id: 1,
            referer: 'referer',
            roomId: 1,
            unitId: 1,
            arrival: '2000-01-01',
            departure: '2000-01-02',
            firstName: 'Test',
            lastName: 'Test',
            phone: '777',
            invoiceItems: $invoiceItems,
            infoItems: $infoItems,
        );

        $roomType = ['id' => 1, 'name' => 'Test', 'units' => [], 'maxPeople' => 1, 'priceRules' => [['extraPerson' => 10]]];
        $this->property = new Property(1, 'Test', 'Test', [], 'Test', 'Test', 'Test', 'Test', 'Test', 'Test', 1.1, 1.1, 'Test', 'Test', 'Test', 'Test', 'Test', 'Test', 'Test', [], [], [], [], [], [], [], [], [], [$roomType]);
        parent::setUp();
    }
}
