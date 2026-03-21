<?php

namespace App\Livewire;

use App\Models\Debt;
use Livewire\Attributes\On;
use Livewire\Component;

class DebtAlert extends Component
{
    /**
     * Listen for debt-updated events (dispatched from MyDebts when marking paid).
     * Empty body - just triggers a rerender.
     */
    #[On('debt-updated')]
    public function refresh(): void {}

    public function render()
    {
        $recentDebts = Debt::where('user_id', auth()->id())
            ->where('is_paid', false)
            ->with(['order', 'lunch', 'creditor'])
            ->latest()
            ->take(4)
            ->get();

        $unpaidCount = Debt::where('user_id', auth()->id())
            ->where('is_paid', false)
            ->count();

        $totalAmount = Debt::where('user_id', auth()->id())
            ->where('is_paid', false)
            ->sum('amount');

        return view('livewire.debt-alert', [
            'recentDebts' => $recentDebts,
            'unpaidCount' => $unpaidCount,
            'totalAmount' => $totalAmount,
        ]);
    }
}
