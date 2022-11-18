<?php

declare(strict_types=1);

namespace App\Providers\PhotoStorage;

use App\Dto\Booking;

interface PhotoStorageInterface
{
    public function put(Booking $booking, string $filepath): string;
}
