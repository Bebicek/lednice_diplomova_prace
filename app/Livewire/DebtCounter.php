<?php

namespace App\Livewire;

use App\Models\Debt;
use Livewire\Attributes\On;
use Livewire\Component;

class DebtCounter extends Component
{
    public bool $unique = true;

    #[On('debt-updated')]
    public function refresh(): void {}

    public function render()
    {
        $query = Debt::where('user_id', auth()->id())
            ->where('is_paid', false)
            ->where('is_accepted', true);

        $count = $this->unique
            ? $query->pluck('creditor_id')->unique()->count()
            : $query->count();

        return view('livewire.debt-counter', ['count' => $count]);
    }
}
