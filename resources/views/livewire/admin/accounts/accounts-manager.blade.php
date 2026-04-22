<div>
    <x-common.page-breadcrumb :pageTitle="'Platby a účty'" />


    {{-- Stats cards --}}
    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">

        {{-- Total unpaid --}}
        <x-common.stat-card
            label="Nezaplaceno celkem"
            :value="number_format($stats['totalUnpaid'] / 100, 2, ',', ' ') . ' Kč'"
            :subtitle="$stats['countDebtors'] . ' ' . ($stats['countDebtors'] === 1 ? 'dlužník' : ($stats['countDebtors'] < 5 ? 'dlužníci' : 'dlužníků'))"
            color="error"
            icon="exclamation-circle"
        />

        {{-- Total paid --}}
        <x-common.stat-card
            label="Zaplaceno celkem"
            :value="number_format($stats['totalPaid'] / 100, 2, ',', ' ') . ' Kč'"
            subtitle="historicky"
            color="success"
            icon="check-circle"
        />

        {{-- Total pair groups --}}
        <x-common.stat-card
            label="Celkem záznamů"
            :value="$stats['countAll']"
            subtitle="v databázi"
            icon="document-text"
        />

        {{-- Average unpaid debt --}}
        <x-common.stat-card
            label="Průměrný dluh"
            :value="$stats['countDebtors'] > 0 ? number_format(($stats['totalUnpaid'] / $stats['countDebtors']) / 100, 2, ',', ' ') . ' Kč' : '-'"
            subtitle="na dlužníka"
            color="warning"
            icon="calculator"
        />
    </div>

    {{-- Debts table --}}
    <div class="rounded-2xl border border-gray-200 bg-white pt-4 dark:border-gray-800 dark:bg-white/[0.03]">

        {{-- Table header toolbar --}}
        <div class="flex flex-col gap-3 px-5 mb-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
            <div>
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Přehled dluhů</h3>
                <p class="mt-0.5 text-theme-sm text-gray-500 dark:text-gray-400">Dluhy seskupené podle dlužníka a věřitele</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <button wire:click="exportCsv"
                        class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-theme-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03]">
                    <x-heroicon-o-arrow-down-tray class="h-4 w-4" />
                    Export CSV
                </button>
                <button wire:click="openAddModal"
                        class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs hover:bg-brand-600">
                    <x-heroicon-o-plus class="h-4 w-4" />
                    Přidat dluh
                </button>
            </div>
        </div>

        {{-- Filters --}}
        <div class="flex flex-wrap gap-3 px-5 mb-4 sm:px-6">
            <div class="relative flex-1 min-w-[180px]">
                <span class="absolute -translate-y-1/2 left-4 top-1/2 pointer-events-none">
                    <x-heroicon-o-magnifying-glass class="w-4 h-4 text-gray-400" />
                </span>
                <input type="text" wire:model.live.debounce.300ms="search"
                       placeholder="Hledat dlužníka..."
                       class="h-[42px] w-full rounded-lg border border-gray-300 bg-transparent py-2.5 pl-10 pr-4 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-none focus:ring-2 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800" />
            </div>
            <select wire:model.live="filterStatus"
                    class="h-[42px] rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-700 shadow-theme-xs focus:border-brand-300 focus:outline-none focus:ring-2 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                <option value="">Všechny stavy</option>
                <option value="unpaid">S nezaplaceným</option>
                <option value="paid">Plně zaplacené</option>
            </select>
            <select wire:model.live="filterType"
                    class="h-[42px] rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-700 shadow-theme-xs focus:border-brand-300 focus:outline-none focus:ring-2 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                <option value="">Všechny typy</option>
                <option value="system">Systémové (lednička)</option>
                <option value="personal">Osobní (mezi uživateli)</option>
            </select>
        </div>

        {{-- Table body --}}
        <div class="overflow-hidden">
            <div class="max-w-full overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                    <tr class="border-gray-200 border-y dark:border-gray-700">
                        <th class="px-5 py-3 text-left text-theme-xs font-medium text-gray-500 dark:text-gray-400">Dlužník</th>
                        <th class="px-5 py-3 text-left text-theme-xs font-medium text-gray-500 dark:text-gray-400">Věřitel / Typ</th>
                        <th class="px-5 py-3 text-right text-theme-xs font-medium text-gray-500 dark:text-gray-400">Nezaplaceno</th>
                        <th class="px-5 py-3 text-right text-theme-xs font-medium text-gray-500 dark:text-gray-400">Zaplaceno</th>
                        <th class="px-5 py-3 text-center text-theme-xs font-medium text-gray-500 dark:text-gray-400">Záznamy</th>
                        <th class="px-5 py-3 w-px"><span class="sr-only">Akce</span></th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($groups as $group)
                        <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02]">

                            {{-- Debtor --}}
                            <td class="px-5 py-3.5 whitespace-nowrap">
                                    <span class="text-theme-sm font-medium text-gray-800 dark:text-white/90">
                                        {{ $group->user?->name ?? '-' }}
                                    </span>
                            </td>

                            {{-- Creditor / type --}}
                            <td class="px-5 py-3.5 whitespace-nowrap">
                                @if($group->creditor)
                                    <span class="inline-flex items-center gap-1 rounded-full bg-purple-50 px-2 py-0.5 text-xs font-medium text-purple-700 dark:bg-purple-500/15 dark:text-purple-400">
                                            <x-heroicon-o-user class="w-3 h-3" />
                                            {{ $group->creditor->name }}
                                        </span>
                                @else
                                    <span class="inline-flex items-center gap-1 rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600 dark:bg-white/[0.06] dark:text-gray-400">
                                            <x-heroicon-o-building-storefront class="w-3 h-3" />
                                            Lednička
                                        </span>
                                @endif
                            </td>

                            {{-- Unpaid total --}}
                            <td class="px-5 py-3.5 whitespace-nowrap text-right">
                                @if($group->unpaid_total > 0)
                                    <span class="text-theme-sm font-semibold text-error-600 dark:text-error-400">
                                            {{ number_format($group->unpaid_total / 100, 2, ',', ' ') }} Kč
                                        </span>
                                    @if($group->unpaid_count > 1)
                                        <span class="ml-1 text-theme-xs text-gray-400">({{ $group->unpaid_count }}×)</span>
                                    @endif
                                @else
                                    <span class="inline-flex items-center gap-1 text-theme-xs text-success-600 dark:text-success-400">
                                            <x-heroicon-o-check-circle class="w-3.5 h-3.5" />
                                            Vyrovnáno
                                        </span>
                                @endif
                            </td>

                            {{-- Paid total --}}
                            <td class="px-5 py-3.5 whitespace-nowrap text-right">
                                @if($group->paid_total > 0)
                                    <span class="text-theme-sm text-gray-500 dark:text-gray-400">
                                            {{ number_format($group->paid_total / 100, 2, ',', ' ') }} Kč
                                        </span>
                                @else
                                    <span class="text-theme-xs text-gray-300 dark:text-gray-600">-</span>
                                @endif
                            </td>

                            {{-- Record count --}}
                            <td class="px-5 py-3.5 whitespace-nowrap text-center">
                                    <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-gray-100 text-xs font-semibold text-gray-600 dark:bg-white/[0.06] dark:text-gray-400">
                                        {{ $group->total_count }}
                                    </span>
                            </td>

                            {{-- Actions --}}
                            <td class="px-5 py-3.5 whitespace-nowrap text-right w-px">
                                <x-common.table-dropdown>
                                    <x-slot name="button">
                                        <button type="button" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                                            <x-heroicon-o-ellipsis-vertical class="w-6 h-6" />
                                        </button>
                                    </x-slot>
                                    <x-slot name="content">
                                        <button wire:click="openDetail({{ $group->user_id }}, {{ $group->creditor_id ?? 'null' }})"
                                                class="flex w-full px-3 py-2 text-left text-theme-xs font-medium text-gray-500 rounded-lg hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300">
                                            <x-heroicon-o-eye class="w-3.5 h-3.5 mr-2 mt-px" />
                                            Zobrazit záznamy
                                        </button>
                                        @if($group->unpaid_count > 0)
                                            <button wire:click="markAllAsPaid({{ $group->user_id }}, {{ $group->creditor_id ?? 'null' }})"
                                                    wire:confirm="Označit všechny nezaplacené dluhy tohoto páru jako zaplacené?"
                                                    class="flex w-full px-3 py-2 text-left text-theme-xs font-medium text-success-600 rounded-lg hover:bg-success-50 hover:text-success-700 dark:text-success-400 dark:hover:bg-success-500/10">
                                                <x-heroicon-o-check-circle class="w-3.5 h-3.5 mr-2 mt-px" />
                                                Označit vše zaplaceno
                                            </button>
                                        @endif
                                    </x-slot>
                                </x-common.table-dropdown>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-16 text-center">
                                <x-heroicon-o-banknotes class="mx-auto mb-3 h-10 w-10 text-gray-300 dark:text-gray-600" />
                                <p class="text-theme-sm font-medium text-gray-700 dark:text-white/70">Žádné dluhy nebyly nalezeny</p>
                                <p class="mt-1 text-theme-xs text-gray-500 dark:text-gray-400">Zkuste změnit filtr nebo hledaný výraz.</p>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Pagination --}}
        @if($lastPage > 1)
            <div class="px-5 py-4 border-t border-gray-200 dark:border-white/[0.05] sm:px-6">
                <div class="flex items-center justify-between">
                    <p class="text-theme-sm text-gray-500 dark:text-gray-400">
                        Zobrazeno <span class="font-medium text-gray-800 dark:text-white/90">{{ (($page - 1) * $perPage) + 1 }}</span>
                        až <span class="font-medium text-gray-800 dark:text-white/90">{{ min($page * $perPage, $total) }}</span>
                        z <span class="font-medium text-gray-800 dark:text-white/90">{{ $total }}</span>
                    </p>
                    <div class="flex items-center gap-2">
                        @if($page <= 1)
                            <span class="flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-3 py-2 text-theme-sm font-medium text-gray-400 shadow-theme-xs cursor-not-allowed dark:border-gray-700 dark:bg-gray-800 dark:text-gray-600">
                                <svg width="16" height="16" viewBox="0 0 20 20" fill="none"><path fill-rule="evenodd" clip-rule="evenodd" d="M2.58301 9.99868C2.58272 10.1909 2.65588 10.3833 2.80249 10.53L7.79915 15.5301C8.09194 15.8231 8.56682 15.8233 8.85981 15.5305C9.15281 15.2377 9.15297 14.7629 8.86018 14.4699L5.14009 10.7472L16.6675 10.7472C17.0817 10.7472 17.4175 10.4114 17.4175 9.99715C17.4175 9.58294 17.0817 9.24715 16.6675 9.24715L5.14554 9.24715L8.86017 5.53016C9.15297 5.23717 9.15282 4.7623 8.85983 4.4695C8.56684 4.1767 8.09197 4.17685 7.79917 4.46984L2.84167 9.43049C2.68321 9.568 2.58301 9.77087 2.58301 9.99715C2.58301 9.99766 2.58301 9.99817 2.58301 9.99868Z" fill="currentColor"/></svg>
                                <span class="hidden sm:inline">Předchozí</span>
                            </span>
                        @else
                            <button wire:click="$set('page', {{ $page - 1 }})" class="flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-3 py-2 text-theme-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">
                                <svg width="16" height="16" viewBox="0 0 20 20" fill="none"><path fill-rule="evenodd" clip-rule="evenodd" d="M2.58301 9.99868C2.58272 10.1909 2.65588 10.3833 2.80249 10.53L7.79915 15.5301C8.09194 15.8231 8.56682 15.8233 8.85981 15.5305C9.15281 15.2377 9.15297 14.7629 8.86018 14.4699L5.14009 10.7472L16.6675 10.7472C17.0817 10.7472 17.4175 10.4114 17.4175 9.99715C17.4175 9.58294 17.0817 9.24715 16.6675 9.24715L5.14554 9.24715L8.86017 5.53016C9.15297 5.23717 9.15282 4.7623 8.85983 4.4695C8.56684 4.1767 8.09197 4.17685 7.79917 4.46984L2.84167 9.43049C2.68321 9.568 2.58301 9.77087 2.58301 9.99715C2.58301 9.99766 2.58301 9.99817 2.58301 9.99868Z" fill="currentColor"/></svg>
                                <span class="hidden sm:inline">Předchozí</span>
                            </button>
                        @endif
                        <div class="hidden items-center gap-0.5 sm:flex">
                            @for($p = 1; $p <= $lastPage; $p++)
                                @if($p == $page)
                                    <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-brand-500 text-theme-sm font-medium text-white">{{ $p }}</span>
                                @else
                                    <button wire:click="$set('page', {{ $p }})" class="flex h-9 w-9 items-center justify-center rounded-lg text-theme-sm font-medium text-gray-700 hover:bg-brand-500/[0.08] hover:text-brand-500 dark:text-gray-400 dark:hover:text-brand-500">{{ $p }}</button>
                                @endif
                            @endfor
                        </div>
                        <span class="block text-sm font-medium text-gray-700 dark:text-gray-400 sm:hidden">{{ $page }} / {{ $lastPage }}</span>
                        @if($page >= $lastPage)
                            <span class="flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-3 py-2 text-theme-sm font-medium text-gray-400 shadow-theme-xs cursor-not-allowed dark:border-gray-700 dark:bg-gray-800 dark:text-gray-600">
                                <span class="hidden sm:inline">Další</span>
                                <svg width="16" height="16" viewBox="0 0 20 20" fill="none"><path fill-rule="evenodd" clip-rule="evenodd" d="M17.4175 9.9986C17.4178 10.1909 17.3446 10.3832 17.198 10.53L12.2013 15.5301C11.9085 15.8231 11.4337 15.8233 11.1407 15.5305C10.8477 15.2377 10.8475 14.7629 11.1403 14.4699L14.8604 10.7472L3.33301 10.7472C2.91879 10.7472 2.58301 10.4114 2.58301 9.99715C2.58301 9.58294 2.91879 9.24715 3.33301 9.24715L14.8549 9.24715L11.1403 5.53016C10.8475 5.23717 10.8477 4.7623 11.1407 4.4695C11.4336 4.1767 11.9085 4.17685 12.2013 4.46984L17.1588 9.43049C17.3173 9.568 17.4175 9.77087 17.4175 9.99715C17.4175 9.99763 17.4175 9.99812 17.4175 9.9986Z" fill="currentColor"/></svg>
                            </span>
                        @else
                            <button wire:click="$set('page', {{ $page + 1 }})" class="flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-3 py-2 text-theme-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">
                                <span class="hidden sm:inline">Další</span>
                                <svg width="16" height="16" viewBox="0 0 20 20" fill="none"><path fill-rule="evenodd" clip-rule="evenodd" d="M17.4175 9.9986C17.4178 10.1909 17.3446 10.3832 17.198 10.53L12.2013 15.5301C11.9085 15.8231 11.4337 15.8233 11.1407 15.5305C10.8477 15.2377 10.8475 14.7629 11.1403 14.4699L14.8604 10.7472L3.33301 10.7472C2.91879 10.7472 2.58301 10.4114 2.58301 9.99715C2.58301 9.58294 2.91879 9.24715 3.33301 9.24715L14.8549 9.24715L11.1403 5.53016C10.8475 5.23717 10.8477 4.7623 11.1407 4.4695C11.4336 4.1767 11.9085 4.17685 12.2013 4.46984L17.1588 9.43049C17.3173 9.568 17.4175 9.77087 17.4175 9.99715C17.4175 9.99763 17.4175 9.99812 17.4175 9.9986Z" fill="currentColor"/></svg>
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    </div>

    {{-- Modal Add debt --}}
    @if($showAddModal)
        <div x-data="{ show: false }" x-init="$nextTick(() => show = true)" class="fixed inset-0 z-[99999] flex items-center justify-center">
            <div x-show="show"
                 x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                 class="fixed inset-0 bg-gray-900/50 dark:bg-gray-900/70" wire:click="closeAddModal"></div>
            <div x-show="show"
                 x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                 class="relative w-full max-w-lg mx-4 max-h-[90vh] overflow-y-auto rounded-2xl border border-gray-200 bg-white p-6 shadow-xl dark:border-gray-700 dark:bg-gray-800">

                <h3 class="mb-5 text-lg font-semibold text-gray-800 dark:text-white">Přidat ruční dluh</h3>

                <form wire:submit="saveDebt" class="space-y-4">

                    <div>
                        <label class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">
                            Dlužník <span class="text-error-500">*</span>
                        </label>
                        <select wire:model.live="addUserId"
                                class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-none focus:ring-2 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                            <option value="">- vyberte uživatele -</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                        @error('addUserId') <p class="mt-1 text-sm text-error-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Typ dluhu</label>
                        <div class="flex gap-3">
                            <label class="flex flex-1 cursor-pointer items-center gap-2.5 rounded-lg border px-4 py-3 transition-colors
                                {{ $addType === 'system' ? 'border-brand-500 bg-brand-50 dark:border-brand-500 dark:bg-brand-500/10' : 'border-gray-200 hover:border-gray-300 dark:border-gray-700' }}">
                                <input type="radio" wire:model.live="addType" value="system" class="sr-only" />
                                <x-heroicon-o-building-storefront class="w-4 h-4 {{ $addType === 'system' ? 'text-brand-500' : 'text-gray-400' }}" />
                                <div>
                                    <p class="text-theme-sm font-medium {{ $addType === 'system' ? 'text-brand-700 dark:text-brand-300' : 'text-gray-700 dark:text-gray-300' }}">Systémový</p>
                                    <p class="text-theme-xs text-gray-400">Dluh vůči ledničce</p>
                                </div>
                            </label>
                            <label class="flex flex-1 cursor-pointer items-center gap-2.5 rounded-lg border px-4 py-3 transition-colors
                                {{ $addType === 'personal' ? 'border-brand-500 bg-brand-50 dark:border-brand-500 dark:bg-brand-500/10' : 'border-gray-200 hover:border-gray-300 dark:border-gray-700' }}">
                                <input type="radio" wire:model.live="addType" value="personal" class="sr-only" />
                                <x-heroicon-o-users class="w-4 h-4 {{ $addType === 'personal' ? 'text-brand-500' : 'text-gray-400' }}" />
                                <div>
                                    <p class="text-theme-sm font-medium {{ $addType === 'personal' ? 'text-brand-700 dark:text-brand-300' : 'text-gray-700 dark:text-gray-300' }}">Osobní</p>
                                    <p class="text-theme-xs text-gray-400">Dluh vůči jinému uživateli</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    @if($addType === 'personal')
                        <div>
                            <label class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">
                                Věřitel <span class="text-error-500">*</span>
                            </label>
                            <select wire:model="addCreditorId"
                                    class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-none focus:ring-2 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                                <option value="">- vyberte věřitele -</option>
                                @foreach($users as $user)
                                    @if($user->id != $addUserId)
                                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                                    @endif
                                @endforeach
                            </select>
                            @error('addCreditorId') <p class="mt-1 text-sm text-error-500">{{ $message }}</p> @enderror
                        </div>
                    @endif

                    <div>
                        <label class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">
                            Částka (Kč) <span class="text-error-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="number" wire:model="addAmount" step="0.01" min="0.01" placeholder="0,00"
                                   class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 pr-10 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-none focus:ring-2 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
                            <span class="absolute right-4 top-1/2 -translate-y-1/2 text-theme-sm text-gray-400">Kč</span>
                        </div>
                        @error('addAmount') <p class="mt-1 text-sm text-error-500">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-2">
                        <button type="button" wire:click="closeAddModal"
                                class="rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-theme-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700">
                            Zrušit
                        </button>
                        <button type="submit"
                                class="rounded-lg bg-brand-500 px-4 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs hover:bg-brand-600">
                            Přidat dluh
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Modal Group detail, individual debt records --}}
    @if($hasDetailOpen && $detailDebts !== null)
        <div x-data="{ show: false }" x-init="$nextTick(() => show = true)" class="fixed inset-0 z-[99999] flex items-center justify-center">
            <div x-show="show"
                 x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                 class="fixed inset-0 bg-gray-900/50 dark:bg-gray-900/70" wire:click="closeDetail"></div>
            <div x-show="show"
                 x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                 class="relative w-full max-w-2xl mx-4 max-h-[90vh] overflow-y-auto rounded-2xl border border-gray-200 bg-white p-6 shadow-xl dark:border-gray-700 dark:bg-gray-800">

                {{-- Header --}}
                <div class="mb-5 flex items-start justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-white">
                            Záznamy: {{ $detailUser?->name }} → {{ $detailCred?->name ?? 'Lednička' }}
                        </h3>
                        <p class="mt-0.5 text-theme-xs text-gray-500">
                            {{ $detailDebts->count() }} {{ $detailDebts->count() === 1 ? 'záznam' : ($detailDebts->count() < 5 ? 'záznamy' : 'záznamů') }}
                            ·
                            Nezaplaceno: <span class="font-semibold text-error-600">{{ number_format($detailDebts->where('is_paid', false)->sum('amount') / 100, 2, ',', ' ') }} Kč</span>
                            ·
                            Zaplaceno: <span class="font-semibold text-success-600">{{ number_format($detailDebts->where('is_paid', true)->sum('amount') / 100, 2, ',', ' ') }} Kč</span>
                        </p>
                    </div>
                    <button wire:click="closeDetail" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                        <x-heroicon-o-x-mark class="w-5 h-5" />
                    </button>
                </div>

                {{-- Individual debt rows --}}
                <div class="divide-y divide-gray-100 rounded-xl border border-gray-200 dark:divide-gray-800 dark:border-gray-700">
                    @foreach($detailDebts as $d)
                        <div class="flex items-center justify-between px-4 py-3 gap-3">
                            {{-- Origin --}}
                            <div class="flex-1 min-w-0">
                                @if($d->order_id)
                                    <span class="inline-flex items-center gap-1 text-theme-xs text-gray-600 dark:text-gray-300 font-medium">
                                        <x-heroicon-o-shopping-cart class="w-3.5 h-3.5 text-gray-400" />
                                        Nákup #{{ $d->order_id }}
                                    </span>
                                    @if($d->order && $d->order->items->isNotEmpty())
                                        <p class="mt-0.5 text-theme-xs text-gray-400 truncate">
                                            {{ $d->order->items->map(fn($i) => $i->commodity->name ?? '?')->implode(', ') }}
                                        </p>
                                    @endif
                                @elseif($d->lunch_id)
                                    <span class="inline-flex items-center gap-1 text-theme-xs text-gray-600 dark:text-gray-300 font-medium">
                                        <x-heroicon-o-cake class="w-3.5 h-3.5 text-gray-400" />
                                        Oběd #{{ $d->lunch_id }}
                                        @if($d->lunch)
                                            - {{ $d->lunch->restaurant_name }}
                                        @endif
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 text-theme-xs text-gray-600 dark:text-gray-300 font-medium">
                                        <x-heroicon-o-pencil-square class="w-3.5 h-3.5 text-gray-400" />
                                        Ruční záznam
                                    </span>
                                @endif
                                <p class="mt-0.5 text-theme-xs text-gray-400">{{ $d->created_at->format('d. m. Y') }}</p>
                            </div>

                            {{-- Amount + status --}}
                            <div class="text-right shrink-0">
                                <p class="text-theme-sm font-semibold {{ $d->is_paid ? 'text-gray-500 dark:text-gray-400' : 'text-error-600 dark:text-error-400' }}">
                                    {{ number_format($d->amount / 100, 2, ',', ' ') }} Kč
                                </p>
                                @if($d->is_paid)
                                    <span class="text-theme-xs text-success-600 dark:text-success-400">✓ {{ $d->paid_at?->format('d. m. Y') }}</span>
                                @else
                                    <span class="text-theme-xs text-error-500">Nezaplaceno</span>
                                @endif
                            </div>

                            {{-- Toggle button --}}
                            <div class="shrink-0">
                                @if(!$d->is_paid)
                                    <button wire:click="markSingleAsPaid({{ $d->id }})"
                                            class="inline-flex items-center gap-1 rounded-lg bg-success-50 px-2.5 py-1.5 text-theme-xs font-medium text-success-700 hover:bg-success-100 dark:bg-success-500/10 dark:text-success-400 dark:hover:bg-success-500/20">
                                        <x-heroicon-o-check class="w-3.5 h-3.5" />
                                        Zaplatit
                                    </button>
                                @else
                                    <button wire:click="markSingleAsUnpaid({{ $d->id }})"
                                            class="inline-flex items-center gap-1 rounded-lg bg-gray-50 px-2.5 py-1.5 text-theme-xs font-medium text-gray-500 hover:bg-gray-100 dark:bg-white/[0.04] dark:text-gray-400 dark:hover:bg-white/[0.07]">
                                        <x-heroicon-o-arrow-uturn-left class="w-3.5 h-3.5" />
                                        Vrátit
                                    </button>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Footer --}}
                <div class="mt-5 flex justify-between items-center gap-3">
                    @php $unpaidInDetail = $detailDebts->where('is_paid', false)->count(); @endphp
                    @if($unpaidInDetail > 0)
                        <button wire:click="markAllAsPaid({{ $detailUserId }}, {{ $detailCreditorId ?? 'null' }})"
                                wire:confirm="Označit všechny nezaplacené dluhy jako zaplacené?"
                                class="inline-flex items-center gap-2 rounded-lg bg-success-500 px-4 py-2.5 text-theme-sm font-medium text-white hover:bg-success-600">
                            <x-heroicon-o-check-circle class="w-4 h-4" />
                            Označit vše zaplaceno ({{ $unpaidInDetail }})
                        </button>
                    @else
                        <span class="inline-flex items-center gap-1.5 text-theme-sm text-success-600 dark:text-success-400">
                            <x-heroicon-o-check-circle class="w-4 h-4" />
                            Vše zaplaceno
                        </span>
                    @endif
                    <button wire:click="closeDetail"
                            class="rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-theme-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400">
                        Zavřít
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>
