<?php

namespace App\Support;

use App\Casts\NormalizedEnumCast;
use BackedEnum;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class EnumInput
{
    public static function tryFrom(string $enumClass, mixed $value): ?BackedEnum
    {
        if (!enum_exists($enumClass)) {
            return null;
        }

        if ($value instanceof $enumClass) {
            return $value;
        }

        if ($value instanceof BackedEnum) {
            $value = $value->value;
        }

        if (is_string($value)) {
            $candidate = trim($value);

            if ($candidate === '') {
                return null;
            }

            $normalized = self::normalizeString($candidate);

            foreach ($enumClass::cases() as $case) {
                if (!$case instanceof BackedEnum) {
                    continue;
                }

                if (self::normalizeString((string) $case->value) === $normalized) {
                    return $case;
                }

                if (self::normalizeString($case->name) === $normalized) {
                    return $case;
                }

                if (method_exists($case, 'label') && self::normalizeString($case->label()) === $normalized) {
                    return $case;
                }
            }

            return self::isBackedEnum($enumClass) ? $enumClass::tryFrom($candidate) : null;
        }

        if (is_int($value) && self::isBackedEnum($enumClass)) {
            return $enumClass::tryFrom($value);
        }

        return null;
    }

    public static function normalizeValue(string $enumClass, mixed $value): mixed
    {
        return self::tryFrom($enumClass, $value)?->value ?? $value;
    }

    public static function normalizeAttributes(string $modelClass, array $attributes): array
    {
        $model = app($modelClass);

        if (!$model instanceof Model) {
            return $attributes;
        }

        foreach ($attributes as $key => $value) {
            $enumClass = self::enumClassFromCast($model->getCasts()[$key] ?? null);

            if ($enumClass) {
                $attributes[$key] = self::normalizeValue($enumClass, $value);
            }
        }

        return $attributes;
    }

    public static function displayValue(mixed $value): mixed
    {
        if (!$value instanceof UnitEnum) {
            return $value;
        }

        if (method_exists($value, 'label')) {
            return $value->label();
        }

        return $value instanceof BackedEnum ? $value->value : $value->name;
    }

    public static function enumClassFromCast(?string $cast): ?string
    {
        if (!$cast) {
            return null;
        }

        [$castClass, $parameterString] = array_pad(explode(':', $cast, 2), 2, null);

        if (enum_exists($castClass) && self::isBackedEnum($castClass)) {
            return $castClass;
        }

        if ($castClass !== NormalizedEnumCast::class || !$parameterString) {
            return null;
        }

        $enumClass = explode(',', $parameterString)[0] ?? null;

        return $enumClass && enum_exists($enumClass) ? $enumClass : null;
    }

    private static function normalizeString(string $value): string
    {
        return mb_strtoupper(trim($value));
    }

    private static function isBackedEnum(string $enumClass): bool
    {
        return is_subclass_of($enumClass, BackedEnum::class);
    }
}
