<?php

use Illuminate\Support\Facades\Route;
use Modules\Admin\Http\Controllers\UserController;
use Modules\Admin\Http\Controllers\RoleController;

Route::middleware(['auth', 'verified', 'role:Superadmin'])->group(function () {
    Route::prefix('app/admin')->name('admin.')->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users');
        Route::put('/users/{user}/password', [UserController::class, 'updatePassword'])->name('users.password');
        Route::put('/users/{user}/profile', [UserController::class, 'updateProfile'])->name('users.profile');
        Route::get('/roles', [RoleController::class, 'index'])->name('roles');
        Route::post('/roles', [RoleController::class, 'store'])->name('roles.store');
        Route::put('/roles/{role}', [RoleController::class, 'update'])->name('roles.update');
        Route::delete('/roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy');
    });
});
