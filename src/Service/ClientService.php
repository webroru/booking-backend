<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Client;
use App\Entity\Token;
use App\Exception\ClientNotFoundException;
use App\Exception\TokenNotFoundException;
use App\Providers\Booking\BookingInterface;
use App\Repository\ClientRepository;
use App\Repository\TokenRepository;

class ClientService
{
    public function __construct(
        private readonly ClientRepository $clientRepository,
        private readonly BookingInterface $booking,
        private readonly TokenRepository $tokenRepository,
    ) {
    }

    public function getClientByOrigin(string $origin): Client
    {
        return $this->clientRepository->findOneBy(['domain' => parse_url($origin, PHP_URL_HOST)]) ??
            throw new ClientNotFoundException("Request from $origin is not allowed");
    }

    public function getClientByName(string $name): Client
    {
        return $this->clientRepository->findOneBy(['name' => parse_url($name, PHP_URL_HOST)]) ??
            throw new ClientNotFoundException("Request from $name is not allowed");
    }

    public function getTokenByOrigin(string $origin): Token
    {
        $token = $this->getClientByOrigin($origin)->getToken() ??
            throw new TokenNotFoundException("Token in not found for $origin");

        if ($token->getExpiresAt() <= (new \DateTime())) {
            $this->refreshToken($token);
        }

        return $token;
    }

    public function getTokenByName(string $name): Token
    {
        $token = $this->getClientByName($name)->getToken() ??
            throw new TokenNotFoundException("Token in not found for $name");

        if ($token->getExpiresAt() <= (new \DateTime())) {
            $this->refreshToken($token);
        }

        return $token;
    }

    public function refreshToken(Token $token): void
    {
        $tokenDto = $this->booking->refreshToken($token->getRefreshToken());
        $token
            ->setToken($tokenDto->token)
            ->setExpiresAt(new \DateTime("+ $tokenDto->expiresIn seconds"))
        ;
        $this->tokenRepository->save($token, true);
    }
}
