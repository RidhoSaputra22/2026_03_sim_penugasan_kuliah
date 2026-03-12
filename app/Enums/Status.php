<?php

namespace App\Enums;

enum Status: string
{
    case SELESAI = 'SELESAI';
    case BELUM = 'BELUM';
    case PROGRESS = 'PROGRESS';
    case COMPLETED = 'COMPLETED';
    case CANCELLED = 'CANCELLED';
    /**
     * Get all values as array
     */
    public static function list(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Validate if value is a valid status
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
            self::SELESAI => 'Selesai',
            self::BELUM => 'Belum',
            self::PROGRESS => 'Progress',
            self::COMPLETED => 'Completed',
            self::CANCELLED => 'Cancelled',
        };
    }
}
