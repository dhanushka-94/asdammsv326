<?php

namespace App\Support;

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Writer\Result\ResultInterface;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MemberQrCode
{
    public const DIRECTORY = 'members/qr';

    public const SIZE = 1024;

    public static function relativePath(string $uniqueId): string
    {
        return self::DIRECTORY.'/'.$uniqueId.'.png';
    }

    public static function make(string $uniqueId, int $size = self::SIZE): ResultInterface
    {
        return (new Builder(
            writer: new PngWriter(),
            data: $uniqueId,
            errorCorrectionLevel: ErrorCorrectionLevel::High,
            size: $size,
            margin: 16,
        ))->build();
    }

    public static function store(string $uniqueId): string
    {
        $disk = Storage::disk('public');
        $path = self::relativePath($uniqueId);

        if (! $disk->exists(self::DIRECTORY)) {
            $disk->makeDirectory(self::DIRECTORY);
        }

        $written = $disk->put($path, self::make($uniqueId)->getString());

        if (! $written || ! $disk->exists($path)) {
            throw new RuntimeException("Unable to write QR code for {$uniqueId}.");
        }

        return $path;
    }

    public static function regenerate(string $uniqueId): string
    {
        self::delete($uniqueId);

        return self::store($uniqueId);
    }

    public static function ensure(string $uniqueId): string
    {
        $path = self::relativePath($uniqueId);

        if (! Storage::disk('public')->exists($path)) {
            self::store($uniqueId);
        }

        return $path;
    }

    public static function url(string $uniqueId): string
    {
        return Storage::disk('public')->url(self::ensure($uniqueId));
    }

    public static function imageResponse(string $uniqueId): Response
    {
        $path = self::ensure($uniqueId);
        $disk = Storage::disk('public');

        return response($disk->get($path), 200, [
            'Content-Type' => 'image/png',
            'Content-Disposition' => 'inline; filename="'.$uniqueId.'.png"',
            'Cache-Control' => 'private, max-age=300',
        ]);
    }

    public static function downloadResponse(string $uniqueId, string $filename): StreamedResponse
    {
        $path = self::ensure($uniqueId);

        return Storage::disk('public')->download($path, $filename);
    }

    public static function delete(?string $uniqueId): void
    {
        if (! $uniqueId) {
            return;
        }

        Storage::disk('public')->delete(self::relativePath($uniqueId));
    }

    public static function downloadFilename(string $memberName, ?string $uniqueId = null): string
    {
        $base = preg_replace('/[^A-Za-z0-9]+/', '_', trim($memberName)) ?: 'member';
        $base = trim($base, '_');

        if ($uniqueId) {
            $base .= '_'.$uniqueId;
        }

        return $base.'_QR.png';
    }
}
