<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Designation;
use App\Models\Event;
use App\Models\EventEnrollment;
use App\Models\Member;
use App\Models\MemberCategory;
use App\Rules\SriLankanMobile;
use App\Rules\SriLankanNic;
use App\Rules\SriLankanPhone;
use App\Support\ActivityLogger;
use App\Support\MemberCsvImporter;
use App\Support\MemberProfileImage;
use App\Support\MemberQrCode;
use App\Support\OrgLookups;
use App\Support\SriLankaFormat;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MemberController extends Controller
{
    public function index(Request $request): View
    {
        $query = Member::query()
            ->with([
                'designation',
                'category',
                'activeEventEnrollments.event',
            ])
            ->withCount('activeEventEnrollments')
            ->where('registration_status', 'approved')
            ->latest();

        if ($search = $request->string('search')->trim()->toString()) {
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                    ->orWhere('nic', 'like', "%{$search}%")
                    ->orWhere('unique_id', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('mobile_1', 'like', "%{$search}%");
            });
        }

        if ($status = $request->string('status')->toString()) {
            $query->where('status', $status);
        }

        if ($categoryId = $request->integer('member_category_id')) {
            $query->where('member_category_id', $categoryId);
        } elseif ($request->string('member_category_id')->toString() === 'none') {
            $query->whereNull('member_category_id');
        }

        if ($request->boolean('over_61')) {
            $over61Ids = (clone $query)->get()
                ->filter(fn (Member $member) => $member->isOverSixtyOne())
                ->pluck('id');

            $query->whereIn('id', $over61Ids->isEmpty() ? [0] : $over61Ids);
        }

        $members = $query->paginate(25)->withQueryString();
        $pendingCount = Member::where('registration_status', 'pending')->count();
        $rejectedCount = Member::where('registration_status', 'rejected')->count();
        $categories = MemberCategory::query()->orderBy('name')->get();

        return view('admin.members.index', compact('members', 'pendingCount', 'rejectedCount', 'categories'));
    }

    public function create(): View
    {
        $designations = Designation::query()->where('is_active', true)->orderBy('name')->get();
        $categories = MemberCategory::query()->where('is_active', true)->orderBy('name')->get();
        $orgTree = OrgLookups::cascadeTree();

        return view('admin.members.create', compact('designations', 'categories', 'orgTree'));
    }

    public function importForm(): View
    {
        $designations = Designation::query()->where('is_active', true)->orderBy('name')->get();

        return view('admin.members.import', compact('designations'));
    }

    public function importTemplate(): StreamedResponse
    {
        $filename = 'asda-members-import-template.csv';
        $headers = MemberCsvImporter::templateHeaders();
        $sample = MemberCsvImporter::sampleRow();

        return response()->streamDownload(function () use ($headers, $sample): void {
            $out = fopen('php://output', 'w');
            fputcsv($out, $headers);
            fputcsv($out, $sample);
            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'csv_file' => ['required', 'file', 'mimes:csv,txt', 'max:5120'],
        ], [
            'csv_file.required' => 'Please choose a CSV file to import.',
            'csv_file.mimes' => 'The file must be a CSV.',
        ]);

        $report = (new MemberCsvImporter)->import($request->file('csv_file'));

        $message = "Import finished: {$report['imported']} imported, {$report['skipped_duplicates']} duplicate ID(s) skipped, {$report['failed']} failed.";

        return redirect()
            ->route('admin.members.import')
            ->with($report['imported'] > 0 ? 'success' : 'error', $message)
            ->with('import_report', $report);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedData($request);
        $data['password'] = Member::defaultPasswordForNic($data['nic']);
        $data['must_change_password'] = true;
        $data['unique_id'] = Member::generateUniqueId();

        if ($request->hasFile('profile_image')) {
            $data['profile_image'] = MemberProfileImage::store(
                $request->file('profile_image'),
                $data['unique_id'],
            );
        }

        if (($data['registration_status'] ?? 'pending') === 'approved') {
            $data['status'] = $data['status'] ?? 'active';
            $data['approved_at'] = now();
            $data['approved_by'] = Auth::id();
        } else {
            $data['registration_status'] = $data['registration_status'] ?? 'pending';
            $data['status'] = $data['status'] ?? 'inactive';
        }

        Member::create($data);

        return redirect()->route('admin.members.index')->with('success', 'Member created successfully.');
    }

    public function show(Request $request, Member $member): View
    {
        $member->load([
            'designation',
            'category',
            'approver',
            'activeEventEnrollments.event',
            'eventEnrollments' => fn ($q) => $q->whereNotNull('kicked_at')->latest('kicked_at')->with(['event', 'kickedBy']),
        ]);
        $qrUrl = $member->qrCodeUrl();

        $activityQuery = ActivityLog::query()
            ->where(function ($query) use ($member) {
                $query->where(function ($q) use ($member) {
                    $q->where('subject_type', Member::class)
                        ->where('subject_id', $member->id);
                })->orWhere(function ($q) use ($member) {
                    $q->where('causer_type', Member::class)
                        ->where('causer_id', $member->id);
                });
            })
            ->latest('created_at');

        if ($search = $request->string('activity_search')->trim()->toString()) {
            $activityQuery->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                    ->orWhere('causer_name', 'like', "%{$search}%")
                    ->orWhere('subject_label', 'like', "%{$search}%")
                    ->orWhere('ip_address', 'like', "%{$search}%")
                    ->orWhere('action', 'like', "%{$search}%");
            });
        }

        if ($action = $request->string('activity_action')->toString()) {
            $activityQuery->where('action', $action);
        }

        if ($dateFrom = $request->string('activity_date_from')->toString()) {
            $activityQuery->whereDate('created_at', '>=', $dateFrom);
        }

        if ($dateTo = $request->string('activity_date_to')->toString()) {
            $activityQuery->whereDate('created_at', '<=', $dateTo);
        }

        $activityLogs = $activityQuery
            ->paginate(15, ['*'], 'activity_page')
            ->withQueryString();

        $activityActions = ActivityLog::query()
            ->where(function ($query) use ($member) {
                $query->where(function ($q) use ($member) {
                    $q->where('subject_type', Member::class)
                        ->where('subject_id', $member->id);
                })->orWhere(function ($q) use ($member) {
                    $q->where('causer_type', Member::class)
                        ->where('causer_id', $member->id);
                });
            })
            ->select('action')
            ->distinct()
            ->orderBy('action')
            ->pluck('action');

        return view('admin.members.show', compact('member', 'qrUrl', 'activityLogs', 'activityActions'));
    }

    public function kickFromEvent(Request $request, Member $member, Event $event): RedirectResponse
    {
        $data = $request->validate([
            'kick_reason' => ['required', 'string', 'max:1000'],
        ], [
            'kick_reason.required' => 'Please provide a reason for removing this member from the event.',
        ]);

        $enrollment = EventEnrollment::query()
            ->active()
            ->where('member_id', $member->id)
            ->where('event_id', $event->id)
            ->first();

        if (! $enrollment) {
            return back()->with('error', 'This member is not enrolled in that event.');
        }

        $enrollment->update([
            'kicked_at' => now(),
            'kick_reason' => trim($data['kick_reason']),
            'kicked_by' => Auth::id(),
        ]);

        ActivityLogger::log(
            'updated',
            'Removed '.$member->displayName().' from event: '.$event->name,
            subject: $member,
            properties: [
                'event_id' => $event->id,
                'event_name' => $event->name,
                'kick_reason' => $enrollment->kick_reason,
            ],
        );

        return back()->with('success', $member->displayName().' was removed from '.$event->name.'.');
    }

    public function edit(Member $member): View
    {
        $designations = Designation::query()->orderBy('name')->get();
        $categories = MemberCategory::query()->orderBy('name')->get();
        $orgTree = OrgLookups::cascadeTree(false);

        return view('admin.members.edit', compact('member', 'designations', 'categories', 'orgTree'));
    }

    public function update(Request $request, Member $member): RedirectResponse
    {
        $data = $this->validatedData($request, $member);

        if ($request->hasFile('profile_image')) {
            $data['profile_image'] = MemberProfileImage::store(
                $request->file('profile_image'),
                $member->unique_id ?: Member::generateUniqueId(),
                $member->profile_image,
            );

            if (! $member->unique_id) {
                $data['unique_id'] = pathinfo(basename($data['profile_image']), PATHINFO_FILENAME);
            }
        }

        if ($request->boolean('reset_password')) {
            $data['password'] = Member::defaultPasswordForNic($data['nic']);
            $data['must_change_password'] = true;
        } elseif ($request->boolean('require_password_change')) {
            $data['must_change_password'] = true;
        }

        $member->update($data);

        return redirect()->route('admin.members.show', $member)->with('success', 'Member updated successfully.');
    }

    public function resetPassword(Member $member): RedirectResponse
    {
        $member->update([
            'password' => $member->defaultPassword(),
            'must_change_password' => true,
        ]);

        return back()->with(
            'success',
            'Member password reset to default (first 4 digits of NIC + @ASDA). They must set a new password on next login.'
        );
    }

    public function requirePasswordChange(Member $member): RedirectResponse
    {
        $member->update([
            'must_change_password' => true,
        ]);

        return back()->with(
            'success',
            'Member must set a new password on next login (first-login password change enabled).'
        );
    }

    public function bulk(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'action' => ['required', 'in:activate,deactivate,require_password_change,reset_password,delete'],
            'member_ids' => ['required', 'array', 'min:1'],
            'member_ids.*' => ['integer', 'exists:members,id'],
        ]);

        $members = Member::query()
            ->where('registration_status', 'approved')
            ->whereIn('id', $data['member_ids'])
            ->get();
        $count = 0;

        foreach ($members as $member) {
            $done = match ($data['action']) {
                'activate' => $member->update(['status' => 'active']),
                'deactivate' => $member->update(['status' => 'inactive']),
                'require_password_change' => $member->update(['must_change_password' => true]),
                'reset_password' => $member->update([
                    'password' => $member->defaultPassword(),
                    'must_change_password' => true,
                ]),
                'delete' => $this->applyDelete($member),
            };

            if ($done) {
                $count++;
            }
        }

        $labels = [
            'activate' => 'activated',
            'deactivate' => 'deactivated',
            'require_password_change' => 'marked to change password',
            'reset_password' => 'password-reset',
            'delete' => 'deleted',
        ];

        ActivityLogger::log(
            'updated',
            'Bulk '.$labels[$data['action']].' '.$count.' member(s)',
            properties: [
                'action' => $data['action'],
                'count' => $count,
                'member_ids' => $data['member_ids'],
            ],
        );

        return back()->with('success', "Bulk action complete: {$count} member(s) {$labels[$data['action']]}.");
    }

    public function applyApprove(Member $member): bool
    {
        if ($member->registration_status === 'approved') {
            return false;
        }

        if (! $member->unique_id) {
            $member->unique_id = Member::generateUniqueId();
        }

        $member->fill([
            'registration_status' => 'approved',
            'status' => 'active',
            'rejection_reason' => null,
            'approved_at' => now(),
            'approved_by' => Auth::id(),
        ])->save();

        return true;
    }

    public function applyReject(Member $member, string $reason): bool
    {
        if ($member->registration_status === 'rejected') {
            return false;
        }

        $member->update([
            'registration_status' => 'rejected',
            'status' => 'inactive',
            'rejection_reason' => $reason,
            'approved_at' => null,
            'approved_by' => Auth::id(),
        ]);

        return true;
    }

    private function applyDelete(Member $member): bool
    {
        if ($member->profile_image) {
            MemberProfileImage::delete($member->profile_image);
        }

        $member->delete();

        return true;
    }

    public function destroy(Member $member): RedirectResponse
    {
        if ($member->profile_image) {
            MemberProfileImage::delete($member->profile_image);
        }

        // QR file is removed in Member::deleting
        $member->delete();

        return redirect()->route('admin.members.index')->with('success', 'Member deleted successfully.');
    }

    public function downloadQr(Member $member)
    {
        if (! $member->unique_id) {
            return back()->with('error', 'This member does not have a Unique ID yet.');
        }

        $path = MemberQrCode::ensure($member->unique_id);
        $filename = MemberQrCode::downloadFilename($member->displayName(), $member->unique_id);

        ActivityLogger::log(
            'downloaded',
            'Downloaded member QR: '.$member->displayName(),
            subject: $member,
        );

        return Storage::disk('public')->download($path, $filename);
    }

    public function approve(Member $member): RedirectResponse
    {
        if ($member->registration_status === 'approved') {
            return back()->with('error', 'Member is already approved.');
        }

        $this->applyApprove($member);

        return back()->with('success', "Member approved. Unique ID: {$member->unique_id}");
    }

    public function reAccept(Member $member): RedirectResponse
    {
        if ($member->registration_status !== 'rejected') {
            return back()->with('error', 'Only rejected members can be re-accepted.');
        }

        $this->applyApprove($member);

        ActivityLogger::log(
            'approved',
            'Re-accepted rejected member: '.$member->displayName(),
            subject: $member,
        );

        return back()->with('success', $member->displayName().' was re-accepted. Unique ID: '.$member->unique_id);
    }

    public function reject(Request $request, Member $member): RedirectResponse
    {
        $data = $request->validate([
            'rejection_reason' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($member->registration_status === 'rejected') {
            return back()->with('error', 'Member is already rejected.');
        }

        $this->applyReject($member, $data['rejection_reason'] ?? 'Rejected by admin.');

        return back()->with('success', 'Member registration rejected.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedData(Request $request, ?Member $member = null): array
    {
        $request->merge([
            'nic' => SriLankaFormat::normalizeNic($request->input('nic')),
            'mobile_1' => SriLankaFormat::normalizePhone($request->input('mobile_1')),
            'mobile_2' => SriLankaFormat::normalizePhone($request->input('mobile_2')),
            'whatsapp' => SriLankaFormat::normalizePhone($request->input('whatsapp')),
            'office_telephone' => SriLankaFormat::normalizePhone($request->input('office_telephone')),
            'member_category_id' => $request->filled('member_category_id')
                ? $request->input('member_category_id')
                : null,
        ]);

        return $request->validate([
            'title' => ['required', 'in:Dr,Mr,Mrs,Ms,Prof,Eng'],
            'full_name' => ['required', 'string', 'max:255'],
            'nic' => [
                'required',
                'string',
                'max:12',
                new SriLankanNic,
                Rule::unique('members', 'nic')->ignore($member?->id),
            ],
            'designation_id' => ['required', 'exists:designations,id'],
            'member_category_id' => ['nullable', 'exists:member_categories,id'],
            'mobile_1' => ['required', 'string', 'max:15', new SriLankanMobile],
            'mobile_2' => ['nullable', 'string', 'max:15', new SriLankanMobile],
            'whatsapp' => ['nullable', 'string', 'max:15', new SriLankanMobile],
            'office_telephone' => ['nullable', 'string', 'max:15', new SriLankanPhone],
            'email' => ['nullable', 'email', 'max:255'],
            'institute' => ['nullable', 'string', 'max:255'],
            'sub_institute' => ['nullable', 'string', 'max:255'],
            'section' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:1000'],
            'profile_image' => ['nullable', 'image', 'max:2048'],
            'registration_status' => ['nullable', 'in:pending,approved,rejected'],
            'status' => ['nullable', 'in:active,inactive'],
        ], [
            'nic.unique' => 'This NIC number is already registered.',
        ]);
    }
}
