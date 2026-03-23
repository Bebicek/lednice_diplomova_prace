<?php

namespace App\Livewire\Admin\Lunches;

use App\Models\Lunch;
use App\Models\User;
use Livewire\Component;

class LunchManager extends Component
{

    public function render()
    {
        $lunches = Lunch::query()
            ->with('user')
            ->get();

        $users = User::query()
            ->where('enabled', true)
            ->orderBy('name')
            ->get();

        return view('livewire.admin.lunches.lunch-manager', [
            'lunches' => $lunches,
            'users' => $users,
        ]);
    }
}
