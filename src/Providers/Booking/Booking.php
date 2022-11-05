<?php

declare(strict_types=1);

namespace App\Providers\Booking;

use App\Providers\Booking\Beds24\Client\Client;
use App\Providers\Booking\Beds24\CommonDtoConverter;
use App\Providers\Booking\Beds24\Dto\Request\GetBookingsDto;
use App\Providers\Booking\Beds24\Dto\Request\GetPropertiesDto;
use App\Providers\Booking\Beds24\Dto\Response\GetAuthenticationSetupDto;
use App\Providers\Booking\Beds24\Dto\Response\GetAuthenticationTokenDto;
use App\Providers\Booking\Beds24\Entity\Property;

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
        $getPropertiesDto = $this->buildGetPropertiesDto($beds24BookingsDto->bookings);
        $beds24Properties = $this->client->getProperties($getPropertiesDto);

        foreach ($beds24BookingsDto->bookings as $booking) {
            $beds24Property = $this->findPropertyById($beds24Properties->properties, $booking->propertyId);
            $result[] = $this->converter->convert($booking, $beds24Property);
        }

        return $result;
    }

    /**
     * @param Beds24\Entity\Booking[] $bookings
     * @return GetPropertiesDto
     */
    private function buildGetPropertiesDto(array $bookings): GetPropertiesDto
    {
        $propertyId = [];
        $roomId = [];

        foreach ($bookings as $booking) {
            $roomId[$booking->roomId] = $booking->roomId;
            $propertyId[$booking->propertyId] = $booking->propertyId;
        }

        return new GetPropertiesDto(id: array_values($propertyId), roomId: array_values($roomId));
    }

    /**
     * @param Property[] $properties
     * @param int $id
     * @return Property
     */
    private function findPropertyById(array $properties, int $id): Property
    {
        foreach ($properties as $property) {
            if ($property->id === $id) {
                return $property;
            }
        }

        throw new \Exception("Can't found property $id in the provided list");
    }
}
