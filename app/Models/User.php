<?php

namespace App\Models;

use App\Support\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'role',
        'status',
        'password',
        'desk_pin_hash',
        'must_change_password',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'desk_pin_hash',
        'remember_token',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'must_change_password' => 'boolean',
        ];
    }

    public function hasDeskPin(): bool
    {
        return filled($this->desk_pin_hash);
    }

    public function setDeskPin(string $pin): void
    {
        $this->forceFill([
            'desk_pin_hash' => password_hash($pin, PASSWORD_BCRYPT),
        ])->save();
    }

    public function clearDeskPin(): void
    {
        $this->forceFill([
            'desk_pin_hash' => null,
        ])->save();
    }

    public function verifyDeskPin(string $pin): bool
    {
        if (! $this->hasDeskPin()) {
            return false;
        }

        return password_verify($pin, $this->desk_pin_hash);
    }

    public function receptionEvents(): BelongsToMany
    {
        return $this->belongsToMany(Event::class, 'event_reception_user')
            ->withTimestamps()
            ->orderBy('start_date');
    }

    public function attendancesCheckedIn(): HasMany
    {
        return $this->hasMany(EventAttendance::class, 'checked_in_by');
    }

    public function homeRoute(): string
    {
        if ($this->must_change_password) {
            return 'admin.set-password.edit';
        }

        if ($this->isReception()) {
            return 'admin.attendance.index';
        }

        return 'admin.dashboard';
    }

    /**
     * Default password: first 4 digits of phone + @ASDA
     */
    public function defaultPassword(): string
    {
        return \App\Support\SriLankaFormat::defaultPasswordFromDigits($this->phone ?: $this->email);
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === UserRole::SUPER_ADMIN;
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::ADMIN;
    }

    public function isViewer(): bool
    {
        return $this->role === UserRole::VIEWER;
    }

    public function isReception(): bool
    {
        return $this->role === UserRole::RECEPTION;
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function roleLabel(): string
    {
        return UserRole::label($this->role);
    }

    /** Super Admin only — system settings (public access / maintenance). */
    public function canManageSettings(): bool
    {
        return $this->isSuperAdmin();
    }

    /** Super Admin only — manage system users. */
    public function canManageUsers(): bool
    {
        return $this->isSuperAdmin();
    }

    /** Super Admin + Admin — create/edit/delete members and approvals. */
    public function canManageMembers(): bool
    {
        return $this->isSuperAdmin() || $this->isAdmin();
    }

    /** Super Admin only — bulk actions and CSV import. */
    public function canBulkManageMembers(): bool
    {
        return $this->isSuperAdmin();
    }

    /** Super Admin + Admin — manage designations. */
    public function canManageDesignations(): bool
    {
        return $this->isSuperAdmin() || $this->isAdmin();
    }

    /** Super Admin + Admin — manage institutes / sub-institutes / sections. */
    public function canManageInstitutes(): bool
    {
        return $this->isSuperAdmin() || $this->isAdmin();
    }

    /** Super Admin + Admin — manage member categories. */
    public function canManageMemberCategories(): bool
    {
        return $this->isSuperAdmin() || $this->isAdmin();
    }

    /** Super Admin + Admin — manage attendance check-in handout items. */
    public function canManageCheckInItems(): bool
    {
        return $this->isSuperAdmin() || $this->isAdmin();
    }

    /** Super Admin + Admin — manage events. */
    public function canManageEvents(): bool
    {
        return $this->isSuperAdmin() || $this->isAdmin();
    }

    /** Super Admin, Admin, Reception — attendance desk. */
    public function canAccessAttendance(): bool
    {
        return $this->isSuperAdmin() || $this->isAdmin() || $this->isReception();
    }

    public function canAccessEventAttendance(Event $event): bool
    {
        if ($this->isSuperAdmin() || $this->isAdmin()) {
            return true;
        }

        if (! $this->isReception()) {
            return false;
        }

        return $this->receptionEvents()->where('events.id', $event->id)->exists();
    }

    /** Super Admin, Admin, Viewer — analytics reports. */
    public function canViewReports(): bool
    {
        return $this->isSuperAdmin() || $this->isAdmin() || $this->isViewer();
    }

    public function canViewMembers(): bool
    {
        return in_array($this->role, UserRole::staff(), true);
    }
}
