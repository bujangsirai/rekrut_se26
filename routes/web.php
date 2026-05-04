<?php

use App\Http\Controllers\PasswordLoginController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicLandingController;
use App\Http\Controllers\SSOBPSController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PublicLandingController::class, 'index'])
    ->name('home');

// SSO Route
Route::get('sso/redirect', [SSOBPSController::class, 'ssoBPSRedirect'])
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
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');
    Route::put('/profile/email', [ProfileController::class, 'updateEmail'])->name('profile.email.update');
});
