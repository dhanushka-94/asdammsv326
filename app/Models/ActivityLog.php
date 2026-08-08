<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ActivityLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'guard',
        'causer_type',
        'causer_id',
        'causer_name',
        'causer_role',
        'action',
        'description',
        'subject_type',
        'subject_id',
        'subject_label',
        'route_name',
        'method',
        'url',
        'ip_address',
        'user_agent',
        'properties',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'properties' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function causer(): MorphTo
    {
        return $this->morphTo();
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    public function actionLabel(): string
    {
        return match ($this->action) {
            'login' => 'Login',
            'login_failed' => 'Login failed',
            'logout' => 'Logout',
            'created' => 'Created',
            'updated' => 'Updated',
            'deleted' => 'Deleted',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            'downloaded' => 'Downloaded',
            'viewed' => 'Viewed',
            'registered' => 'Registered',
            'password_changed' => 'Password changed',
            'password_reset' => 'Password reset',
            default => ucfirst(str_replace('_', ' ', $this->action)),
        };
    }

    public function badgeClass(): string
    {
        return match ($this->action) {
            'login', 'approved' => 'badge-green',
            'logout' => 'badge-muted',
            'login_failed', 'deleted', 'rejected' => 'badge-orange',
            'created', 'registered', 'password_changed', 'password_reset', 'updated', 'downloaded' => 'badge-blue',
            default => 'badge-muted',
        };
    }

    public function guardLabel(): string
    {
        return match ($this->guard) {
            'web' => 'System user',
            'member' => 'Member',
            default => $this->guard ? ucfirst($this->guard) : 'System',
        };
    }
}
