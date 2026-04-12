<?php

namespace App\Livewire;

use App\Models\Debt;
use App\Models\Lunch;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\LunchParticipant;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.client')]
class Dashboard extends Component
{
    public function render()
    {
        $user = auth()->user();
        $userId = $user->id;
        $startOfMonth = now()->startOfMonth();

        $ordersThisMonth = Order::where('user_id', $userId)
            ->where('created_at', '>=', $startOfMonth)
            ->count();

        $spentThisMonth = Order::where('user_id', $userId)
            ->where('created_at', '>=', $startOfMonth)
            ->sum('total_amount');

        $unpaidDebt = Debt::where('user_id', $userId)
            ->where('is_paid', false)
            ->sum('amount');

        // User is a participant of a lunch and the lunch is not paid
        $activeLunches = LunchParticipant::where('user_id', $userId)
            ->whereHas('lunch', fn($q) => $q->whereHas('debts', fn($q) => $q->where('is_paid', false)))
            ->count();

        $topProducts = OrderItem::select('commodity_id', DB::raw('SUM(quantity) as total_quantity'))
            ->whereHas('order', fn($q) => $q->where('user_id', $userId))
            ->with('commodity')
            ->groupBy('commodity_id')
            ->orderByDesc('total_quantity')
            ->limit(3)
            ->get();

        // Where user is a participant
        $recentLunches = LunchParticipant::where('user_id', $userId)
            ->with(['lunch.organizer', 'lunch.participants'])
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn($p) => $p->lunch)
            ->filter()
            ->unique('id')
            ->values();

        return view('livewire.dashboard', [
            'ordersThisMonth' => $ordersThisMonth,
            'spentThisMonth' => $spentThisMonth,
            'unpaidDebt' => $unpaidDebt,
            'activeLunches' => $activeLunches,
            'topProducts' => $topProducts,
            'recentLunches' => $recentLunches,
        ]);
    }
}
