<?php

declare(strict_types=1);

namespace App\Enum;

enum DocumentType: string
{
    case PASSPORT = 'H';
    case ID = 'I';

    public static function fromName(string $name): ?self
    {
        foreach (self::cases() as $case) {
            if (strcasecmp($case->name, $name) === 0) {
                return $case;
            }
        }
        return null;
    }
}
