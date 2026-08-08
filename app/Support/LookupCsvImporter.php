<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class LookupCsvImporter
{
    /**
     * @param  list<string>  $requiredHeaders
     * @param  array<string, string>  $aliases  normalized_header => canonical
     * @param  callable(array<string, string>, int): ?string  $rowHandler  return error message or null on success
     * @return array{imported: int, skipped: int, failed: int, errors: list<array{row: int, message: string}>}
     */
    public function import(
        UploadedFile $file,
        array $requiredHeaders,
        array $aliases,
        callable $rowHandler,
    ): array {
        $handle = fopen($file->getRealPath(), 'r');

        if ($handle === false) {
            return $this->result(0, 0, 1, [[
                'row' => 0,
                'message' => 'Unable to read the uploaded CSV file.',
            ]]);
        }

        $headerRow = fgetcsv($handle);

        if ($headerRow === false || $this->rowIsEmpty($headerRow)) {
            fclose($handle);

            return $this->result(0, 0, 1, [[
                'row' => 1,
                'message' => 'CSV file is empty or missing a header row.',
            ]]);
        }

        if (isset($headerRow[0])) {
            $headerRow[0] = preg_replace('/^\xEF\xBB\xBF/', '', (string) $headerRow[0]) ?? (string) $headerRow[0];
        }

        $columnMap = $this->mapHeaders($headerRow, $aliases);
        $missing = array_values(array_diff($requiredHeaders, array_keys($columnMap)));

        if ($missing !== []) {
            fclose($handle);

            return $this->result(0, 0, 1, [[
                'row' => 1,
                'message' => 'Missing required column(s): '.implode(', ', $missing).'.',
            ]]);
        }

        $imported = 0;
        $skipped = 0;
        $failed = 0;
        $errors = [];
        $rowNumber = 1;

        while (($row = fgetcsv($handle)) !== false) {
            $rowNumber++;

            if ($this->rowIsEmpty($row)) {
                continue;
            }

            $data = [];
            foreach ($columnMap as $canonical => $index) {
                $data[$canonical] = trim((string) ($row[$index] ?? ''));
            }

            $message = $rowHandler($data, $rowNumber);

            if ($message === null) {
                $imported++;
            } elseif ($message === '__skipped__') {
                $skipped++;
            } else {
                $failed++;
                $errors[] = [
                    'row' => $rowNumber,
                    'message' => $message,
                ];
            }
        }

        fclose($handle);

        return $this->result($imported, $skipped, $failed, $errors);
    }

    public static function parseActive(?string $value, bool $default = true): bool
    {
        if ($value === null || trim($value) === '') {
            return $default;
        }

        return in_array(Str::lower(trim($value)), ['1', 'true', 'yes', 'on', 'active'], true);
    }

    /**
     * @param  list<string|null>  $row
     */
    private function rowIsEmpty(array $row): bool
    {
        foreach ($row as $cell) {
            if (trim((string) $cell) !== '') {
                return false;
            }
        }

        return true;
    }

    /**
     * @param  list<string|null>  $headerRow
     * @param  array<string, string>  $aliases
     * @return array<string, int>
     */
    private function mapHeaders(array $headerRow, array $aliases): array
    {
        $map = [];

        foreach ($headerRow as $index => $header) {
            $normalized = Str::lower(trim((string) $header));
            $normalized = str_replace([' ', '-'], '_', $normalized);

            if (! isset($aliases[$normalized])) {
                continue;
            }

            $canonical = $aliases[$normalized];
            if (! isset($map[$canonical])) {
                $map[$canonical] = $index;
            }
        }

        return $map;
    }

    /**
     * @param  list<array{row: int, message: string}>  $errors
     * @return array{imported: int, skipped: int, failed: int, errors: list<array{row: int, message: string}>}
     */
    private function result(int $imported, int $skipped, int $failed, array $errors): array
    {
        return compact('imported', 'skipped', 'failed', 'errors');
    }
}
