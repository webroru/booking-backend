<?php

declare(strict_types=1);

namespace App\Providers\Booking;

use App\Dto\BookingDto;

interface BookingInterface
{
    public const PAYMENT = 'payment';

    public function setToken(string $token): void;
    public function fetchToken(string $code): object;
    public function refreshToken(string $refreshToken): object;
    public function findById(int $id): BookingDto;
    public function findBy(array $filter): array;
    public function acceptRule(int $bookingId, bool $isRuleAccepted): void;
    public function setPaidStatus(int $bookingId, string $paymentStatus): void;
    public function setCheckInStatus(int $bookingId, bool $checkIn): void;
    public function setCheckOutStatus(int $bookingId): void;
    public function addPhoto(int $bookingId, string $photoUrl): void;
    public function removePhoto(int $id, string $photoUrl): void;
    public function addInvoice(int $id, string $type, float $amount, string $description): void;
    public function updateGuests(BookingDto $bookingDto): void;
    public function cancel(int $bookingId): void;
    public function sendMessage(int $bookingId, string $text): void;
}
