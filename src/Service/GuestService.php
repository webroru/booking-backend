<?php

declare(strict_types=1);

namespace App\Service;

use App\Dto\GuestDto;
use App\Entity\Client;
use App\Entity\Guest;
use App\Entity\Room;
use App\Enum\DocumentType;
use App\Enum\Gender;
use App\Repository\GuestRepository;
use App\Repository\RoomRepository;

readonly class GuestService
{
    public function __construct(
        private GuestRepository $guestRepository,
        private RoomRepository $roomRepository,
        private ClientService $clientService,
    ) {
    }

    /**
     * @param GuestDto[] $guestsDto
     * @throws \DateMalformedStringException
     */
    public function updateGuests(
        array $guestsDto,
        int $bookingId,
        string $originalReferer,
        string $propertyName,
        string $roomNumber,
        int $roomExternalId,
        string $checkInDate,
    ): void {
        $client = $this->clientService->getClient();
        $guests = $this->guestRepository->findBy(['bookingId' => $bookingId, 'client' => $client]);
        $removedGuests = $this->getRemovedGuests($guestsDto, $guests);
        $this->removeGuests($removedGuests);
        $room = $this->roomRepository->getByExternalIdAndClient($roomExternalId, $roomNumber, $client);
        $this->addGuests(
            $guestsDto,
            $bookingId,
            $originalReferer,
            $propertyName,
            $checkInDate,
            $room,
            $client,
        );
    }

    /**
     * @param GuestDto[] $guestsDto
     * @param Guest[] $guests
     * @return Guest[]
     */
    private function getRemovedGuests(array $guestsDto, array $guests): array
    {
        $guestsDtoIds = array_map(fn(GuestDto $guestDto) => $guestDto->id, $guestsDto);
        return array_filter($guests, fn($guest) => !in_array($guest->getId(), $guestsDtoIds));
    }

    private function removeGuests(array $guests): void
    {
        foreach ($guests as $guest) {
            $this->guestRepository->remove($guest, true);
        }
    }

    /**
     * @param GuestDto[] $guestsDto
     * @throws \DateMalformedStringException
     */
    private function addGuests(
        array $guestsDto,
        int $bookingId,
        string $originalReferer,
        string $propertyName,
        string $checkInDate,
        Room $room,
        Client $client,
    ): void {
        foreach ($guestsDto as $guestDto) {
            if ($guestDto->id) {
                continue;
            }
            $guest = (new Guest())
                ->setBookingId($bookingId)
                ->setFirstName($guestDto->firstName)
                ->setLastName($guestDto->lastName)
                ->setDocumentNumber($guestDto->documentNumber)
                ->setDocumentType(DocumentType::fromName($guestDto->documentType))
                ->setDateOfBirth(new \DateTimeImmutable($guestDto->dateOfBirth))
                ->setNationality($guestDto->nationality)
                ->setGender(Gender::from($guestDto->gender))
                ->setRegistrationDate(new \DateTimeImmutable())
                ->setCheckInDate(new \DateTimeImmutable($checkInDate))
                ->setCheckOutDate(new \DateTimeImmutable($guestDto->checkOutDate))
                ->setCheckOutTime(new \DateTimeImmutable($guestDto->checkOutTime))
                ->setCityTaxExemption($guestDto->cityTaxExemption)
                ->setReferer($originalReferer)
                ->setPropertyName($propertyName)
                ->setRoom($room)
                ->setClient($client)
            ;
            $this->guestRepository->save($guest, true);
        }
    }
}
