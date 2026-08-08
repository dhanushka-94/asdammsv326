<?php

namespace App\Support;

use App\Models\Event;
use App\Models\Member;
use Illuminate\Support\Facades\Storage;
use setasign\Fpdi\Fpdi;
use Symfony\Component\HttpFoundation\StreamedResponse;
use RuntimeException;

class EventInvitationPdf
{
    public const TYPE_LETTER = 'letter';

    public const TYPE_CARD = 'card';

    /**
     * @return array{name: array{x: float, y: float, size: float, align: string}, address?: array{x: float, y: float, size: float, width: float, align: string}}
     */
    public static function defaultLetterSettings(): array
    {
        return [
            'name' => [
                'x' => 25.0,
                'y' => 48.0,
                'size' => 12.0,
                'align' => 'L',
            ],
            'address' => [
                'x' => 25.0,
                'y' => 56.0,
                'size' => 11.0,
                'width' => 110.0,
                'align' => 'L',
            ],
        ];
    }

    /**
     * @return array{name: array{x: float|null, y: float, size: float, align: string, center_x?: bool}}
     */
    public static function defaultCardSettings(): array
    {
        return [
            'name' => [
                'x' => null,
                'y' => 95.0,
                'size' => 16.0,
                'align' => 'C',
                'center_x' => true,
            ],
        ];
    }

    public static function storageDirectory(Event $event): string
    {
        return 'events/invitations/'.$event->id;
    }

    public static function absolutePath(?string $relativePath): ?string
    {
        if (! $relativePath) {
            return null;
        }

        $full = Storage::disk('public')->path($relativePath);

        return is_file($full) ? $full : null;
    }

    public static function download(Event $event, Member $member, string $type): StreamedResponse
    {
        $binary = self::generate($event, $member, $type);
        $suffix = $type === self::TYPE_CARD ? 'Card' : 'Letter';
        $memberKey = $member->unique_id ?: ('M'.$member->id);
        $filename = 'ASDA_Invitation_'.$suffix.'_'.$memberKey.'.pdf';

        return response()->streamDownload(function () use ($binary): void {
            echo $binary;
        }, $filename, [
            'Content-Type' => 'application/pdf',
        ]);
    }

    public static function generate(Event $event, Member $member, string $type): string
    {
        $relative = $type === self::TYPE_CARD
            ? $event->invitation_card_path
            : $event->invitation_letter_path;

        $template = self::absolutePath($relative);
        if (! $template) {
            throw new RuntimeException('Invitation template is not available.');
        }

        $settings = $type === self::TYPE_CARD
            ? array_replace_recursive(self::defaultCardSettings(), $event->invitation_card_settings ?? [])
            : array_replace_recursive(self::defaultLetterSettings(), $event->invitation_letter_settings ?? []);

        $pdf = new Fpdi;
        $pageCount = $pdf->setSourceFile($template);

        for ($page = 1; $page <= $pageCount; $page++) {
            $tpl = $pdf->importPage($page);
            $size = $pdf->getTemplateSize($tpl);
            $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
            $pdf->useTemplate($tpl);

            if ($page === 1) {
                if ($type === self::TYPE_CARD) {
                    self::writeCardFields($pdf, $member, $settings, (float) $size['width']);
                } else {
                    self::writeLetterFields($pdf, $member, $settings);
                }
            }
        }

        return $pdf->Output('S');
    }

    /**
     * @param  array<string, mixed>  $settings
     */
    private static function writeLetterFields(Fpdi $pdf, Member $member, array $settings): void
    {
        $name = self::safeText($member->displayName());
        $address = trim((string) $member->address);
        $address = $address !== '' ? self::safeText($address) : '—';

        $nameCfg = $settings['name'] ?? [];
        $pdf->SetFont('Helvetica', 'B', (float) ($nameCfg['size'] ?? 12));
        $pdf->SetTextColor(20, 20, 20);
        $pdf->SetXY((float) ($nameCfg['x'] ?? 25), (float) ($nameCfg['y'] ?? 48));
        $pdf->Cell(0, 6, $name, 0, 1, (string) ($nameCfg['align'] ?? 'L'));

        $addressCfg = $settings['address'] ?? [];
        $pdf->SetFont('Helvetica', '', (float) ($addressCfg['size'] ?? 11));
        $pdf->SetXY((float) ($addressCfg['x'] ?? 25), (float) ($addressCfg['y'] ?? 56));
        $pdf->MultiCell(
            (float) ($addressCfg['width'] ?? 110),
            5,
            $address,
            0,
            (string) ($addressCfg['align'] ?? 'L')
        );
    }

    /**
     * @param  array<string, mixed>  $settings
     */
    private static function writeCardFields(Fpdi $pdf, Member $member, array $settings, float $pageWidth): void
    {
        $name = self::safeText($member->displayName());
        $nameCfg = $settings['name'] ?? [];
        $size = (float) ($nameCfg['size'] ?? 16);
        $y = (float) ($nameCfg['y'] ?? 95);
        $align = (string) ($nameCfg['align'] ?? 'C');
        $center = (bool) ($nameCfg['center_x'] ?? true);

        $pdf->SetFont('Helvetica', 'B', $size);
        $pdf->SetTextColor(20, 20, 20);

        if ($center || ($nameCfg['x'] ?? null) === null) {
            $pdf->SetXY(10, $y);
            $pdf->Cell($pageWidth - 20, 8, $name, 0, 0, $align);
        } else {
            $pdf->SetXY((float) $nameCfg['x'], $y);
            $pdf->Cell(0, 8, $name, 0, 0, $align);
        }
    }

    private static function safeText(string $value): string
    {
        $converted = @iconv('UTF-8', 'ISO-8859-1//TRANSLIT//IGNORE', $value);

        return $converted !== false ? $converted : preg_replace('/[^\x20-\x7E]/', '', $value) ?? $value;
    }
}
