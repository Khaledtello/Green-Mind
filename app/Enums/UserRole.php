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

    public static function fromSearchTerm(string $term): ?UserRole
    {
        $term = mb_strtolower(trim($term));

        foreach (self::cases() as $case)
            if (str_contains($case->value, $term))
                return $case;

        $arabicMap = [
            'مدير'  => self::Admin,
            'مهندس' => self::Engineer,
            'مزارع' => self::Farmer,
        ];

        foreach ($arabicMap as $arWord => $case)
            if (str_contains($arWord, $term))
                return $case;

        return null;
    }
}
