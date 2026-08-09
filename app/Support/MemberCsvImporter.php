<?php

namespace App\Support;

use App\Models\Designation;
use App\Models\Member;
use App\Models\MemberCategory;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class MemberCsvImporter
{
    /** @var list<string> */
    public const REQUIRED_HEADERS = [
        'title',
        'full_name',
        'nic',
        'designation',
        'mobile_1',
    ];

    /** @var array<string, string> */
    private const HEADER_ALIASES = [
        'title' => 'title',
        'full_name' => 'full_name',
        'fullname' => 'full_name',
        'fullname_name' => 'full_name',
        'nic' => 'nic',
        'id_number' => 'nic',
        'id_no' => 'nic',
        'nic_number' => 'nic',
        'national_id' => 'nic',
        'nic_no' => 'nic',
        'designation' => 'designation',
        'designation_name' => 'designation',
        'category' => 'category',
        'member_category' => 'category',
        'category_name' => 'category',
        'mobile_1' => 'mobile_1',
        'mobile' => 'mobile_1',
        'mobile_number' => 'mobile_1',
        'mobile_number_1' => 'mobile_1',
        'mobile_2' => 'mobile_2',
        'mobile_number_2' => 'mobile_2',
        'whatsapp' => 'whatsapp',
        'office_telephone' => 'office_telephone',
        'office_phone' => 'office_telephone',
        'telephone' => 'office_telephone',
        'email' => 'email',
        'institute' => 'institute',
        'sub_institute' => 'sub_institute',
        'section' => 'section',
        'address' => 'address',
        'registration_status' => 'registration_status',
        'status' => 'status',
    ];

    /**
     * @return array{
     *     imported: int,
     *     skipped_duplicates: int,
     *     failed: int,
     *     errors: list<array{row: int, nic: string|null, message: string}>
     * }
     */
    public function import(UploadedFile $file): array
    {
        $handle = fopen($file->getRealPath(), 'r');

        if ($handle === false) {
            return $this->result(0, 0, 1, [[
                'row' => 0,
                'nic' => null,
                'message' => 'Unable to read the uploaded CSV file.',
            ]]);
        }

        $headerRow = fgetcsv($handle);

        if ($headerRow === false || $this->rowIsEmpty($headerRow)) {
            fclose($handle);

            return $this->result(0, 0, 1, [[
                'row' => 1,
                'nic' => null,
                'message' => 'CSV file is empty or missing a header row.',
            ]]);
        }

        // Strip UTF-8 BOM from first header cell if present.
        if (isset($headerRow[0])) {
            $headerRow[0] = preg_replace('/^\xEF\xBB\xBF/', '', (string) $headerRow[0]) ?? (string) $headerRow[0];
        }

        $columnMap = $this->mapHeaders($headerRow);
        $missing = array_values(array_diff(self::REQUIRED_HEADERS, array_keys($columnMap)));

        if ($missing !== []) {
            fclose($handle);

            return $this->result(0, 0, 1, [[
                'row' => 1,
                'nic' => null,
                'message' => 'Missing required column(s): '.implode(', ', $missing).'.',
            ]]);
        }

        $designations = Designation::query()
            ->get()
            ->keyBy(fn (Designation $d) => Str::lower(trim($d->name)));

        $categories = MemberCategory::query()
            ->get()
            ->keyBy(fn (MemberCategory $c) => Str::lower(trim($c->name)));

        $existingNics = Member::query()
            ->pluck('nic')
            ->map(fn ($nic) => SriLankaFormat::normalizeNic((string) $nic))
            ->flip()
            ->all();

        $seenInFile = [];
        $imported = 0;
        $skippedDuplicates = 0;
        $failed = 0;
        $errors = [];
        $rowNumber = 1;

        while (($row = fgetcsv($handle)) !== false) {
            $rowNumber++;

            if ($this->rowIsEmpty($row)) {
                continue;
            }

            $data = $this->extractRow($row, $columnMap);
            $nic = SriLankaFormat::normalizeNic($data['nic'] ?? null);
            $data['nic'] = $nic;

            if ($nic === '') {
                $failed++;
                $errors[] = [
                    'row' => $rowNumber,
                    'nic' => null,
                    'message' => 'NIC / ID number is required.',
                ];

                continue;
            }

            if (isset($existingNics[$nic]) || isset($seenInFile[$nic])) {
                $skippedDuplicates++;
                $errors[] = [
                    'row' => $rowNumber,
                    'nic' => $nic,
                    'message' => isset($seenInFile[$nic])
                        ? 'Duplicate ID number in this CSV file (skipped).'
                        : 'ID number already registered (skipped).',
                ];

                continue;
            }

            $designationKey = Str::lower(trim((string) ($data['designation'] ?? '')));
            $designation = $designations->get($designationKey);

            if (! $designation) {
                $failed++;
                $errors[] = [
                    'row' => $rowNumber,
                    'nic' => $nic,
                    'message' => 'Unknown designation: '.trim((string) ($data['designation'] ?? '')).'.',
                ];

                continue;
            }

            $categoryName = trim((string) ($data['category'] ?? ''));
            $categoryId = null;

            if ($categoryName !== '') {
                $category = $categories->get(Str::lower($categoryName));

                if (! $category) {
                    $failed++;
                    $errors[] = [
                        'row' => $rowNumber,
                        'nic' => $nic,
                        'message' => 'Unknown category: '.$categoryName.'.',
                    ];

                    continue;
                }

                $categoryId = $category->id;
            }

            $rawStatus = trim((string) ($data['status'] ?? ''));

            $payload = [
                'title' => $this->normalizeTitle($data['title'] ?? null),
                'full_name' => trim((string) ($data['full_name'] ?? '')),
                'nic' => $nic,
                'designation_id' => $designation->id,
                'member_category_id' => $categoryId,
                'mobile_1' => SriLankaFormat::normalizePhone($data['mobile_1'] ?? null),
                'mobile_2' => SriLankaFormat::normalizePhone($data['mobile_2'] ?? null),
                'whatsapp' => SriLankaFormat::normalizePhone($data['whatsapp'] ?? null),
                'office_telephone' => SriLankaFormat::normalizePhone($data['office_telephone'] ?? null),
                'email' => $this->nullableString($data['email'] ?? null),
                'institute' => $this->nullableString($data['institute'] ?? null),
                'sub_institute' => $this->nullableString($data['sub_institute'] ?? null),
                'section' => $this->nullableString($data['section'] ?? null),
                'address' => $this->nullableString($data['address'] ?? null),
                'registration_status' => $this->normalizeEnum($data['registration_status'] ?? null, ['pending', 'approved', 'rejected'], 'pending'),
                'status' => $this->normalizeEnum($rawStatus, ['active', 'inactive'], 'inactive'),
            ];

            $validator = Validator::make($payload, [
                'title' => ['required', 'in:Dr,Mr,Mrs,Ms,Prof,Eng'],
                'full_name' => ['required', 'string', 'max:255'],
                'nic' => ['required', 'string', 'max:12', function (string $attribute, mixed $value, \Closure $fail): void {
                    if (! SriLankaFormat::isValidNic((string) $value)) {
                        $fail('Invalid Sri Lankan NIC / ID number.');
                    }
                }],
                'designation_id' => ['required', 'integer'],
                'member_category_id' => ['nullable', 'integer'],
                'mobile_1' => ['required', 'string', function (string $attribute, mixed $value, \Closure $fail): void {
                    if (! SriLankaFormat::isValidMobile((string) $value)) {
                        $fail('Invalid mobile number 1.');
                    }
                }],
                'mobile_2' => ['nullable', 'string', function (string $attribute, mixed $value, \Closure $fail): void {
                    if ($value !== null && $value !== '' && ! SriLankaFormat::isValidMobile((string) $value)) {
                        $fail('Invalid mobile number 2.');
                    }
                }],
                'whatsapp' => ['nullable', 'string', function (string $attribute, mixed $value, \Closure $fail): void {
                    if ($value !== null && $value !== '' && ! SriLankaFormat::isValidMobile((string) $value)) {
                        $fail('Invalid WhatsApp number.');
                    }
                }],
                'office_telephone' => ['nullable', 'string', function (string $attribute, mixed $value, \Closure $fail): void {
                    if ($value !== null && $value !== '' && ! SriLankaFormat::isValidLandline((string) $value)) {
                        $fail('Invalid office landline number.');
                    }
                }],
                'email' => ['nullable', 'email', 'max:255'],
                'institute' => ['nullable', 'string', 'max:255'],
                'sub_institute' => ['nullable', 'string', 'max:255'],
                'section' => ['nullable', 'string', 'max:255'],
                'address' => ['nullable', 'string', 'max:1000'],
                'registration_status' => ['required', 'in:pending,approved,rejected'],
                'status' => ['required', 'in:active,inactive'],
            ]);

            if ($validator->fails()) {
                $failed++;
                $errors[] = [
                    'row' => $rowNumber,
                    'nic' => $nic,
                    'message' => $validator->errors()->first(),
                ];

                continue;
            }

            $create = $validator->validated();
            $create['password'] = Member::defaultPasswordForNic($create['nic']);
            $create['must_change_password'] = true;
            $create['unique_id'] = Member::generateUniqueId();

            if ($create['registration_status'] === 'approved') {
                if ($rawStatus === '') {
                    $create['status'] = 'active';
                }
                $create['approved_at'] = now();
                $create['approved_by'] = Auth::id();
            } elseif ($create['registration_status'] === 'pending' && $rawStatus === '') {
                $create['status'] = 'inactive';
            }

            Member::create($create);

            $seenInFile[$nic] = true;
            $existingNics[$nic] = true;
            $imported++;
        }

        fclose($handle);

        return $this->result($imported, $skippedDuplicates, $failed, $errors);
    }

    /**
     * @return list<string>
     */
    public static function templateHeaders(): array
    {
        return [
            'title',
            'full_name',
            'nic',
            'designation',
            'category',
            'mobile_1',
            'mobile_2',
            'whatsapp',
            'office_telephone',
            'email',
            'institute',
            'sub_institute',
            'section',
            'address',
            'registration_status',
            'status',
        ];
    }

    /**
     * @return list<string>
     */
    public static function sampleRow(): array
    {
        return [
            'Mr',
            'Sample Member',
            '199012345678',
            'Agricultural Officer',
            'General',
            '0771234567',
            '',
            '0771234567',
            '',
            'sample@example.com',
            'Department of Agriculture',
            '',
            '',
            'Colombo',
            'pending',
            'inactive',
        ];
    }

    /**
     * @param  list<string|null>  $headerRow
     * @return array<string, int>
     */
    private function mapHeaders(array $headerRow): array
    {
        $map = [];

        foreach ($headerRow as $index => $header) {
            $key = $this->normalizeHeader((string) $header);

            if ($key === '' || ! isset(self::HEADER_ALIASES[$key])) {
                continue;
            }

            $canonical = self::HEADER_ALIASES[$key];

            if (! isset($map[$canonical])) {
                $map[$canonical] = $index;
            }
        }

        return $map;
    }

    private function normalizeHeader(string $header): string
    {
        $header = Str::lower(trim($header));
        $header = str_replace(['/', '\\'], ' ', $header);
        $header = preg_replace('/[^a-z0-9]+/', '_', $header) ?? $header;

        return trim($header, '_');
    }

    /**
     * @param  list<string|null>  $row
     * @param  array<string, int>  $columnMap
     * @return array<string, string|null>
     */
    private function extractRow(array $row, array $columnMap): array
    {
        $data = [];

        foreach ($columnMap as $field => $index) {
            $data[$field] = isset($row[$index]) ? trim((string) $row[$index]) : null;
        }

        return $data;
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

    private function nullableString(mixed $value): ?string
    {
        $value = trim((string) ($value ?? ''));

        return $value === '' ? null : $value;
    }

    private function normalizeTitle(mixed $value): string
    {
        $value = trim((string) ($value ?? ''));
        $map = [
            'dr' => 'Dr',
            'mr' => 'Mr',
            'mrs' => 'Mrs',
            'ms' => 'Ms',
            'prof' => 'Prof',
            'eng' => 'Eng',
        ];

        return $map[Str::lower($value)] ?? $value;
    }

    /**
     * @param  list<string>  $allowed
     */
    private function normalizeEnum(mixed $value, array $allowed, string $default): string
    {
        $value = Str::lower(trim((string) ($value ?? '')));

        return in_array($value, $allowed, true) ? $value : $default;
    }

    /**
     * @param  list<array{row: int, nic: string|null, message: string}>  $errors
     * @return array{imported: int, skipped_duplicates: int, failed: int, errors: list<array{row: int, nic: string|null, message: string}>}
     */
    private function result(int $imported, int $skippedDuplicates, int $failed, array $errors): array
    {
        return [
            'imported' => $imported,
            'skipped_duplicates' => $skippedDuplicates,
            'failed' => $failed,
            'errors' => $errors,
        ];
    }
}
