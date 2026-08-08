@php
    $memberModel = $member ?? null;
@endphp

<div class="grid gap-5 sm:grid-cols-2">
    <div>
        <label for="title" class="form-label">Title</label>
        <select id="title" name="title" required class="form-select">
            @foreach (['Dr', 'Mr', 'Mrs', 'Ms', 'Prof', 'Eng'] as $title)
                <option value="{{ $title }}" @selected(old('title', $memberModel->title ?? '') === $title)>{{ $title }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label for="full_name" class="form-label">Full name</label>
        <input id="full_name" type="text" name="full_name" value="{{ old('full_name', $memberModel->full_name ?? '') }}" required class="form-input" placeholder="Full name">
    </div>

    <div>
        <label for="nic" class="form-label">NIC number</label>
        <input id="nic" type="text" name="nic" value="{{ old('nic', $memberModel->nic ?? '') }}" required class="form-input" placeholder="123456789V or 199012345678" maxlength="12" data-format="sl-nic" inputmode="text" autocomplete="off">
        <p class="mt-1 text-xs text-muted">Sri Lankan NIC only (old or new format). Used as username. Default password = first 4 digits + @ASDA.</p>
    </div>

    <div>
        <label for="designation_id" class="form-label">Designation</label>
        <select id="designation_id" name="designation_id" required class="form-select">
            <option value="">Select designation</option>
            @foreach ($designations as $designation)
                <option value="{{ $designation->id }}" @selected((string) old('designation_id', $memberModel->designation_id ?? '') === (string) $designation->id)>
                    {{ $designation->name }}
                </option>
            @endforeach
        </select>
    </div>

    @isset($showAdminStatus)
        <div>
            <label for="member_category_id" class="form-label">Category <span class="font-normal text-muted">(optional)</span></label>
            <select id="member_category_id" name="member_category_id" class="form-select">
                <option value="">No category</option>
                @foreach (($categories ?? []) as $category)
                    <option value="{{ $category->id }}" @selected((string) old('member_category_id', $memberModel->member_category_id ?? '') === (string) $category->id)>
                        {{ $category->name }}{{ ! $category->is_active ? ' (inactive)' : '' }}
                    </option>
                @endforeach
            </select>
            <p class="mt-1 text-xs text-muted">Only admins can assign categories. Members may register without one.</p>
        </div>
    @endisset

    <div>
        <label for="mobile_1" class="form-label">Mobile number 1</label>
        <input id="mobile_1" type="text" name="mobile_1" value="{{ old('mobile_1', $memberModel->mobile_1 ?? '') }}" required class="form-input" placeholder="0771234567" maxlength="15" data-format="sl-phone" inputmode="tel" autocomplete="tel">
        <p class="mt-1 text-xs text-muted">Format: 07XXXXXXXX or +947XXXXXXXX</p>
    </div>

    <div>
        <label for="mobile_2" class="form-label">Mobile number 2</label>
        <input id="mobile_2" type="text" name="mobile_2" value="{{ old('mobile_2', $memberModel->mobile_2 ?? '') }}" class="form-input" placeholder="0771234567" maxlength="15" data-format="sl-phone" inputmode="tel" autocomplete="tel">
    </div>

    <div>
        <label for="whatsapp" class="form-label">WhatsApp number</label>
        <input id="whatsapp" type="text" name="whatsapp" value="{{ old('whatsapp', $memberModel->whatsapp ?? '') }}" class="form-input" placeholder="0771234567" maxlength="15" data-format="sl-phone" inputmode="tel">
    </div>

    <div>
        <label for="office_telephone" class="form-label">Office telephone</label>
        <input id="office_telephone" type="text" name="office_telephone" value="{{ old('office_telephone', $memberModel->office_telephone ?? '') }}" class="form-input" placeholder="0112345678" maxlength="15" data-format="sl-phone" inputmode="tel">
        <p class="mt-1 text-xs text-muted">Landline or mobile (e.g. 0112345678)</p>
    </div>

    <div class="sm:col-span-2">
        <label for="email" class="form-label">Email</label>
        <input id="email" type="email" name="email" value="{{ old('email', $memberModel->email ?? '') }}" class="form-input">
    </div>

    <div class="sm:col-span-2 grid gap-5 sm:grid-cols-2" data-org-cascade>
        <div>
            <label for="institute" class="form-label">Institute</label>
            <select id="institute" name="institute" class="form-select" data-org-institute>
                <option value="">Select institute</option>
                @foreach (($orgTree ?? []) as $orgInstitute)
                    <option value="{{ $orgInstitute['name'] }}" @selected(old('institute', $memberModel->institute ?? '') === $orgInstitute['name'])>
                        {{ $orgInstitute['name'] }}
                    </option>
                @endforeach
                @php
                    $currentInstitute = old('institute', $memberModel->institute ?? '');
                    $knownInstitutes = collect($orgTree ?? [])->pluck('name')->all();
                @endphp
                @if ($currentInstitute && ! in_array($currentInstitute, $knownInstitutes, true))
                    <option value="{{ $currentInstitute }}" selected>{{ $currentInstitute }} (legacy)</option>
                @endif
            </select>
        </div>

        <div>
            <label for="sub_institute" class="form-label">Sub-institute</label>
            <select id="sub_institute" name="sub_institute" class="form-select" data-org-sub-institute data-selected="{{ old('sub_institute', $memberModel->sub_institute ?? '') }}">
                <option value="">Select sub-institute</option>
            </select>
        </div>

        <div class="sm:col-span-2">
            <label for="section" class="form-label">Section</label>
            <select id="section" name="section" class="form-select" data-org-section data-selected="{{ old('section', $memberModel->section ?? '') }}">
                <option value="">Select section</option>
            </select>
        </div>

        <script type="application/json" data-org-tree>{!! json_encode($orgTree ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}</script>
    </div>

    <div class="sm:col-span-2">
        <label for="address" class="form-label">Address</label>
        <textarea id="address" name="address" rows="3" class="form-input">{{ old('address', $memberModel->address ?? '') }}</textarea>
    </div>

    <div class="sm:col-span-2" data-profile-image-crop>
        <label for="profile_image" class="form-label">Profile image</label>
        <p class="mb-2 text-xs text-muted">Choose a photo, then crop to a square (1:1). Saved as Unique ID filename.</p>
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
        @if ($memberModel?->profile_image)
            <div class="mt-3 flex items-center gap-3" data-profile-image-current>
                <img src="{{ $memberModel->profileImageUrl() }}" alt="Current photo" class="h-14 w-14 rounded-xl border border-slate-200 object-cover">
                <p class="text-xs text-muted">Upload a new image to replace the current one.</p>
            </div>
        @endif
    </div>

    @isset($showAdminStatus)
        <div>
            <label for="registration_status" class="form-label">Registration status</label>
            <select id="registration_status" name="registration_status" class="form-select">
                @foreach (['pending', 'approved', 'rejected'] as $regStatus)
                    <option value="{{ $regStatus }}" @selected(old('registration_status', $memberModel->registration_status ?? 'pending') === $regStatus)>{{ ucfirst($regStatus) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="status" class="form-label">Status</label>
            <select id="status" name="status" class="form-select">
                <option value="active" @selected(old('status', $memberModel->status ?? 'inactive') === 'active')>Active</option>
                <option value="inactive" @selected(old('status', $memberModel->status ?? 'inactive') === 'inactive')>Inactive</option>
            </select>
        </div>
        @if ($memberModel)
            <div class="sm:col-span-2 space-y-3 rounded-xl border border-slate-200 bg-surface p-4">
                <p class="text-xs font-semibold uppercase tracking-wide text-muted">Password options</p>
                <label class="flex items-start gap-2 text-sm text-ink">
                    <input type="checkbox" name="reset_password" value="1" class="mt-0.5 h-4 w-4 rounded border-slate-300 text-brand-green focus:ring-brand-green">
                    <span>
                        <span class="font-medium">Reset password to default</span>
                        <span class="block text-xs text-muted">Sets password to first 4 NIC digits + @ASDA and requires a new password on next login.</span>
                    </span>
                </label>
                <label class="flex items-start gap-2 text-sm text-ink">
                    <input type="checkbox" name="require_password_change" value="1" class="mt-0.5 h-4 w-4 rounded border-slate-300 text-brand-green focus:ring-brand-green">
                    <span>
                        <span class="font-medium">Require password change on next login</span>
                        <span class="block text-xs text-muted">Keeps current password, but forces the first-login set-password screen.</span>
                    </span>
                </label>
            </div>
        @endif
    @endisset
</div>
