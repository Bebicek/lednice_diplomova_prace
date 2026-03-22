<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.client')]
class UserProfile extends Component
{
    public string $name = '';
    public string $email = '';
    public string $bank_number = '';
    public string $bank_code = '';
    public string $current_password = '';
    public string $new_password = '';
    public string $new_password_confirmation = '';

    public function mount(): void
    {
        $user = auth()->user();
        $this->name = $user->name;
        $this->email = $user->email;
        $this->bank_number = $user->bank_number ?? '';
        $this->bank_code = $user->bank_code ?? '';
    }

    public function saveProfile(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255',
                Rule::unique('users')->ignore(auth()->id())],
        ]);

        auth()->user()->update([
            'name' => $this->name,
            'email' => $this->email,
        ]);

        session()->flash('profile_success', 'Profil byl uložen.');
    }

    public function saveBankDetails(): void
    {
        $this->validate([
            'bank_number' => ['nullable', 'string', 'max:50', 'regex:/^\d{1,6}-?\d{1,10}$/'],
            'bank_code' => ['nullable', 'string', 'size:4', 'regex:/^\d{4}$/'],
        ], [
            'bank_number.regex' => 'Číslo účtu musí být ve formátu 123456-1234567890 nebo 1234567890.',
            'bank_code.size' => 'Kód banky musí mít přesně 4 číslice.',
            'bank_code.regex' => 'Kód banky musí obsahovat pouze číslice.',
        ]);

        auth()->user()->update([
            'bank_number' => $this->bank_number ?: null,
            'bank_code' => $this->bank_code ?: null,
        ]);

        session()->flash('bank_success', 'Bankovní údaje byly uloženy.');
    }

    public function changePassword(): void
    {
        $this->validate([
            'current_password' => ['required'],
            'new_password' => ['required', 'confirmed', Password::min(8)],
        ]);

        if (! Hash::check($this->current_password, auth()->user()->password)) {
            $this->addError('current_password', 'Stávající heslo není správné.');
            return;
        }

        auth()->user()->update([
            'password' => Hash::make($this->new_password),
        ]);

        $this->reset(['current_password', 'new_password', 'new_password_confirmation']);
        session()->flash('password_success', 'Heslo bylo úspěšně změněno.');
    }

    public function render()
    {
        return view('livewire.user-profile');
    }
}
