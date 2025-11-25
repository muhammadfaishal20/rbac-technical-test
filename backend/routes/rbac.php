<?php

use App\Http\Controllers\RBAC\RoleController;
use App\Http\Controllers\RBAC\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| RBAC Routes
|--------------------------------------------------------------------------
|
| Routes for Role-Based Access Control (RBAC) management
|
*/

// Role Routes - Only admin and management-user can access (manage-roles permission)
Route::prefix('roles')->name('roles.')->group(function () {
    Route::get('/', [RoleController::class, 'index'])->name('index');
    Route::post('/', [RoleController::class, 'store'])->name('store');
    Route::get('/permissions', [RoleController::class, 'getPermissions'])->name('permissions');
    Route::get('/{role}', [RoleController::class, 'show'])->name('show');
    Route::put('/{role}', [RoleController::class, 'update'])->name('update');
    Route::patch('/{role}', [RoleController::class, 'update'])->name('update');
    Route::delete('/{role}', [RoleController::class, 'destroy'])->name('destroy');
});

// User Routes - Only admin can access (manage-users permission)
Route::middleware(['permission:manage-users'])->prefix('users')->name('users.')->group(function () {
    Route::get('/', [UserController::class, 'index'])->name('index');
    Route::post('/', [UserController::class, 'store'])->name('store');
    Route::get('/roles', [UserController::class, 'getRoles'])->name('roles');
    Route::get('/{user}', [UserController::class, 'show'])->name('show');
    Route::put('/{user}', [UserController::class, 'update'])->name('update');
    Route::patch('/{user}', [UserController::class, 'update'])->name('update');
    Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');
});

