<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Admin\Http\Requests\StoreRoleRequest;
use Modules\Admin\Http\Requests\UpdateRoleRequest;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function index(): Response
    {
        $roles = Role::query()
            ->with(['users' => function ($query) {
                $query->orderBy('nip_baru', 'asc');
            }])
            ->withCount('users')
            ->orderBy('name')
            ->get()
            ->map(fn (Role $role) => [
                'id' => $role->id,
                'name' => $role->name,
                'description' => $role->description,
                'users_count' => $role->users_count,
                'users' => $role->users->map(fn ($user) => [
                    'id' => $user->id,
                    'nama' => $user->nama,
                    'nip_baru' => $user->nip_baru,
                    'email_bps' => $user->email_bps,
                    'email_gmail' => $user->email_gmail,
                    'status_pegawai' => $user->status_pegawai,
                    'url_foto' => $user->url_foto,
                ]),
            ]);

        $availableUsers = \App\Models\User::orderBy('nama', 'asc')->get()->map(fn ($user) => [
            'value' => (string) $user->id,
            'label' => $user->nama,
        ]);

        return Inertia::render('admin::RoleIndexPage', [
            'roles' => $roles,
            'availableUsers' => $availableUsers,
        ]);
    }

    public function store(StoreRoleRequest $request)
    {
        $validated = $request->validated();

        Role::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'guard_name' => 'web',
        ]);

        return back()->with('success', 'Role berhasil ditambahkan');
    }

    public function update(UpdateRoleRequest $request, Role $role)
    {
        $validated = $request->validated();

        $role->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
        ]);

        if (isset($validated['users'])) {
            $role->users()->sync($validated['users']);
        } else {
            $role->users()->sync([]);
        }

        return back()->with('success', 'Role berhasil diperbarui');
    }

    public function destroy(Role $role)
    {
        if ($role->users()->count() > 0) {
            return back()->withErrors(['error' => 'Gagal! Role ini masih digunakan oleh '.$role->users()->count().' user.']);
        }

        $role->delete();

        return back()->with('success', 'Role berhasil dihapus');
    }
}
