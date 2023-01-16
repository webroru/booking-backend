<?php

declare(strict_types=1);

namespace App\Providers\Booking\Beds24\Entity;

abstract class AbstractEntity implements EntityInterface
{
    public function toArray(): array
    {
        $properties = get_object_vars($this);
        foreach ($properties as $key => $property) {
            $properties[$key] = $this->entityToArray($property);
        }
        return $properties;
    }

    private function entityToArray(mixed $property): mixed
    {
        if ($property instanceof EntityInterface) {
            return $property->toArray();
        }

        if (is_array($property)) {
            $result = [];
            foreach ($property as $key => $item) {
                $result[$key] = $this->entityToArray($item);
            }

            return $result;
        }

        return $property;
    }
}
