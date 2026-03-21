<?php

namespace App\Livewire\Admin\Categories;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Rule;
use Livewire\Component;
use App\Models\Category;
use Livewire\WithPagination;


#[Layout('layouts.app')]
class CategoryManager extends Component
{
    use WithPagination;

    // Search, filter, sort
    public string $search = '';
    public string $sortBy = 'name';
    public string $sortDirection = 'asc';
    public int $perPage = 7;

    #[Rule('required|min:3|max:255')]
    public string $name = '';

    public ?int $editingId = null;
    public ?int $confirmingDeleteId = null;
    public bool $showModal = false;

    protected $queryString = [
        'search' => ['except' => ''],
        'sortBy' => ['except' => 'name'],
        'sortDirection' => ['except' => 'asc'],
    ];

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
        $this->reset(['search', 'sortBy', 'sortDirection']);
        $this->resetPage();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function openModal(?int $id = null): void
    {
        if ($id) {
            $category = Category::findOrFail($id);
            $this->editingId = $category->id;
            $this->name = $category->name;
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
            'name' => $this->name,
        ];

        if ($this->editingId) {
            $category = Category::findOrFail($this->editingId);
            $category->update($data);
            $this->dispatch('toast-success', message: 'Kategorie byla úspěšně aktualizována.');
        } else {
            Category::create($data);
            $this->dispatch('toast-success', message: 'Kategorie byla úspěšně vytvořena.');
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
        $category = Category::findOrFail($id);

        if ($category->commodities()->count() > 0) {
            $this->dispatch('toast-error', message: 'Nelze smazat kategorii, která obsahuje produkty.');
            $this->confirmingDeleteId = null;
            return;
        }

        $category->delete();
        $this->confirmingDeleteId = null;

        $this->dispatch('toast-success', message: 'Kategorie byla úspěšně smazána.');
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->name = '';
        $this->resetValidation();
    }

    public function render()
    {
        $allowedSorts = ['name', 'sort_order', 'created_at'];
        $sortColumn = in_array($this->sortBy, $allowedSorts) ? $this->sortBy : 'name';

        $categories = Category::query()
            ->when($this->search, fn($q) => $q->where('name', 'ilike', "%{$this->search}%"))
            ->orderBy($sortColumn, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.admin.categories.category-manager', [
            'categories' => $categories,
        ]);
    }
}
