@php
    $isEdit = isset($user);
@endphp

@if ($errors->any())
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
    <div class="sm:col-span-2" data-profile-image-crop>
        <label for="profile_image" class="form-label">Profile picture</label>
        <p class="mb-2 text-xs text-muted">Choose a photo, then crop to a square (1:1).</p>
        <input
            id="profile_image"
            type="file"
            name="profile_image"
            accept="image/*"
            class="form-input"
            data-profile-image-input
        >
        <div class="mt-3 hidden" data-profile-image-preview-wrap>
            <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-muted">Cropped preview</p>
            <img src="" alt="Cropped preview" class="h-24 w-24 rounded-xl border border-slate-200 object-cover" data-profile-image-preview>
        </div>
        @if ($isEdit && $user->profile_image)
            <div class="mt-3 flex items-center gap-3" data-profile-image-current>
                <img src="{{ $user->profileImageUrl() }}" alt="Current photo" class="h-14 w-14 rounded-xl border border-slate-200 object-cover">
                <p class="text-xs text-muted">Upload a new image to replace the current one.</p>
            </div>
        @endif
    </div>

    <div class="sm:col-span-2">
        <label for="name" class="form-label">Full name</label>
        <input id="name" type="text" name="name" value="{{ old('name', $user->name ?? '') }}" required class="form-input" placeholder="Jane Doe">
    </div>

    <div>
        <label for="email" class="form-label">Email</label>
        <input id="email" type="email" name="email" value="{{ old('email', $user->email ?? '') }}" required class="form-input" placeholder="user@company.com">
    </div>

    <div>
        <label for="phone" class="form-label">Phone</label>
        <input id="phone" type="text" name="phone" value="{{ old('phone', $user->phone ?? '') }}" class="form-input" placeholder="0771234567">
    </div>

    <div>
        <label for="role" class="form-label">Role</label>
        <select id="role" name="role" required class="form-select" data-user-role-select>
            @foreach (\App\Support\UserRole::labels() as $value => $label)
                <option value="{{ $value }}" @selected(old('role', $user->role ?? \App\Support\UserRole::VIEWER) === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label for="status" class="form-label">Status</label>
        <select id="status" name="status" required class="form-select">
            <option value="active" @selected(old('status', $user->status ?? 'active') === 'active')>Active</option>
            <option value="inactive" @selected(old('status', $user->status ?? '') === 'inactive')>Inactive</option>
        </select>
    </div>

    @php
        $selectedEventIds = collect(old('event_ids', isset($user) ? $user->receptionEvents->pluck('id')->all() : []))->map(fn ($id) => (string) $id)->all();
        $eventsList = $events ?? collect();
    @endphp
    <div class="sm:col-span-2 {{ old('role', $user->role ?? '') === \App\Support\UserRole::RECEPTION ? '' : 'hidden' }}" data-reception-events>
        <label class="form-label">Assigned events <span class="font-normal text-muted">(Reception only)</span></label>
        <p class="mb-2 text-xs text-muted">Reception officers can only run attendance for these events.</p>
        @if ($eventsList->isEmpty())
            <p class="rounded-xl border border-dashed border-slate-200 bg-surface/60 px-4 py-3 text-sm text-muted">No events available to assign yet.</p>
        @else
            <div class="max-h-56 space-y-2 overflow-y-auto rounded-xl border border-slate-200 bg-white p-3">
                @foreach ($eventsList as $event)
                    <label class="flex items-start gap-2 text-sm text-ink">
                        <input
                            type="checkbox"
                            name="event_ids[]"
                            value="{{ $event->id }}"
                            class="mt-0.5 h-4 w-4 rounded border-slate-300 text-brand-green focus:ring-brand-green"
                            @checked(in_array((string) $event->id, $selectedEventIds, true))
                        >
                        <span>
                            <span class="font-medium">{{ $event->name }}</span>
                            <span class="block text-xs text-muted">
                                {{ \App\Support\SriLankaDate::date($event->start_date) }}
                                · {{ $event->status === 'active' ? 'Active' : 'Inactive' }}
                            </span>
                        </span>
                    </label>
                @endforeach
            </div>
        @endif
        @error('event_ids')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="password" class="form-label">
            Password
            @if ($isEdit)
                <span class="font-normal text-muted">(leave blank to keep current)</span>
            @endif
        </label>
        <input id="password" type="password" name="password" @unless($isEdit) required @endunless class="form-input" autocomplete="new-password" minlength="8">
        <p class="mt-1 text-xs text-muted">
            At least 8 characters.
            @unless($isEdit)
                User must change this on first login.
            @endunless
            Reset style uses first 4 phone digits + @ASDA.
        </p>
    </div>

    <div>
        <label for="password_confirmation" class="form-label">Confirm password</label>
        <input id="password_confirmation" type="password" name="password_confirmation" @unless($isEdit) required @endunless class="form-input" autocomplete="new-password" minlength="8">
    </div>

    @if ($isEdit)
        <div class="sm:col-span-2">
            <label class="flex items-start gap-2 text-sm text-ink">
                <input type="checkbox" name="require_password_change" value="1" class="mt-0.5 h-4 w-4 rounded border-slate-300 text-brand-green focus:ring-brand-green">
                <span>
                    <span class="font-medium">Require password change on next login</span>
                    <span class="block text-xs text-muted">Forces the set-password screen after they sign in.</span>
                </span>
            </label>
        </div>
    @endif
</div>
