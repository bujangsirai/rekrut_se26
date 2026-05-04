<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Http\RedirectResponse;

class ProfileController extends Controller
{
    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'password' => ['required', 'string', 'confirmed'],
        ]);

        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('success', 'Password berhasil diubah.');
    }
    public function updateEmail(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email_gmail' => ['nullable', 'email', 'max:255'],
        ]);

        $request->user()->update([
            'email_gmail' => $validated['email_gmail'],
        ]);

        return back()->with('success', 'Email berhasil diperbarui.');
    }
}
