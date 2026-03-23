<div>
    <x-common.page-breadcrumb :pageTitle="'Správa uživatelů'" />


    @if(session('success'))
        <div class="mb-4 rounded-lg bg-green-50 p-3 text-sm text-green-700 dark:bg-green-500/10 dark:text-green-400">
            {{ session('success') }}
        </div>
    @endif

    <div class="rounded-2xl border border-gray-200 bg-white pt-4 dark:border-gray-800 dark:bg-white/[0.03]">
        <!-- Header: Title + Actions -->
        <div class="flex flex-col gap-2 px-5 mb-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
            <div>
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Uživatelé</h3>
                <p class="mt-1 text-theme-sm text-gray-500 dark:text-gray-400">Správa uživatelů v kancelářské ledničce</p>
            </div>
            <div class="flex items-center gap-3">
                <!-- Add user -->
                <button wire:click="openModal()"
                        class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs hover:bg-brand-600">
                    <x-heroicon-o-plus class="h-4 w-4" />
                    Přidat uživatele
                </button>
            </div>
        </div>

        <!-- Search + Filter bar -->
        <div class="flex flex-col gap-3 px-5 mb-4 sm:flex-row sm:items-center sm:px-6">
            <!-- Search -->
            <div class="relative flex-1">
                <span class="absolute -translate-y-1/2 left-4 top-1/2 pointer-events-none">
                    <x-heroicon-o-magnifying-glass class="w-5 h-5 fill-gray-500 dark:fill-gray-400" />
                </span>
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Hledat uživatele..."
                       class="h-[42px] w-full rounded-lg border border-gray-300 bg-transparent py-2.5 pl-[42px] pr-4 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-none focus:ring-2 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800 xl:w-[300px]" />
            </div>
            <!-- Filter -->
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open" type="button"
                        class="inline-flex items-center gap-2 rounded-lg border px-4 py-2.5 text-theme-sm font-medium shadow-theme-xs
                        {{ $filterRole ? 'border-brand-300 bg-brand-50 text-brand-700 dark:border-brand-700 dark:bg-brand-500/10 dark:text-brand-400' : 'border-gray-300 bg-white text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03]' }}">
                    <x-heroicon-o-funnel class="h-4 w-4" />
                    Filtr{{ $filterRole ? ': ' . ($filterRole === 'admin' ? 'Admin' : 'Zaměstnanec') : '' }}
                </button>
                <div x-show="open" @click.away="open = false" x-cloak x-transition
                     class="absolute right-0 z-10 mt-1 w-44 rounded-xl border border-gray-200 bg-white shadow-lg dark:border-gray-700 dark:bg-gray-900">
                    <div class="p-2 space-y-1">
                        <button wire:click="$set('filterRole', '')" @click="open = false"
                                class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-theme-xs font-medium text-left hover:bg-gray-100 dark:hover:bg-white/5 {{ $filterRole === '' ? 'text-brand-600 dark:text-brand-400' : 'text-gray-700 dark:text-gray-300' }}">
                            Všechny role
                        </button>
                        <button wire:click="$set('filterRole', 'admin')" @click="open = false"
                                class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-theme-xs font-medium text-left hover:bg-gray-100 dark:hover:bg-white/5 {{ $filterRole === 'admin' ? 'text-brand-600 dark:text-brand-400' : 'text-gray-700 dark:text-gray-300' }}">
                            <span class="inline-flex rounded-full px-2 py-0.5 text-theme-xs font-medium bg-orange-50 text-orange-700 dark:bg-orange-500/15 dark:text-orange-400">Admin</span>
                        </button>
                        <button wire:click="$set('filterRole', 'employee')" @click="open = false"
                                class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-theme-xs font-medium text-left hover:bg-gray-100 dark:hover:bg-white/5 {{ $filterRole === 'employee' ? 'text-brand-600 dark:text-brand-400' : 'text-gray-700 dark:text-gray-300' }}">
                            <span class="inline-flex rounded-full px-2 py-0.5 text-theme-xs font-medium bg-blue-50 text-blue-700 dark:bg-blue-500/15 dark:text-blue-400">Zaměstnanec</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="overflow-hidden">
            <div class="max-w-full overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                    <tr class="border-gray-200 border-y dark:border-gray-700">
                        <th scope="col" class="px-6 py-3 font-medium text-gray-500 text-theme-xs dark:text-gray-400 text-start">
                            <button wire:click="sort('name')" class="inline-flex items-center gap-1.5 hover:text-gray-700 dark:hover:text-gray-200">
                                Jméno
                                @if($sortBy === 'name')
                                    <svg class="fill-current" width="12" height="12" viewBox="0 0 12 12">
                                        @if($sortDirection === 'asc') <path d="M6 2L10 8H2L6 2Z"/> @else <path d="M6 10L2 4H10L6 10Z"/> @endif
                                    </svg>
                                @else
                                    <svg class="fill-gray-400 dark:fill-gray-600" width="12" height="12" viewBox="0 0 12 12"><path d="M6 2L9 5.5H3L6 2Z"/><path d="M6 10L3 6.5H9L6 10Z"/></svg>
                                @endif
                            </button>
                        </th>
                        <th scope="col" class="px-6 py-3 font-medium text-gray-500 text-theme-xs dark:text-gray-400 text-start">
                            <button wire:click="sort('email')" class="inline-flex items-center gap-1.5 hover:text-gray-700 dark:hover:text-gray-200">
                                Email
                                @if($sortBy === 'email')
                                    <svg class="fill-current" width="12" height="12" viewBox="0 0 12 12">
                                        @if($sortDirection === 'asc') <path d="M6 2L10 8H2L6 2Z"/> @else <path d="M6 10L2 4H10L6 10Z"/> @endif
                                    </svg>
                                @else
                                    <svg class="fill-gray-400 dark:fill-gray-600" width="12" height="12" viewBox="0 0 12 12"><path d="M6 2L9 5.5H3L6 2Z"/><path d="M6 10L3 6.5H9L6 10Z"/></svg>
                                @endif
                            </button>
                        </th>
                        <th scope="col" class="px-6 py-3 font-medium text-gray-500 text-theme-xs dark:text-gray-400 text-start">
                            Role
                        </th>
                        <th scope="col" class="px-6 py-3 font-medium text-gray-500 text-theme-xs dark:text-gray-400 text-start">
                            <button wire:click="sort('bank_number')" class="inline-flex items-center gap-1.5 hover:text-gray-700 dark:hover:text-gray-200">
                                Číslo účtu
                                @if($sortBy === 'bank_number')
                                    <svg class="fill-current" width="12" height="12" viewBox="0 0 12 12">
                                        @if($sortDirection === 'asc') <path d="M6 2L10 8H2L6 2Z"/> @else <path d="M6 10L2 4H10L6 10Z"/> @endif
                                    </svg>
                                @else
                                    <svg class="fill-gray-400 dark:fill-gray-600" width="12" height="12" viewBox="0 0 12 12"><path d="M6 2L9 5.5H3L6 2Z"/><path d="M6 10L3 6.5H9L6 10Z"/></svg>
                                @endif
                            </button>
                        </th>
                        <th scope="col" class="px-6 py-3 font-medium text-gray-500 text-theme-xs dark:text-gray-400 text-start">
                            <button wire:click="sort('bank_code')" class="inline-flex items-center gap-1.5 hover:text-gray-700 dark:hover:text-gray-200">
                                Číslo banky
                                @if($sortBy === 'bank_code')
                                    <svg class="fill-current" width="12" height="12" viewBox="0 0 12 12">
                                        @if($sortDirection === 'asc') <path d="M6 2L10 8H2L6 2Z"/> @else <path d="M6 10L2 4H10L6 10Z"/> @endif
                                    </svg>
                                @else
                                    <svg class="fill-gray-400 dark:fill-gray-600" width="12" height="12" viewBox="0 0 12 12"><path d="M6 2L9 5.5H3L6 2Z"/><path d="M6 10L3 6.5H9L6 10Z"/></svg>
                                @endif
                            </button>
                        </th>
                        <th scope="col" class="px-6 py-3 font-medium text-gray-500 text-theme-xs dark:text-gray-400 text-start">
                            <button wire:click="sort('enabled')" class="inline-flex items-center gap-1.5 hover:text-gray-700 dark:hover:text-gray-200">
                                Stav
                                @if($sortBy === 'enabled')
                                    <svg class="fill-current" width="12" height="12" viewBox="0 0 12 12">
                                        @if($sortDirection === 'asc') <path d="M6 2L10 8H2L6 2Z"/> @else <path d="M6 10L2 4H10L6 10Z"/> @endif
                                    </svg>
                                @else
                                    <svg class="fill-gray-400 dark:fill-gray-600" width="12" height="12" viewBox="0 0 12 12"><path d="M6 2L9 5.5H3L6 2Z"/><path d="M6 10L3 6.5H9L6 10Z"/></svg>
                                @endif
                            </button>
                        </th>
                        <th scope="col" class="px-6 py-3 font-medium text-gray-500 text-theme-xs dark:text-gray-400 text-start">
                            <button wire:click="sort('created_at')" class="inline-flex items-center gap-1.5 hover:text-gray-700 dark:hover:text-gray-200">
                                Přidáno
                                @if($sortBy === 'created_at')
                                    <svg class="fill-current" width="12" height="12" viewBox="0 0 12 12">
                                        @if($sortDirection === 'asc') <path d="M6 2L10 8H2L6 2Z"/> @else <path d="M6 10L2 4H10L6 10Z"/> @endif
                                    </svg>
                                @else
                                    <svg class="fill-gray-400 dark:fill-gray-600" width="12" height="12" viewBox="0 0 12 12"><path d="M6 2L9 5.5H3L6 2Z"/><path d="M6 10L3 6.5H9L6 10Z"/></svg>
                                @endif
                            </button>
                        </th>
                        <th scope="col" class="px-6 py-3 font-medium text-gray-500 text-theme-xs dark:text-gray-400 text-start">
                            <span class="sr-only">Akce</span>
                        </th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($users as $user)
                        <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02]">
                            <td class="px-4 sm:px-6 py-3.5 whitespace-nowrap">
                                <span class="text-theme-sm text-gray-500 dark:text-gray-400">{{ $user->name }}</span>
                            </td>

                            <td class="px-4 sm:px-6 py-3.5 whitespace-nowrap">
                                <span class="text-theme-sm font-medium text-gray-800 dark:text-white/90">{{ $user->email }}</span>
                            </td>

                            <td class="px-4 sm:px-6 py-3.5 whitespace-nowrap">
                                @forelse($user->getRoleNames() as $role)
                                    <span class="inline-flex rounded-full px-2 py-0.5 text-theme-xs font-medium
                                            {{ $role === 'admin'
                                                ? 'bg-brand-50 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400'
                                                : 'bg-gray-100 text-gray-600 dark:bg-gray-700/50 dark:text-gray-400' }}">
                                            {{ $role === 'admin' ? 'Admin' : 'Zaměstnanec' }}
                                        </span>
                                @empty
                                    <span class="text-theme-sm text-gray-400 dark:text-gray-600"> - </span>
                                @endforelse
                            </td>

                            <td class="px-4 sm:px-6 py-3.5 whitespace-nowrap">
                                    <span class="text-theme-sm font-medium text-gray-800 dark:text-white/90">
                                        {{ $user->bank_number ?: '-' }}
                                    </span>
                            </td>

                            <td class="px-4 sm:px-6 py-3.5 whitespace-nowrap">
                                    <span class="text-theme-sm font-medium text-gray-800 dark:text-white/90">
                                        {{ $user->bank_code ?: '-' }}
                                    </span>
                            </td>

                            <td class="px-4 sm:px-6 py-3.5 whitespace-nowrap">
                                <button wire:click="toggleActive({{ $user->id }})"
                                        class="inline-flex rounded-full px-2 py-0.5 text-theme-xs font-medium {{ $user->enabled
                                            ? 'bg-green-50 text-green-700 dark:bg-green-500/15 dark:text-green-500'
                                            : 'bg-red-50 text-red-700 dark:bg-red-500/15 dark:text-red-500' }}">
                                    {{ $user->enabled ? 'Aktivní' : 'Neaktivní' }}
                                </button>
                            </td>

                            <td class="px-4 sm:px-6 py-3.5 whitespace-nowrap">
                                <span class="text-theme-sm text-gray-500 dark:text-gray-400">{{ $user->created_at->format('d. m. Y') }}</span>
                            </td>

                            <td class="px-4 sm:px-6 py-3.5 whitespace-nowrap text-right">
                                @if($confirmingDeleteId === $user->id)
                                    <span class="text-theme-sm text-gray-600 dark:text-gray-400 mr-2">Smazat?</span>
                                    <button wire:click="delete({{ $user->id }})" class="text-error-500 hover:text-error-700 text-theme-sm font-medium mr-1">Ano</button>
                                    <button wire:click="cancelDelete" class="text-gray-500 hover:text-gray-700 text-theme-sm font-medium dark:text-gray-400 dark:hover:text-gray-200">Ne</button>
                                @else
                                    <x-common.table-dropdown>
                                        <x-slot name="button">
                                            <button type="button" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                                                <x-heroicon-o-ellipsis-vertical class="w-6 h-6 fill-current" />
                                            </button>
                                        </x-slot>
                                        <x-slot name="content">
                                            <button wire:click="openModal({{ $user->id }})"
                                                    class="flex w-full px-3 py-2 font-medium text-left text-gray-500 rounded-lg text-theme-xs hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300">
                                                Upravit
                                            </button>
                                            <button wire:click="confirmDelete({{ $user->id }})"
                                                    class="flex w-full px-3 py-2 font-medium text-left text-red-500 rounded-lg text-theme-xs hover:bg-red-50 hover:text-red-700 dark:text-red-400 dark:hover:bg-red-500/10 dark:hover:text-red-300">
                                                Smazat
                                            </button>
                                        </x-slot>
                                    </x-common.table-dropdown>
                                @endif
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center">
                                <x-heroicon-o-archive-box class="mx-auto mb-3 h-10 w-10 text-gray-300 dark:text-gray-600" />
                                <p class="text-gray-500 dark:text-gray-400">Žádní uživatelé nebyly nalezeny.</p>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal -->
    @if($showModal)
        <div class="fixed inset-0 z-[99999] flex items-center justify-center overflow-y-auto">
            <div class="fixed inset-0 bg-gray-900/50 dark:bg-gray-900/70" wire:click="closeModal"></div>
            <div class="relative w-full max-w-lg mx-4 max-h-[90vh] overflow-y-auto rounded-2xl bg-white p-6 shadow-xl dark:bg-gray-900">
                <h3 class="mb-5 text-lg font-semibold text-gray-800 dark:text-white">
                    {{ $editingId ? 'Upravit uživatele' : 'Nový uživatel' }}
                </h3>

                <form wire:submit="save">
                    <div class="space-y-4">
                        <div>
                            <label class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Jméno *</label>
                            <input type="text" wire:model="name" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800" />
                            @error('name') <p class="mt-1 text-sm text-error-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Email *</label>
                            <input type="email" wire:model="email" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800" />
                            @error('email') <p class="mt-1 text-sm text-error-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">
                                Heslo {{ $editingId ? '' : '*' }}
                                @if($editingId) <span class="text-gray-400 font-normal">(ponechte prázdné pro zachování stávajícího)</span> @endif
                            </label>
                            <input type="password" wire:model="password" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800" />
                            @error('password') <p class="mt-1 text-sm text-error-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Role *</label>
                            <div class="flex flex-wrap gap-3">
                                @foreach($availableRoles as $role)
                                    <div x-data
                                         class="flex cursor-pointer select-none items-center gap-2.5 rounded-lg border px-3 py-2 transition-colors"
                                         :class="$wire.selectedroles.includes('{{ $role->name }}')
                                            ? 'border-brand-500 bg-brand-50 dark:border-brand-500 dark:bg-brand-500/10'
                                            : 'border-gray-200 bg-white hover:border-gray-300 dark:border-gray-700 dark:bg-transparent dark:hover:border-gray-600'"
                                         @click="
                                             let val = '{{ $role->name }}';
                                             let roles = [...$wire.selectedroles];
                                             if (roles.includes(val)) {
                                                 $wire.set('selectedroles', roles.filter(r => r !== val));
                                             } else {
                                                 roles.push(val);
                                                 $wire.set('selectedroles', roles);
                                             }
                                         ">
                                        <div class="flex h-5 w-5 shrink-0 items-center justify-center rounded-md border-[1.25px]"
                                             :class="$wire.selectedroles.includes('{{ $role->name }}')
                                                ? 'border-brand-500 bg-brand-500'
                                                : 'border-gray-300 bg-transparent dark:border-gray-600'">
                                            <span :class="$wire.selectedroles.includes('{{ $role->name }}') ? '' : 'opacity-0'">
                                                <svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M11.6666 3.5L5.24992 9.91667L2.33325 7" stroke="white" stroke-width="1.94437" stroke-linecap="round" stroke-linejoin="round"/>
                                                </svg>
                                            </span>
                                        </div>
                                        <span class="text-theme-sm font-medium transition-colors"
                                              :class="$wire.selectedroles.includes('{{ $role->name }}')
                                                ? 'text-brand-600 dark:text-brand-400'
                                                : 'text-gray-700 dark:text-gray-300'">
                                            {{ $role->name === 'admin' ? 'Admin' : 'Zaměstnanec' }}
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div>
                            <label class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Bankovní účet</label>
                            <input type="text" wire:model="bank_number" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800" />
                            @error('bank_number') <p class="mt-1 text-sm text-error-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Číslo banky</label>
                            <input type="text" wire:model="bank_code" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800" />
                            @error('bank_code') <p class="mt-1 text-sm text-error-500">{{ $message }}</p> @enderror
                        </div>

                        <div x-data class="flex cursor-pointer select-none items-center gap-3"
                             @click="$wire.set('enabled', !$wire.enabled)">
                            <div class="flex h-5 w-5 items-center justify-center rounded-md border-[1.25px] hover:border-brand-500 dark:hover:border-brand-500"
                                 :class="$wire.enabled
                                    ? 'border-brand-500 bg-brand-500'
                                    : 'bg-transparent border-gray-300 dark:border-gray-700'">
                                <span :class="$wire.enabled ? '' : 'opacity-0'">
                                    <svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M11.6666 3.5L5.24992 9.91667L2.33325 7" stroke="white" stroke-width="1.94437" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </span>
                            </div>
                            <span class="text-theme-sm text-gray-700 dark:text-gray-400">Aktivní uživatel</span>
                        </div>
                    </div>

                    <div class="mt-6 flex items-center justify-end gap-3">
                        <button type="button" wire:click="closeModal" class="rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-theme-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700">
                            Zrušit
                        </button>
                        <button type="submit" class="rounded-lg bg-brand-500 px-4 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs hover:bg-brand-600">
                            {{ $editingId ? 'Uložit změny' : 'Vytvořit' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
