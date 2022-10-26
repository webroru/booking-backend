<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\TokenRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TokenRepository::class)]
class Token
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $token = null;

    #[ORM\Column(length: 255)]
    private ?string $refreshToken = null;

    #[ORM\Column]
    private ?int $expiresIn = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(string $token): self
    {
        $this->token = $token;

        return $this;
    }

    public function getRefreshToken(): ?string
    {
        return $this->refreshToken;
    }

    public function setRefreshToken(string $refreshToken): self
    {
        $this->refreshToken = $refreshToken;

        return $this;
    }

    public function getExpiresIn(): ?int
    {
        return $this->expiresIn;
    }

    public function setExpiresIn(int $expiresIn): self
    {
        $this->expiresIn = $expiresIn;

        return $this;
    }
}
