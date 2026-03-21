<?php

namespace App\Livewire\Admin\Commodities;

use App\Models\Commodity;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Rule;
use Livewire\Component;
use App\Models\Category;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class CommodityManager extends Component
{
    use WithFileUploads, WithPagination;

    // Search, filter, sort
    public string $search = '';
    public ?int $filterCategoryId = null;
    public string $sortBy = 'name';
    public string $sortDirection = 'asc';
    public int $perPage = 7;

    // Form fields
    #[Rule('required|min:3|max:255')]
    public string $name = '';

    #[Rule('required|numeric|min:0')]
    public string $price = '0.00';

    #[Rule('nullable|max:255')]
    public ?string $description = '';

    #[Rule('required|exists:categories,id')]
    public ?int $category_id = null;

    #[Rule('nullable|max:255')]
    public string $barcode = '';

    #[Rule('nullable|date')]
    public ?string $expiresAt = null;

    #[Rule('nullable|image|max:2048')]
    public $image = null;

    public ?string $existingImage = null;

    public bool $is_active = true;

    public ?int $editingId = null;

    public ?int $confirmingDeleteId = null;

    public bool $showModal = false;

    public array $selected = [];

    protected $queryString = [
        'search' => ['except' => ''],
        'filterCategoryId' => ['except' => null],
        'sortBy' => ['except' => 'name'],
        'sortDirection' => ['except' => 'asc'],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilterCategoryId()
    {
        $this->resetPage();
    }

    public function sort(string $column): void
    {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'filterCategoryId', 'sortBy', 'sortDirection']);
        $this->resetPage();
    }

    public function openModal(?int $id = null): void
    {
        if ($id) {
            $commodity = Commodity::findOrFail($id);
            $this->editingId = $commodity->id;
            $this->name = $commodity->name;
            $this->price = number_format($commodity->price / 100, 2, '.', '');
            $this->description = $commodity->description ?? '';
            $this->category_id = $commodity->category_id;
            $this->barcode = $commodity->barcode ?? '';
            $this->expiresAt = $commodity->expires_at?->format('Y-m-d');
            $this->is_active = $commodity->is_active;
            $this->existingImage = $commodity->image_path;
        } else {
            $this->resetForm();
        }

        $this->showModal = true;
        $this->resetValidation();
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'name'       => $this->name,
            'price'      => (int) round((float) str_replace(',', '.', $this->price) * 100),
            'description'=> $this->description ?: null,
            'barcode'    => $this->barcode ?: null,
            'expires_at' => $this->expiresAt ?: null,
            'category_id'=> $this->category_id,
            'is_active'  => $this->is_active,
        ];

        // Handle image upload
        if ($this->image) {
            $data['image_path'] = $this->image->store('commodities', 'public');
        }

        if ($this->editingId) {
            $commodity = Commodity::findOrFail($this->editingId);
            $commodity->update($data);
            $this->dispatch('toast-success', message: 'Produkt byl úspěšně aktualizován.');
        } else {
            Commodity::create($data);
            $this->dispatch('toast-success', message: 'Produkt byl úspěšně vytvořen.');
        }

        $this->closeModal();
    }

    public function confirmDelete(int $id): void
    {
        $this->confirmingDeleteId = $id;
    }

    public function cancelDelete(): void
    {
        $this->confirmingDeleteId = null;
    }

    public function delete(int $id): void
    {
        $commodity = Commodity::findOrFail($id);

        if ($commodity->orderItems()->count() > 0) {
            $this->dispatch('toast-error', message: 'Nelze smazat produkt, který je využíván v nějakém objednávce.');
            $this->confirmingDeleteId = null;
            return;
        }

        $commodity->delete();
        $this->confirmingDeleteId = null;

        $this->dispatch('toast-success', message: 'Produkt byl úspěšně smazán.');
    }

    private function resetForm()
    {
        $this->editingId = null;
        $this->name = '';
        $this->price = '0.00';
        $this->description = '';
        $this->barcode = '';
        $this->expiresAt = null;
        $this->image = null;
        $this->existingImage = null;
        $this->is_active = true;
    }

    public function removeImage(): void
    {
        $this->image = null;
        $this->existingImage = null;
    }

    public function toggleActive(int $id): void
    {
        $commodity = Commodity::findOrFail($id);
        $commodity->update(['is_active' => !$commodity->is_active]);
    }

    public function bulkDelete(): void
    {
        if (empty($this->selected)){
            return;
        }

        $count = count($this->selected);
        Commodity::whereIn('id', $this->selected)->delete();
        $this->selected = [];

        session()->flash('success', "Smazáno {$count} produtků");
    }

    public function render()
    {
        $allowedSorts = ['name', 'price', 'category_id', 'created_at', 'is_active'];
        $sortColumn = in_array($this->sortBy, $allowedSorts) ? $this->sortBy : 'name';

        $commodities = Commodity::query()
            ->with('category')
            ->when($this->search, fn($q) => $q->where('name', 'ilike', "%{$this->search}%"))
            ->when($this->filterCategoryId, fn($q) => $q->where('category_id', $this->filterCategoryId))
            ->orderBy($sortColumn, $this->sortDirection)
            ->paginate($this->perPage);

        $categories = Category::orderBy('name')->get();

        return view('livewire.admin.commodities.commodity-manager', [
            'commodities' => $commodities,
            'categories' => $categories,
        ]);
    }
}
