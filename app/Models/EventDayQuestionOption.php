<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventDayQuestionOption extends Model
{
    protected $fillable = [
        'event_day_question_id',
        'sort_order',
        'label',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(EventDayQuestion::class, 'event_day_question_id');
    }
}
