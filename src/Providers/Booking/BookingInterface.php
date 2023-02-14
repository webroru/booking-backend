<?php

declare(strict_types=1);

namespace App\Providers\Booking;

use App\Dto\Booking;

interface BookingInterface
{
    public function setToken(string $token): void;
    public function fetchToken(string $code): object;
    public function refreshToken(string $refreshToken): object;
    public function findById(int $id): Booking;
    public function findBy(array $filter): array;
    public function acceptRule(int $bookingId, bool $isRuleAccepted): void;
    public function setPaidStatus(int $bookingId, string $paymentStatus): void;
    public function setCheckInStatus(int $bookingId, bool $checkIn): void;
    public function addPhoto(int $bookingId, string $photoUrl): void;
    public function removePhoto(int $id, string $photoUrl): void;
    public function addInvoice(int $id, string $type, float $amount, string $description): void;
    public function updateGuests(Booking $bookingDto): void;
    public function cancel(int $bookingId): void;
}
