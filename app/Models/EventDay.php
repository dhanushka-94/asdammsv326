<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EventDay extends Model
{
    protected $fillable = [
        'event_id',
        'sort_order',
        'day_number',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'day_number' => 'integer',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(EventDaySession::class)->orderBy('sort_order');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(EventDayQuestion::class)->orderBy('sort_order');
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(EventAttendance::class);
    }

    public function dayLabel(): string
    {
        return 'Day '.$this->day_number;
    }

    public function summaryLabel(): string
    {
        $sessionCount = $this->relationLoaded('sessions')
            ? $this->sessions->count()
            : $this->sessions()->count();

        return $this->dayLabel().' · '.$sessionCount.' session'.($sessionCount === 1 ? '' : 's');
    }
}
