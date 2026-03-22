<?php

namespace App\Livewire\Admin\Expiry;

use App\Models\Commodity;
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
        $commodity = Commodity::findOrFail($id);
        $this->editingId = $id;
        $this->editDate = $commodity->expires_at?->format('Y-m-d') ?? '';
    }

    public function saveEdit(): void
    {
        $this->validate(['editDate' => 'nullable|date']);

        Commodity::findOrFail($this->editingId)
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
        Commodity::findOrFail($id)->update(['expires_at' => null]);
        $this->dispatch('toast-success', message: 'Datum spotřeby bylo odebráno.');
    }

    public function render()
    {
        $all = Commodity::all();

        $stats = [
            'expired' => $all->filter(fn ($c) => $c->expiry_status === 'expired')->count(),
            'critical' => $all->filter(fn ($c) => $c->expiry_status === 'critical')->count(),
            'warning' => $all->filter(fn ($c) => $c->expiry_status === 'warning')->count(),
            'none' => $all->filter(fn ($c) => $c->expires_at === null)->count(),
        ];

        $commodities = Commodity::with(['category', 'stock'])
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
            ->orderBy('name')
            ->get();

        return view('livewire.admin.expiry.expiry-manager', [
            'stats' => $stats,
            'commodities' => $commodities,
        ]);
    }
}
