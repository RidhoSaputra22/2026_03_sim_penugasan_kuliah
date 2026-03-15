<?php

namespace App\Enums;

enum AttendanceStatus: string
{
    case HADIR = 'hadir';
    case IZIN = 'izin';
    case SAKIT = 'sakit';
    case ALPHA = 'alpha';

    public static function list(?array $cases = null): array
    {
        return array_map(
            static fn (self $case) => $case->value,
            $cases ?? self::cases()
        );
    }

    public static function options(?array $cases = null): array
    {
        $options = [];

        foreach ($cases ?? self::cases() as $case) {
            $options[$case->value] = $case->label();
        }

        return $options;
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
