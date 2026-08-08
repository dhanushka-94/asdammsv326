<?php

namespace App\Support;

class UserRole
{
    public const SUPER_ADMIN = 'super_admin';

    public const ADMIN = 'admin';

    public const VIEWER = 'viewer';

    public const RECEPTION = 'reception';

    /**
     * @return list<string>
     */
    public static function all(): array
    {
        return [
            self::SUPER_ADMIN,
            self::ADMIN,
            self::VIEWER,
            self::RECEPTION,
        ];
    }

    /**
     * Roles that can use the full admin console (not reception desk only).
     *
     * @return list<string>
     */
    public static function staff(): array
    {
        return [
            self::SUPER_ADMIN,
            self::ADMIN,
            self::VIEWER,
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function labels(): array
    {
        return [
            self::SUPER_ADMIN => 'Super Admin',
            self::ADMIN => 'Admin',
            self::VIEWER => 'Viewer',
            self::RECEPTION => 'Reception',
        ];
    }

    public static function label(string $role): string
    {
        return self::labels()[$role] ?? ucfirst(str_replace('_', ' ', $role));
    }

    public static function validationRule(): string
    {
        return 'in:'.implode(',', self::all());
    }
}
