<?php

declare(strict_types=1);

namespace App\Enum;

enum DocumentType: string
{
    case PASSPORT = 'H';
    case ID = 'I';
}
