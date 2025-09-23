<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\RoomRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RoomRepository::class)]
#[ORM\Table(
    uniqueConstraints: [
        new ORM\UniqueConstraint(
            name: 'unique_client_external_id_unit',
            columns: ['client_id', 'external_id_unit']
        )
    ]
)]
class Room
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Client::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Client $client;

    #[ORM\Column(type: 'string', length: 255)]
    private string $unit;

    #[ORM\Column(length: 255)]
    private int $externalId;

    #[ORM\Column(length: 255, nullable: true)]
    private ?int $governmentPortalId;

    public function __toString(): string
    {
        return $this->unit;
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getUnit(): string
    {
        return $this->unit;
    }

    public function setUnit(string $unit): self
    {
        $this->unit = $unit;
        return $this;
    }

    public function getExternalId(): int
    {
        return $this->externalId;
    }

    public function setExternalId(int $externalId): self
    {
        $this->externalId = $externalId;
        return $this;
    }

    public function getGovernmentPortalId(): ?int
    {
        return $this->governmentPortalId;
    }

    public function setGovernmentPortalId(int $governmentPortalId): self
    {
        $this->governmentPortalId = $governmentPortalId;
        return $this;
    }
}
