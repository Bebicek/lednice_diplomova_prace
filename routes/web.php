<?php

use App\Livewire\Admin\AdminDashboard;
use App\Livewire\Admin\Categories\CategoryManager;
use App\Livewire\Admin\Commodities\CommodityManager;
use App\Livewire\Admin\Stock\StockManager;
use App\Livewire\Admin\Users\UsersManager;
use App\Livewire\Auth\Login;
use App\Livewire\Cart;
use App\Livewire\Catalog;
use App\Livewire\Dashboard;
use App\Livewire\MyDebts;
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

// TODO: Have to make all of them or remove them later
// Client routes WIP
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', Dashboard::class)->name('dashboard');
    Route::get('/catalog', Catalog::class)->name('catalog');
    Route::get('/cart', Cart::class)->name('cart');
    Route::get('/my-debts', MyDebts::class)->name('my-debts');
    Route::get('/profile', function () {return "todo";})->name('profile');
    Route::get('/lunches', function () {return "todo";})->name('lunches');
    Route::get('/lunches/create', function () {return "todo";})->name('lunches.create');
});
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













