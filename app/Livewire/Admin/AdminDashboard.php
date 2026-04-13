<?php

namespace App\Livewire\Admin;

use App\Models\Commodity;
use App\Models\Debt;
use App\Models\Order;
use App\Models\Setting;
use App\Models\Stock;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class AdminDashboard extends Component
{
    public function render()
    {
        $lowStockThreshold = (int) Setting::where('key', 'low_stock_threshold')->value('value') ?? 3;

        $totalProducts = Commodity::where('is_active', true)->count();

        $lowStockCount = Stock::where('location', 'fridge')
            ->where('quantity', '>', 0)
            ->where('quantity', '<=', $lowStockThreshold)
            ->count();

        $activeUsers = User::where('enabled', true)->count();

        $expiringCount = Stock::where('location', 'fridge')
            ->where('quantity', '>', 0)
            ->whereNotNull('expires_at')
            ->where('expires_at', '>=', now()->startOfDay())
            ->where('expires_at', '<=', now()->addDays(7)->endOfDay())
            ->count();

        // Products with 0 stock in fridge
        $emptyFridgeProducts = Commodity::where('is_active', true)
            ->whereDoesntHave('stock', fn($q) => $q->where('location', 'fridge')->where('quantity', '>', 0))
            ->select('id', 'name', 'image_path')
            ->limit(8)
            ->get();

        $emptyFridgeCount = Commodity::where('is_active', true)
            ->whereDoesntHave('stock', fn($q) => $q->where('location', 'fridge')->where('quantity', '>', 0))
            ->count();

        // Recently paid debts
        $recentlyPaidDebts = Debt::where('is_paid', true)
            ->whereNotNull('paid_at')
            ->with(['user', 'creditor'])
            ->orderByDesc('paid_at')
            ->limit(5)
            ->get();

        // Revenue per month last 6 months
        $revenueData = Order::select(
                DB::raw('EXTRACT(YEAR FROM created_at)::int as year'),
                DB::raw('EXTRACT(MONTH FROM created_at)::int as month'),
                DB::raw('SUM(total_amount) as total')
            )
            ->where('created_at', '>=', now()->subMonths(5)->startOfMonth())
            ->groupBy(DB::raw('EXTRACT(YEAR FROM created_at)'), DB::raw('EXTRACT(MONTH FROM created_at)'))
            ->orderBy(DB::raw('EXTRACT(YEAR FROM created_at)'))
            ->orderBy(DB::raw('EXTRACT(MONTH FROM created_at)'))
            ->get()
            ->keyBy(fn($row) => $row->year . '-' . str_pad($row->month, 2, '0', STR_PAD_LEFT));

        $revenueMonths = [];
        $revenueValues = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $key = $date->year . '-' . str_pad($date->month, 2, '0', STR_PAD_LEFT);
            $revenueMonths[] = $date->locale('cs')->isoFormat('MMM YY');
            $revenueValues[] = round(($revenueData[$key]->total ?? 0) / 100, 2);
        }

        // Top 5 debtors
        $topDebtors = Debt::where('is_paid', false)
            ->whereNotNull('user_id')
            ->select('user_id', DB::raw('SUM(amount) as total_debt'))
            ->groupBy('user_id')
            ->orderByDesc('total_debt')
            ->with('user')
            ->limit(5)
            ->get();

        return view('livewire.admin.dashboard', [
            'totalProducts' => $totalProducts,
            'lowStockCount' => $lowStockCount,
            'activeUsers' => $activeUsers,
            'expiringCount' => $expiringCount,
            'emptyFridgeProducts' => $emptyFridgeProducts,
            'emptyFridgeCount' => $emptyFridgeCount,
            'recentlyPaidDebts' => $recentlyPaidDebts,
            'revenueMonths' => $revenueMonths,
            'revenueValues' => $revenueValues,
            'topDebtors' => $topDebtors,
        ]);
    }
}
