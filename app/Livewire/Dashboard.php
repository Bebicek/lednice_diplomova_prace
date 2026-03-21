<?php

namespace App\Livewire;

use App\Models\Debt;
use App\Models\Lunch;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\LunchParticipant;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.client')]
class Dashboard extends Component
{
    public function render()
    {
        $user = auth()->user();



        return view('livewire.dashboard', [
        ]);
    }
}
