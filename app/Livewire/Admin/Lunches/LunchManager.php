<?php

namespace App\Livewire\Admin\Lunches;

use App\Models\Debt;
use App\Models\Lunch;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class LunchManager extends Component
{
    public string $search = '';
    public string $filterStatus  = ''; // '' | 'settled' | 'unsettled'
    public string $sortBy  = 'created_at';
    public string $sortDirection = 'desc';
    public int $page = 1;
    public int $perPage = 7;

    public ?int $detailLunchId = null;
    public bool $hasDetailOpen = false;

    public function sort(string $column): void
    {
        $allowed = ['restaurant_name', 'organizer_name', 'participants_count', 'total_amount', 'split_method', 'is_settled', 'created_at'];
        if (!in_array($column, $allowed)) return;

        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }
        $this->page = 1;
    }

    public function updatingSearch(): void
    {
        $this->page = 1;
    }

    public function updatingFilterStatus(): void
    {
        $this->page = 1;
    }

    public function openDetail(int $lunchId): void
    {
        $this->detailLunchId = $lunchId;
        $this->hasDetailOpen = true;
    }

    public function closeDetail(): void
    {
        $this->hasDetailOpen = false;
        $this->detailLunchId = null;
    }

    // Maybe nonsense too, need further info
    public function closeAllDebts(int $lunchId): void
    {
        $count = Debt::where('lunch_id', $lunchId)
            ->where('is_paid', false)
            ->update(['is_paid' => true, 'paid_at' => now()]);

        $this->dispatch('toast-success', message: "Označeno {$count} dluhů jako zaplacených.");
    }

//    TODO: Propably nonsense to adding this feature to lunch manager for admin (remove or do something with it later)
//    public function deleteLunch(int $lunchId): void
//    {
//        $lunch = Lunch::findOrFail($lunchId);
//
//        if (!$lunch->isSettled()) {
//            $this->dispatch('toast-error', message: 'Nelze smazat oběd s nezaplacenými dluhy.');
//            return;
//        }
//
//        $lunch->delete();
//        $this->dispatch('toast-success', message: 'Oběd byl smazán.');
//    }

    public function render(): View
    {
        $lunches = Lunch::query()
            ->with(['organizer', 'participants.user', 'debts'])
            ->withCount('participants')
            ->when($this->search, fn($q) => $q->where(function ($q) {
                $q->where('restaurant_name', 'ilike', "%{$this->search}%")
                    ->orWhereHas('organizer', fn($q) => $q->where('name', 'ilike', "%{$this->search}%"));
            }))
            ->get();

        if ($this->filterStatus === 'settled') {
            $lunches = $lunches->filter(fn($l) => $l->isSettled())->values();
        } elseif ($this->filterStatus === 'unsettled') {
            $lunches = $lunches->filter(fn($l) => !$l->isSettled())->values();
        }

        // Collection level sort (supports PHP-computed columns like organizer_name, is_settled)
        // For big data it could be not that good but for our purpose its good enough
        $desc = $this->sortDirection === 'desc';
        $lunches = $lunches->sortBy(match ($this->sortBy) {
            'organizer_name' => fn($l) => $l->organizer?->name ?? '',
            'participants_count' => fn($l) => $l->participants_count,
            'is_settled' => fn($l) => $l->isSettled() ? 1 : 0,
            default => fn($l) => $l->{$this->sortBy},
        }, SORT_REGULAR, $desc)->values();

        // Stats
        $allLunches = Lunch::with('debts')->get();
        $settledCount = $allLunches->filter(fn($l) => $l->isSettled())->count();
        $totalLunchCount = $allLunches->count();

        $stats = [
            'total' => $totalLunchCount,
            'settled' => $settledCount,
            'unsettled' => $totalLunchCount - $settledCount,
            'totalAmount' => Lunch::sum('total_amount'),
        ];

        // Detail modal data
        $detailLunch = null;
        if ($this->hasDetailOpen && $this->detailLunchId !== null) {
            $detailLunch = Lunch::with([
                'organizer',
                'participants.user',
                'participants.items',
                'debts',
            ])->find($this->detailLunchId);
        }

        $total = $lunches->count();
        $lastPage = max(1, (int) ceil($total / $this->perPage));
        $this->page = min($this->page, $lastPage);
        $paginatedLunches = $lunches->forPage($this->page, $this->perPage);

        return view('livewire.admin.lunches.lunch-manager', [
            'lunches' => $paginatedLunches,
            'stats' => $stats,
            'detailLunch' => $detailLunch,
            'total' => $total,
            'page' => $this->page,
            'lastPage' => $lastPage,
            'perPage' => $this->perPage,
        ]);
    }
}
