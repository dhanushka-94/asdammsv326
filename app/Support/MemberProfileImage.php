<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class MemberProfileImage
{
    public const DIRECTORY = 'members/profiles';

    /**
     * Store a profile image named after the member Unique ID (e.g. ASDA26K7M2X9.jpg).
     */
    public static function store(UploadedFile $file, string $uniqueId, ?string $previousPath = null): string
    {
        $uniqueId = trim($uniqueId);
        $extension = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'jpg');

        if (! in_array($extension, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true)) {
            $extension = 'jpg';
        }

        if ($extension === 'jpeg') {
            $extension = 'jpg';
        }

        self::deleteForUniqueId($uniqueId);

        if ($previousPath && ! str_contains($previousPath, $uniqueId.'.')) {
            Storage::disk('public')->delete($previousPath);
        }

        $filename = $uniqueId.'.'.$extension;

        return $file->storeAs(self::DIRECTORY, $filename, 'public');
    }

    public static function deleteForUniqueId(string $uniqueId): void
    {
        $uniqueId = trim($uniqueId);
        if ($uniqueId === '') {
            return;
        }

        foreach (['jpg', 'jpeg', 'png', 'webp', 'gif'] as $extension) {
            Storage::disk('public')->delete(self::DIRECTORY.'/'.$uniqueId.'.'.$extension);
        }
    }

    public static function delete(?string $path): void
    {
        if ($path) {
            Storage::disk('public')->delete($path);
        }
    }
}
