<?php

use App\Http\Controllers\File\FileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| File Upload Routes
|--------------------------------------------------------------------------
|
| Routes for file upload and management
|
*/

Route::middleware(['auth:sanctum'])->group(function () {
    // All authenticated users can upload files (manage-files permission)
    Route::post('/upload', [FileController::class, 'store'])->name('upload');
    
    // List files - users can only see their own, admin can see all
    Route::get('/', [FileController::class, 'index'])->name('index');
    
    // Show file details
    Route::get('/{file}', [FileController::class, 'show'])->name('show');
    
    // Download file
    Route::get('/{file}/download', [FileController::class, 'download'])->name('download');
    
    // Delete file
    Route::delete('/{file}', [FileController::class, 'destroy'])->name('destroy');
});

