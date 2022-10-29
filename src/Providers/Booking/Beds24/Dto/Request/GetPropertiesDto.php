<?php

declare(strict_types=1);

namespace App\Providers\Booking\Beds24\Dto\Request;

class GetPropertiesDto implements RequestDtoInterface
{
    public function __construct(
        public readonly ?array $id = null,
        public readonly ?array $includeLanguages = null,
        public readonly ?string $includeTexts = null,
        public readonly ?bool $includePictures = null,
        public readonly ?bool $includeOffers = null,
        public readonly ?bool $includePriceRules = null,
        public readonly ?bool $includeAllRooms = null,
        public readonly ?array $roomId = null,
        public readonly ?int $page = null,
    ) {
    }

    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
