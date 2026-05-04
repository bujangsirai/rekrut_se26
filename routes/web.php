<?php

use App\Http\Controllers\PasswordLoginController;
use App\Http\Controllers\SSOBPSController;
use Illuminate\Support\Facades\Route;


// SSO Route
Route::get('/', [SSOBPSController::class, 'ssoBPSRedirect'])
    ->name('sso.redirect');

Route::get('login_sso', [SSOBPSController::class, 'ssoBPSLogin'])
    ->name('sso.login');

Route::get('logout', [SSOBPSController::class, 'logout'])
    ->name('logout');

Route::middleware('guest')->group(function () {
    Route::post('login-password', [PasswordLoginController::class, 'store'])
        ->name('password.login');
});

Route::middleware(['auth', 'verified'])->prefix('app')->group(function () {
    Route::put('/profile/password', [\App\Http\Controllers\ProfileController::class, 'updatePassword'])->name('profile.password.update');
    Route::put('/profile/email', [\App\Http\Controllers\ProfileController::class, 'updateEmail'])->name('profile.email.update');
});
