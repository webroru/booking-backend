<?php

declare(strict_types=1);

namespace App\Service;

use App\Dto\GuestDto;
use App\Entity\Guest;
use App\Enum\DocumentType;
use App\Enum\Gender;
use App\Repository\GuestRepository;

readonly class GuestService
{
    public function __construct(private GuestRepository $guestRepository)
    {
    }

    /**
     * @param GuestDto[] $guestsDto
     * @param int $bookingId
     * @return void
     */
    public function updateGuests(array $guestsDto, int $bookingId): void
    {
        $guests = $this->guestRepository->findBy(['bookingId' => $bookingId]);
        $removedGuests = $this->getRemovedGuests($guestsDto, $guests);
        $this->removeGuests($removedGuests);
        $this->addGuests($guestsDto, $bookingId);
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
     * @return void
     */
    private function addGuests(array $guestsDto, int $bookingId): void
    {
        foreach ($guestsDto as $guestDto) {
            if ($guestDto->id) {
                continue;
            }
            $guest = (new Guest())
                ->setBookingId($bookingId)
                ->setFirstName($guestDto->firstName)
                ->setLastName($guestDto->lastName)
                ->setDocumentNumber($guestDto->documentNumber)
                ->setDocumentType(DocumentType::from($guestDto->documentType))
                ->setDateOfBirth(new \DateTimeImmutable($guestDto->dateOfBirth))
                ->setNationality($guestDto->nationality)
                ->setGender(Gender::from($guestDto->gender))
                ->setCheckOutDate(new \DateTimeImmutable($guestDto->checkOutDate))
                ->setCheckOutTime(new \DateTimeImmutable($guestDto->checkOutTime))
                ->setCityTaxExemption($guestDto->cityTaxExemption)
            ;
            $this->guestRepository->save($guest, true);
        }
    }
}
