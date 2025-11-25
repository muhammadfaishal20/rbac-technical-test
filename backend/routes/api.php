<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Authentication Routes
Route::prefix('auth')->group(base_path('routes/auth.php'));

// RBAC Routes - Only admin and management-user can access (manage-roles permission)
Route::middleware(['auth:sanctum', 'permission:manage-roles'])->prefix('rbac')->group(base_path('routes/rbac.php'));

// File Upload Routes - Only admin and management-file can access (manage-files permission)
Route::middleware(['auth:sanctum', 'permission:manage-files'])->prefix('files')->group(base_path('routes/file.php'));
