<?php

declare(strict_types=1);

namespace App\Providers\PhotoStorage;

use App\Dto\BookingDto;
use App\Entity\Photo;

interface PhotoStorageInterface
{
    public function put(BookingDto $booking, string $filepath): Photo;
    public function remove(Photo $photo): void;
}
