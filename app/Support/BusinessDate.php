<?php

namespace App\Support;

use Carbon\Carbon;

final class BusinessDate
{
    /**
     * The hour at which a new business day begins.
     * Uses the application's configured timezone.
     */
    private const DAY_START_HOUR = 10;

    /**
     * Get the current business date.
     *
     * Before 10:00 AM, return yesterday.
     * From 10:00 AM onward, return today.
     */
    public static function today(): Carbon
    {
        $date = Carbon::now();

        if ($date->hour < self::DAY_START_HOUR) {
            $date->subDay();
        }

        return $date->startOfDay();
    }

    /**
     * Get the business date as YYYY-MM-DD.
     */
    public static function toDateString(): string
    {
        return self::today()->toDateString();
    }

    /**
     * Check whether a date is the current business date.
     */
    public static function isToday($date): bool
    {
        return Carbon::parse($date)->isSameDay(self::today());
    }
}
