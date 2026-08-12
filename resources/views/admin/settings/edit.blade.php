@extends('layouts.dashboard')

@section('title', 'System Settings')
@section('page-title', 'System Settings')
@section('page-subtitle', 'Registration, maintenance, and UI credits')

@section('content')
<div class="mx-auto max-w-3xl space-y-6">
    <form method="POST" action="{{ route('admin.settings.update') }}" class="card p-5 sm:p-8">
        @csrf
        @method('PUT')

        @if ($errors->any())
            <div class="alert-error mb-5"><span>{{ $errors->first() }}</span></div>
        @endif

        <div class="flex items-start justify-between gap-4 border-b border-slate-100 pb-5">
            <div>
                <h2 class="font-display text-lg font-bold text-ink">Register Member</h2>
                <p class="mt-1 text-sm text-muted">
                    Frontend public registration at <span class="font-medium text-ink">/register</span>.
                    <span class="font-medium text-ink">ON</span> = visitors can register.
                    <span class="font-medium text-ink">OFF</span> = registration link hidden and form blocked.
                    Admins can still add members from the admin panel.
                </p>
            </div>
            <label class="relative inline-flex cursor-pointer items-center">
                <input type="checkbox" name="member_registration_enabled" value="1" class="peer sr-only" @checked(old('member_registration_enabled', $memberRegistrationEnabled))>
                <span class="h-7 w-12 rounded-full bg-slate-300 transition peer-checked:bg-brand-green peer-focus:ring-2 peer-focus:ring-brand-green/30 after:absolute after:left-0.5 after:top-0.5 after:h-6 after:w-6 after:rounded-full after:bg-white after:shadow after:transition-transform peer-checked:after:translate-x-5"></span>
            </label>
        </div>

        <div class="mt-5">
            <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-muted">Registration status</p>
            @if ($memberRegistrationEnabled)
                <span class="badge-green">ON · public registration open</span>
            @else
                <span class="badge-orange">OFF · public registration closed</span>
            @endif
        </div>

        <div class="mt-8 flex items-start justify-between gap-4 border-b border-slate-100 pb-5">
            <div>
                <h2 class="font-display text-lg font-bold text-ink">Maintenance mode</h2>
                <p class="mt-1 text-sm text-muted">
                    <span class="font-medium text-ink">ON</span> = public site blocked (maintenance page).
                    <span class="font-medium text-ink">OFF</span> = public site working.
                    Admin panel at <span class="font-medium text-ink">/admin</span> always works.
                </p>
            </div>
            <label class="relative inline-flex cursor-pointer items-center">
                <input type="checkbox" name="maintenance_mode" value="1" class="peer sr-only" @checked(old('maintenance_mode', $maintenanceMode))>
                <span class="h-7 w-12 rounded-full bg-slate-300 transition peer-checked:bg-brand-orange peer-focus:ring-2 peer-focus:ring-brand-orange/30 after:absolute after:left-0.5 after:top-0.5 after:h-6 after:w-6 after:rounded-full after:bg-white after:shadow after:transition-transform peer-checked:after:translate-x-5"></span>
            </label>
        </div>

        <div class="mt-5">
            <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-muted">Maintenance status</p>
            @if ($maintenanceMode)
                <span class="badge-orange">ON · public blocked</span>
            @else
                <span class="badge-green">OFF · public working</span>
            @endif
        </div>

        <div class="mt-6">
            <label for="maintenance_message" class="form-label">Maintenance page message</label>
            <textarea id="maintenance_message" name="maintenance_message" rows="4" required class="form-input">{{ old('maintenance_message', $maintenanceMessage) }}</textarea>
            <p class="mt-1 text-xs text-muted">Shown to visitors when maintenance mode is ON. Turning ON also signs out all logged-in members.</p>
        </div>

        <div class="mt-8 border-t border-slate-100 pt-6">
            <h2 class="font-display text-lg font-bold text-ink">UI credits</h2>
            <p class="mt-1 text-sm text-muted">
                Shown in footers across member and system screens. Year is added automatically to the copyright line.
            </p>

            <div class="mt-5">
                <label for="developer_credits" class="form-label">Developer credits</label>
                <input
                    id="developer_credits"
                    type="text"
                    name="developer_credits"
                    value="{{ old('developer_credits', $developerCredits) }}"
                    required
                    maxlength="255"
                    class="form-input"
                    placeholder="Developed by 1920 & TFBS - Department of Agriculture"
                >
            </div>

            <div class="mt-5">
                <label for="footer_copyright" class="form-label">Copyright text</label>
                <input
                    id="footer_copyright"
                    type="text"
                    name="footer_copyright"
                    value="{{ old('footer_copyright', $footerCopyright) }}"
                    required
                    maxlength="255"
                    class="form-input"
                    placeholder="Annual Symposium of the Department of Agriculture (ASDA). All rights reserved."
                >
                <p class="mt-1 text-xs text-muted">Displayed as: © {{ now()->year }} [your text]</p>
            </div>
        </div>

        <div class="mt-6 flex justify-end gap-3 border-t border-slate-100 pt-5">
            <button type="submit" class="btn-primary">Save settings</button>
        </div>
    </form>
</div>
@endsection
