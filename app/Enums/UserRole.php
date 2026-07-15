<?php

namespace App\Enums;

enum UserRole: string
{
    case Engineer = 'engineer';
    case Admin = 'admin';
    case Farmer = 'farmer';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}