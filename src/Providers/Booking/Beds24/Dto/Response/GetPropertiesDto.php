<?php

declare(strict_types=1);

namespace App\Providers\Booking\Beds24\Dto\Response;

use App\Providers\Booking\Beds24\Entity\Property;

class GetPropertiesDto
{
    /** @var Property[] */
    public array $properties;

    public function __construct(
        public readonly bool $success,
        public readonly string $type,
        public readonly ?int $count = null,
        public readonly ?array $pages = null,
        ?array $data = null,
    ) {
        foreach ($data as $item) {
            $this->properties[] = new Property(...$item);
        }
    }
}
