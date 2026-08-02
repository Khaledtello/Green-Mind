<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin    = 'admin';
    case Engineer = 'engineer';
    case Farmer   = 'farmer';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}