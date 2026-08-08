<?php

namespace App\Models;

use App\Support\SriLankaDate;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class Event extends Model
{
    protected $fillable = [
        'name',
        'description',
        'method',
        'start_date',
        'end_date',
        'start_time',
        'end_time',
        'status',
        'invitation_letter_path',
        'invitation_card_path',
        'invitation_letter_settings',
        'invitation_card_settings',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'invitation_letter_settings' => 'array',
            'invitation_card_settings' => 'array',
        ];
    }

    public function venues(): HasMany
    {
        return $this->hasMany(EventVenue::class)->orderBy('sort_order');
    }

    public function days(): HasMany
    {
        return $this->hasMany(EventDay::class)->orderBy('sort_order')->orderBy('day_number');
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(EventEnrollment::class);
    }

    public function activeEnrollments(): HasMany
    {
        return $this->hasMany(EventEnrollment::class)->active();
    }

    public function receptionUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'event_reception_user')
            ->withTimestamps()
            ->orderBy('name');
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(EventAttendance::class);
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(Member::class, 'event_enrollments')
            ->wherePivotNull('kicked_at')
            ->withPivot(['enrolled_at', 'kicked_at', 'kick_reason', 'kicked_by'])
            ->withTimestamps();
    }

    public function isPhysical(): bool
    {
        return in_array($this->method, ['physical', 'both'], true);
    }

    public function isOnline(): bool
    {
        return in_array($this->method, ['online', 'both'], true);
    }

    public function isBothMethods(): bool
    {
        return $this->method === 'both';
    }

    public function methodLabel(): string
    {
        return match ($this->method) {
            'online' => 'Online',
            'both' => 'Physical + Online',
            default => 'Physical',
        };
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isInactive(): bool
    {
        return $this->status === 'inactive';
    }

    public function isUpcoming(): bool
    {
        $start = $this->startsAt();

        return $start !== null && SriLankaDate::now()->lessThan($start);
    }

    public function isOngoing(): bool
    {
        $start = $this->startsAt();
        $end = $this->endsAt();

        if (! $start || ! $end) {
            return false;
        }

        $now = SriLankaDate::now();

        return $now->greaterThanOrEqualTo($start) && $now->lessThanOrEqualTo($end);
    }

    public function requiresVenue(): bool
    {
        return $this->isPhysical();
    }

    public function isOpenForEnrollment(): bool
    {
        if (! $this->isActive()) {
            return false;
        }

        return ! $this->hasEnded();
    }

    public static function hasAvailableForMembers(): bool
    {
        return static::query()
            ->where('status', 'active')
            ->get()
            ->contains(fn (self $event) => $event->isOpenForEnrollment());
    }

    public function statusLabel(): string
    {
        return $this->isActive() ? 'Active' : 'Inactive';
    }

    public function timelineLabel(): string
    {
        if ($this->hasEnded()) {
            return 'Ended';
        }

        if ($this->isUpcoming()) {
            return 'Upcoming';
        }

        if ($this->isOngoing()) {
            return 'Ongoing';
        }

        return 'Scheduled';
    }

    public function hasEnded(): bool
    {
        $end = $this->endsAt();

        return $end !== null && SriLankaDate::now()->greaterThan($end);
    }

    public function startsAt(): ?Carbon
    {
        if (! $this->start_date || ! $this->start_time) {
            return null;
        }

        return SriLankaDate::of($this->start_date->format('Y-m-d').' '.$this->start_time);
    }

    public function endsAt(): ?Carbon
    {
        if (! $this->end_date || ! $this->end_time) {
            return null;
        }

        return SriLankaDate::of($this->end_date->format('Y-m-d').' '.$this->end_time);
    }

    public function scheduleLabel(): string
    {
        $startDate = SriLankaDate::date($this->start_date);
        $endDate = SriLankaDate::date($this->end_date);
        $startTime = substr((string) $this->start_time, 0, 5);
        $endTime = substr((string) $this->end_time, 0, 5);

        $startTimeLabel = SriLankaDate::format($this->start_date->format('Y-m-d').' '.$startTime, SriLankaDate::TIME);
        $endTimeLabel = SriLankaDate::format($this->end_date->format('Y-m-d').' '.$endTime, SriLankaDate::TIME);

        if ($startDate === $endDate) {
            return $startDate.' · '.$startTimeLabel.' – '.$endTimeLabel;
        }

        return $startDate.' '.$startTimeLabel.' → '.$endDate.' '.$endTimeLabel;
    }

    public function hasInvitationLetter(): bool
    {
        return filled($this->invitation_letter_path);
    }

    public function hasInvitationCard(): bool
    {
        return filled($this->invitation_card_path);
    }

    public function hasAnyInvitation(): bool
    {
        return $this->hasInvitationLetter() || $this->hasInvitationCard();
    }
}
