<?php

use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::get('logout', \Filament\Auth\Http\Controllers\LogoutController::class);
});
