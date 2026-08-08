<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Throwable;

class AppSettings
{
    public const MAINTENANCE_MODE = 'maintenance_mode';

    public const MAINTENANCE_MESSAGE = 'maintenance_message';

    public const MEMBER_REGISTRATION_ENABLED = 'member_registration_enabled';

    private const CACHE_TTL_SECONDS = 60;

    public static function get(string $key, ?string $default = null): ?string
    {
        try {
            return Cache::remember(
                self::cacheKey($key),
                self::CACHE_TTL_SECONDS,
                function () use ($key, $default) {
                    $setting = Setting::query()->where('key', $key)->first();

                    return $setting?->value ?? $default;
                }
            );
        } catch (Throwable) {
            return $default;
        }
    }

    public static function set(string $key, ?string $value): void
    {
        Setting::query()->updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );

        Cache::forget(self::cacheKey($key));
    }

    public static function bool(string $key, bool $default = false): bool
    {
        $value = self::get($key, $default ? '1' : '0');

        return in_array(strtolower((string) $value), ['1', 'true', 'yes', 'on'], true);
    }

    /** ON = public blocked / maintenance page. OFF = public working. */
    public static function maintenanceMode(): bool
    {
        return self::bool(self::MAINTENANCE_MODE, false);
    }

    public static function publicAccessEnabled(): bool
    {
        return ! self::maintenanceMode();
    }

    public static function maintenanceMessage(): string
    {
        return (string) self::get(
            self::MAINTENANCE_MESSAGE,
            'The ASDA Member Management System is temporarily unavailable while we perform maintenance. Please check back soon.'
        );
    }

    public static function setMaintenanceMode(bool $enabled): void
    {
        self::set(self::MAINTENANCE_MODE, $enabled ? '1' : '0');
    }

    public static function setMaintenanceMessage(string $message): void
    {
        self::set(self::MAINTENANCE_MESSAGE, $message);
    }

    /** ON = public /register available. OFF = registration closed. */
    public static function memberRegistrationEnabled(): bool
    {
        return self::bool(self::MEMBER_REGISTRATION_ENABLED, true);
    }

    public static function setMemberRegistrationEnabled(bool $enabled): void
    {
        self::set(self::MEMBER_REGISTRATION_ENABLED, $enabled ? '1' : '0');
    }

    private static function cacheKey(string $key): string
    {
        return 'app_settings.'.$key;
    }
}
