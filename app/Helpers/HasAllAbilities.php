<?php

namespace App\Helpers;

use Illuminate\Routing\Controllers\Middleware;

class HasAllAbilities
{
    /**
     * Specify the ability and methods for the middleware.
     */
    public static function using(array|string|\BackedEnum $ability, ?array $only = null, ?array $except = null)
    {
        return new Middleware('abilities:'.self::parseAbilitiesToString($ability), $only, $except);
    }

    /**
     * Convert array or string of abilities to string representation.
     */
    protected static function parseAbilitiesToString(array|string|\BackedEnum $ability)
    {
        // Convert Enum to its value if an Enum is passed
        if ($ability instanceof \BackedEnum) {
            $ability = $ability->value;
        }

        if (is_array($ability)) {
            $ability = array_map(fn ($a) => $a instanceof \BackedEnum ? $a->value : $a, $ability);

            return implode(',', $ability);
        }

        return (string) $ability;
    }
}
