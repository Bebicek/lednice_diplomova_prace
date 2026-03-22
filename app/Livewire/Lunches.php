<?php

namespace App\Livewire;

use App\Models\Debt;
use App\Models\Lunch;
use App\Models\LunchParticipant;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.client')]
class Lunches extends Component
{
    public function acceptDebt(int $debtId): void
    {
        $debt = Debt::where('id', $debtId)
            ->where('user_id', auth()->id())
            ->where('is_accepted', false)
            ->firstOrFail();

        $debt->update([
            'is_accepted' => true,
            'accepted_at' => now(),
        ]);

        //sync lunch_participant approval
        LunchParticipant::where('lunch_id', $debt->lunch_id)
            ->where('user_id', auth()->id())
            ->update(['is_approved' => true, 'approved_at' => now()]);

        session()->flash('success', 'Dluh byl přijat a zapsán.');
    }

    public function render()
    {
        $userId = auth()->id();

        //lunches I organized
        $myLunches = Lunch::where('organizer_id', $userId)
            ->with(['participants.user', 'participants.items', 'debts'])
            ->latest()
            ->get();

        //lunches I participated in but didnt organize
        $participatedLunches = Lunch::whereHas('participants', fn ($q) => $q->where('user_id', $userId))
            ->where('organizer_id', '!=', $userId)
            ->with(['organizer', 'participants' => fn ($q) => $q->where('user_id', $userId)->with('items')])
            ->latest()
            ->get();

        // pending debts lunch debts not yet accepted
        $pendingDebts = Debt::where('user_id', $userId)
            ->where('is_accepted', false)
            ->whereNotNull('lunch_id')
            ->with(['lunch.organizer', 'lunch.participants' => fn ($q) => $q->where('user_id', $userId)->with('items')])
            ->latest()
            ->get();

        return view('livewire.lunches', [
            'myLunches' => $myLunches,
            'participatedLunches' => $participatedLunches,
            'pendingDebts' => $pendingDebts,
        ]);
    }
}
