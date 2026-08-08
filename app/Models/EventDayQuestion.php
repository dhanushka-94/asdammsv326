<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EventDayQuestion extends Model
{
    protected $fillable = [
        'event_day_id',
        'sort_order',
        'question',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    public function day(): BelongsTo
    {
        return $this->belongsTo(EventDay::class, 'event_day_id');
    }

    public function options(): HasMany
    {
        return $this->hasMany(EventDayQuestionOption::class)->orderBy('sort_order');
    }
}
