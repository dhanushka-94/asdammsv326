<?php

namespace App\Support;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Throwable;

class MemberSessionInvalidator
{
    /**
     * Log out the current member (if any) and remove all member sessions.
     */
    public static function flushAll(): void
    {
        try {
            if (Auth::guard('member')->check()) {
                Auth::guard('member')->logout();
            }
        } catch (Throwable) {
            //
        }

        self::deleteMemberSessionsFromDatabase();
    }

    private static function deleteMemberSessionsFromDatabase(): void
    {
        if (config('session.driver') !== 'database') {
            return;
        }

        try {
            $table = config('session.table', 'sessions');

            DB::table($table)->orderBy('id')->chunk(100, function ($sessions) use ($table): void {
                foreach ($sessions as $session) {
                    if (self::payloadHasMemberLogin((string) $session->payload)) {
                        DB::table($table)->where('id', $session->id)->delete();
                    }
                }
            });
        } catch (Throwable) {
            // Never break settings save because session cleanup failed.
        }
    }

    private static function payloadHasMemberLogin(string $payload): bool
    {
        $data = self::decodePayload($payload);

        if (! is_array($data)) {
            $hasMember = str_contains($payload, 'login_member_');
            $hasWeb = str_contains($payload, 'login_web_');

            return $hasMember && ! $hasWeb;
        }

        $hasMember = false;
        $hasWeb = false;

        foreach (array_keys($data) as $key) {
            $key = (string) $key;

            if (str_starts_with($key, 'login_member_')) {
                $hasMember = true;
            }

            if (str_starts_with($key, 'login_web_')) {
                $hasWeb = true;
            }
        }

        return $hasMember && ! $hasWeb;
    }

    /**
     * @return array<string, mixed>|null
     */
    private static function decodePayload(string $payload): ?array
    {
        $decoded = @unserialize($payload);

        if (is_array($decoded)) {
            return $decoded;
        }

        $base64 = @base64_decode($payload, true);

        if ($base64 !== false) {
            $decoded = @unserialize($base64);

            if (is_array($decoded)) {
                return $decoded;
            }
        }

        return null;
    }
}
