<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Client;
use App\Exception\InfoNotFoundException;
use App\Repository\ClientRepository;

class ClientService
{
    public function __construct(
        private readonly ClientRepository $repository,
    ) {
    }

    public function getClientByOrigin(string $origin): Client
    {
        return $this->repository->findOneBy(['domain' => parse_url($origin, PHP_URL_HOST)]) ??
            throw new InfoNotFoundException("Request from $origin is not allowed");
    }
}
