<?php

namespace App\Support;

use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;

class SriLankaFormat
{
    public static function normalizeNic(?string $nic): string
    {
        $nic = strtoupper(preg_replace('/[\s\-]/', '', (string) $nic) ?? '');

        return $nic;
    }

    public static function isValidNic(string $nic): bool
    {
        $nic = self::normalizeNic($nic);

        return (bool) preg_match('/^(?:\d{9}[VX]|\d{12})$/', $nic);
    }

    /**
     * Default system-user password style: first 4 digits + @ASDA
     * Example: 0772111001 → 0772@ASDA
     */
    public static function defaultPasswordFromDigits(?string $value): string
    {
        $digits = preg_replace('/\D/', '', (string) $value) ?? '';
        $prefix = substr(str_pad($digits, 4, '0', STR_PAD_LEFT), 0, 4);

        return $prefix.'@ASDA';
    }

    /**
     * Default member password: first 4 digits of NIC + @ASDA
     * Example: 196204512345 → 1962@ASDA
     */
    public static function defaultPasswordFromNic(?string $nic): string
    {
        $digits = preg_replace('/\D/', '', self::normalizeNic($nic)) ?? '';
        $prefix = substr(str_pad($digits, 4, '0', STR_PAD_LEFT), 0, 4);

        return $prefix.'@ASDA';
    }

    /**
     * Derive date of birth from a Sri Lankan NIC (old or new format).
     */
    public static function birthDateFromNic(?string $nic): ?Carbon
    {
        $nic = self::normalizeNic($nic);

        if (! self::isValidNic($nic)) {
            return null;
        }

        try {
            if (strlen($nic) === 12) {
                $year = (int) substr($nic, 0, 4);
                $dayOfYear = (int) substr($nic, 4, 3);
            } else {
                // Old NIC years are relative to 1900.
                $year = 1900 + (int) substr($nic, 0, 2);
                $dayOfYear = (int) substr($nic, 2, 3);
            }

            // Days 501–866 indicate female (day of year + 500).
            if ($dayOfYear > 500) {
                $dayOfYear -= 500;
            }

            if ($dayOfYear < 1) {
                return null;
            }

            $start = Carbon::create($year, 1, 1, 0, 0, 0, SriLankaDate::TIMEZONE);
            $maxDay = $start->isLeapYear() ? 366 : 365;

            if ($dayOfYear > $maxDay) {
                return null;
            }

            return $start->copy()->addDays($dayOfYear - 1)->startOfDay();
        } catch (InvalidFormatException) {
            return null;
        }
    }

    public static function ageFromNic(?string $nic): ?int
    {
        $dob = self::birthDateFromNic($nic);

        return $dob?->age;
    }

    public static function isOverAgeFromNic(?string $nic, int $years = 61): bool
    {
        $age = self::ageFromNic($nic);

        return $age !== null && $age > $years;
    }

    /**
     * Normalize SL phone to local format: 0XXXXXXXXX (10 digits).
     */
    public static function normalizePhone(?string $phone): ?string
    {
        if ($phone === null || trim($phone) === '') {
            return null;
        }

        $digits = preg_replace('/[^\d+]/', '', $phone) ?? '';
        $digits = preg_replace('/^\+/', '', $digits) ?? '';

        if (str_starts_with($digits, '94') && strlen($digits) >= 11) {
            $digits = '0'.substr($digits, 2);
        }

        $digits = preg_replace('/\D/', '', $digits) ?? '';

        return $digits !== '' ? $digits : null;
    }

    public static function isValidMobile(?string $phone): bool
    {
        $normalized = self::normalizePhone($phone);

        if ($normalized === null) {
            return false;
        }

        // 07X XXX XXXX
        return (bool) preg_match('/^07[0-9]{8}$/', $normalized);
    }

    public static function isValidPhone(?string $phone): bool
    {
        $normalized = self::normalizePhone($phone);

        if ($normalized === null) {
            return false;
        }

        // Mobile or landline: 0 + 9 digits
        return (bool) preg_match('/^0[1-9][0-9]{8}$/', $normalized);
    }
}
