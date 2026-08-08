@extends('layouts.app')

@section('title', 'Edit Profile')

@section('body')
<div class="min-h-screen bg-surface px-4 py-8 sm:px-6">
    <div class="mx-auto max-w-3xl">
        <div class="mb-6 flex items-center justify-between gap-3">
            <div>
                <h1 class="font-display text-2xl font-bold text-ink">Edit profile</h1>
                <p class="text-sm text-muted">{{ $member->displayName() }} · {{ $member->unique_id }}</p>
            </div>
            <a href="{{ route('member.profile') }}" class="btn-outline">Back</a>
        </div>

        <form method="POST" action="{{ route('member.profile.update') }}" enctype="multipart/form-data" class="card p-5 sm:p-8">
            @csrf
            @method('PUT')

            @if ($errors->any())
                <div class="alert-error mb-5">
                    <ul class="list-disc pl-4">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="mb-5 rounded-xl bg-brand-blue-soft px-4 py-3 text-sm text-brand-blue">
                Title, full name, NIC, and designation can only be changed by system administrators.
            </div>

            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="mobile_1" class="form-label">Mobile number 1</label>
                    <input id="mobile_1" type="text" name="mobile_1" value="{{ old('mobile_1', $member->mobile_1) }}" required class="form-input" placeholder="0771234567" maxlength="15" data-format="sl-phone" inputmode="tel" autocomplete="tel">
                    <p class="mt-1 text-xs text-muted">Format: 07XXXXXXXX or +947XXXXXXXX</p>
                </div>
                <div>
                    <label for="mobile_2" class="form-label">Mobile number 2</label>
                    <input id="mobile_2" type="text" name="mobile_2" value="{{ old('mobile_2', $member->mobile_2) }}" class="form-input" placeholder="0771234567" maxlength="15" data-format="sl-phone" inputmode="tel">
                </div>
                <div>
                    <label for="whatsapp" class="form-label">WhatsApp number</label>
                    <input id="whatsapp" type="text" name="whatsapp" value="{{ old('whatsapp', $member->whatsapp) }}" class="form-input" placeholder="0771234567" maxlength="15" data-format="sl-phone" inputmode="tel">
                </div>
                <div>
                    <label for="office_telephone" class="form-label">Office telephone</label>
                    <input id="office_telephone" type="text" name="office_telephone" value="{{ old('office_telephone', $member->office_telephone) }}" class="form-input" placeholder="0112345678" maxlength="15" data-format="sl-phone" inputmode="tel">
                    <p class="mt-1 text-xs text-muted">Landline or mobile (e.g. 0112345678)</p>
                </div>
                <div class="sm:col-span-2">
                    <label for="email" class="form-label">Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email', $member->email) }}" class="form-input">
                </div>
                <div class="sm:col-span-2 grid gap-5 sm:grid-cols-2" data-org-cascade>
                    <div>
                        <label for="institute" class="form-label">Institute</label>
                        <select id="institute" name="institute" class="form-select" data-org-institute>
                            <option value="">Select institute</option>
                            @foreach (($orgTree ?? []) as $orgInstitute)
                                <option value="{{ $orgInstitute['name'] }}" @selected(old('institute', $member->institute) === $orgInstitute['name'])>
                                    {{ $orgInstitute['name'] }}
                                </option>
                            @endforeach
                            @php
                                $currentInstitute = old('institute', $member->institute);
                                $knownInstitutes = collect($orgTree ?? [])->pluck('name')->all();
                            @endphp
                            @if ($currentInstitute && ! in_array($currentInstitute, $knownInstitutes, true))
                                <option value="{{ $currentInstitute }}" selected>{{ $currentInstitute }} (legacy)</option>
                            @endif
                        </select>
                    </div>
                    <div>
                        <label for="sub_institute" class="form-label">Sub-institute</label>
                        <select id="sub_institute" name="sub_institute" class="form-select" data-org-sub-institute data-selected="{{ old('sub_institute', $member->sub_institute) }}">
                            <option value="">Select sub-institute</option>
                        </select>
                    </div>
                    <div class="sm:col-span-2">
                        <label for="section" class="form-label">Section</label>
                        <select id="section" name="section" class="form-select" data-org-section data-selected="{{ old('section', $member->section) }}">
                            <option value="">Select section</option>
                        </select>
                    </div>
                    <script type="application/json" data-org-tree>{!! json_encode($orgTree ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}</script>
                </div>
                <div class="sm:col-span-2">
                    <label for="address" class="form-label">Address</label>
                    <textarea id="address" name="address" rows="3" class="form-input">{{ old('address', $member->address) }}</textarea>
                </div>
                <div class="sm:col-span-2" data-profile-image-crop>
                    <label for="profile_image" class="form-label">Profile image</label>
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
                    @if ($member->profile_image)
                        <div class="mt-3 flex items-center gap-3" data-profile-image-current>
                            <img src="{{ $member->profileImageUrl() }}" alt="Current photo" class="h-14 w-14 rounded-xl border border-slate-200 object-cover">
                            <p class="text-xs text-muted">Upload a new image to replace the current one.</p>
                        </div>
                    @endif
                </div>
                <div>
                    <label for="password" class="form-label">New password <span class="font-normal text-muted">(optional)</span></label>
                    <input id="password" type="password" name="password" class="form-input" autocomplete="new-password" minlength="8">
                    <p class="mt-1 text-xs text-muted">At least 8 characters if changing password.</p>
                </div>
                <div>
                    <label for="password_confirmation" class="form-label">Confirm password</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" class="form-input" autocomplete="new-password" minlength="8">
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-3 border-t border-slate-100 pt-5">
                <a href="{{ route('member.profile') }}" class="btn-outline">Cancel</a>
                <button type="submit" class="btn-primary">Save changes</button>
            </div>
        </form>
    </div>
</div>
@endsection
