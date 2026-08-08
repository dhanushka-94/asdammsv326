<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\Event;
use App\Models\User;
use App\Support\UserRole;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $query = User::query()->latest();

        if ($search = $request->string('search')->trim()->toString()) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($role = $request->string('role')->toString()) {
            $query->where('role', $role);
        }

        if ($status = $request->string('status')->toString()) {
            $query->where('status', $status);
        }

        $users = $query->paginate(10)->withQueryString();

        return view('users.index', compact('users'));
    }

    public function create(): View
    {
        $events = Event::query()->orderByDesc('start_date')->get(['id', 'name', 'start_date', 'status']);

        return view('users.create', compact('events'));
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $eventIds = $data['event_ids'] ?? [];
        unset($data['event_ids']);
        $data['must_change_password'] = true;

        $user = User::create($data);
        $this->syncReceptionEvents($user, $eventIds);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User created successfully. They must set a new password on first login.');
    }

    public function show(User $user): View
    {
        $user->load('receptionEvents:id,name,start_date,status');

        return view('users.show', compact('user'));
    }

    public function edit(User $user): View
    {
        $user->load('receptionEvents:id');
        $events = Event::query()->orderByDesc('start_date')->get(['id', 'name', 'start_date', 'status']);

        return view('users.edit', compact('user', 'events'));
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $data = $request->validated();
        $eventIds = $data['event_ids'] ?? [];
        unset($data['event_ids']);

        if (empty($data['password'])) {
            unset($data['password']);
        } else {
            $data['must_change_password'] = true;
        }

        if ($request->boolean('require_password_change')) {
            $data['must_change_password'] = true;
        }

        if (
            $user->isSuperAdmin()
            && ($data['role'] ?? null) !== UserRole::SUPER_ADMIN
            && User::where('role', UserRole::SUPER_ADMIN)->count() <= 1
        ) {
            return back()->with('error', 'Cannot demote the last Super Admin user.');
        }

        $user->update($data);
        $this->syncReceptionEvents($user->fresh(), $eventIds);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User updated successfully.');
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        if ($user->isSuperAdmin() && User::where('role', UserRole::SUPER_ADMIN)->count() <= 1) {
            return back()->with('error', 'Cannot delete the last Super Admin user.');
        }

        $user->delete();

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User deleted successfully.');
    }

    public function resetPassword(User $user): RedirectResponse
    {
        $temporary = $user->defaultPassword();

        $user->update([
            'password' => $temporary,
            'must_change_password' => true,
        ]);

        return back()->with(
            'success',
            "Password reset for {$user->name} to {$temporary}. They must set a new password on next login."
        );
    }

    public function requirePasswordChange(User $user): RedirectResponse
    {
        $user->update([
            'must_change_password' => true,
        ]);

        return back()->with(
            'success',
            "{$user->name} must set a new password on next login."
        );
    }

    /**
     * @param  list<int|string>|null  $eventIds
     */
    private function syncReceptionEvents(User $user, ?array $eventIds): void
    {
        if ($user->role !== UserRole::RECEPTION) {
            $user->receptionEvents()->sync([]);

            return;
        }

        $ids = collect($eventIds ?? [])
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values()
            ->all();

        $user->receptionEvents()->sync($ids);
    }
}
