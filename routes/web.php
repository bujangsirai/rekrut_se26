<?php

use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\LoginPageController;
use App\Http\Controllers\PublicLandingController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PublicLandingController::class, 'index'])
    ->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginPageController::class, 'index'])
        ->name('login');
    Route::post('/login-password', [AuthController::class, 'store'])
        ->name('password.login');
});

Route::middleware('auth')->group(function () {
    Route::prefix('admin')->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])
            ->name('admin.dashboard');
        Route::get('/logout', [AuthController::class, 'logout'])
            ->name('admin.logout');
    });
});
