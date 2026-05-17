<?php

use Illuminate\Support\Facades\Lang;

/**
 * Return a formatted Carbon date.
 */
function lang_trans(string $key, string $prefix = "lp", ?string $default = null, $lowerCase = false): string
{
    if (isset($prefix)) {
        $key = "{$prefix}.{$key}";
    }

    if ($lowerCase) {
        $key = strtolower($key);
    }

    if (Lang::has($key)) {
        return __($key);
    }

    if ($default) {
        return $default;
    }

    return "";
    return "<{$key}>";
}
