<?php

namespace App\Http\Controllers;

use App\Http\Requests\AdminUserStoreRequest;
use App\Http\Requests\AdminUserUpdateRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

class AdminUserController extends Controller
{
    public function index(Request $request): Response
    {
        $users = User::query()
            ->select(['id', 'username', 'created_at'])
            ->with('roles:name')
            ->latest('id')
            ->get()
            ->map(static fn (User $user): array => [
                'id' => $user->id,
                'nama' => $user->username,
                'username' => $user->username,
                'role' => $user->roles->first()?->name,
                'created_at' => $user->created_at?->toDateTimeString(),
            ])
            ->values();

        return Inertia::render('AdminUsersPage', [
            'users' => $users,
            'availableRoles' => Role::query()
                ->orderBy('name')
                ->pluck('name')
                ->map(fn (string $name): array => [
                    'label' => $name,
                    'value' => $name,
                ])
                ->values(),
        ]);
    }

    public function store(AdminUserStoreRequest $request): RedirectResponse
    {
        $payload = $request->validated();
        $role = $payload['role'];
        unset($payload['role']);

        $user = User::query()->create($payload);
        $user->syncRoles([$role]);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User berhasil ditambahkan.');
    }

    public function update(AdminUserUpdateRequest $request, User $user): RedirectResponse
    {
        $payload = $request->validated();
        $role = $payload['role'];
        unset($payload['role']);
        if (empty($payload['password'])) {
            unset($payload['password']);
        }

        $user->update($payload);
        $user->syncRoles([$role]);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User berhasil diperbarui.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($request->user()?->is($user)) {
            return redirect()
                ->route('admin.users.index')
                ->with('error', 'Tidak bisa menghapus user yang sedang login.');
        }

        $user->deleteOrFail();

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User berhasil dihapus.');
    }
}
