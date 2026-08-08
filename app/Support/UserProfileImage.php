<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class UserProfileImage
{
    public const DIRECTORY = 'users/profiles';

    /**
     * Store a system-user profile image as user-{id}.jpg (or similar extension).
     */
    public static function store(UploadedFile $file, int|string $userId, ?string $previousPath = null): string
    {
        $key = 'user-'.$userId;
        $extension = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'jpg');

        if (! in_array($extension, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true)) {
            $extension = 'jpg';
        }

        if ($extension === 'jpeg') {
            $extension = 'jpg';
        }

        self::deleteForUserId($userId);

        if ($previousPath && ! str_contains($previousPath, $key.'.')) {
            Storage::disk('public')->delete($previousPath);
        }

        return $file->storeAs(self::DIRECTORY, $key.'.'.$extension, 'public');
    }

    public static function deleteForUserId(int|string $userId): void
    {
        $key = 'user-'.$userId;

        foreach (['jpg', 'jpeg', 'png', 'webp', 'gif'] as $extension) {
            Storage::disk('public')->delete(self::DIRECTORY.'/'.$key.'.'.$extension);
        }
    }

    public static function delete(?string $path): void
    {
        if ($path) {
            Storage::disk('public')->delete($path);
        }
    }
}
