<?php

declare(strict_types=1);

namespace App\Tests\Providers\Booking\Beds24\Transformer;

use App\Dto\BookingDto;
use App\Providers\Booking\Beds24\Entity\Booking;
use App\Providers\Booking\Beds24\Entity\InfoItem;
use App\Providers\Booking\Beds24\Entity\InvoiceItem;
use App\Providers\Booking\Beds24\Entity\Property;
use App\Providers\Booking\Beds24\Service\InfoItemService;
use App\Providers\Booking\Beds24\Service\InvoiceItemService;
use App\Providers\Booking\Beds24\Transformer\BookingEntityToDtoTransformer;
use App\Repository\GuestRepository;
use App\Repository\PhotoRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class BookingEntityToDtoTransformerTest extends TestCase
{
    private Booking $booking;
    private Property $property;
    private PhotoRepository|MockObject $photoRepositoryMock;
    private InfoItemService|MockObject $infoItemServiceMock;
    private InvoiceItemService|MockObject $invoiceItemServiceMock;
    private GuestRepository|MockObject $guestRepositoryMock;

    public function testConvert(): void
    {
        $converter = new BookingEntityToDtoTransformer(
            $this->photoRepositoryMock,
            $this->guestRepositoryMock,
            $this->infoItemServiceMock,
            $this->invoiceItemServiceMock,
        );

        $bookingDTO = $converter->transform($this->booking, $this->property, []);
        $this->assertInstanceOf(BookingDto::class, $bookingDTO);
    }

    protected function setUp(): void
    {
        $this->photoRepositoryMock = $this->getMockBuilder(PhotoRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->guestRepositoryMock = $this->getMockBuilder(GuestRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->infoItemServiceMock = $this->getMockBuilder(InfoItemService::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->invoiceItemServiceMock = $this->getMockBuilder(InvoiceItemService::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->photoRepositoryMock->expects($this->once())
            ->method('findBy')
            ->willReturn([]);

        $this->guestRepositoryMock->expects($this->once())
            ->method('findBy')
            ->willReturn([]);

        $this->infoItemServiceMock->expects($this->any())
            ->method('getInfoItemValue')
            ->willReturn('Test InfoItem Value');

        $this->invoiceItemServiceMock->expects($this->once())
            ->method('getDebt')
            ->willReturn(0.0);

        $invoiceItems = [new InvoiceItem(
            amount: 1.1,
            type: InvoiceItem::CHARGE,
            id: 1,
            bookingId: 1,
            invoiceId: 1,
            description: 'Test',
            status: 'Test',
            qty: 1,
            lineTotal: 1.1,
            vatRate: 1.1,
            createdBy: 1,
            createTime: '2000-01-01',
            invoiceDate: '2000-01-01',
        )];
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

        $roomType = [
            'id' => 1,
            'name' => 'Test',
            'units' => [
                ['id' => 1, 'name' => 'Unit 1'],
                ['id' => 2, 'name' => 'Unit 2']
            ],
            'maxPeople' => 1,
            'priceRules' => [['extraPerson' => 10]]
        ];
        $this->property = new Property(1, 'Test', 'Test', [], 'Test', 'Test', 'Test', 'Test', 'Test', 'Test', 1.1, 1.1, 'Test', 'Test', 'Test', 'Test', 'Test', 'Test', 'Test', [], [], [], [], [], [], [], [], [], [$roomType]);
        parent::setUp();
    }
}
