<?php

declare(strict_types=1);

namespace App\Providers\PhotoStorage\Local;

use App\Dto\Booking;
use App\Providers\PhotoStorage\PhotoStorageInterface;

class Local implements PhotoStorageInterface
{
    public function __construct(
        private readonly string $targetDirectory,
        private readonly string $photosUrl,
    ) {
    }

    public function put(Booking $booking, string $filepath): string
    {
        $newFileName = md5_file($filepath) . '.' . pathinfo($filepath, PATHINFO_EXTENSION);
        $dir = "{$this->targetDirectory}/{$booking->checkOutDate}/{$booking->orderId}";
        $path = "$dir/$newFileName";
        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }

        copy($filepath, $path);

        return "{$this->photosUrl}/{$booking->checkOutDate}/{$booking->orderId}/$newFileName";
    }
}
