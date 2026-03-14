<?php

namespace App\Support;

use Carbon\Carbon;
use DateTimeInterface;

class ScheduleTime
{
    public static function normalize(mixed $value): ?string
    {
        $time = self::parse($value);

        return $time?->format('H:i:s');
    }

    public static function format(mixed $value, string $format = 'H:i'): ?string
    {
        $time = self::parse($value);

        return $time?->format($format);
    }

    public static function diffInMinutes(mixed $start, mixed $end): ?int
    {
        $startTime = self::parse($start);
        $endTime = self::parse($end);

        if (!$startTime || !$endTime || $endTime->lessThanOrEqualTo($startTime)) {
            return null;
        }

        return $startTime->diffInMinutes($endTime);
    }

    public static function humanizeDuration(
        mixed $start,
        mixed $end,
        string $fallback = 'Jam tidak valid'
    ): string {
        $minutes = self::diffInMinutes($start, $end);

        if ($minutes === null) {
            return $fallback;
        }

        return sprintf('%d jam %d menit', intdiv($minutes, 60), $minutes % 60);
    }

    public static function isValidRange(mixed $start, mixed $end): bool
    {
        return self::diffInMinutes($start, $end) !== null;
    }

    private static function parse(mixed $value): ?Carbon
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof DateTimeInterface) {
            return Carbon::instance($value);
        }

        if (is_int($value) || is_float($value)) {
            return self::parseNumeric((float) $value);
        }

        $stringValue = trim((string) $value);

        if ($stringValue === '') {
            return null;
        }

        $normalizedValue = preg_replace('/\s+/', ' ', $stringValue) ?? $stringValue;

        if (preg_match('/^\d{1,2}\.\d{2}(:\d{2})?$/', $normalizedValue)) {
            $normalizedValue = str_replace('.', ':', $normalizedValue);
        }

        if (is_numeric($normalizedValue)) {
            return self::parseNumeric((float) $normalizedValue);
        }

        $formats = [
            '!H:i:s',
            '!H:i',
            '!G:i:s',
            '!G:i',
            '!g:i A',
            '!g:i:s A',
            '!g:i a',
            '!g:i:s a',
            'Y-m-d H:i:s',
            'Y-m-d H:i',
            'Y-m-d\TH:i:sP',
            'Y-m-d\TH:i:s',
            'Y-m-d\TH:i',
        ];

        foreach ($formats as $format) {
            try {
                $time = Carbon::createFromFormat($format, $normalizedValue);
            } catch (\Throwable) {
                $time = false;
            }

            if ($time !== false) {
                return $time;
            }
        }

        try {
            return Carbon::parse($normalizedValue);
        } catch (\Throwable) {
            return null;
        }
    }

    private static function parseNumeric(float $value): ?Carbon
    {
        if ($value < 0 || $value >= 1) {
            return null;
        }

        $seconds = (int) round($value * 86400);
        $seconds = min($seconds, 86399);

        return Carbon::today()->startOfDay()->addSeconds($seconds);
    }
}
