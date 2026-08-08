@extends('layouts.dashboard')

@section('title', 'Edit Profile')
@section('page-title', 'Edit Profile')
@section('page-subtitle', 'Update your account details')

@section('page-actions')
<a href="{{ route('admin.profile.show') }}" class="btn-outline">Back</a>
@endsection

@section('content')
<div class="mx-auto max-w-2xl space-y-5">
    <form method="POST" action="{{ route('admin.profile.update') }}" class="card p-5 sm:p-8">
        @csrf
        @method('PUT')

        @if ($errors->any() && ! $errors->hasAny(['desk_pin', 'desk_pin_confirmation', 'current_desk_pin']))
            <div class="alert-error mb-5">
                <div>
                    <p class="font-semibold">Please fix the following:</p>
                    <ul class="mt-1 list-disc pl-4">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <div class="grid gap-5 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <label for="name" class="form-label">Full name</label>
                <input id="name" type="text" name="name" value="{{ old('name', $user->name) }}" required class="form-input">
            </div>

            <div>
                <label for="email" class="form-label">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email', $user->email) }}" required class="form-input">
            </div>

            <div>
                <label for="phone" class="form-label">Phone</label>
                <input id="phone" type="text" name="phone" value="{{ old('phone', $user->phone) }}" class="form-input" placeholder="0771234567">
            </div>

            <div class="sm:col-span-2 rounded-xl bg-surface p-4">
                <p class="text-xs font-semibold uppercase tracking-wide text-muted">Role</p>
                <p class="mt-1 font-semibold text-ink">{{ $user->roleLabel() }}</p>
                <p class="mt-1 text-xs text-muted">Role can only be changed by a Super Admin from System Users.</p>
            </div>

            <div>
                <label for="password" class="form-label">New password <span class="font-normal text-muted">(optional)</span></label>
                <input id="password" type="password" name="password" class="form-input" autocomplete="new-password" minlength="8">
                <p class="mt-1 text-xs text-muted">At least 8 characters. Leave blank to keep current.</p>
            </div>

            <div>
                <label for="password_confirmation" class="form-label">Confirm password</label>
                <input id="password_confirmation" type="password" name="password_confirmation" class="form-input" autocomplete="new-password" minlength="8">
            </div>
        </div>

        <div class="mt-6 flex flex-wrap gap-3">
            <button type="submit" class="btn-primary">Save changes</button>
            <a href="{{ route('admin.profile.show') }}" class="btn-outline">Cancel</a>
        </div>
    </form>

    @if ($user->canAccessAttendance())
        <section class="card space-y-5 p-5 sm:p-8">
            <div>
                <h2 class="font-display text-lg font-bold text-ink">Attendance desk PIN</h2>
                <p class="mt-1 text-sm text-muted">
                    Set a 4-digit PIN to quickly lock and unlock the attendance desk without signing out.
                    @if ($user->hasDeskPin())
                        <span class="font-semibold text-brand-green">PIN is set.</span>
                    @else
                        <span class="font-semibold text-brand-orange">No PIN set yet.</span>
                    @endif
                </p>
            </div>

            @if ($errors->hasAny(['desk_pin', 'desk_pin_confirmation', 'current_desk_pin']))
                <div class="alert-error">
                    <div>
                        <p class="font-semibold">Please fix the PIN form:</p>
                        <ul class="mt-1 list-disc pl-4">
                            @foreach (['desk_pin', 'desk_pin_confirmation', 'current_desk_pin'] as $field)
                                @error($field)
                                    <li>{{ $message }}</li>
                                @enderror
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.profile.desk-pin') }}" class="grid gap-4 sm:grid-cols-2">
                @csrf
                @method('PUT')
                <input type="hidden" name="action" value="save">

                @if ($user->hasDeskPin())
                    <div class="sm:col-span-2">
                        <label for="current_desk_pin" class="form-label">Current desk PIN</label>
                        <input id="current_desk_pin" type="password" name="current_desk_pin" inputmode="numeric" pattern="[0-9]{4}" maxlength="4" class="form-input" autocomplete="off">
                    </div>
                @endif

                <div>
                    <label for="desk_pin" class="form-label">{{ $user->hasDeskPin() ? 'New 4-digit PIN' : '4-digit PIN' }}</label>
                    <input id="desk_pin" type="password" name="desk_pin" inputmode="numeric" pattern="[0-9]{4}" maxlength="4" required class="form-input" autocomplete="off">
                </div>

                <div>
                    <label for="desk_pin_confirmation" class="form-label">Confirm PIN</label>
                    <input id="desk_pin_confirmation" type="password" name="desk_pin_confirmation" inputmode="numeric" pattern="[0-9]{4}" maxlength="4" required class="form-input" autocomplete="off">
                </div>

                <div class="sm:col-span-2 flex flex-wrap gap-3">
                    <button type="submit" class="btn-primary">{{ $user->hasDeskPin() ? 'Update desk PIN' : 'Save desk PIN' }}</button>
                </div>
            </form>

            @if ($user->hasDeskPin())
                <form method="POST" action="{{ route('admin.profile.desk-pin') }}" class="border-t border-slate-100 pt-5" data-confirm="Remove your attendance desk PIN? You will not be able to lock the desk until you set a new one.">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="action" value="clear">
                    <div class="grid gap-4 sm:grid-cols-2 sm:items-end">
                        <div>
                            <label for="clear_current_desk_pin" class="form-label">Current desk PIN to remove</label>
                            <input id="clear_current_desk_pin" type="password" name="current_desk_pin" inputmode="numeric" pattern="[0-9]{4}" maxlength="4" required class="form-input" autocomplete="off">
                        </div>
                        <button type="submit" class="btn-outline justify-center text-red-700">Remove desk PIN</button>
                    </div>
                </form>
            @endif
        </section>
    @endif
</div>
@endsection
