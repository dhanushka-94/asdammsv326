<?php

namespace App\Support;

use App\Models\Institute;

class OrgLookups
{
    /**
     * Nested tree for cascading institute → sub-institute → section selects.
     *
     * @return list<array{name: string, sub_institutes: list<array{name: string, sections: list<string>}>}>
     */
    public static function cascadeTree(bool $activeOnly = true): array
    {
        $query = Institute::query()
            ->with(['subInstitutes' => function ($q) use ($activeOnly) {
                if ($activeOnly) {
                    $q->where('is_active', true);
                }
                $q->with(['sections' => function ($sq) use ($activeOnly) {
                    if ($activeOnly) {
                        $sq->where('is_active', true);
                    }
                    $sq->orderBy('name');
                }])->orderBy('name');
            }])
            ->orderBy('name');

        if ($activeOnly) {
            $query->where('is_active', true);
        }

        return $query->get()->map(function (Institute $institute) {
            return [
                'name' => $institute->name,
                'sub_institutes' => $institute->subInstitutes->map(function ($sub) {
                    return [
                        'name' => $sub->name,
                        'sections' => $sub->sections->pluck('name')->values()->all(),
                    ];
                })->values()->all(),
            ];
        })->values()->all();
    }
}
