<?php

namespace App\Models;

use App\Support\MemberQrCode;
use App\Support\SriLankaFormat;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class Member extends Authenticatable
{
    protected static function booted(): void
    {
        static::saved(function (Member $member) {
            if ($member->unique_id && ($member->wasRecentlyCreated || $member->wasChanged('unique_id'))) {
                try {
                    MemberQrCode::store($member->unique_id);
                } catch (\Throwable $e) {
                    report($e);
                }
            }
        });

        static::deleting(function (Member $member) {
            MemberQrCode::delete($member->unique_id);
        });
    }

    protected $fillable = [
        'unique_id',
        'title',
        'full_name',
        'nic',
        'designation_id',
        'member_category_id',
        'mobile_1',
        'mobile_2',
        'whatsapp',
        'office_telephone',
        'email',
        'institute',
        'sub_institute',
        'section',
        'address',
        'profile_image',
        'registration_status',
        'status',
        'password',
        'must_change_password',
        'rejection_reason',
        'approved_at',
        'approved_by',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'approved_at' => 'datetime',
            'must_change_password' => 'boolean',
            'last_login_at' => 'datetime',
            'qr_last_downloaded_at' => 'datetime',
        ];
    }

    public function designation(): BelongsTo
    {
        return $this->belongsTo(Designation::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(MemberCategory::class, 'member_category_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function eventEnrollments(): HasMany
    {
        return $this->hasMany(EventEnrollment::class);
    }

    public function activeEventEnrollments(): HasMany
    {
        return $this->hasMany(EventEnrollment::class)->active()->latest('enrolled_at');
    }

    public function events(): BelongsToMany
    {
        return $this->belongsToMany(Event::class, 'event_enrollments')
            ->wherePivotNull('kicked_at')
            ->withPivot(['enrolled_at', 'kicked_at', 'kick_reason', 'kicked_by'])
            ->withTimestamps();
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(EventAttendance::class)->latest('checked_in_at');
    }

    public function isApproved(): bool
    {
        return $this->registration_status === 'approved';
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function canLogin(): bool
    {
        return $this->isApproved() && $this->isActive();
    }

    public function homeRoute(): string
    {
        if (! $this->canLogin()) {
            return 'member.waiting-approval';
        }

        if ($this->must_change_password) {
            return 'member.password.edit';
        }

        if (Event::hasAvailableForMembers()) {
            return 'member.events.index';
        }

        return 'member.profile';
    }

    public function displayName(): string
    {
        return trim($this->title.' '.$this->full_name);
    }

    public function recordLogin(): void
    {
        $this->forceFill([
            'last_login_at' => now(),
        ])->save();
    }

    public function recordQrDownload(): void
    {
        $this->forceFill([
            'qr_download_count' => $this->qr_download_count + 1,
            'qr_last_downloaded_at' => now(),
        ])->save();
    }

    public function hasDownloadedQr(): bool
    {
        return (int) $this->qr_download_count > 0;
    }

    public static function defaultPasswordForNic(string $nic): string
    {
        return SriLankaFormat::defaultPasswordFromNic($nic);
    }

    public function defaultPassword(): string
    {
        return self::defaultPasswordForNic($this->nic);
    }

    public function dateOfBirth(): ?\Carbon\Carbon
    {
        return SriLankaFormat::birthDateFromNic($this->nic);
    }

    public function age(): ?int
    {
        return SriLankaFormat::ageFromNic($this->nic);
    }

    public function isOverSixtyOne(): bool
    {
        return SriLankaFormat::isOverAgeFromNic($this->nic, 61);
    }

    public function profileImageUrl(): ?string
    {
        return $this->profile_image
            ? Storage::disk('public')->url($this->profile_image)
            : null;
    }

    public function qrCodeUrl(): ?string
    {
        return $this->unique_id ? MemberQrCode::url($this->unique_id) : null;
    }

    public function qrCodePath(): ?string
    {
        return $this->unique_id ? MemberQrCode::ensure($this->unique_id) : null;
    }

    /**
     * Pattern: ASDA + YY + 6 random alphanumeric chars (A-Z, 2-9).
     * Example: ASDA26K7M2X9 — one word, no special characters.
     */
    public static function generateUniqueId(): string
    {
        $prefix = 'ASDA'.now()->format('y');
        $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

        do {
            $random = '';
            for ($i = 0; $i < 6; $i++) {
                $random .= $alphabet[random_int(0, strlen($alphabet) - 1)];
            }

            $uniqueId = $prefix.$random;
        } while (static::query()->where('unique_id', $uniqueId)->exists());

        return $uniqueId;
    }

    public function assignUniqueIdIfMissing(): void
    {
        if ($this->unique_id) {
            return;
        }

        DB::transaction(function () {
            $this->unique_id = static::generateUniqueId();
            $this->save();
        });
    }
}
