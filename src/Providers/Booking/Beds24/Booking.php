<?php

declare(strict_types=1);

namespace App\Providers\Booking\Beds24;

use App\Dto\BookingDto;
use App\Dto\GuestDto;
use App\Providers\Booking\Beds24\Client\Client;
use App\Providers\Booking\Beds24\Dto\Request\GetBookingsDto;
use App\Providers\Booking\Beds24\Dto\Request\GetPropertiesDto;
use App\Providers\Booking\Beds24\Dto\Request\PostBookingsDto;
use App\Providers\Booking\Beds24\Dto\Response\GetAuthenticationSetupDto;
use App\Providers\Booking\Beds24\Entity\InfoItem;
use App\Providers\Booking\Beds24\Entity\InvoiceItem;
use App\Providers\Booking\Beds24\Entity\Property;
use App\Providers\Booking\Beds24\Service\GuestService;
use App\Providers\Booking\Beds24\Service\InfoItemService;
use App\Providers\Booking\Beds24\Transformer\BookingEntityToDtoTransformer;
use App\Providers\Booking\BookingInterface;

readonly class Booking implements BookingInterface
{
    public function __construct(
        private Client $client,
        private BookingEntityToDtoTransformer $transformer,
        private InfoItemService $infoItemService,
        private GuestService $guestService,
    ) {
    }

    public function fetchToken(string $code): GetAuthenticationSetupDto
    {
        $this->client->setCode($code);
        return $this->client->getAuthenticationSetup();
    }

    /**
     * @throws \Exception
     */
    public function findById(int $id): BookingDto
    {
        $departureFrom = (new \DateTime())->format('Y-m-d');
        $arrivalTo = (new \DateTime('+3 days'))->format('Y-m-d');
        $filter['departureFrom'] = $departureFrom;
        $filter['arrivalTo'] = $arrivalTo;
        $filter['includeInvoiceItems'] = true;
        $filter['includeInfoItems'] = true;
        $filter = [
            'id' => [$id],
            ...$this->getDefaultFilter(),
        ];
        $bookings = $this->findBy($filter);

        if (count($bookings) === 0) {
            throw new \Exception("Booking id $id i not found");
        }

        return $bookings[0];
    }

    /**
     * @param array $filter
     * @return BookingDto[]
     * @throws \Exception
     */
    public function findBy(array $filter): array
    {
        $filter = [
            ...$this->getDefaultFilter(),
            ...$filter,
        ];
        $searchString = null;
        if (isset($filter['searchString'])) {
            $searchString = $filter['searchString'];
            unset($filter['searchString']);
        }

        $bookings = $this->getBookings($filter);

        if ($searchString) {
            $bookings = $this->filterByLastNameAndBookingId($bookings, $searchString);
        }

        $getPropertiesDto = $this->buildGetPropertiesDto($bookings);
        $beds24Properties = $this->client->getProperties($getPropertiesDto);
        $groups = array_filter(array_map(fn(Entity\Booking $booking) => $booking->masterId, $bookings));

        $result = [];
        foreach ($bookings as $booking) {
            $beds24Property = $this->findPropertyById($beds24Properties->properties, $booking->propertyId);
            $result[] = $this->transformer->transform($booking, $beds24Property, $groups);
        }

        return $result;
    }

    /**
     * @throws \Exception
     */
    public function setPaidStatus(int $bookingId, string $paymentStatus): void
    {
        $booking = $this->getBookingEntityById($bookingId);
        $this->infoItemService->updateInfoItem($booking, (new InfoItem('paymentStatus', $paymentStatus)));
        $this->infoItemService->updateInfoItem($booking, (new InfoItem('checkIn', $paymentStatus !== '' ? 'true' : 'false')));
        $postBookingsDto = new PostBookingsDto([$booking]);
        $this->update($postBookingsDto);
    }

    /**
     * @throws \Exception
     */
    public function addInvoice(int $id, string $type, float $amount, string $description = ''): void
    {
        $invoiceItems = [new InvoiceItem(amount: $amount, type: $type, description: $description)];
        $booking = new Entity\Booking(id: $id, invoiceItems: $invoiceItems);
        $postBookingsDto = new PostBookingsDto([$booking]);
        $this->update($postBookingsDto);
    }

    /**
     * @throws \Exception
     */
    public function sendMessage(int $bookingId, string $text): void
    {
        $booking = $this->getBookingEntityById($bookingId);
        $booking->message = $text;
        $postBookingsDto = new PostBookingsDto([$booking]);
        $this->update($postBookingsDto);
    }

    /**
     * @throws \Exception
     */
    public function updateBooking(BookingDto $bookingDto): void
    {
        $booking = $this->getBookingEntityById($bookingDto->orderId);
        $overmax = $this->occupancy($bookingDto) > $bookingDto->capacity + 2 ? $this->occupancy($bookingDto) : 0;
        $plusGuest =  $this->occupancy($bookingDto) > $booking->numAdult + $booking->numChild;
        $lessDocs = $this->confirmedGuests($bookingDto) < $booking->numAdult + $booking->numChild;

        $infoItems = [];
        $infoItems[] = new InfoItem('checkIn', $bookingDto->checkIn ? 'true' : 'false');
        $infoItems[] = new InfoItem('paymentStatus', $bookingDto->paymentStatus);
        $infoItems[] = new InfoItem('isRuleAccepted', $bookingDto->isRuleAccepted ? 'true' : 'false');
        $infoItems[] = new InfoItem('checkOut', $bookingDto->checkOut ? 'true' : 'false');
        $infoItems[] = new InfoItem('overmax', (string) $overmax);
        $infoItems[] = new InfoItem('plusGuest', $plusGuest ? 'true' : null);
        $infoItems[] = new InfoItem('lessDocs', $lessDocs ? 'true' : 'false');
        foreach ($infoItems as $infoItem) {
            $this->infoItemService->updateInfoItem($booking, $infoItem);
        }

        $this->guestService->overwriteGuests($booking, $bookingDto);

        $this->updateCityTax($booking, $bookingDto);
        $this->updateExtraGuestInvoice($booking, $bookingDto);

        if ($bookingDto->paymentStatus === 'disagree') {
            $booking->status = 'cancelled';
        }

        $postBookingsDto = new PostBookingsDto([$booking]);
        $this->update($postBookingsDto);
    }

    /**
     * @throws \Exception
     */
    private function update(PostBookingsDto $postBookingsDto): void
    {
        $postBookingsResponseDto = $this->client->postBookings($postBookingsDto);
        foreach ($postBookingsResponseDto->result as $item) {
            if ($item['success'] === true) {
                continue;
            }

            $message = "Can not update Booking";
            if ($item['errors']) {
                foreach ($item['errors'] as $error) {
                    $message .= " Field {$error['field']}: {$error['message']}";
                }
            }
            throw new \Exception($message);
        }
    }

    /**
     * @param Entity\Booking[] $bookings
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

        return new GetPropertiesDto(
            id: array_values($propertyId),
            includePriceRules: true,
            roomId: array_values($roomId),
        );
    }

    /**
     * @param Property[] $properties
     * @param int $id
     * @return Property
     * @throws \Exception
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
     * @param Entity\Booking[] $bookings
     * @param string $search
     * @return Entity\Booking[]
     */
    private function filterByLastNameAndBookingId(array $bookings, string $search): array
    {
        $result = [];
        $transliterator = \Transliterator::create("Any-Latin; Latin-ASCII; Lower()");
        $search = $transliterator->transliterate($search);
        foreach ($bookings as $booking) {
            $name = $transliterator->transliterate($booking->lastName);
            $searchItems = explode(' ', $search);
            $nameItems = explode(' ', $name);
            $lastNameMatch = count(array_intersect($searchItems, $nameItems)) > 0;
            $apiReferenceContain = str_contains($booking->apiReference, $search);
            $bookingIdContains = $booking->id === (int) $search;
            $masterId = $booking->masterId === (int) $search;
            if ($lastNameMatch || $apiReferenceContain || $bookingIdContains || $masterId) {
                $result[] = $booking;
            }
        }

        $groups = array_filter(array_map(fn(Entity\Booking $booking) => $booking->masterId, $result));
        foreach ($bookings as $booking) {
            if (in_array($booking->id, $groups) && !in_array($booking, $result)) {
                $result[] = $booking;
            }
        }

        return $result;
    }

    /**
     * @param InvoiceItem[] $invoiceItems
     */
    private function findInvoiceItemByDescription(array $invoiceItems, string $description): ?InvoiceItem
    {
        foreach ($invoiceItems as $invoiceItem) {
            if ($invoiceItem->description === $description) {
                return $invoiceItem;
            }
        }

        return null;
    }

    /**
     * @param InvoiceItem[] $invoiceItems
     */
    private function findEmptyInvoiceItem(array $invoiceItems): ?InvoiceItem
    {
        foreach ($invoiceItems as $invoiceItem) {
            if ($invoiceItem->qty === 0 && $invoiceItem->description === '') {
                return $invoiceItem;
            }
        }

        return null;
    }

    /**
     * @param InfoItem[] $infoItems
     */
    private function findInfoItemByValue(array $infoItems, string $value): ?InfoItem
    {
        foreach ($infoItems as $infoItem) {
            if ($infoItem->text === $value) {
                return $infoItem;
            }
        }

        return null;
    }

    private function updateInvoiceItem(Entity\Booking $booking, InvoiceItem $invoiceItem): void
    {
        $items = $booking->invoiceItems;
        $existedInvoiceItem = $this->findInvoiceItemByDescription($items, $invoiceItem->description) ??
            $this->findEmptyInvoiceItem($items);

        if ($existedInvoiceItem) {
            $existedInvoiceItem->qty = $invoiceItem->qty;
            $existedInvoiceItem->amount = $invoiceItem->amount;
            $existedInvoiceItem->description = $invoiceItem->description;
            $existedInvoiceItem->type = $invoiceItem->type;
        } elseif ($invoiceItem->qty > 0) {
            $booking->invoiceItems[] = $invoiceItem;
        }
    }

    private function removeCityTaxInvoices(Entity\Booking $booking): void
    {
        /** @var InvoiceItem $item */
        foreach ($booking->invoiceItems as $item) {
            if (
                stripos($item->description, 'city tax') === false
                && stripos($item->description, 'Городской налог') === false
            ) {
                continue;
            }
            $item->qty = 0;
            $item->description = '';
            $item->amount = 0;
        }
    }

    private function removeExtraGuestInvoice(Entity\Booking $booking): void
    {
        /** @var InvoiceItem $item */
        foreach ($booking->invoiceItems as $item) {
            if (stripos($item->description, 'extra guest') === false) {
                continue;
            }
            $item->qty = 0;
            $item->description = '';
            $item->amount = 0;
        }
    }

    /**
     * @throws \Exception
     */
    private function addCityTaxInvoices(Entity\Booking $booking, BookingDto $bookingDto): void
    {
        $guestsAgeCategories = $this->getGuestsAgeCategories($bookingDto);

        foreach ($guestsAgeCategories as $category) {
            if (!$category['quantity']) {
                continue;
            }
            $arrival = new \DateTime($booking->arrival);
            $departure = new \DateTime($booking->departure);
            $nights = $arrival->diff($departure)->d;
            $qty = $nights * $category['quantity'];

            $invoiceItem = new InvoiceItem(
                amount: $category['price'],
                type: InvoiceItem::CHARGE,
                description: "City tax {$category['name']}",
                qty: $qty,
            );
            $this->updateInvoiceItem($booking, $invoiceItem);
        }
    }

    /**
     * @throws \Exception
     */
    private function addExtraGuestInvoice(Entity\Booking $booking, BookingDto $bookingDto): void
    {
        $amount = $bookingDto->extraPerson;
        if (!$amount) {
            return;
        }

        $confirmedGuests = $this->occupancy($bookingDto);
        $arrival = new \DateTime($booking->arrival);
        $departure = new \DateTime($booking->departure);
        $nights = $arrival->diff($departure)->d;
        $qty = $nights * ($confirmedGuests - $bookingDto->guestsAmount);
        if (!$qty) {
            return;
        }
        $invoiceItem = new InvoiceItem(
            amount: $amount,
            type: InvoiceItem::CHARGE,
            description: 'Extra guest(s)',
            qty: max($qty, 0),
        );
        $this->updateInvoiceItem($booking, $invoiceItem);
    }

    /**
     * @throws \Exception
     */
    private function updateCityTax(Entity\Booking $booking, BookingDto $bookingDto): void
    {
        $this->removeCityTaxInvoices($booking);
        $this->addCityTaxInvoices($booking, $bookingDto);
    }

    /**
     * @throws \Exception
     */
    private function updateExtraGuestInvoice(Entity\Booking $booking, BookingDto $bookingDto): void
    {
        $this->removeExtraGuestInvoice($booking);
        $this->addExtraGuestInvoice($booking, $bookingDto);
    }

    /**
     * @throws \Exception
     */
    private function getBookingEntityById(int $id): Entity\Booking
    {
        $dto = new GetBookingsDto(id: [$id], includeInvoiceItems: true, includeInfoItems: true);
        $beds24BookingsDto = $this->client->getBookings($dto);
        if (!isset($beds24BookingsDto->bookings[0])) {
            throw new \Exception("Booking id $id is not found");
        }
        return $beds24BookingsDto->bookings[0];
    }

    private function getDefaultFilter(): array
    {
        $departureFrom = (new \DateTime('-3 days'))->format('Y-m-d');
        $arrivalTo = (new \DateTime('+14 days'))->format('Y-m-d');

        return [
            'departureFrom' => $departureFrom,
            'arrivalTo' => $arrivalTo,
            'includeInvoiceItems' => true,
            'includeInfoItems' => true,
            'status' => ['confirmed', 'new'],
        ];
    }

    /**
     * @return Entity\Booking[]
     */
    private function getBookings(array $filter, int $page = 1): array
    {
        $filter['page'] = $page;
        $dto = new GetBookingsDto(...$filter);
        $beds24BookingsDto = $this->client->getBookings($dto);
        $bookings = $beds24BookingsDto->bookings;

        if ($beds24BookingsDto->pages['nextPageExists']) {
            $bookings = [...$bookings, ...$this->getBookings($filter, $page + 1)];
        }

        return $bookings;
    }

    private function getAges(GuestDto $guest): int
    {
        return (int) date('Y') - (int) date('Y', strtotime($guest->dateOfBirth));
    }

    private function getGuestsAgeCategories(BookingDto $bookingDto): array
    {
        return [
            [
                'name' => 'Adults (18+)',
                'price' => 3.13,
                'quantity' => $this->getGuestsQuantityByAges($bookingDto)['adults'],
            ],
            [
                'name' => 'Children (7—18)',
                'price' => 1.57,
                'quantity' => $this->getGuestsQuantityByAges($bookingDto)['children'],
            ],
        ];
    }

    private function getGuestsQuantityByAges(BookingDto $bookingDto): array
    {
        return [
            'adults' => array_reduce(
                $bookingDto->guests,
                fn ($acc, $guest) => $acc + ($this->getAges($guest) >= 18 ? 1 : 0),
                0,
            ),
            'children' => array_reduce(
                $bookingDto->guests,
                fn ($acc, $guest) => $acc + ($this->getAges($guest) >= 7 && $this->getAges($guest) < 18 ? 1 : 0),
                0,
            ),
            'preschoolers' => array_reduce(
                $bookingDto->guests,
                fn ($acc, $guest) => $acc + ($this->getAges($guest) >= 4 && $this->getAges($guest) < 7 ? 1 : 0),
                0,
            ),
            'toddlers' => array_reduce(
                $bookingDto->guests,
                fn ($acc, $guest) => $acc + ($this->getAges($guest) < 4 ? 1 : 0),
                0,
            ),
        ];
    }

    private function occupancy(BookingDto $bookingDto): int
    {
        $guestsQuantityByAges = $this->getGuestsQuantityByAges($bookingDto);
        $confirmedGuests = $this->confirmedGuests($bookingDto);
        if ($guestsQuantityByAges['toddlers']) {
            $confirmedGuests--;
        }

        return $confirmedGuests;
    }

    private function confirmedGuests(BookingDto $bookingDto): int
    {
        $guestsQuantityByAges = $this->getGuestsQuantityByAges($bookingDto);

        return $guestsQuantityByAges['adults']
            + $guestsQuantityByAges['children']
            + $guestsQuantityByAges['preschoolers']
            + $guestsQuantityByAges['toddlers'];
    }
}
