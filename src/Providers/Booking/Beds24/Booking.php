<?php

declare(strict_types=1);

namespace App\Providers\Booking\Beds24;

use App\Dto\BookingDto;
use App\Providers\Booking\Beds24\Client\Client;
use App\Providers\Booking\Beds24\Dto\Request\GetBookingsDto;
use App\Providers\Booking\Beds24\Dto\Request\GetPropertiesDto;
use App\Providers\Booking\Beds24\Dto\Request\PostBookingsDto;
use App\Providers\Booking\Beds24\Dto\Response\GetAuthenticationSetupDto;
use App\Providers\Booking\Beds24\Entity\InfoItem;
use App\Providers\Booking\Beds24\Entity\InvoiceItem;
use App\Providers\Booking\Beds24\Entity\Property;
use App\Providers\Booking\BookingInterface;

class Booking implements BookingInterface
{
    public const GUESTS_AGE_CATEGORIES = [
        self::ADULTS => 'Adults (18+)',
        self::CHILDREN => 'Children (7—18)',
        self::BABIES => 'Children (4—7)',
        self::SUCKLINGS => 'Children (0—4)',
    ];
    public const ADULTS = 'adults';
    public const CHILDREN = 'children';
    public const BABIES = 'babies';
    public const SUCKLINGS = 'sucklings';

    public function __construct(
        private readonly Client $client,
        private readonly CommonDtoConverter $converter,
    ) {
    }

    public function fetchToken(string $code): GetAuthenticationSetupDto
    {
        $this->client->setCode($code);
        return $this->client->getAuthenticationSetup();
    }

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
            $result[] = $this->converter->convert($booking, $beds24Property, $groups);
        }

        return $result;
    }

    /**
     * @throws \Exception
     */
    public function acceptRule(int $bookingId, bool $isRuleAccepted): void
    {
        $booking = $this->getBookingEntityById($bookingId);
        $infoItem = new InfoItem(code: 'isRuleAccepted', text: $isRuleAccepted ? 'true' : 'false');
        $this->updateInfoItem($booking, $infoItem);
        $postBookingsDto = new PostBookingsDto([$booking]);
        $this->update($postBookingsDto);
    }

    /**
     * @throws \Exception
     */
    public function setPaidStatus(int $bookingId, string $paymentStatus): void
    {
        $booking = $this->getBookingEntityById($bookingId);
        $infoItem = new InfoItem('paymentStatus', $paymentStatus);
        $this->updateInfoItem($booking, $infoItem);
        $infoItem = new InfoItem('checkIn', $paymentStatus !== '' ? 'true' : 'false');
        $this->updateInfoItem($booking, $infoItem);
        $postBookingsDto = new PostBookingsDto([$booking]);
        $this->update($postBookingsDto);
    }

    /**
     * @throws \Exception
     */
    public function setCheckInStatus(int $bookingId, bool $checkIn): void
    {
        $booking = $this->getBookingEntityById($bookingId);
        $infoItem = new InfoItem('checkIn', $checkIn ? 'true' : 'false');
        $this->updateInfoItem($booking, $infoItem);
        $postBookingsDto = new PostBookingsDto([$booking]);
        $this->update($postBookingsDto);
    }

    /**
     * @throws \Exception
     */
    public function setCheckOutStatus(int $bookingId): void
    {
        $booking = $this->getBookingEntityById($bookingId);
        $infoItem = new InfoItem('checkOut', 'true');
        $this->updateInfoItem($booking, $infoItem);
        $postBookingsDto = new PostBookingsDto([$booking]);
        $this->update($postBookingsDto);
    }

    /**
     * @throws \Exception
     */
    public function addPhoto(int $bookingId, string $photoUrl): void
    {
        $booking = $this->getBookingEntityById($bookingId);
        $infoItem = new InfoItem(code: 'photos', text: $this->getPhotoLink($photoUrl));
        $booking->infoItems[] = $infoItem;
        $postBookingsDto = new PostBookingsDto([$booking]);
        $this->update($postBookingsDto);
    }

    /**
     * @throws \Exception
     */
    public function removePhoto(int $id, string $photoUrl): void
    {
        $booking = $this->getBookingEntityById($id);
        $infoItem = $this->findInfoItemByValue($booking->infoItems, $this->getPhotoLink($photoUrl));
        if (!$infoItem) {
            return;
        }
        $infoItem->text = null;
        $infoItem->code = null;
        $infoItem->bookingId = null;
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
    public function updateGuests(BookingDto $bookingDto): void
    {
        $booking = $this->getBookingEntityById($bookingDto->orderId);
        $this->updateGuestsInfoItems($booking, $bookingDto);
        $this->updateCityTax($booking, $bookingDto);
        $this->updateExtraGuestInvoice($booking, $bookingDto);
        $this->updateExtraGuestsInfoItems($booking, $bookingDto);
        $postBookingsDto = new PostBookingsDto([$booking]);
        $this->update($postBookingsDto);
    }

    /**
     * @throws \Exception
     */
    public function cancel(int $bookingId): void
    {
        $booking = $this->getBookingEntityById($bookingId);
        $infoItem = new InfoItem('paymentStatus', 'disagree');
        $this->updateInfoItem($booking, $infoItem);
        $infoItem = new InfoItem('checkIn', 'false');
        $this->updateInfoItem($booking, $infoItem);
        $booking->status = 'cancelled';
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
     * @param InfoItem[] $infoItems
     */
    private function findInfoItemByCode(array $infoItems, string $code): ?InfoItem
    {
        foreach ($infoItems as $infoItem) {
            if (isset($infoItem->code) && $infoItem->code === $code) {
                return $infoItem;
            }
        }

        return null;
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

    private function updateInfoItem(Entity\Booking $booking, InfoItem $infoItem): void
    {
        $existedInfoItem = $this->findInfoItemByCode($booking->infoItems, $infoItem->code);
        if ($existedInfoItem && $infoItem->text === null) {
            unset($existedInfoItem->text);
            unset($existedInfoItem->code);
            unset($existedInfoItem->bookingId);
            return;
        }

        if (!$existedInfoItem) {
            $booking->infoItems[] = $infoItem;
        } else {
            $existedInfoItem->text = $infoItem->text;
        }
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
        foreach (self::GUESTS_AGE_CATEGORIES as $category => $description) {
            if (empty($bookingDto->$category)) {
                continue;
            }
            $amount = $this->getCitiTaxAmount($category);
            if (!$amount) {
                continue;
            }
            $arrival = new \DateTime($booking->arrival);
            $departure = new \DateTime($booking->departure);
            $nights = $arrival->diff($departure)->d;
            $qty = $nights * $bookingDto->$category;
            if (!$qty) {
                return;
            }
            $invoiceItem = new InvoiceItem(
                amount: $amount,
                type: InvoiceItem::CHARGE,
                description: "City tax $description",
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
        $confirmedGuests = $bookingDto->adults + $bookingDto->children + $bookingDto->babies + max(0, $bookingDto->sucklings - 1);
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

    private function getCitiTaxAmount(string $category): float
    {
        return match ($category) {
            self::ADULTS => 3.13,
            self::CHILDREN => 1.57,
            default => 0,
        };
    }

    /**
     * @throws \HttpRequestException
     */
    private function updateGuestsInfoItems(Entity\Booking $booking, BookingDto $bookingDto): void
    {
        foreach (self::GUESTS_AGE_CATEGORIES as $category => $description) {
            if (!isset($bookingDto->$category)) {
                throw new \HttpRequestException("Property $category not found");
            }
            $infoItem = new InfoItem($description, (string) $bookingDto->$category);
            $this->updateInfoItem($booking, $infoItem);
        }
    }

    private function updateExtraGuestsInfoItems(Entity\Booking $booking, BookingDto $bookingDto): void
    {
        $infoItems = [];
        $infoItems[] = new InfoItem('overmax', (string) ($bookingDto->overmax));
        $infoItems[] = new InfoItem('plusGuest', $bookingDto->plusGuest ? 'true' : null);
        $infoItems[] = new InfoItem('lessDocs', $bookingDto->lessDocs ? 'true' : 'false');

        foreach ($infoItems as $infoItem) {
            $this->updateInfoItem($booking, $infoItem);
        }
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
            'includeGuests' => true,
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

    private function getPhotoLink($photoUrl): string
    {
        return "<a href='$photoUrl'>Link for photos</a>";
    }
}
