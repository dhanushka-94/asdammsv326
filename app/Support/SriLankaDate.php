<?php

namespace App\Support;

use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

class SriLankaDate
{
    public const TIMEZONE = 'Asia/Colombo';

    public const DATE = 'd/m/Y';

    public const TIME = 'h:i A';

    public const DATETIME = 'd/m/Y h:i A';

    public const DATE_LONG = 'd M Y';

    public const DATE_TEXT = 'd F Y';

    public const DATETIME_LONG = 'd M Y, h:i A';

    public static function of(mixed $value): ?Carbon
    {
        if ($value === null || $value === '') {
            return null;
        }

        if ($value instanceof CarbonInterface) {
            return Carbon::instance($value)->timezone(self::TIMEZONE);
        }

        return Carbon::parse($value, self::TIMEZONE)->timezone(self::TIMEZONE);
    }

    public static function format(mixed $value, string $format = self::DATETIME): string
    {
        $date = self::of($value);

        return $date ? $date->format($format) : '—';
    }

    public static function date(mixed $value): string
    {
        return self::format($value, self::DATE);
    }

    public static function dateText(mixed $value): string
    {
        return self::format($value, self::DATE_TEXT);
    }

    public static function datetime(mixed $value): string
    {
        return self::format($value, self::DATETIME);
    }

    public static function now(): Carbon
    {
        return Carbon::now(self::TIMEZONE);
    }
}
