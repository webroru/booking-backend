<?php

namespace App\Providers\Booking;

use App\Providers\Booking\Beds24\Client\Client;

class Booking
{
    public function __construct(
        private readonly Client $client,
    ) {
    }

    public function fetchToken(string $code): Beds24\Dto\Response\GetAuthenticationSetupDto
    {
        $this->client->setCode($code);
        return $this->client->getAuthenticationSetup();
    }
}
