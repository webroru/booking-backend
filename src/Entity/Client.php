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

    #[ORM\OneToOne(inversedBy: 'client', cascade: ['persist', 'remove'])]
    private ?Token $token = null;

    /** @var Collection<int, Info> */
    #[ORM\OneToMany(mappedBy: 'client', targetEntity: Info::class)]
    private Collection $info;

    public function __construct()
    {
        $this->info = new ArrayCollection();
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

    public function getToken(): ?Token
    {
        return $this->token;
    }

    public function setToken(?Token $token): self
    {
        $this->token = $token;

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

    public function __toString(): string
    {
        return $this->name;
    }
}
