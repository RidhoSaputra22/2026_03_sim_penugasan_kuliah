<?php

namespace App\Enums;

enum DayOfWeek: string
{
    case MONDAY = 'SENIN';
    case TUESDAY = 'SELASA';
    case WEDNESDAY = 'RABU';
    case THURSDAY = 'KAMIS';
    case FRIDAY = 'JUMAT';
    case SATURDAY = 'SABTU';
    case SUNDAY = 'MINGGU';
    /**
     * Get all values as array
     */
    public static function list(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Validate if value is a valid day
     */
    public static function isValid(string $value): bool
    {
        return in_array($value, self::list(), true);
    }

    /**
     * Get human readable label
     */
    public function label(): string
    {
        return match($this) {
            self::MONDAY => 'Senin',
            self::TUESDAY => 'Selasa',
            self::WEDNESDAY => 'Rabu',
            self::THURSDAY => 'Kamis',
            self::FRIDAY => 'Jumat',
            self::SATURDAY => 'Sabtu',
            self::SUNDAY => 'Minggu',
        };
    }


    /**
     * Get short label (e.g. Sen, Sel)
     */
    public function short(): string
    {
        return match($this) {
            self::MONDAY => 'Sen',
            self::TUESDAY => 'Sel',
            self::WEDNESDAY => 'Rab',
            self::THURSDAY => 'Kam',
            self::FRIDAY => 'Jum',
            self::SATURDAY => 'Sab',
            self::SUNDAY => 'Min',
        };
    }

    /**
     * Convert to FullCalendar day-of-week index (0=Sun, 1=Mon, ..., 6=Sat)
     */
    public function toFullCalendar(): int
    {
        return match($this) {
            self::SUNDAY => 0,
            self::MONDAY => 1,
            self::TUESDAY => 2,
            self::WEDNESDAY => 3,
            self::THURSDAY => 4,
            self::FRIDAY => 5,
            self::SATURDAY => 6,
        };
    }

    // convert to array
    public static function toArray(): array
    {
        return array_map(fn($case) => $case->value, self::cases());
    }



}
