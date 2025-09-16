<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Login;

Route::get('/login', Login::class)->name('login')->middleware('guest');

Route::middleware('auth')->group(function () {
    Route::get('logout', \Filament\Auth\Http\Controllers\LogoutController::class);
});
