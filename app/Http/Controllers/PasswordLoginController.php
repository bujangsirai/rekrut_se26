<?php

namespace App\Http\Controllers;

use App\Http\Requests\PasswordLoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class PasswordLoginController extends Controller
{
    /**
     * Handle an incoming username and password login request.
     *
     * @throws ValidationException
     */
    public function store(PasswordLoginRequest $request): RedirectResponse
    {
        $credentials = $request->safe()->only(['username', 'password']);

        if (! Auth::attempt($credentials)) {
            throw ValidationException::withMessages([
                'username' => 'Username atau password tidak valid.',
            ]);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('home'));
    }
}
