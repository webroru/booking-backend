<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ClientRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: ClientRepository::class)]
#[UniqueEntity('domain')]
#[UniqueEntity('name')]
class Client
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $domain = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $name = null;

    #[ORM\Column(type: 'time')]
    private \DateTimeInterface $checkInTime;

    /** @var Collection<int, Info> */
    #[ORM\OneToMany(mappedBy: 'client', targetEntity: Info::class)]
    private Collection $info;

    /** @var Collection<int, Guest> */
    #[ORM\OneToMany(mappedBy: 'client', targetEntity: Guest::class)]
    private Collection $guests;

    #[ORM\Column(type: 'boolean')]
    private bool $isAutoSend = false;

    #[ORM\ManyToOne(targetEntity: Admin::class, inversedBy: 'clients')]
    private ?Admin $admin = null;

    public function __construct()
    {
        $this->info = new ArrayCollection();
        $this->guests = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDomain(): ?string
    {
        return $this->domain;
    }

    public function setDomain(string $domain): self
    {
        $this->domain = $domain;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getCheckInTime(): \DateTimeInterface
    {
        return $this->checkInTime;
    }

    public function setCheckInTime(\DateTimeInterface $checkInTime): self
    {
        $this->checkInTime = $checkInTime;
        return $this;
    }

    /**
     * @return Collection<int, Info>
     */
    public function getInfo(): Collection
    {
        return $this->info;
    }

    public function addInfo(?Info $info): self
    {
        $this->info[] = $info;
        return $this;
    }

    /**
     * @return Collection<int, Guest>
     */
    public function getGuests(): Collection
    {
        return $this->guests;
    }

    public function addGuest(?Info $guest): self
    {
        if (!$this->guests->contains($guest)) {
            $this->guests[] = $guest;
            $guest->setClient($this);
        }
        return $this;
    }

    public function removeGuest(Guest $guest): void
    {
        $this->guests->removeElement($guest);
    }

    public function __toString(): string
    {
        return $this->name;
    }

    public function isAutoSend(): bool
    {
        return $this->isAutoSend;
    }

    public function setIsAutoSend(bool $isAutoSend): self
    {
        $this->isAutoSend = $isAutoSend;
        return $this;
    }

    public function getAdmin(): ?Admin
    {
        return $this->admin;
    }

    public function setAdmin(?Admin $admin): self
    {
        $this->admin = $admin;
        return $this;
    }
}
