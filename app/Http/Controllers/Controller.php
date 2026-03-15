<?php

namespace App\Http\Controllers;

use App\Support\EnumInput;
use Illuminate\Http\Request;

abstract class Controller
{
    protected function normalizeRequestEnums(Request $request, array $map): void
    {
        $normalized = [];

        foreach ($map as $key => $enumClass) {
            if (!$request->exists($key)) {
                continue;
            }

            $normalized[$key] = EnumInput::normalizeValue($enumClass, $request->input($key));
        }

        if ($normalized !== []) {
            $request->merge($normalized);
        }
    }

    protected function normalizedEnumValue(mixed $value, string $enumClass): mixed
    {
        return EnumInput::normalizeValue($enumClass, $value);
    }
}
