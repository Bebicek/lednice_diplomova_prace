<?php

namespace App\Livewire\Admin\Expiry;

use App\Models\Stock;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Rule;
use Livewire\Component;

#[Layout('layouts.app')]
class ExpiryManager extends Component
{
    //filters all | expired | critical | warning | ok | none
    public string $filterStatus = 'all';

    public ?int $editingId = null;
    public string $editDate = '';

    public function openEdit(int $id): void
    {
        $stock = Stock::findOrFail($id);
        $this->editingId = $id;
        $this->editDate = $stock->expires_at?->format('Y-m-d') ?? '';
    }

    public function saveEdit(): void
    {
        $this->validate(['editDate' => 'nullable|date']);

        Stock::findOrFail($this->editingId)
            ->update(['expires_at' => $this->editDate ?: null]);

        $this->editingId = null;
        $this->editDate = '';
        $this->dispatch('toast-success', message: 'Datum spotřeby bylo uloženo.');
    }

    public function cancelEdit(): void
    {
        $this->editingId = null;
        $this->editDate = '';
    }

    public function clearExpiry(int $id): void
    {
        Stock::findOrFail($id)->update(['expires_at' => null]);
        $this->dispatch('toast-success', message: 'Datum spotřeby bylo odebráno.');
    }

    public function render()
    {
        $allFridgeStock = Stock::with(['commodity.category'])
            ->where('location', 'fridge')
            ->get();

        $stats = [
            'expired' => $allFridgeStock->filter(fn ($s) => $s->expiry_status === 'expired')->count(),
            'critical' => $allFridgeStock->filter(fn ($s) => $s->expiry_status === 'critical')->count(),
            'warning' => $allFridgeStock->filter(fn ($s) => $s->expiry_status === 'warning')->count(),
            'none' => $allFridgeStock->filter(fn ($s) => $s->expires_at === null)->count(),
        ];

        $stocks = Stock::with(['commodity.category'])
            ->where('location', 'fridge')
            ->when($this->filterStatus !== 'all', function ($q) {
                match ($this->filterStatus) {
                    'expired' => $q->whereNotNull('expires_at')->whereDate('expires_at', '<', today()),
                    'critical' => $q->whereNotNull('expires_at')->whereDate('expires_at', '>=', today())->whereDate('expires_at', '<=', today()->addDays(3)),
                    'warning' => $q->whereNotNull('expires_at')->whereDate('expires_at', '>', today()->addDays(3))->whereDate('expires_at', '<=', today()->addDays(7)),
                    'ok' => $q->whereNotNull('expires_at')->whereDate('expires_at', '>', today()->addDays(7)),
                    'none' => $q->whereNull('expires_at'),
                    default => null,
                };
            })
            ->orderByRaw('CASE WHEN expires_at IS NULL THEN 1 ELSE 0 END')
            ->orderBy('expires_at')
            ->get()
            ->sortBy(fn ($s) => $s->commodity?->name);

        return view('livewire.admin.expiry.expiry-manager', [
            'stats' => $stats,
            'stocks' => $stocks,
        ]);
    }
}
