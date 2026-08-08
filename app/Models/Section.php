<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Section extends Model
{
    protected $fillable = [
        'sub_institute_id',
        'name',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function subInstitute(): BelongsTo
    {
        return $this->belongsTo(SubInstitute::class);
    }

    public function members(): HasMany
    {
        return $this->hasMany(Member::class, 'section', 'name');
    }
}
