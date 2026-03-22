<?php

namespace App\Livewire\Admin\Accounts;

use App\Models\Debt;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Symfony\Component\HttpFoundation\StreamedResponse;

#[Layout('layouts.app')]
class AccountsManager extends Component
{
    public string $search = '';
    public string $filterStatus = '';   // '' | 'unpaid' | 'paid'
    public string $filterType = '';   // '' | 'system' | 'personal'

    public bool $showAddModal = false;
    public ?int $addUserId = null;
    public string $addAmount = '';
    public string $addType = 'system';
    public ?int $addCreditorId = null;

    // detailCreditorId = null system debt
    public ?int $detailUserId = null;
    public ?int $detailCreditorId = null;
    public bool $hasDetailOpen = false;

    protected function rules(): array
    {
        return [
            'addUserId' => 'required|exists:users,id',
            'addAmount' => 'required|numeric|min:0.01',
            'addType' => 'required|in:system,personal',
            'addCreditorId' => 'nullable|required_if:addType,personal|exists:users,id',
        ];
    }

    protected function messages(): array
    {
        return [
            'addUserId.required' => 'Vyberte dlužníka.',
            'addAmount.required' => 'Zadejte částku.',
            'addAmount.min' => 'Částka musí být kladná.',
            'addCreditorId.required_if' => 'Vyberte věřitele.',
        ];
    }

    public function markAllAsPaid(int $userId, ?int $creditorId): void
    {
        $q = Debt::where('user_id', $userId)->where('is_paid', false);
        $creditorId === null
            ? $q->whereNull('creditor_id')
            : $q->where('creditor_id', $creditorId);

        $count = $q->update(['is_paid' => true, 'paid_at' => now()]);
        $this->dispatch('toast-success', message: "Označeno {$count} dluhů jako zaplacených.");
    }

    public function markSingleAsPaid(int $id): void
    {
        Debt::findOrFail($id)->update(['is_paid' => true, 'paid_at' => now()]);
        $this->dispatch('toast-success', message: 'Dluh byl označen jako zaplacený.');
    }

    public function markSingleAsUnpaid(int $id): void
    {
        Debt::findOrFail($id)->update(['is_paid' => false, 'paid_at' => null]);
        $this->dispatch('toast-success', message: 'Dluh byl vrácen do stavu nezaplaceno.');
    }

    // Add manual debt
    public function openAddModal(): void
    {
        $this->showAddModal = true;
        $this->addUserId = null;
        $this->addAmount = '';
        $this->addType = 'system';
        $this->addCreditorId = null;
        $this->resetValidation();
    }

    public function closeAddModal(): void
    {
        $this->showAddModal = false;
    }

    public function saveDebt(): void
    {
        $this->validate();

        $creditorId = $this->addType === 'personal' ? $this->addCreditorId : null;

        if ($creditorId && $creditorId === $this->addUserId) {
            $this->addError('addCreditorId', 'Věřitel nemůže být stejný jako dlužník.');
            return;
        }

        Debt::create([
            'user_id' => $this->addUserId,
            'creditor_id' => $creditorId,
            'amount' => (int) round((float) str_replace(',', '.', $this->addAmount) * 100),
            'is_paid' => false,
        ]);

        $this->closeAddModal();
        $this->dispatch('toast-success', message: 'Dluh byl úspěšně přidán.');
    }

    // Group detail modal
    public function openDetail(int $userId, ?int $creditorId): void
    {
        $this->detailUserId = $userId;
        $this->detailCreditorId = $creditorId;
        $this->hasDetailOpen = true;
    }

    public function closeDetail(): void
    {
        $this->hasDetailOpen = false;
        $this->detailUserId = null;
        $this->detailCreditorId = null;
    }

    // CSV export individual records
    public function exportCsv(): StreamedResponse
    {
        $debts = $this->buildRawQuery()->get();

        return response()->streamDownload(function () use ($debts) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($handle, ['ID', 'Dlužník', 'Věřitel', 'Typ', 'Částka (Kč)', 'Stav', 'Datum vzniku', 'Zaplaceno dne'], ';');

            foreach ($debts as $debt) {
                $type = match (true) {
                    $debt->order_id !== null => 'Nákup',
                    $debt->lunch_id !== null => 'Oběd',
                    default => 'Ruční',
                };
                fputcsv($handle, [
                    $debt->id,
                    $debt->user->name,
                    $debt->creditor?->name ?? 'Systém (lednička)',
                    $type,
                    number_format($debt->amount / 100, 2, ',', ' '),
                    $debt->is_paid ? 'Zaplaceno' : 'Nezaplaceno',
                    $debt->created_at->format('d.m.Y'),
                    $debt->paid_at?->format('d.m.Y') ?? '',
                ], ';');
            }
            fclose($handle);
        }, 'dluhy-' . now()->format('Y-m-d') . '.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    // query for CSV export
    private function buildRawQuery()
    {
        return Debt::query()
            ->with(['user', 'creditor', 'order.items.commodity', 'lunch'])
            ->when($this->search, fn($q) => $q->whereHas(
                'user', fn($q) => $q->where('name', 'ilike', "%{$this->search}%")
            ))
            ->when($this->filterStatus === 'unpaid', fn($q) => $q->where('is_paid', false))
            ->when($this->filterStatus === 'paid', fn($q) => $q->where('is_paid', true))
            ->when($this->filterType === 'system', fn($q) => $q->whereNull('creditor_id'))
            ->when($this->filterType === 'personal', fn($q) => $q->whereNotNull('creditor_id'))
            ->latest();
    }

    private function buildGroups(): Collection
    {
        $debts = Debt::query()
            ->with(['user', 'creditor'])
            ->when($this->search, fn($q) => $q->whereHas(
                'user', fn($q) => $q->where('name', 'ilike', "%{$this->search}%")
            ))
            ->when($this->filterType === 'system',   fn($q) => $q->whereNull('creditor_id'))
            ->when($this->filterType === 'personal', fn($q) => $q->whereNotNull('creditor_id'))
            ->get();

        $groups = $debts
            ->groupBy(fn($d) => $d->user_id . '_' . ($d->creditor_id ?? 'null'))
            ->map(function ($group) {
                $unpaid = $group->where('is_paid', false);
                $paid = $group->where('is_paid', true);
                $first = $group->first();

                return (object) [
                    'user' => $first->user,
                    'user_id' => $first->user_id,
                    'creditor' => $first->creditor,
                    'creditor_id' => $first->creditor_id,
                    'unpaid_total' => $unpaid->sum('amount'),
                    'paid_total' => $paid->sum('amount'),
                    'unpaid_count' => $unpaid->count(),
                    'paid_count' => $paid->count(),
                    'total_count' => $group->count(),
                    'latest_at' => $group->max('created_at'),
                ];
            })
            ->values();

        // Apply status filter on groups
        if ($this->filterStatus === 'unpaid') {
            $groups = $groups->filter(fn($g) => $g->unpaid_count > 0)->values();
        } elseif ($this->filterStatus === 'paid') {
            $groups = $groups->filter(fn($g) => $g->paid_count > 0)->values();
        }

        return $groups->sortByDesc('latest_at')->values();
    }

    public function render(): View
    {
        $groups = $this->buildGroups();

        $stats = [
            'totalUnpaid' => Debt::where('is_paid', false)->sum('amount'),
            'totalPaid' => Debt::where('is_paid', true)->sum('amount'),
            'countDebtors' => Debt::where('is_paid', false)->distinct('user_id')->count('user_id'),
            'countAll' => Debt::count(),
        ];

        // individual debts for the selected group
        $detailDebts = null;
        $detailUser = null;
        $detailCred = null;

        if ($this->hasDetailOpen && $this->detailUserId !== null) {
            $q = Debt::where('user_id', $this->detailUserId)
                ->with(['order.items.commodity', 'lunch']);

            $this->detailCreditorId === null
                ? $q->whereNull('creditor_id')
                : $q->where('creditor_id', $this->detailCreditorId);

            $detailDebts = $q->latest()->get();
            $detailUser = User::find($this->detailUserId);
            $detailCred = $this->detailCreditorId ? User::find($this->detailCreditorId) : null;
        }

        $users = User::orderBy('name')->get();

        return view('livewire.admin.accounts.accounts-manager', [
            'groups' => $groups,
            'stats' => $stats,
            'users' => $users,
            'detailDebts' => $detailDebts,
            'detailUser' => $detailUser,
            'detailCred' => $detailCred,
        ]);
    }
}
