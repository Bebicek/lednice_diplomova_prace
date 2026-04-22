<?php

namespace App\Livewire\Admin\Users;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Rule;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;

#[Layout('layouts.app')]
class UsersManager extends Component
{
    use WithPagination;

    public int $perPage = 7;

    #[Rule('required|min:2|max:255')]
    public string $name = '';

    public array $selectedroles = [];

    #[Rule('required|email')]
    public string $email = '';

    #[Rule('nullable|min:6')]
    public string $password = '';

    #[Rule('nullable|max:255')]
    public ?string $bank_number = '';

    #[Rule('nullable|max:255')]
    public ?string $bank_code = '';

    public bool $enabled = true;

    public ?int $editingId = null;

    public ?int $confirmingDeleteId = null;

    public bool $showModal = false;

    public string $search = '';

    public string $filterRole = '';

    public string $sortBy = 'name';

    public string $sortDirection = 'asc';

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

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterRole(): void
    {
        $this->resetPage();
    }

    public function toggleActive(int $id): void
    {
        $user = User::findOrFail($id);
        $user->update(['enabled' => !$user->enabled]);
        $this->dispatch('toast-success', message: 'Stav uživatele byl změněn.');
    }

    public function openModal(?int $id = null): void
    {
        if ($id) {
            $user = User::findOrFail($id);
            $this->editingId = $user->id;
            $this->name = $user->name;
            $this->selectedroles = $user->getRoleNames()->toArray();
            $this->email = $user->email;
            $this->password = '';
            $this->bank_number = $user->bank_number;
            $this->bank_code = $user->bank_code;
            $this->enabled = $user->enabled;
        } else {
            $this->resetForm();
        }

        $this->showModal = true;
        $this->resetValidation();
    }

    public function resetForm(): void
    {
        $this->editingId = null;
        $this->name = '';
        $this->email = '';
        $this->password = '';
        $this->bank_number = '';
        $this->bank_code = '';
        $this->enabled = true;
        $this->selectedroles = [];

        $this->resetValidation();
    }

    public function save(): void
    {
        $rules = [
            'email' => 'required|email|unique:users,email' .
                ($this->editingId ? ',' . $this->editingId : ''),
        ];

        if (!$this->editingId) {
            $rules['password'] = 'required|min:6';
        }

        $this->validate($rules);

        $data = [
            'name' => $this->name,
            'email' => $this->email,
            'bank_number' => $this->bank_number,
            'bank_code' => $this->bank_code,
            'enabled' => $this->enabled,
        ];

        if ($this->password) {
            $data['password'] = Hash::make($this->password);
        }

        if ($this->editingId) {
            $user = User::findOrFail($this->editingId);
            $user->update($data);
            $this->dispatch('toast-success', message: 'Uživatel byl úspěšně aktualizován.');
        } else {
            $user = User::create($data);
            $this->dispatch('toast-success', message: 'Uživatel byl úspěšně vytvořen.');
        }

        $user->syncRoles($this->selectedroles);
        $this->closeModal();
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
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
        $user = User::findOrFail($id);

        if ($user->debts()->where('is_paid', false)->count() > 0) {
            $this->dispatch('toast-error', message: 'Nelze smazat uživatele s neuhrazenými dluhy.');
            $this->confirmingDeleteId = null;
            return;
        }

        $user->delete();
        $this->confirmingDeleteId = null;
        $this->dispatch('toast-success', message: 'Uživatel byl úspěšně smazán.');
    }

    public function render(): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\View\View
    {
        $users = User::query()
            ->when($this->search, fn($q) => $q->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('email', 'like', '%' . $this->search . '%');
            }))
            ->when($this->filterRole, fn($q) => $q->whereHas('roles', fn($q) => $q->where('name', $this->filterRole)))
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.admin.users.users-manager', [
            'users' => $users,
            'availableRoles' => Role::orderBy('name')->get(),
        ]);
    }
}
