<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Redis;
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

Route::post('/api/generate-api-key', function () {
    $user = Auth::user();
    $user->generateAPIKey();
    session()->flash('success', 'API Key generated successfully');
    return response()->json([
        'message' => 'API Key generated successfully',
        'api_key' => $user->getAPIKey()
    ]);
})->name('api.generate-api-key');

