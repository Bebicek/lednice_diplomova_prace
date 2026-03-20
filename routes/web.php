<?php

use App\Livewire\Auth\Login;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Login
Route::get('/', fn () => redirect()->route('login'));

Route::get('/login', Login::class)->name('login')->middleware('guest');

Route::post('/logout', function () {
    Auth::guard('web')->logout();
    session()->invalidate();
    session()->regenerateToken();
    return redirect()->route('login');
})->name('logout');

// Dashboard
Route::get('/admin', \App\Livewire\Admin\Dashboard::class)
    ->middleware('auth')
    ->name('admin.dashboard');


















