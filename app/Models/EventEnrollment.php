<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EventEnrollment extends Model
{
    protected $fillable = [
        'event_id',
        'member_id',
        'enrolled_at',
        'participation_mode',
        'kicked_at',
        'kick_reason',
        'kicked_by',
    ];

    protected function casts(): array
    {
        return [
            'enrolled_at' => 'datetime',
            'kicked_at' => 'datetime',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function kickedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'kicked_by');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(EventEnrollmentAnswer::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('kicked_at');
    }

    public function isActive(): bool
    {
        return $this->kicked_at === null;
    }

    public function isKicked(): bool
    {
        return $this->kicked_at !== null;
    }

    public function participationModeLabel(): string
    {
        return match ($this->participation_mode) {
            'online' => 'Online',
            'physical' => 'Physical',
            default => '—',
        };
    }
}
