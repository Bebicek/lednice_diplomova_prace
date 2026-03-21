<?php

use App\Livewire\Admin\AdminDashboard;
use App\Livewire\Admin\Categories\CategoryManager;
use App\Livewire\Admin\Commodities\CommodityManager;
use App\Livewire\Admin\Stock\StockManager;
use App\Livewire\Admin\Users\UsersManager;
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

// Admin routes
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', AdminDashboard::class)->name('dashboard');

    Route::get('/commodities', CommodityManager::class)
        ->name('commodities');

    Route::get('/categories', CategoryManager::class)
        ->name('categories');

    Route::get('/users', UsersManager::class)
        ->name('users');

    Route::get('/stock', StockManager::class)
        ->name('stock');
});













