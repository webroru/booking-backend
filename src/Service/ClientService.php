<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Client;
use App\Exception\ClientNotFoundException;
use App\Repository\ClientRepository;

class ClientService
{
    public function __construct(
        private readonly ClientRepository $clientRepository,
        private ?Client $client,
    ) {
    }

    public function getClientByOrigin(string $origin): Client
    {
        return $this->clientRepository->findOneBy(['domain' => parse_url($origin, PHP_URL_HOST)]) ??
            throw new ClientNotFoundException("Request from $origin is not allowed");
    }

    public function getClientByName(string $name): Client
    {
        return $this->clientRepository->findOneBy(['name' => $name]) ??
            throw new ClientNotFoundException("Request from $name is not allowed");
    }

    public function setClientByName(string $name): void
    {
        $this->client = $this->getClientByName($name);
    }

    public function setClientByOrigin(string $origin): void
    {
        $this->client = $this->getClientByOrigin($origin);
    }

    public function getClient(): Client
    {
        return $this->client;
    }
}
