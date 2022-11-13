<?php

declare(strict_types=1);

namespace App\Providers\Booking\Beds24\Dto\Response;

class PostBookingsDto
{
    public array $result = [];

    public function __construct(...$result)
    {
        foreach ($result as $item) {
            $this->result[] = $item;
        }
    }
}
