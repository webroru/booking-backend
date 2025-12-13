<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Entity\Client;
use App\Entity\Guest;
use App\Entity\Room;
use App\Enum\DocumentType;
use App\Enum\Gender;
use App\Repository\GuestRepository;
use App\Service\CityTaxCalculatorService;
use App\Service\GuestReportService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use SoapClient;

class GuestReportServiceTest extends TestCase
{
    private CityTaxCalculatorService|MockObject $cityTaxCalculatorService;
    private GuestRepository|MockObject $guestRepository;
    private SoapClient|MockObject $client;
    private LoggerInterface|MockObject $logger;
    private Guest $guest;

    public function testReportGuests(): void
    {
        $guestReportService = new GuestReportService(
            $this->cityTaxCalculatorService,
            $this->guestRepository,
            $this->client,
            $this->logger,
        );

        $guestReportService->reportGuests([$this->guest], 'testUser', 'testPassword');
    }

    protected function setUp(): void
    {
        $this->cityTaxCalculatorService = $this->createMock(CityTaxCalculatorService::class);
        $this->guestRepository = $this->createMock(GuestRepository::class);
        $this->client = $this->createMock(SoapClient::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $data = new \stdClass();
        $data->oddajPorociloResult = json_encode([]);

        $this->client->expects($this->once())
            ->method('__soapCall')
            ->willReturn($data);

        $this->guest = (new Guest())
            ->setBookingId(1)
            ->setFirstName('John')
            ->setLastName('Doe')
            ->setDocumentNumber('123456789')
            ->setDocumentType(DocumentType::fromName('Passport'))
            ->setDateOfBirth(new \DateTimeImmutable('1990-01-01'))
            ->setNationality('US')
            ->setGender(Gender::from('M'))
            ->setRegistrationDate(new \DateTimeImmutable())
            ->setCheckInDate(new \DateTimeImmutable('2024-01-01'))
            ->setCheckInTime(new \DateTimeImmutable('12:00:00'))
            ->setCheckOutDate(new \DateTimeImmutable('2024-01-05'))
            ->setCheckOutTime(new \DateTimeImmutable('12:00:00'))
            ->setCityTaxExemption(0)
            ->setReferer('https://example.com')
            ->setPropertyName('Example Property')
            ->setRoom((new Room())->setGovernmentPortalId(666))
            ->setClient((new Client())->setCheckInTime(new \DateTimeImmutable('12:00:00')))
        ;

        parent::setUp();
    }
}
