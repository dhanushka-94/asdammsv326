<?php

namespace App\Support;

use App\Models\ActivityLog;
use App\Models\CheckInItem;
use App\Models\Event;
use App\Models\Member;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Throwable;

class ActivityLogger
{
    public static function log(
        string $action,
        string $description,
        ?Model $subject = null,
        ?array $properties = null,
        ?string $guard = null,
        ?Model $causer = null,
    ): void {
        try {
            $request = request();
            $guard = $guard ?? self::detectGuard();
            $causer = $causer ?? self::resolveCauser($guard);

            ActivityLog::query()->create([
                'guard' => $guard,
                'causer_type' => $causer ? $causer::class : null,
                'causer_id' => $causer?->getKey(),
                'causer_name' => self::causerName($causer),
                'causer_role' => self::causerRole($causer),
                'action' => $action,
                'description' => $description,
                'subject_type' => $subject ? $subject::class : null,
                'subject_id' => $subject?->getKey(),
                'subject_label' => self::subjectLabel($subject),
                'route_name' => $request?->route()?->getName(),
                'method' => $request?->method(),
                'url' => $request ? substr($request->fullUrl(), 0, 500) : null,
                'ip_address' => $request?->ip(),
                'user_agent' => $request?->userAgent(),
                'properties' => $properties,
                'created_at' => now(),
            ]);
        } catch (Throwable) {
            // Never break the app because logging failed.
        }
    }

    public static function fromRequest(Request $request): void
    {
        if (! in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            return;
        }

        $routeName = $request->route()?->getName();

        if (! $routeName || self::shouldSkipRoute($routeName)) {
            return;
        }

        [$action, $description] = self::describeRoute($routeName, $request);
        $subject = self::subjectFromRequest($request);

        if ($subject && $label = self::subjectLabel($subject)) {
            $description .= ': '.$label;
        }

        self::log($action, $description, $subject, [
            'route' => $routeName,
            'input_keys' => array_keys($request->except([
                'password',
                'password_confirmation',
                'current_password',
                'desk_pin',
                'desk_pin_confirmation',
                'current_desk_pin',
                'pin',
                '_token',
                '_method',
            ])),
        ]);
    }

    private static function shouldSkipRoute(string $routeName): bool
    {
        // Noisy / already logged with richer in-controller messages.
        return str_starts_with($routeName, 'admin.activity-logs')
            || in_array($routeName, [
                'member.waiting-approval.status',
                'admin.login.store',
                'member.login.store',
                'admin.logout',
                'member.logout',
                'admin.attendance.lookup',
                'admin.attendance.check-in',
                'admin.attendance.update-items',
                'admin.attendance.lock.store',
                'admin.attendance.unlock',
                'admin.attendance.start',
                'admin.profile.desk-pin',
                'admin.members.bulk',
                'admin.members.events.kick',
                'admin.members.re-accept',
                'admin.members.qr.regenerate',
                'admin.waiting-approvals.bulk',
                'admin.rejected-members.bulk',
                'admin.events.invites.update',
                'member.events.enroll',
                'member.events.unenroll',
                'member.register.store',
            ], true);
    }

    /**
     * @return array{0: string, 1: string}
     */
    private static function describeRoute(string $routeName, Request $request): array
    {
        if ($routeName === 'admin.profile.desk-pin') {
            return $request->input('action') === 'clear'
                ? ['updated', 'Removed attendance desk PIN']
                : ['updated', 'Saved attendance desk PIN'];
        }

        $map = [
            // Members
            'admin.members.bulk' => ['updated', 'Performed bulk action on members'],
            'admin.members.import.store' => ['created', 'Imported members from CSV'],
            'admin.members.store' => ['created', 'Created a member'],
            'admin.members.update' => ['updated', 'Updated a member'],
            'admin.members.destroy' => ['deleted', 'Deleted a member'],
            'admin.members.approve' => ['approved', 'Approved a member registration'],
            'admin.members.re-accept' => ['approved', 'Re-accepted a rejected member'],
            'admin.members.reject' => ['rejected', 'Rejected a member registration'],
            'admin.waiting-approvals.bulk' => ['updated', 'Bulk action on waiting approvals'],
            'admin.rejected-members.bulk' => ['updated', 'Bulk action on rejected members'],
            'admin.members.events.kick' => ['updated', 'Removed a member from an event'],
            'admin.members.reset-password' => ['password_reset', 'Reset member password to default'],
            'admin.members.require-password-change' => ['password_reset', 'Required member to change password on next login'],
            'admin.members.qr.regenerate' => ['updated', 'Regenerated membership QR'],

            // System users
            'admin.users.reset-password' => ['password_reset', 'Reset system user password to default'],
            'admin.users.require-password-change' => ['password_reset', 'Required system user to change password on next login'],
            'admin.set-password.update' => ['password_changed', 'System user set a new password on forced change'],
            'admin.users.store' => ['created', 'Created a system user'],
            'admin.users.update' => ['updated', 'Updated a system user'],
            'admin.users.destroy' => ['deleted', 'Deleted a system user'],

            // Lookups / settings
            'admin.designations.store' => ['created', 'Created a designation'],
            'admin.designations.update' => ['updated', 'Updated a designation'],
            'admin.designations.destroy' => ['deleted', 'Deleted a designation'],
            'admin.designations.import.store' => ['created', 'Imported designations from CSV'],
            'admin.institutes.store' => ['created', 'Created an institute'],
            'admin.institutes.update' => ['updated', 'Updated an institute'],
            'admin.institutes.destroy' => ['deleted', 'Deleted an institute'],
            'admin.institutes.import.store' => ['created', 'Imported institutes from CSV'],
            'admin.sub-institutes.store' => ['created', 'Created a sub-institute'],
            'admin.sub-institutes.update' => ['updated', 'Updated a sub-institute'],
            'admin.sub-institutes.destroy' => ['deleted', 'Deleted a sub-institute'],
            'admin.sub-institutes.import.store' => ['created', 'Imported sub-institutes from CSV'],
            'admin.sections.store' => ['created', 'Created a section'],
            'admin.sections.update' => ['updated', 'Updated a section'],
            'admin.sections.destroy' => ['deleted', 'Deleted a section'],
            'admin.sections.import.store' => ['created', 'Imported sections from CSV'],
            'admin.member-categories.store' => ['created', 'Created a member category'],
            'admin.member-categories.update' => ['updated', 'Updated a member category'],
            'admin.member-categories.destroy' => ['deleted', 'Deleted a member category'],
            'admin.check-in-items.store' => ['created', 'Created a check-in item'],
            'admin.check-in-items.update' => ['updated', 'Updated a check-in item'],
            'admin.check-in-items.destroy' => ['deleted', 'Deleted a check-in item'],
            'admin.settings.update' => ['updated', 'Updated system settings'],

            // Events
            'admin.events.store' => ['created', 'Created an event'],
            'admin.events.update' => ['updated', 'Updated an event'],
            'admin.events.destroy' => ['deleted', 'Deleted an event'],
            'admin.events.invites.update' => ['updated', 'Updated event invited members'],
            'member.events.enroll' => ['created', 'Enrolled in an event'],
            'member.events.unenroll' => ['deleted', 'Left an event'],

            // Attendance desk
            'admin.attendance.start' => ['updated', 'Started attendance desk session'],
            'admin.attendance.lock.store' => ['locked', 'Locked attendance desk'],
            'admin.attendance.unlock' => ['unlocked', 'Unlocked attendance desk'],
            'admin.attendance.check-in' => ['created', 'Checked in a member'],
            'admin.attendance.update-items' => ['updated', 'Updated check-in items for a member'],

            // Profiles / auth
            'admin.profile.update' => ['updated', 'Updated own system profile'],
            'admin.profile.desk-pin' => ['updated', 'Updated attendance desk PIN'],
            'member.register.store' => ['registered', 'Registered as a new member'],
            'member.password.update' => ['password_changed', 'Set a new member password on first login'],
            'member.profile.update' => ['updated', 'Updated member profile'],
            'admin.password.email' => ['password_reset', 'Requested system password reset email'],
            'admin.password.update' => ['password_reset', 'Reset system user password via email link'],
        ];

        if (isset($map[$routeName])) {
            return $map[$routeName];
        }

        $action = match ($request->method()) {
            'POST' => 'created',
            'PUT', 'PATCH' => 'updated',
            'DELETE' => 'deleted',
            default => 'action',
        };

        return [$action, 'Performed '.$request->method().' on '.$routeName];
    }

    private static function subjectFromRequest(Request $request): ?Model
    {
        $route = $request->route();
        if (! $route) {
            return null;
        }

        foreach ([
            'member',
            'user',
            'designation',
            'member_category',
            'check_in_item',
            'event',
            'institute',
            'sub_institute',
            'section',
        ] as $param) {
            $value = $route->parameter($param);
            if ($value instanceof Model) {
                return $value;
            }
        }

        return null;
    }

    private static function detectGuard(): ?string
    {
        if (Auth::guard('web')->check()) {
            return 'web';
        }

        if (Auth::guard('member')->check()) {
            return 'member';
        }

        return null;
    }

    private static function resolveCauser(?string $guard): ?Model
    {
        if ($guard === 'web') {
            return Auth::guard('web')->user();
        }

        if ($guard === 'member') {
            return Auth::guard('member')->user();
        }

        return Auth::guard('web')->user() ?? Auth::guard('member')->user();
    }

    private static function causerName(?Model $causer): ?string
    {
        if ($causer instanceof User) {
            return $causer->name;
        }

        if ($causer instanceof Member) {
            return $causer->displayName();
        }

        return null;
    }

    private static function causerRole(?Model $causer): ?string
    {
        if ($causer instanceof User) {
            return $causer->roleLabel();
        }

        if ($causer instanceof Member) {
            return 'Member';
        }

        return null;
    }

    private static function subjectLabel(?Model $subject): ?string
    {
        if ($subject instanceof User) {
            return $subject->name.' ('.$subject->email.')';
        }

        if ($subject instanceof Member) {
            return $subject->displayName().($subject->unique_id ? ' · '.$subject->unique_id : '');
        }

        if ($subject instanceof Event) {
            return $subject->name;
        }

        if ($subject instanceof CheckInItem) {
            return $subject->name;
        }

        if ($subject && method_exists($subject, 'getAttribute') && $subject->getAttribute('name')) {
            return (string) $subject->getAttribute('name');
        }

        return null;
    }
}
