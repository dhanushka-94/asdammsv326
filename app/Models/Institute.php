<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Institute extends Model
{
    protected $fillable = [
        'name',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function subInstitutes(): HasMany
    {
        return $this->hasMany(SubInstitute::class)->orderBy('name');
    }

    public function members(): HasMany
    {
        return $this->hasMany(Member::class, 'institute', 'name');
    }
}
