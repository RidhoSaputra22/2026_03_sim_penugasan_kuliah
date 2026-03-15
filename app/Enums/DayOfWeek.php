<?php

namespace App\Enums;

use InvalidArgumentException;

enum DayOfWeek: string
{
    case MONDAY = 'Senin';
    case TUESDAY = 'Selasa';
    case WEDNESDAY = 'Rabu';
    case THURSDAY = 'Kamis';
    case FRIDAY = 'Jumat';
    case SATURDAY = 'Sabtu';
    case SUNDAY = 'Minggu';

    public static function list(?array $cases = null): array
    {
        return array_map(
            static fn (self $case) => $case->value,
            $cases ?? self::cases()
        );
    }

    public static function academicCases(): array
    {
        return [
            self::MONDAY,
            self::TUESDAY,
            self::WEDNESDAY,
            self::THURSDAY,
            self::FRIDAY,
            self::SATURDAY,
        ];
    }

    public static function academicList(): array
    {
        return self::list(self::academicCases());
    }

    public static function options(?array $cases = null): array
    {
        $options = [];

        foreach ($cases ?? self::cases() as $case) {
            $options[$case->value] = $case->label();
        }

        return $options;
    }

    public static function academicOptions(): array
    {
        return self::options(self::academicCases());
    }

    public static function fromIsoDayNumber(int $day): self
    {
        return match ($day) {
            1 => self::MONDAY,
            2 => self::TUESDAY,
            3 => self::WEDNESDAY,
            4 => self::THURSDAY,
            5 => self::FRIDAY,
            6 => self::SATURDAY,
            7 => self::SUNDAY,
            default => throw new InvalidArgumentException('Nomor hari ISO harus bernilai 1 sampai 7.'),
        };
    }

    public static function isValid(string $value): bool
    {
        return in_array($value, self::list(), true);
    }

    public function label(): string
    {
        return $this->value;
    }

    public function short(): string
    {
        return match ($this) {
            self::MONDAY => 'Sen',
            self::TUESDAY => 'Sel',
            self::WEDNESDAY => 'Rab',
            self::THURSDAY => 'Kam',
            self::FRIDAY => 'Jum',
            self::SATURDAY => 'Sab',
            self::SUNDAY => 'Min',
        };
    }

    public function toFullCalendar(): int
    {
        return match ($this) {
            self::SUNDAY => 0,
            self::MONDAY => 1,
            self::TUESDAY => 2,
            self::WEDNESDAY => 3,
            self::THURSDAY => 4,
            self::FRIDAY => 5,
            self::SATURDAY => 6,
        };
    }

    public static function toArray(): array
    {
        return self::list();
    }
}
