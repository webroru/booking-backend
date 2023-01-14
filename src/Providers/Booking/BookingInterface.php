<?php

declare(strict_types=1);

namespace App\Providers\Booking;

use App\Providers\Booking\Beds24\Dto\Request\PostBookingsDto;

interface BookingInterface
{
    public function setToken(string $token): void;
    public function fetchToken(string $code): object;
    public function refreshToken(string $refreshToken): object;
    public function findById(int $id): \App\Dto\Booking;
    public function findBy(array $filter): array;
    public function acceptRule(int $bookingId, bool $isRuleAccepted): void;
    public function addPhoto(int $bookingId, string $photoUrl): void;
    public function removePhoto(int $id, string $photoUrl): void;
    public function update(PostBookingsDto $postBookingsDto): void;
}