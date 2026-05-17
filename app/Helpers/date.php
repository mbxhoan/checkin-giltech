<?php

use Illuminate\Support\Carbon;

/**
 * Return a Carbon instance.
 */
function carbon(string $parseString = '', ?string $tz = null): Carbon
{
    return new Carbon($parseString, $tz);
}

/**
 * Return a formatted Carbon date.
 */
function humanize_date($date, string $format = 'd F Y, H:i'): string
{
    if (empty($date)) {
        return "";
    }

    if (is_string($date)) {
        $date = Carbon::parse($date);
    }

    return $date->format($format);
}
