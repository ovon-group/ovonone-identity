<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Login;
use App\Livewire\ForgotPassword;
use App\Livewire\ResetPassword;

Route::get('/login', Login::class)->name('login')->middleware('guest');
Route::get('/forgot-password', ForgotPassword::class)->name('password.request')->middleware('guest');
Route::get('/reset-password/{token}', ResetPassword::class)->name('password.reset')->middleware('guest');

Route::middleware('auth')->group(function () {
    Route::get('logout', \Filament\Auth\Http\Controllers\LogoutController::class);
});
