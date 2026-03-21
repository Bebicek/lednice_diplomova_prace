<?php

namespace App\Livewire;

use App\Models\Category;
use App\Models\Commodity;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.client')]
class Catalog extends Component
{
    use WithPagination;

    public string $search = '';
    public ?int $categoryId = null;
    public string $sortBy = 'name';
    public ?int $selectedCommodityId = null;

    protected $queryString = ['search', 'categoryId', 'sortBy'];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingCategoryId(): void
    {
        $this->resetPage();
    }

    public function openDetail(int $id): void
    {
        $this->selectedCommodityId = $id;
    }

    public function closeDetail(): void
    {
        $this->selectedCommodityId = null;
    }

    public function addToCart(int $commodityId): void
    {
        $commodity = Commodity::with('stock')->find($commodityId);

        if (! $commodity || $commodity->fridge_quantity <= 0) {
            session()->flash('error', 'Produkt není aktuálně dostupný v lednici.');
            return;
        }

        $cart = session()->get('cart', []);

        if (isset($cart[$commodityId])) {
            $cart[$commodityId]['quantity']++;
        } else {
            $cart[$commodityId] = [
                'id' => $commodity->id,
                'name' => $commodity->name,
                'price' => $commodity->price,
                'quantity' => 1,
            ];
        }

        session()->put('cart', $cart);
        $this->dispatch('cart-updated');
        session()->flash('success', 'Přidáno do košíku!');
    }

    public function render()
    {
        $commodities = Commodity::query()
            ->where('is_active', true)
            ->with(['category', 'stock'])
            ->when($this->search, fn($q) => $q->where('name', 'ilike', "%{$this->search}%"))
            ->when($this->categoryId, fn($q) => $q->where('category_id', $this->categoryId))
            ->orderBy($this->sortBy)
            ->paginate(12);

        $categories = Category::orderBy('name')->get();

        $selectedCommodity = $this->selectedCommodityId
            ? Commodity::with(['category', 'stock'])->find($this->selectedCommodityId)
            : null;

        return view('livewire.catalog', [
            'commodities' => $commodities,
            'categories' => $categories,
            'selectedCommodity' => $selectedCommodity,
        ]);
    }
}
