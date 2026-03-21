<?php

use App\Livewire\Admin\Commodities\CommodityManager;
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

// Admin Routes
// Dashboard
Route::get('/admin', \App\Livewire\Admin\Dashboard::class)
    ->middleware('auth')
    ->name('admin.dashboard');

// Commodity Manager
Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    Route::get('/commodities', CommodityManager::class)->name('admin.commodities');
});


















