<?php

declare(strict_types=1);

namespace App\Providers\Booking;

use App\Providers\Booking\Beds24\Client\Client;
use App\Providers\Booking\Beds24\CommonDtoConverter;
use App\Providers\Booking\Beds24\Dto\Request\GetBookingsDto;
use App\Providers\Booking\Beds24\Dto\Request\GetPropertiesDto;
use App\Providers\Booking\Beds24\Dto\Request\PostBookingsDto;
use App\Providers\Booking\Beds24\Dto\Response\GetAuthenticationSetupDto;
use App\Providers\Booking\Beds24\Dto\Response\GetAuthenticationTokenDto;
use App\Providers\Booking\Beds24\Entity\InfoItem;
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

    public function findById(int $id): \App\Dto\Booking
    {
        $today = (new \DateTime('- 10 days'))->format('Y-m-d');
        $lastDay = (new \DateTime('+ 10 days'))->format('Y-m-d');
        $filter['arrivalFrom'] = $today;
        $filter['arrivalTo'] = $lastDay;
        $filter['includeInvoiceItems'] = true;
        $filter['includeInfoItems'] = true;
        $filter['id'] = [$id];
        $bookings = $this->findBy($filter);

        if (count($bookings) === 0) {
            throw new \Exception("Booking id $id i not found");
        }

        return $bookings[0];
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
        $bookings = $beds24BookingsDto->bookings;
        if (isset($filter['surname'])) {
            $bookings = $this->filterBySurname($bookings, $filter['surname']);
        }

        if (isset($filter['originalReferer'])) {
            $bookings = $this->filterByReferer($bookings, $filter['originalReferer']);
        }
        $getPropertiesDto = $this->buildGetPropertiesDto($bookings);
        $beds24Properties = $this->client->getProperties($getPropertiesDto);

        foreach ($bookings as $booking) {
            $beds24Property = $this->findPropertyById($beds24Properties->properties, $booking->propertyId);
            $result[] = $this->converter->convert($booking, $beds24Property);
        }

        return $result;
    }

    public function acceptRule(int $bookingId, bool $isRuleAccepted): void
    {
        $dto = new GetBookingsDto(id: [$bookingId], includeInfoItems: true);
        $beds24BookingsDto = $this->client->getBookings($dto);
        if (!isset($beds24BookingsDto->bookings[0])) {
            throw new \Exception("Booking id $bookingId is not found");
        }
        $booking = $beds24BookingsDto->bookings[0];
        $infoItem = $this->findInfoItemByCode($booking->infoItems, 'isRuleAccepted');
        if (!$infoItem) {
            $infoItem = new InfoItem(code: 'isRuleAccepted', text: (string) $isRuleAccepted);
            $booking->infoItems[] = $infoItem;
        } else {
            $infoItem->text = (string) $isRuleAccepted;
        }
        $postBookingsDto = new PostBookingsDto([$booking]);
        $postBookingsResponseDto = $this->client->postBookings($postBookingsDto);
        foreach ($postBookingsResponseDto->result as $item) {
            if ($item['success'] !== true) {
                $message = "Can not update Booking {$booking->id}.";
                if ($item['errors']) {
                    $error = implode(', ', $item['errors']);
                    $message .= " Details: $error";
                }
                throw new \Exception($message);
            }
        }
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

    /**
     * @param Beds24\Entity\Booking[] $bookings
     * @param string $surname
     * @return Beds24\Entity\Booking[]
     */
    private function filterBySurname(array $bookings, string $surname): array
    {
        $result = [];
        foreach ($bookings as $booking) {
            if (mb_strtolower($booking->lastName) === mb_strtolower($surname)) {
                $result[] = $booking;
            }
        }

        return $result;
    }

    /**
     * @param Beds24\Entity\Booking[] $bookings
     * @param string $referer
     * @return Beds24\Entity\Booking[]
     */
    private function filterByReferer(array $bookings, string $referer): array
    {
        $result = [];
        foreach ($bookings as $booking) {
            if ($booking->apiReference === $referer) {
                $result[] = $booking;
            }
        }

        return $result;
    }

    /**
     * @param InfoItem[] $infoItems
     */
    private function findInfoItemByCode(array $infoItems, string $code): ?InfoItem
    {
        foreach ($infoItems as $infoItem) {
            if ($infoItem->code === $code) {
                return $infoItem;
            }
        }

        return null;
    }
}
