<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventEnrollmentAnswer extends Model
{
    protected $fillable = [
        'event_enrollment_id',
        'event_day_question_id',
        'event_day_question_option_id',
    ];

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(EventEnrollment::class, 'event_enrollment_id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(EventDayQuestion::class, 'event_day_question_id');
    }

    public function option(): BelongsTo
    {
        return $this->belongsTo(EventDayQuestionOption::class, 'event_day_question_option_id');
    }
}
