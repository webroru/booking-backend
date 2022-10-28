<?php

namespace App\Providers\Booking;

use App\Providers\Booking\Beds24\Client\Client;
use App\Providers\Booking\Beds24\CommonDtoConverter;
use App\Providers\Booking\Beds24\Dto\Request\GetBookingsDto;
use App\Providers\Booking\Beds24\Dto\Response\GetAuthenticationSetupDto;
use App\Providers\Booking\Beds24\Dto\Response\GetAuthenticationTokenDto;

class Booking
{
    public function __construct(
        private readonly Client $client,
        private readonly CommonDtoConverter $converter,
    ) {
    }

    public function setToken(string $token): void
    {
        $this->client->setToken($token);
    }

    public function fetchToken(string $code): GetAuthenticationSetupDto
    {
        $this->client->setCode($code);
        return $this->client->getAuthenticationSetup();
    }

    public function refreshToken(string $refreshToken): GetAuthenticationTokenDto
    {
        $this->client->setRefreshToken($refreshToken);
        return $this->client->getAuthenticationToken();
    }

    /**
     * @param array $filter
     * @return \App\Dto\Booking[]
     */
    public function findBy(array $filter): array
    {
        $dto = new GetBookingsDto(...$filter);
        $beds24BookingsDto = $this->client->getBookings($dto);
        $result = [];
        foreach ($beds24BookingsDto->bookings as $booking) {
            $result[] = $this->converter->convert($booking);
        }

        return $result;
    }
}
