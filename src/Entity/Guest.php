<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\DocumentType;
use App\Enum\Gender;
use App\Repository\GuestRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints;

#[ORM\Entity(repositoryClass: GuestRepository::class)]
class Guest
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'integer', length: 16)]
    private int $bookingId;

    #[ORM\Column(length: 255)]
    private string $firstName;

    #[ORM\Column(length: 255)]
    private string $lastName;

    #[ORM\Column(length: 255)]
    private string $documentNumber;

    #[ORM\Column(enumType: DocumentType::class)]
    private DocumentType $documentType;

    #[ORM\Column(type: 'date')]
    private \DateTimeInterface $dateOfBirth;

    #[ORM\Column(length: 2)]
    #[Constraints\Length(max: 2)]
    private string $nationality;

    #[ORM\Column(enumType: Gender::class)]
    private Gender $gender;

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $registrationDate;

    #[ORM\Column(type: 'date')]
    private \DateTimeInterface $checkInDate;

    #[ORM\Column(type: 'date')]
    private \DateTimeInterface $checkOutDate;

    #[ORM\Column(type: 'time')]
    private \DateTimeInterface $checkOutTime;

    #[ORM\Column(length: 2)]
    private int $cityTaxExemption;

    #[ORM\Column(length: 255)]
    private string $referer;

    #[ORM\Column(length: 255)]
    private string $propertyName;

    #[ORM\Column(length: 255)]
    private string $room;

    #[ORM\Column(type: 'boolean')]
    private bool $isReported = false;

    #[ORM\ManyToOne(inversedBy: 'guests')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Client $client;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBookingId(): int
    {
        return $this->bookingId;
    }

    public function setBookingId(int $bookingId): self
    {
        $this->bookingId = $bookingId;
        return $this;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;
        return $this;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;
        return $this;
    }

    public function getDocumentNumber(): string
    {
        return $this->documentNumber;
    }

    public function setDocumentNumber(string $documentNumber): self
    {
        $this->documentNumber = $documentNumber;
        return $this;
    }

    public function getDocumentType(): DocumentType
    {
        return $this->documentType;
    }

    public function setDocumentType(DocumentType $documentType): self
    {
        $this->documentType = $documentType;
        return $this;
    }

    public function getDateOfBirth(): \DateTimeInterface
    {
        return $this->dateOfBirth;
    }

    public function setDateOfBirth(\DateTimeInterface $dateOfBirth): self
    {
        $this->dateOfBirth = $dateOfBirth;
        return $this;
    }

    public function getNationality(): string
    {
        return $this->nationality;
    }

    public function setNationality(string $nationality): self
    {
        $this->nationality = $nationality;
        return $this;
    }

    public function getGender(): Gender
    {
        return $this->gender;
    }

    public function setGender(Gender $gender): self
    {
        $this->gender = $gender;
        return $this;
    }

    public function getRegistrationDate(): \DateTimeInterface
    {
        return $this->registrationDate;
    }

    public function setRegistrationDate(\DateTimeInterface $registrationDate): self
    {
        $this->registrationDate = $registrationDate;
        return $this;
    }

    public function getCheckInDate(): \DateTimeInterface
    {
        return $this->checkInDate;
    }

    public function setCheckInDate(\DateTimeInterface $checkInDate): self
    {
        $this->checkInDate = $checkInDate;
        return $this;
    }

    public function getCheckOutDate(): \DateTimeInterface
    {
        return $this->checkOutDate;
    }

    public function setCheckOutDate(\DateTimeInterface $checkOutDate): self
    {
        $this->checkOutDate = $checkOutDate;
        return $this;
    }

    public function getCheckOutTime(): \DateTimeInterface
    {
        return $this->checkOutTime;
    }

    public function setCheckOutTime(\DateTimeInterface $checkOutTime): self
    {
        $this->checkOutTime = $checkOutTime;
        return $this;
    }

    public function getCityTaxExemption(): int
    {
        return $this->cityTaxExemption;
    }

    public function setCityTaxExemption(int $cityTaxExemption): self
    {
        $this->cityTaxExemption = $cityTaxExemption;
        return $this;
    }

    public function getReferer(): string
    {
        return $this->referer;
    }

    public function setReferer(string $referer): self
    {
        $this->referer = $referer;
        return $this;
    }

    public function getPropertyName(): string
    {
        return $this->propertyName;
    }

    public function setPropertyName(string $propertyName): self
    {
        $this->propertyName = $propertyName;
        return $this;
    }

    public function getRoom(): string
    {
        return $this->room;
    }

    public function setRoom(string $room): self
    {
        $this->room = $room;
        return $this;
    }

    public function isReported(): bool
    {
        return $this->isReported;
    }

    public function setIsReported(bool $isReported): self
    {
        $this->isReported = $isReported;
        return $this;
    }

    public function getClient(): Client
    {
        return $this->client;
    }

    public function setClient(Client $client): self
    {
        $this->client = $client;
        return $this;
    }
}
