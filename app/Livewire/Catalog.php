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
    public string $sortBy = 'popular';
    public ?int $selectedCommodityId = null;
    public float $priceMin = 0;
    public float $priceMax = 100;
    public bool $onlyInStock = false;
    public string $viewMode = 'grid';

    protected $queryString = ['search', 'categoryId', 'sortBy', 'onlyInStock'];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingCategoryId(): void
    {
        $this->resetPage();
    }

    public function updatingOnlyInStock(): void
    {
        $this->resetPage();
    }

    public function updatingSortBy(): void
    {
        $this->resetPage();
    }

    public function setCategory(?int $id): void
    {
        $this->categoryId = $this->categoryId === $id ? null : $id;
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
            $this->dispatch('toast-error', message: 'Produkt není aktuálně dostupný v lednici.');
            return;
        }

        $cart = session()->get('cart', []);

        $cartCurrentQuantity = $cart[$commodityId]['quantity'] ?? 0;

        // Check if the user can add the commodity to the cart
        if ($cartCurrentQuantity + 1 > $commodity->fridge_quantity) {
            $this->dispatch('toast-error', message: 'V lednici už není dostatek kusů');
            return;
        }

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
        $query = Commodity::query()
            ->where('is_active', true)
            ->with(['category', 'stock'])
            ->when($this->search, fn($q) => $q->where('name', 'ilike', "%{$this->search}%"))
            ->when($this->categoryId, fn($q) => $q->where('category_id', $this->categoryId));

        if ($this->onlyInStock) {
            $query->whereHas('stock', function ($q) {
                $q->where('location', 'fridge')->where('quantity', '>', 0);
            });
        }

        // Sorting
        match ($this->sortBy) {
            'price_asc' => $query->orderBy('price', 'asc'),
            'price_desc' => $query->orderBy('price', 'desc'),
            'name' => $query->orderBy('name', 'asc'),
            default => $query->orderBy('name', 'asc'), // popular = default
        };

        $commodities = $query->paginate(9);

        // Categories with commodity counts
        $categories = Category::withCount(['commodities' => function ($q) {
            $q->where('is_active', true);
        }])->orderBy('name')->get();

        $selectedCommodity = $this->selectedCommodityId
            ? Commodity::with(['category', 'stock'])->find($this->selectedCommodityId)
            : null;


        $cart = session()->get('cart', []);

        return view('livewire.catalog', [
            'commodities' => $commodities,
            'categories' => $categories,
            'selectedCommodity' => $selectedCommodity,
            'cart' => $cart,
        ]);
    }
}
