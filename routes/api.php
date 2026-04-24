<?php

use App\Http\Controllers\EmailValidationController;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;


Route::post('/validate-email', [EmailValidationController::class, 'index'])
    ->middleware('auth.api');

Route::post('/bulk-validate-email', [EmailValidationController::class, 'bulk'])
    ->middleware('auth.api');