<?php

namespace App\Enums;

enum AttendanceStatus: string
{
    case HADIR = 'hadir';
    case IZIN = 'izin';
    case SAKIT = 'sakit';
    case ALPHA = 'alpha';

    public static function list(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function isValid(string $value): bool
    {
        return in_array($value, self::list(), true);
    }

    public function label(): string
    {
        return match ($this) {
            self::HADIR => 'Hadir',
            self::IZIN => 'Izin',
            self::SAKIT => 'Sakit',
            self::ALPHA => 'Alpha',
        };
    }
}
