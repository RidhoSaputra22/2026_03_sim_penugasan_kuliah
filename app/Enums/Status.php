<?php

namespace App\Enums;

enum Status: string
{
    case SELESAI = 'SELESAI';
    case BELUM = 'BELUM';
    case PROGRESS = 'PROGRESS';
    case COMPLETED = 'COMPLETED';
    case CANCELLED = 'CANCELLED';

    public static function list(?array $cases = null): array
    {
        return array_map(
            static fn (self $case) => $case->value,
            $cases ?? self::cases()
        );
    }

    public static function taskCases(): array
    {
        return [
            self::BELUM,
            self::PROGRESS,
            self::SELESAI,
        ];
    }

    public static function taskValues(): array
    {
        return self::list(self::taskCases());
    }

    public static function options(?array $cases = null): array
    {
        $options = [];

        foreach ($cases ?? self::cases() as $case) {
            $options[$case->value] = $case->label();
        }

        return $options;
    }

    public static function taskOptions(): array
    {
        return self::options(self::taskCases());
    }

    public static function isValid(string $value): bool
    {
        return in_array($value, self::list(), true);
    }

    public function label(): string
    {
        return match ($this) {
            self::SELESAI => 'Selesai',
            self::BELUM => 'Belum',
            self::PROGRESS => 'Progress',
            self::COMPLETED => 'Completed',
            self::CANCELLED => 'Cancelled',
        };
    }
}
