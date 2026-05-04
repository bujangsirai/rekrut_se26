<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Admin\Http\Requests\UpdateUserPasswordRequest;
use Modules\Admin\Http\Requests\UpdateUserProfileRequest;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(): Response
    {
        $users = User::query()
            ->with('roles')
            ->orderByRaw("CASE WHEN LOWER(status_pegawai) = 'tidak aktif' THEN 1 ELSE 0 END")
            ->orderBy('nip_baru')
            ->get()
            ->map(fn (User $user) => [
                'id' => $user->id,
                'nama' => $user->nama,
                'nip' => $user->nip,
                'nip_baru' => $user->nip_baru,
                'username' => $user->username,
                'email_bps' => $user->email_bps,
                'email_gmail' => $user->email_gmail,
                'golongan' => $user->golongan,
                'jabatan' => $user->jabatan,
                'status_pegawai' => $user->status_pegawai,
                'url_foto' => $user->url_foto,
                'roles' => $user->getRoleNames()->values()->all(),
            ]);

        $roles = Role::query()->orderBy('name')->pluck('name');

        return Inertia::render('admin::UserIndexPage', [
            'users' => $users,
            'roles' => $roles,
        ]);
    }

    public function updatePassword(UpdateUserPasswordRequest $request, User $user): RedirectResponse
    {
        $validated = $request->validated();

        $user->update(['password' => $validated['password']]);

        return back()->with('success', "Password {$user->nama} berhasil diubah.");
    }

    public function updateProfile(UpdateUserProfileRequest $request, User $user): RedirectResponse
    {
        $validated = $request->validated();

        $user->update([
            'email_gmail' => $validated['email_gmail'],
            'status_pegawai' => $validated['status_pegawai'],
        ]);

        $user->syncRoles($validated['roles']);

        return back()->with('success', "Profil {$user->nama} berhasil diperbarui.");
    }
}
