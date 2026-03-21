<?php

namespace App\Livewire\Admin\Stock;

use App\Models\Category;
use App\Models\Commodity;
use App\Models\Stock;
use App\Models\StockMovement;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class StockManager extends Component
{
    use WithPagination;

    // Search, filter, sort for operations
    public string $search = '';
    public string $sortBy = 'name';
    public string $sortDirection = 'asc';
    public ?int $filterCategoryId = null;
    public string $filterStatus = ''; // '' | 'in_warehouse' | 'in_fridge' | 'low' | 'empty'

    // Search, filter, sort for history
    public string $historySearch = '';
    public string $historyFilterType = '';

    public bool $showOperationModal = false; // true = opened from the "New movement" button
    public bool $isGlobalOperation = false;

    public string $operationType = ''; // '' | receipt | transfer | loss
    public ?int $operationCommodityId = null;
    public string $operationCommodityName = '';
    public int $operationQuantity = 1;
    public string $operationLocation = 'warehouse';
    public int $operationMaxQuantity = 9999;
    public int $operationWarehouseMax = 0;
    public int $operationFridgeMax = 0;

    // Lifycycle hooks for reset pagination
    public function updatingSearch(): void
    {
        $this->resetPage();
    }
    public function updatingFilterByCategoryId(): void
    {
        $this->resetPage();
    }
    public function updatingFilterStatus(): void
    {
        $this->resetPage();
    }

    public function updatingHistorySearch(): void
    {
        $this->resetPage('history');
    }
    public function updatingHistoryFilterType(): void
    {
        $this->resetPage('history');
    }

    // Sorting
    public function sort(string $column): void {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }
    }

    public function resetFilters():void {
        $this->reset(['search', 'filterCategoryId', 'filterStatus']);
        $this->resetPage();
    }

    /** Opens the modal for a specific product and type (from a table row). */
    public function openNewOperationModal(): void {
        $this->isGlobalOperation = true;
        $this->operationCommodityId = null;
        $this->operationCommodityName = '';
        $this->operationType = '';
        $this->operationQuantity = 1;
        $this->operationLocation = 'warehouse';
        $this->operationWarehouseMax = 0;
        $this->operationFridgeMax  = 0;
        $this->operationMaxQuantity  = 9999;

        $this->showOperationModal = true;
        $this->resetValidation();
    }

    public function openOperationModal(?int $commodityId, string $type): void {

        $commodity = Commodity::with('stock')->findOrFail($commodityId);

        $this->isGlobalOperation = false;
        $this->operationCommodityId = $commodityId;
        $this->operationCommodityName = $commodity->name;
        $this->operationType = $type;
        $this->operationQuantity = 1;
        $this->operationLocation = 'warehouse';
        $this->operationWarehouseMax = $commodity->warehouse_quantity;
        $this->operationFridgeMax  = $commodity->fridge_quantity;
        $this->operationMaxQuantity  = $this->resolveMax();

        $this->showOperationModal = true;
        $this->resetValidation();
    }

    /** Called by Livewire when the selected product changes (in the "New movement" modal). */
    public function updatedOperationCommodityId(): void
    {
        if (!$this->operationCommodityId) {
            $this->operationCommodityName = '';
            $this->operationWarehouseMax  = 0;
            $this->operationFridgeMax     = 0;
            $this->operationMaxQuantity   = 9999;
            return;
        }

        $commodity = Commodity::with('stock')->findOrFail($this->operationCommodityId);
        $this->operationCommodityName = $commodity->name;
        $this->operationWarehouseMax  = $commodity->warehouse_quantity;
        $this->operationFridgeMax     = $commodity->fridge_quantity;
        $this->operationQuantity      = 1;
        $this->operationMaxQuantity   = $this->resolveMax();
    }

    public function updatedOperationType(): void
    {
        $this->operationQuantity    = 1;
        $this->operationLocation    = 'warehouse';
        $this->operationMaxQuantity = $this->resolveMax();
    }

    /** Called by Livewire when the location changes (for the loss type). */
    public function updatedOperationLocation(): void
    {
        if ($this->operationType !== 'loss') {
            return;
        }

        $this->operationMaxQuantity = $this->operationLocation === 'warehouse'
            ? $this->operationWarehouseMax
            : $this->operationFridgeMax;

        if ($this->operationQuantity > $this->operationMaxQuantity) {
            $this->operationQuantity = max(1, $this->operationMaxQuantity);
        }
    }

    public function closeModal(): void
    {
        $this->showOperationModal = false;
        $this->isGlobalOperation = false;
        $this->operationCommodityId = null;
        $this->operationCommodityName = '';
        $this->operationType = '';
        $this->operationQuantity = 1;
        $this->operationLocation = 'warehouse';
        $this->operationMaxQuantity = 9999;
        $this->operationWarehouseMax = 0;
        $this->operationFridgeMax = 0;
        $this->resetValidation();
    }

    public function executeOperation(): void {
        $rules = [];

        if ($this->isGlobalOperation) {
            $rules['operationCommodityId'] = ['required', 'exists:commodities,id'];
            $rules['operationType'] = ['required', 'in:receipt,transfer,loss'];
        }

        $rules['operationQuantity'] = ['required', 'integer', 'min:1', 'max:' . $this->operationMaxQuantity];

        $this->validate($rules, [
            'operationCommodityId.required' => 'Vyberte produkt.',
            'operationCommodityId.exists' => 'Vybraný produkt neexistuje.',
            'operationType.required' => 'Vyberte typ pohybu.',
            'operationType.in' => 'Neplatný typ pohybu.',
            'operationQuantity.required' => 'Množství je povinné.',
            'operationQuantity.integer' => 'Množství musí být celé číslo.',
            'operationQuantity.min' => 'Množství musí být alespoň 1 ks.',
            'operationQuantity.max' => 'Nedostačující stav. Maximum: ' . $this->operationMaxQuantity . ' ks.',
        ]);

        try {
            DB::transaction(function () {
                match ($this->operationType) {
                    'receipt' => $this->processReceipt(),
                    'transfer' => $this->processTransfer(),
                    'loss' => $this->processLoss(),
                    default => throw new \InvalidArgumentException('Neznámý typ operace.'),
                };
            });

            $messages = [
                'receipt' => 'Příjem ' . $this->operationQuantity . ' ks byl úspěšně zaznamenán.',
                'transfer' => 'Přesun ' . $this->operationQuantity . ' ks do lednice byl úspěšně proveden.',
                'loss' => 'Ztráta ' . $this->operationQuantity . ' ks byla úspěšně zaznamenána.',
            ];

            $this->dispatch('toast-success', message: $messages[$this->operationType]);
            $this->closeModal();

        } catch (\Exception $e) {
            $this->dispatch('toast-error', message: 'Operaci se nepodařilo provést. Zkuste to znovu.');
        }
    }

    // Private stock operations
    private function resolveMax(): int
    {
        return match ($this->operationType) {
            'transfer' => $this->operationWarehouseMax,
            'loss'     => $this->operationLocation === 'warehouse'
                ? $this->operationWarehouseMax
                : $this->operationFridgeMax,
            default => 9999,
        };
    }

    private function processReceipt(): void
    {
        Stock::firstOrCreate(
            ['commodity_id' => $this->operationCommodityId, 'location' => 'warehouse'],
            ['quantity' => 0]
        )->increment('quantity', $this->operationQuantity);

        StockMovement::create([
            'commodity_id' => $this->operationCommodityId,
            'user_id' => auth()->id(),
            'type' => 'receipt',
            'quantity' => $this->operationQuantity,
            'note' => null,
        ]);
    }

    private function processTransfer(): void
    {
        $warehouseStock = Stock::where('commodity_id', $this->operationCommodityId)
            ->where('location', 'warehouse')
            ->lockForUpdate()
            ->first();

        if (!$warehouseStock || $warehouseStock->quantity < $this->operationQuantity) {
            $this->dispatch('toast-error', message: 'Nedostatečný stav ve skladu.');
            return;
        }

        $warehouseStock->decrement('quantity', $this->operationQuantity);

        Stock::firstOrCreate(
            ['commodity_id' => $this->operationCommodityId, 'location' => 'fridge'],
            ['quantity' => 0]
        )->increment('quantity', $this->operationQuantity);

        StockMovement::create([
            'commodity_id' => $this->operationCommodityId,
            'user_id' => auth()->id(),
            'type' => 'transfer',
            'quantity' => $this->operationQuantity,
            'note' => null,
        ]);
    }

    private function processLoss(): void
    {
        $stock = Stock::where('commodity_id', $this->operationCommodityId)
            ->where('location', $this->operationLocation)
            ->lockForUpdate()
            ->first();

        if (!$stock || $stock->quantity < $this->operationQuantity) {
            $this->dispatch('toast-error', message: 'Nedostatečný stav na zvolené lokaci.');
            return;
        }

        $stock->decrement('quantity', $this->operationQuantity);

        StockMovement::create([
            'commodity_id' => $this->operationCommodityId,
            'user_id' => auth()->id(),
            'type' => 'loss',
            'quantity' => $this->operationQuantity,
            'note' => 'Lokace: ' . ($this->operationLocation === 'warehouse' ? 'Sklad' : 'Lednice'),
        ]);
    }

    // Render
    public function render(): View
    {
        $allowedSorts = ['name', 'category_id'];
        $sortColumn   = in_array($this->sortBy, $allowedSorts) ? $this->sortBy : 'name';

        $commodities = Commodity::query()
            ->with(['category', 'stock'])
            ->when($this->search, fn($q) => $q->where('name', 'ilike', "%{$this->search}%"))
            ->when($this->filterCategoryId, fn($q) => $q->where('category_id', $this->filterCategoryId))
            ->orderBy($sortColumn, $this->sortDirection)
            ->get();

        // We apply a condition filter to the collection (the stock is eager-loaded, without N+1)
        if ($this->filterStatus) {
            $commodities = match ($this->filterStatus) {
                'in_warehouse' => $commodities->filter(fn($c) => $c->warehouse_quantity > 0),
                'in_fridge' => $commodities->filter(fn($c) => $c->fridge_quantity > 0),
                'low' => $commodities->filter(fn($c) => ($c->warehouse_quantity + $c->fridge_quantity) > 0
                    && ($c->warehouse_quantity + $c->fridge_quantity) <= 3),
                'empty' => $commodities->filter(fn($c) => ($c->warehouse_quantity + $c->fridge_quantity) === 0),
                default => $commodities,
            };
        }

        $movements = StockMovement::query()
            ->with(['commodity', 'user'])
            ->when($this->historySearch, fn($q) => $q->whereHas(
                'commodity',
                fn($q) => $q->where('name', 'ilike', "%{$this->historySearch}%")
            ))
            ->when($this->historyFilterType, fn($q) => $q->where('type', $this->historyFilterType))
            ->orderBy('created_at', 'desc')
            ->paginate(15, pageName: 'history');

        return view('livewire.admin.stock.stock-manager', [
            'commodities' => $commodities,
            'allCommodities' => Commodity::where('is_active', true)->orderBy('name')->get(),
            'categories' => Category::orderBy('name')->get(),
            'movements' => $movements,
        ]);
    }
}
