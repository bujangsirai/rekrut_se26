<?php

namespace App\Http\Controllers;

use App\Http\Requests\AdminRoleStoreRequest;
use App\Http\Requests\AdminRoleUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

class AdminRoleController extends Controller
{
    public function index(Request $request): Response
    {
        $roles = Role::query()
            ->withCount('users')
            ->orderBy('name')
            ->get(['id', 'name', 'description', 'created_at'])
            ->map(static fn (Role $role): array => [
                'id' => $role->id,
                'name' => $role->name,
                'description' => $role->description,
                'users_count' => $role->users_count,
                'created_at' => $role->created_at?->toDateTimeString(),
            ])
            ->values();

        return Inertia::render('AdminRolesPage', [
            'roles' => $roles,
        ]);
    }

    public function store(AdminRoleStoreRequest $request): RedirectResponse
    {
        Role::query()->create([
            ...$request->validated(),
            'guard_name' => 'web',
        ]);

        return redirect()
            ->route('admin.roles')
            ->with('success', 'Role berhasil ditambahkan.');
    }

    public function update(AdminRoleUpdateRequest $request, Role $role): RedirectResponse
    {
        $role->update($request->validated());

        return redirect()
            ->route('admin.roles')
            ->with('success', 'Role berhasil diperbarui.');
    }

    public function destroy(Role $role): RedirectResponse
    {
        if ($role->name === 'Superadmin') {
            return redirect()
                ->route('admin.roles')
                ->with('error', 'Role Superadmin tidak boleh dihapus.');
        }

        $assignedUsersCount = $role->users()->count();
        if ($assignedUsersCount > 0) {
            return redirect()
                ->route('admin.roles')
                ->with('error', "Role tidak bisa dihapus karena masih dipakai oleh {$assignedUsersCount} user.");
        }

        $role->deleteOrFail();

        return redirect()
            ->route('admin.roles')
            ->with('success', 'Role berhasil dihapus.');
    }
}
