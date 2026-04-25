<?php

use App\Http\Controllers\APIController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UsageController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('pages.home');
})->name('home');

Route::get('/login', function () {
    return view('auth.login');
})->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('auth.login');

Route::get('/register', function () {
    return view('auth.register');
});
Route::post('/register', [AuthController::class, 'register'])->name('auth.register');
Route::get('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/usage', [UsageController::class, 'index'])->name('usage');
    Route::get('/usage/validation/{id}', [UsageController::class, 'showValidation'])->name('usage.validation');
    Route::get('/usage/export', [UsageController::class, 'export'])->name('usage.export');
    Route::post('/bulk-jobs/{id}/cancel', [APIController::class, 'cancelBulkJob'])->name('bulk.cancel');
    Route::get('/bulk-validation', [APIController::class, 'bulkValidation'])->name('bulk-validation');
    Route::get('/docs', function () {
        return view('pages.docs.index');
    })->name('docs');
    Route::post('/api/generate-api-key', function () {
        $user = Auth::user();
        $user->generateAPIKey();
        session()->flash('success', 'API Key generated successfully');
        return response()->json([
            'message' => 'API Key generated successfully',
            'api_key' => $user->getAPIKey()
        ]);
    })->name('api.generate-api-key');
});





