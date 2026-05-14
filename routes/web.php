<?php

use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AdminKuesionerController;
use App\Http\Controllers\AdminMitraController;
use App\Http\Controllers\AdminRoleController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\LoginPageController;
use App\Http\Controllers\PublicLandingController;
use App\Http\Controllers\PublicRegistrationController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PublicLandingController::class, 'index'])
    ->name('home');
// TODO
// Route::get('/daftar', [PublicRegistrationController::class, 'create'])
//     ->name('public.register');
// Route::post('/daftar', [PublicRegistrationController::class, 'store'])
//     ->name('public.register.store');
// Route::get('/daftar/sukses', [PublicRegistrationController::class, 'success'])
//     ->name('public.register.success');

Route::post('/cek-status', [PublicRegistrationController::class, 'checkStatus'])
    ->name('public.check-status');
Route::get('/status', [PublicRegistrationController::class, 'showStatus'])
    ->name('public.status');
Route::post('/status/upload-sobat', [PublicRegistrationController::class, 'uploadSobat'])
    ->name('public.status.upload-sobat');
Route::get('/seleksi/{kode_akses}', [PublicRegistrationController::class, 'selection'])
    ->name('public.selection');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginPageController::class, 'index'])
        ->name('login');
    Route::post('/login-password', [AuthController::class, 'store'])
        ->name('password.login');
});

Route::middleware('auth')->group(function () {
    Route::get('/logout', [AuthController::class, 'logout'])
        ->name('logout');

    Route::prefix('admin')->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])
            ->name('admin.dashboard');
        Route::middleware('role:admin')->group(function () {
            Route::get('/roles', [AdminRoleController::class, 'index'])
                ->name('admin.roles');
            Route::post('/roles', [AdminRoleController::class, 'store'])
                ->name('admin.roles.store');
            Route::put('/roles/{role}', [AdminRoleController::class, 'update'])
                ->name('admin.roles.update');
            Route::delete('/roles/{role}', [AdminRoleController::class, 'destroy'])
                ->name('admin.roles.destroy');
            Route::get('/users', [AdminUserController::class, 'index'])
                ->name('admin.users.index');
            Route::post('/users', [AdminUserController::class, 'store'])
                ->name('admin.users.store');
            Route::put('/users/{user}', [AdminUserController::class, 'update'])
                ->name('admin.users.update');
            Route::delete('/users/{user}', [AdminUserController::class, 'destroy'])
                ->name('admin.users.destroy');
            Route::get('/kuesioner', [AdminKuesionerController::class, 'index'])
                ->name('admin.kuesioner.index');
            Route::post('/kuesioner', [AdminKuesionerController::class, 'store'])
                ->name('admin.kuesioner.store');
            Route::put('/kuesioner/{kuesioner}', [AdminKuesionerController::class, 'update'])
                ->name('admin.kuesioner.update');
        });
        Route::get('/mitra', [AdminMitraController::class, 'index'])
            ->name('admin.mitra.index');
        Route::get('/mitra/export', [AdminMitraController::class, 'export'])
            ->name('admin.mitra.export');
        Route::get('/file/{payload}', [AdminMitraController::class, 'file'])
            ->name('admin.file.show');
        Route::post('/mitra', [AdminMitraController::class, 'store'])
            ->name('admin.mitra.store');
        Route::put('/mitra/{mitra}', [AdminMitraController::class, 'update'])
            ->name('admin.mitra.update');
        Route::delete('/mitra/{mitra}', [AdminMitraController::class, 'destroy'])
            ->name('admin.mitra.destroy');
    });
});
