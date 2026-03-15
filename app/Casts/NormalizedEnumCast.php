<?php

namespace App\Casts;

use App\Support\EnumInput;
use BackedEnum;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use InvalidArgumentException;
use ValueError;

class NormalizedEnumCast implements CastsAttributes
{
    public function __construct(
        private readonly string $enumClass
    ) {
        if (!enum_exists($this->enumClass)) {
            throw new InvalidArgumentException("Enum {$this->enumClass} tidak ditemukan.");
        }

        if (!is_subclass_of($this->enumClass, BackedEnum::class)) {
            throw new InvalidArgumentException("{$this->enumClass} bukan backed enum.");
        }
    }

    public function get($model, string $key, $value, array $attributes): ?BackedEnum
    {
        if ($value === null || $value === '') {
            return null;
        }

        $enum = EnumInput::tryFrom($this->enumClass, $value);

        if ($enum) {
            return $enum;
        }

        throw new ValueError("{$value} is not a valid backing value for enum {$this->enumClass}");
    }

    public function set($model, string $key, $value, array $attributes): string|int|null
    {
        if ($value === null || $value === '') {
            return null;
        }

        $enum = EnumInput::tryFrom($this->enumClass, $value);

        if ($enum) {
            return $enum->value;
        }

        throw new ValueError("{$value} is not a valid backing value for enum {$this->enumClass}");
    }
}
