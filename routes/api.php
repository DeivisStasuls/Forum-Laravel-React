<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register'])
        ->name('api.auth.register');
    Route::post('/login', [AuthController::class, 'login'])
        ->name('api.auth.login');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])
            ->name('api.auth.logout');
        Route::get('/user', [AuthController::class, 'user'])
            ->name('api.auth.user');
    });
});
