<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\InfoRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints;

#[ORM\Entity(repositoryClass: InfoRepository::class)]
class Info
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Constraints\Length(max: 255)]
    #[Constraints\NotBlank()]
    private ?string $hotelName = null;

    #[ORM\Column(length: 1024)]
    #[Constraints\Length(max: 1024)]
    private ?string $address = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $rules = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $checkoutInfo = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Constraints\Length(max: 255)]
    private ?string $callTime = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Constraints\Length(max: 255)]
    private ?string $phoneNumber = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $howToMakeIt = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $facilities = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $extras = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $instruction = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $cashPaymentInstruction = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getHotelName(): ?string
    {
        return $this->hotelName;
    }

    public function setHotelName(string $hotelName): self
    {
        $this->hotelName = $hotelName;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getRules(): ?string
    {
        return $this->rules;
    }

    public function setRules(?string $rules): self
    {
        $this->rules = $rules;

        return $this;
    }

    public function getCheckoutInfo(): ?string
    {
        return $this->checkoutInfo;
    }

    public function setCheckoutInfo(?string $checkoutInfo): self
    {
        $this->checkoutInfo = $checkoutInfo;

        return $this;
    }

    public function getCallTime(): ?string
    {
        return $this->callTime;
    }

    public function setCallTime(?string $callTime): self
    {
        $this->callTime = $callTime;

        return $this;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(?string $phoneNumber): self
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }

    public function getHowToMakeIt(): ?string
    {
        return $this->howToMakeIt;
    }

    public function setHowToMakeIt(?string $howToMakeIt): self
    {
        $this->howToMakeIt = $howToMakeIt;

        return $this;
    }

    public function getFacilities(): ?string
    {
        return $this->facilities;
    }

    public function setFacilities(?string $facilities): self
    {
        $this->facilities = $facilities;

        return $this;
    }

    public function getExtras(): ?string
    {
        return $this->extras;
    }

    public function setExtras(?string $extras): self
    {
        $this->extras = $extras;

        return $this;
    }

    public function getInstruction(): ?string
    {
        return $this->instruction;
    }

    public function setInstruction(?string $instruction): self
    {
        $this->instruction = $instruction;

        return $this;
    }

    public function getCashPaymentInstruction(): ?string
    {
        return $this->cashPaymentInstruction;
    }

    public function setCashPaymentInstruction(?string $cashPaymentInstruction): self
    {
        $this->cashPaymentInstruction = $cashPaymentInstruction;
        return $this;
    }
}
