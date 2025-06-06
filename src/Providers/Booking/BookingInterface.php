<?php

declare(strict_types=1);

namespace App\Providers\Booking;

use App\Dto\BookingDto;

interface BookingInterface
{
    public const PAYMENT = 'payment';

    public function fetchToken(string $code): object;
    public function findById(int $id): BookingDto;
    public function findBy(array $filter): array;
    public function setPaidStatus(int $bookingId, string $paymentStatus): void;
    public function addInvoice(int $id, string $type, float $amount, string $description): void;
    public function updateBooking(BookingDto $bookingDto): void;
    public function sendMessage(int $bookingId, string $text): void;
}
