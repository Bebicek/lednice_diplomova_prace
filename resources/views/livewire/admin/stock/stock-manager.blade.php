<div x-data="{ tab: 'overview' }">
    <x-common.page-breadcrumb :pageTitle="'Správa skladu'" />

    {{-- Toast notifications --}}
    <div x-data="{ show: false, message: '', type: 'success' }"
         @toast-success.window="show = true; message = $event.detail.message; type = 'success'; setTimeout(() => show = false, 3500)"
         @toast-error.window="show = true; message = $event.detail.message; type = 'error'; setTimeout(() => show = false, 4000)">
        <div x-show="show" x-transition class="fixed top-5 right-5 z-50 rounded-lg px-4 py-3 text-white shadow-lg"
             :class="type === 'success' ? 'bg-success-500' : 'bg-error-500'">
            <span x-text="message"></span>
        </div>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white pt-4 dark:border-gray-800 dark:bg-white/[0.03]">

        {{-- Header --}}
        <div class="flex flex-col gap-2 px-5 mb-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
            <div>
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Sklad</h3>
                <p class="mt-1 text-theme-sm text-gray-500 dark:text-gray-400">
                    Správa skladových zásob - příjem, přesuny a odpisy
                </p>
            </div>
            <div class="flex items-center gap-3">
                <button wire:click="openNewOperationModal()"
                        class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs hover:bg-brand-600">
                    <x-heroicon-o-plus class="h-4 w-4" />
                    Nový pohyb
                </button>
            </div>
        </div>

        {{-- Tabs --}}
        <div class="flex border-b border-gray-200 dark:border-gray-700 px-5 sm:px-6">
            <button @click="tab = 'overview'"
                    class="px-4 py-3 text-theme-sm font-medium border-b-2 -mb-px transition-colors"
                    :class="tab === 'overview'
                    ? 'border-brand-500 text-brand-600 dark:text-brand-400'
                    : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200'">
                Přehled skladu
            </button>
            <button @click="tab = 'history'"
                    class="px-4 py-3 text-theme-sm font-medium border-b-2 -mb-px transition-colors"
                    :class="tab === 'history'
                    ? 'border-brand-500 text-brand-600 dark:text-brand-400'
                    : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200'">
                Historie pohybů
            </button>
        </div>

        {{-- Iventory overview --}}
        <div x-show="tab === 'overview'" x-cloak>

            {{-- Search + Filter bar --}}
            <div class="flex flex-col gap-3 px-5 sm:px-6 py-4 sm:flex-row sm:items-center">
                {{-- Search --}}
                <div class="relative flex-1">
                    <span class="absolute -translate-y-1/2 left-4 top-1/2 pointer-events-none">
                        <x-heroicon-o-magnifying-glass class="w-5 h-5 fill-gray-500 dark:fill-gray-400" />
                    </span>
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Hledat produkt..."
                           class="h-[42px] w-full rounded-lg border border-gray-300 bg-transparent py-2.5 pl-[42px] pr-4 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-none focus:ring-2 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800 xl:w-[300px]" />
                </div>

                {{-- Filter button --}}
                <div x-data="{ open: false }" class="relative">
                    @php $hasFilter = $filterCategoryId || $filterStatus; @endphp
                    <button @click="open = !open" type="button"
                            class="inline-flex items-center gap-2 rounded-lg border px-4 py-2.5 text-theme-sm font-medium shadow-theme-xs
                            {{ $hasFilter
                                ? 'border-brand-300 bg-brand-50 text-brand-700 dark:border-brand-700 dark:bg-brand-500/10 dark:text-brand-400'
                                : 'border-gray-300 bg-white text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03]' }}">
                        <x-heroicon-o-funnel class="h-4 w-4" />
                        Filtr
                        @if($hasFilter)
                            <span class="flex h-5 w-5 items-center justify-center rounded-full bg-brand-500 text-[10px] font-bold text-white">
                                {{ ($filterCategoryId ? 1 : 0) + ($filterStatus ? 1 : 0) }}
                            </span>
                        @endif
                    </button>

                    <div x-show="open" @click.away="open = false" x-cloak x-transition
                         class="absolute right-0 z-10 mt-1 w-64 rounded-xl border border-gray-200 bg-white shadow-lg dark:border-gray-700 dark:bg-gray-900">
                        <div class="p-3 space-y-4">

                            {{-- Category --}}
                            <div>
                                <p class="mb-1.5 text-theme-xs font-medium uppercase tracking-wide text-gray-400 dark:text-gray-500">Kategorie</p>
                                <div class="space-y-1">
                                    <button wire:click="$set('filterCategoryId', null)"
                                            class="flex w-full rounded-lg px-3 py-1.5 text-theme-xs font-medium text-left hover:bg-gray-100 dark:hover:bg-white/5
                                            {{ !$filterCategoryId ? 'text-brand-600 dark:text-brand-400' : 'text-gray-700 dark:text-gray-300' }}">
                                        Všechny kategorie
                                    </button>
                                    @foreach($categories as $cat)
                                        <button wire:click="$set('filterCategoryId', {{ $cat->id }})"
                                                class="flex w-full rounded-lg px-3 py-1.5 text-theme-xs font-medium text-left hover:bg-gray-100 dark:hover:bg-white/5
                                                {{ $filterCategoryId === $cat->id ? 'text-brand-600 dark:text-brand-400' : 'text-gray-700 dark:text-gray-300' }}">
                                            {{ $cat->name }}
                                        </button>
                                    @endforeach
                                </div>
                            </div>

                            <div class="border-t border-gray-100 dark:border-gray-800"></div>

                            {{-- Stock Status --}}
                            <div>
                                <p class="mb-1.5 text-theme-xs font-medium uppercase tracking-wide text-gray-400 dark:text-gray-500">Stav skladu</p>
                                <div class="space-y-1">
                                    @foreach([
                                        ''             => 'Vše',
                                        'in_warehouse' => 'Na skladě',
                                        'in_fridge'    => 'V lednici',
                                        'low'          => 'Nízký stav (≤ 3 ks)',
                                        'empty'        => 'Prázdné zásoby',
                                    ] as $val => $label)
                                        <button wire:click="$set('filterStatus', '{{ $val }}')"
                                                class="flex w-full rounded-lg px-3 py-1.5 text-theme-xs font-medium text-left hover:bg-gray-100 dark:hover:bg-white/5
                                                {{ $filterStatus === $val ? 'text-brand-600 dark:text-brand-400' : 'text-gray-700 dark:text-gray-300' }}">
                                            {{ $label }}
                                        </button>
                                    @endforeach
                                </div>
                            </div>

                            @if($hasFilter)
                                <div class="border-t border-gray-100 dark:border-gray-800 pt-2">
                                    <button wire:click="resetFilters" @click="open = false"
                                            class="flex w-full items-center justify-center gap-1.5 rounded-lg px-3 py-1.5 text-theme-xs font-medium text-error-500 hover:bg-red-50 dark:hover:bg-red-500/10">
                                        <x-heroicon-o-x-mark class="h-3.5 w-3.5" />
                                        Zrušit filtry
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Product Table --}}
            <div class="overflow-hidden">
                <div class="max-w-full overflow-x-auto">
                    <table class="min-w-full">
                        <thead>
                        <tr class="border-gray-200 border-y dark:border-gray-700">
                            <th scope="col" class="px-6 py-3 font-medium text-gray-500 text-theme-xs dark:text-gray-400 text-start">
                                <button wire:click="sort('name')" class="inline-flex items-center gap-1.5 hover:text-gray-700 dark:hover:text-gray-200">
                                    Produkt
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
                                <button wire:click="sort('category_id')" class="inline-flex items-center gap-1.5 hover:text-gray-700 dark:hover:text-gray-200">
                                    Kategorie
                                    @if($sortBy === 'category_id')
                                        <svg class="fill-current" width="12" height="12" viewBox="0 0 12 12">
                                            @if($sortDirection === 'asc') <path d="M6 2L10 8H2L6 2Z"/> @else <path d="M6 10L2 4H10L6 10Z"/> @endif
                                        </svg>
                                    @else
                                        <svg class="fill-gray-400 dark:fill-gray-600" width="12" height="12" viewBox="0 0 12 12"><path d="M6 2L9 5.5H3L6 2Z"/><path d="M6 10L3 6.5H9L6 10Z"/></svg>
                                    @endif
                                </button>
                            </th>
                            <th scope="col" class="px-6 py-3 font-medium text-gray-500 text-theme-xs dark:text-gray-400 text-start">Na skladě</th>
                            <th scope="col" class="px-6 py-3 font-medium text-gray-500 text-theme-xs dark:text-gray-400 text-start">V lednici</th>
                            <th scope="col" class="px-6 py-3 font-medium text-gray-500 text-theme-xs dark:text-gray-400 text-start">Celkem</th>
                            <th scope="col" class="px-6 py-3 font-medium text-gray-500 text-theme-xs dark:text-gray-400 text-start">
                                <span class="sr-only">Akce</span>
                            </th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($commodities as $commodity)
                            @php
                                $warehouseQty = $commodity->warehouse_quantity;
                                $fridgeQty    = $commodity->fridge_quantity;
                                $totalQty     = $warehouseQty + $fridgeQty;
                            @endphp
                            <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02]">

                                {{-- Product --}}
                                <td class="px-4 sm:px-6 py-3.5 whitespace-nowrap">
                                    <div class="flex items-center gap-3">
                                        @if($commodity->image_path)
                                            <img src="{{ Storage::url($commodity->image_path) }}"
                                                 alt="{{ $commodity->name }}"
                                                 class="h-8 w-8 rounded-lg object-cover flex-shrink-0" />
                                        @else
                                            <div class="h-8 w-8 rounded-lg bg-gray-100 dark:bg-gray-800 flex items-center justify-center flex-shrink-0">
                                                <x-heroicon-o-photo class="h-4 w-4 text-gray-400" />
                                            </div>
                                        @endif
                                        <span class="text-theme-sm font-medium text-gray-800 dark:text-white/90">{{ $commodity->name }}</span>
                                    </div>
                                </td>

                                {{-- Category --}}
                                <td class="px-4 sm:px-6 py-3.5 whitespace-nowrap">
                                        <span class="text-theme-sm text-gray-500 dark:text-gray-400">
                                            {{ $commodity->category?->name ?? '—' }}
                                        </span>
                                </td>

                                {{-- Stock --}}
                                <td class="px-4 sm:px-6 py-3.5 whitespace-nowrap">
                                        <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-theme-xs font-medium
                                            {{ $warehouseQty === 0
                                                ? 'bg-gray-100 text-gray-400 dark:bg-gray-700/50 dark:text-gray-500'
                                                : ($warehouseQty <= 3
                                                    ? 'bg-amber-50 text-amber-700 dark:bg-amber-500/15 dark:text-amber-400'
                                                    : 'bg-blue-50 text-blue-700 dark:bg-blue-500/15 dark:text-blue-400') }}">
                                            <x-heroicon-o-cube class="h-3 w-3" />
                                            {{ $warehouseQty }} ks
                                        </span>
                                </td>

                                {{-- Fridge --}}
                                <td class="px-4 sm:px-6 py-3.5 whitespace-nowrap">
                                        <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-theme-xs font-medium
                                            {{ $fridgeQty === 0
                                                ? 'bg-gray-100 text-gray-400 dark:bg-gray-700/50 dark:text-gray-500'
                                                : ($fridgeQty <= 3
                                                    ? 'bg-amber-50 text-amber-700 dark:bg-amber-500/15 dark:text-amber-400'
                                                    : 'bg-cyan-50 text-cyan-700 dark:bg-cyan-500/15 dark:text-cyan-400') }}">
                                            <x-heroicon-o-sparkles class="h-3 w-3" />
                                            {{ $fridgeQty }} ks
                                        </span>
                                </td>

                                {{-- Total --}}
                                <td class="px-4 sm:px-6 py-3.5 whitespace-nowrap">
                                        <span class="text-theme-sm font-semibold {{ $totalQty === 0 ? 'text-gray-400 dark:text-gray-600' : 'text-gray-800 dark:text-white/90' }}">
                                            {{ $totalQty }} ks
                                        </span>
                                </td>

                                {{-- Actions dropdown --}}
                                <td class="px-4 sm:px-6 py-3.5 whitespace-nowrap text-right">
                                    <x-common.table-dropdown>
                                        <x-slot name="button">
                                            <button type="button" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                                                <x-heroicon-o-ellipsis-vertical class="w-6 h-6 fill-current" />
                                            </button>
                                        </x-slot>
                                        <x-slot name="content">
                                            {{-- receipt --}}
                                            <button wire:click="openOperationModal({{ $commodity->id }}, 'receipt')"
                                                    class="flex w-full items-center gap-2 px-3 py-2 font-medium text-left text-gray-500 rounded-lg text-theme-xs hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300">
                                                <x-heroicon-o-plus class="h-4 w-4 text-green-500" />
                                                Příjem na sklad
                                            </button>

                                            {{-- Transfer --}}
                                            @if($warehouseQty > 0)
                                                <button wire:click="openOperationModal({{ $commodity->id }}, 'transfer')"
                                                        class="flex w-full items-center gap-2 px-3 py-2 font-medium text-left text-gray-500 rounded-lg text-theme-xs hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300">
                                                    <x-heroicon-o-arrow-right class="h-4 w-4 text-brand-500" />
                                                    Přesun do lednice
                                                </button>
                                            @else
                                                <span class="flex w-full items-center gap-2 px-3 py-2 font-medium text-left rounded-lg text-theme-xs opacity-40 cursor-not-allowed text-gray-400 dark:text-gray-600">
                                                        <x-heroicon-o-arrow-right class="h-4 w-4" />
                                                        Přesun do lednice
                                                    </span>
                                            @endif

                                            <div class="my-1 border-t border-gray-100 dark:border-gray-800"></div>

                                            {{-- Loss --}}
                                            @if($totalQty > 0)
                                                <button wire:click="openOperationModal({{ $commodity->id }}, 'loss')"
                                                        class="flex w-full items-center gap-2 px-3 py-2 font-medium text-left text-red-500 rounded-lg text-theme-xs hover:bg-red-50 hover:text-red-700 dark:text-red-400 dark:hover:bg-red-500/10 dark:hover:text-red-300">
                                                    <x-heroicon-o-minus class="h-4 w-4" />
                                                    Zaznamenat ztrátu
                                                </button>
                                            @else
                                                <span class="flex w-full items-center gap-2 px-3 py-2 font-medium text-left rounded-lg text-theme-xs opacity-40 cursor-not-allowed text-gray-400 dark:text-gray-600">
                                                        <x-heroicon-o-minus class="h-4 w-4" />
                                                        Zaznamenat ztrátu
                                                    </span>
                                            @endif
                                        </x-slot>
                                    </x-common.table-dropdown>
                                </td>

                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center">
                                    <x-heroicon-o-archive-box class="mx-auto mb-3 h-10 w-10 text-gray-300 dark:text-gray-600" />
                                    <p class="text-gray-500 dark:text-gray-400">Žádné produkty nebyly nalezeny.</p>
                                    @if($search || $filterCategoryId || $filterStatus)
                                        <button wire:click="resetFilters" class="mt-2 text-theme-sm text-brand-500 hover:text-brand-600">Zrušit filtry</button>
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Legend --}}
            @if($commodities->isNotEmpty())
                <div class="flex flex-wrap items-center gap-4 px-5 sm:px-6 py-3 border-t border-gray-100 dark:border-gray-800">
                    <span class="text-theme-xs text-gray-400 dark:text-gray-500">Legenda:</span>
                    <span class="inline-flex items-center gap-1 text-theme-xs text-blue-600 dark:text-blue-400">
                        <x-heroicon-o-cube class="h-3 w-3" />
                        Sklad
                    </span>
                    <span class="inline-flex items-center gap-1 text-theme-xs text-cyan-600 dark:text-cyan-400">
                        <x-heroicon-o-sparkles class="h-3 w-3" />
                        Lednice
                    </span>
                    <span class="inline-flex items-center gap-1 text-theme-xs text-amber-600 dark:text-amber-400">
                        <x-heroicon-o-exclamation-triangle class="h-3 w-3" />
                        Nízký stav (≤ 3 ks)
                    </span>
                </div>
            @endif

        </div>

        {{-- History of movements --}}
        <div x-show="tab === 'history'" x-cloak>

            {{-- Filters --}}
            <div class="flex flex-col gap-3 px-5 sm:px-6 py-4 sm:flex-row sm:items-center">
                <div class="relative flex-1">
                    <span class="absolute -translate-y-1/2 left-4 top-1/2 pointer-events-none">
                        <x-heroicon-o-magnifying-glass class="w-5 h-5 fill-gray-500 dark:fill-gray-400" />
                    </span>
                    <input type="text" wire:model.live.debounce.300ms="historySearch" placeholder="Hledat podle produktu..."
                           class="h-[42px] w-full rounded-lg border border-gray-300 bg-transparent py-2.5 pl-[42px] pr-4 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-none focus:ring-2 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800 xl:w-[300px]" />
                </div>

                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open" type="button"
                            class="inline-flex items-center gap-2 rounded-lg border px-4 py-2.5 text-theme-sm font-medium shadow-theme-xs
                            {{ $historyFilterType ? 'border-brand-300 bg-brand-50 text-brand-700 dark:border-brand-700 dark:bg-brand-500/10 dark:text-brand-400' : 'border-gray-300 bg-white text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03]' }}">
                        <x-heroicon-o-funnel class="h-4 w-4" />
                        @php $typeLabels = ['receipt' => 'Příjem', 'transfer' => 'Přesun', 'loss' => 'Ztráta']; @endphp
                        {{ $historyFilterType ? $typeLabels[$historyFilterType] : 'Typ pohybu' }}
                    </button>
                    <div x-show="open" @click.away="open = false" x-cloak x-transition
                         class="absolute right-0 z-10 mt-1 w-44 rounded-xl border border-gray-200 bg-white shadow-lg dark:border-gray-700 dark:bg-gray-900">
                        <div class="p-2 space-y-1">
                            <button wire:click="$set('historyFilterType', '')" @click="open = false"
                                    class="flex w-full rounded-lg px-3 py-2 text-theme-xs font-medium text-left hover:bg-gray-100 dark:hover:bg-white/5 {{ !$historyFilterType ? 'text-brand-600 dark:text-brand-400' : 'text-gray-700 dark:text-gray-300' }}">
                                Všechny typy
                            </button>
                            @foreach([
                                'receipt'  => ['label' => 'Příjem',  'cls' => 'bg-green-50 text-green-700 dark:bg-green-500/15 dark:text-green-400'],
                                'transfer' => ['label' => 'Přesun',  'cls' => 'bg-brand-50 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400'],
                                'loss'     => ['label' => 'Ztráta',  'cls' => 'bg-red-50 text-red-700 dark:bg-red-500/15 dark:text-red-500'],
                            ] as $typeKey => $typeData)
                                <button wire:click="$set('historyFilterType', '{{ $typeKey }}')" @click="open = false"
                                        class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-theme-xs font-medium text-left hover:bg-gray-100 dark:hover:bg-white/5 {{ $historyFilterType === $typeKey ? 'text-brand-600 dark:text-brand-400' : 'text-gray-700 dark:text-gray-300' }}">
                                    <span class="inline-flex rounded-full px-2 py-0.5 text-theme-xs font-medium {{ $typeData['cls'] }}">{{ $typeData['label'] }}</span>
                                </button>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            {{-- Movement Table --}}
            <div class="overflow-hidden">
                <div class="max-w-full overflow-x-auto">
                    <table class="min-w-full">
                        <thead>
                        <tr class="border-gray-200 border-y dark:border-gray-700">
                            <th scope="col" class="px-6 py-3 font-medium text-gray-500 text-theme-xs dark:text-gray-400 text-start">Datum</th>
                            <th scope="col" class="px-6 py-3 font-medium text-gray-500 text-theme-xs dark:text-gray-400 text-start">Produkt</th>
                            <th scope="col" class="px-6 py-3 font-medium text-gray-500 text-theme-xs dark:text-gray-400 text-start">Typ pohybu</th>
                            <th scope="col" class="px-6 py-3 font-medium text-gray-500 text-theme-xs dark:text-gray-400 text-start">Množství</th>
                            <th scope="col" class="px-6 py-3 font-medium text-gray-500 text-theme-xs dark:text-gray-400 text-start">Provedl</th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($movements as $movement)
                            @php
                                $typeMap = [
                                    'receipt'    => ['label' => 'Příjem',  'cls' => 'bg-green-50 text-green-700 dark:bg-green-500/15 dark:text-green-400',     'sign' => '+', 'color' => 'text-green-600 dark:text-green-400'],
                                    'transfer'   => ['label' => 'Přesun',  'cls' => 'bg-brand-50 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400',     'sign' => '↔', 'color' => 'text-brand-600 dark:text-brand-400'],
                                    'loss'       => ['label' => 'Ztráta',  'cls' => 'bg-red-50 text-red-700 dark:bg-red-500/15 dark:text-red-500',             'sign' => '−', 'color' => 'text-red-600 dark:text-red-400'],
                                    'sale'       => ['label' => 'Prodej',  'cls' => 'bg-orange-50 text-orange-700 dark:bg-orange-500/15 dark:text-orange-400', 'sign' => '−', 'color' => 'text-orange-600 dark:text-orange-400'],
                                    'adjustment' => ['label' => 'Korekce', 'cls' => 'bg-gray-100 text-gray-600 dark:bg-gray-700/50 dark:text-gray-400',        'sign' => '±', 'color' => 'text-gray-600 dark:text-gray-400'],
                                ];
                                $typeInfo = $typeMap[$movement->type] ?? ['label' => $movement->type, 'cls' => 'bg-gray-100 text-gray-500', 'sign' => '', 'color' => 'text-gray-600'];
                            @endphp
                            <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02]">
                                <td class="px-4 sm:px-6 py-3.5 whitespace-nowrap">
                                    <span class="text-theme-sm text-gray-500 dark:text-gray-400">{{ $movement->created_at->format('d. m. Y') }}</span>
                                    <span class="block text-xs text-gray-400 dark:text-gray-500">{{ $movement->created_at->format('H:i') }}</span>
                                </td>
                                <td class="px-4 sm:px-6 py-3.5 whitespace-nowrap">
                                    <span class="text-theme-sm font-medium text-gray-800 dark:text-white/90">{{ $movement->commodity?->name ?? '—' }}</span>
                                </td>
                                <td class="px-4 sm:px-6 py-3.5 whitespace-nowrap">
                                    <span class="inline-flex rounded-full px-2 py-0.5 text-theme-xs font-medium {{ $typeInfo['cls'] }}">{{ $typeInfo['label'] }}</span>
                                    @if($movement->type === 'loss' && $movement->note)
                                        @php $location = str_contains($movement->note, 'Lednice') ? 'Lednice' : 'Sklad'; @endphp
                                        <span class="ml-1.5 text-xs text-gray-400 dark:text-gray-500">{{ $location }}</span>
                                    @endif
                                </td>
                                <td class="px-4 sm:px-6 py-3.5 whitespace-nowrap">
                                    <span class="text-theme-sm font-semibold {{ $typeInfo['color'] }}">{{ $typeInfo['sign'] }} {{ $movement->quantity }} ks</span>
                                </td>
                                <td class="px-4 sm:px-6 py-3.5 whitespace-nowrap">
                                    <span class="text-theme-sm text-gray-500 dark:text-gray-400">{{ $movement->user?->name ?? '—' }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center">
                                    <x-heroicon-o-archive-box class="mx-auto mb-3 h-10 w-10 text-gray-300 dark:text-gray-600" />
                                    <p class="text-gray-500 dark:text-gray-400">Žádné pohyby nebyly nalezeny.</p>
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if($movements->hasPages())
                <div class="px-5 sm:px-6 py-4 border-t border-gray-100 dark:border-gray-800">
                    {{ $movements->links() }}
                </div>
            @endif

        </div>

    </div>

    {{-- Operating mode --}}
    @if($showOperationModal)
        <div class="fixed inset-0 z-[99999] flex items-center justify-center">
            <div class="fixed inset-0 bg-gray-900/50 dark:bg-gray-900/70" wire:click="closeModal"></div>
            <div class="relative w-full max-w-md mx-4 max-h-[90vh] overflow-y-auto rounded-2xl bg-white p-6 shadow-xl dark:bg-gray-900">

                {{-- Title + icon --}}
                <div class="mb-5 flex items-start gap-4">
                    @php
                        $modalIcon  = match($operationType) { 'receipt' => 'plus', 'transfer' => 'arrow', 'loss' => 'minus', default => 'sparkle' };
                        $modalColor = match($operationType) { 'receipt' => 'green', 'transfer' => 'brand', 'loss' => 'red', default => 'gray' };
                        $iconBg = ['green' => 'bg-green-100 dark:bg-green-500/15', 'brand' => 'bg-brand-100 dark:bg-brand-500/15', 'red' => 'bg-red-100 dark:bg-red-500/15', 'gray' => 'bg-gray-100 dark:bg-gray-800'][$modalColor];
                        $iconCls = ['green' => 'text-green-600 dark:text-green-400', 'brand' => 'text-brand-600 dark:text-brand-400', 'red' => 'text-red-600 dark:text-red-400', 'gray' => 'text-gray-500 dark:text-gray-400'][$modalColor];
                    @endphp
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl {{ $iconBg }}">
                        @if($operationType === 'receipt')
                            <x-heroicon-o-plus class="h-5 w-5 {{ $iconCls }}" />
                        @elseif($operationType === 'transfer')
                            <x-heroicon-o-arrow-right class="h-5 w-5 {{ $iconCls }}" />
                        @elseif($operationType === 'loss')
                            <x-heroicon-o-minus class="h-5 w-5 {{ $iconCls }}" />
                        @else
                            <x-heroicon-o-plus class="h-5 w-5 {{ $iconCls }}" />
                        @endif
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-white">
                            @if($isGlobalOperation) Nový pohyb
                            @elseif($operationType === 'receipt') Příjem zboží
                            @elseif($operationType === 'transfer') Přesun do lednice
                            @else Ztráta / Odpis
                            @endif
                        </h3>
                        @if(!$isGlobalOperation && $operationCommodityName)
                            <p class="mt-0.5 text-theme-sm text-gray-500 dark:text-gray-400">
                                <span class="font-medium text-gray-700 dark:text-gray-300">{{ $operationCommodityName }}</span>
                            </p>
                        @endif
                    </div>
                </div>

                <form wire:submit="executeOperation">
                    <div class="space-y-4">

                        {{-- Product selection (for "New Transaction" only) --}}
                        @if($isGlobalOperation)
                            <div>
                                <label class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Produkt *</label>
                                <select wire:model.live="operationCommodityId"
                                        class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
                                    <option value="">— Vyberte produkt —</option>
                                    @foreach($allCommodities as $c)
                                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                                    @endforeach
                                </select>
                                @error('operationCommodityId') <p class="mt-1 text-sm text-error-500">{{ $message }}</p> @enderror
                            </div>
                        @endif

                        {{-- Select transaction type (only for "New transaction") --}}
                        @if($isGlobalOperation)
                            <div>
                                <label class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Typ pohybu *</label>
                                <div class="flex flex-wrap gap-3">
                                    <button type="button" wire:click="$set('operationType', 'receipt')"
                                            class="flex-1 min-w-[120px] rounded-lg border px-3 py-2.5 text-theme-xs font-medium text-center transition-colors cursor-pointer
                                            @if($operationType === 'receipt') border-green-500 bg-green-50 text-green-700 dark:border-green-500 dark:bg-green-500/10 dark:text-green-400
                                            @else border-gray-200 bg-white text-gray-600 hover:border-gray-300 dark:border-gray-700 dark:bg-transparent dark:text-gray-400 dark:hover:border-gray-600
                                            @endif">
                                        Příjem na sklad
                                    </button>
                                    <button type="button" wire:click="$set('operationType', 'transfer')"
                                            class="flex-1 min-w-[120px] rounded-lg border px-3 py-2.5 text-theme-xs font-medium text-center transition-colors cursor-pointer
                                            @if($operationType === 'transfer') border-brand-500 bg-brand-50 text-brand-700 dark:border-brand-500 dark:bg-brand-500/10 dark:text-brand-400
                                            @else border-gray-200 bg-white text-gray-600 hover:border-gray-300 dark:border-gray-700 dark:bg-transparent dark:text-gray-400 dark:hover:border-gray-600
                                            @endif">
                                        Přesun do lednice
                                    </button>
                                    <button type="button" wire:click="$set('operationType', 'loss')"
                                            class="flex-1 min-w-[120px] rounded-lg border px-3 py-2.5 text-theme-xs font-medium text-center transition-colors cursor-pointer
                                            @if($operationType === 'loss') border-red-500 bg-red-50 text-red-700 dark:border-red-500 dark:bg-red-500/10 dark:text-red-400
                                            @else border-gray-200 bg-white text-gray-600 hover:border-gray-300 dark:border-gray-700 dark:bg-transparent dark:text-gray-400 dark:hover:border-gray-600
                                            @endif">
                                        Ztráta / Odpis
                                    </button>
                                </div>
                                @error('operationType') <p class="mt-1 text-sm text-error-500">{{ $message }}</p> @enderror
                            </div>
                        @endif

                        {{-- Availability information (for global shipping of the selected product) --}}
                        @if($isGlobalOperation && $operationCommodityId && $operationType)
                            <div class="flex gap-3">
                                <div class="flex-1 rounded-xl border border-gray-100 bg-gray-50 px-3 py-2.5 dark:border-gray-700 dark:bg-gray-800/50 text-center">
                                    <p class="text-xs text-gray-400 dark:text-gray-500">Na skladě</p>
                                    <p class="text-theme-sm font-semibold text-blue-600 dark:text-blue-400">{{ $operationWarehouseMax }} ks</p>
                                </div>
                                <div class="flex-1 rounded-xl border border-gray-100 bg-gray-50 px-3 py-2.5 dark:border-gray-700 dark:bg-gray-800/50 text-center">
                                    <p class="text-xs text-gray-400 dark:text-gray-500">V lednici</p>
                                    <p class="text-theme-sm font-semibold text-cyan-600 dark:text-cyan-400">{{ $operationFridgeMax }} ks</p>
                                </div>
                            </div>
                        @endif

                        {{-- Location of the write off (for "loss" type only) --}}
                        @if($operationType === 'loss')
                            <div>
                                <label class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Lokace odpisu *</label>
                                <select wire:model.live="operationLocation"
                                        class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
                                    <option value="warehouse">Sklad ({{ $operationWarehouseMax }} ks dostupných)</option>
                                    <option value="fridge">Lednice ({{ $operationFridgeMax }} ks dostupných)</option>
                                </select>
                            </div>
                        @endif

                        {{-- Amount (Display if a type is selected or if it is a row action) --}}
                        @if($operationType)
                            <div>
                                <label class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">
                                    @if($operationType === 'receipt') Přijímané množství (ks) *
                                    @elseif($operationType === 'transfer') Přesouvané množství (ks) *
                                    @else Odpisované množství (ks) *
                                    @endif
                                </label>
                                <input type="number" wire:model="operationQuantity" min="1"
                                       @if($operationMaxQuantity < 9999) max="{{ $operationMaxQuantity }}" @endif
                                       class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800" />
                                @if($operationType !== 'receipt' && $operationMaxQuantity < 9999)
                                    <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">
                                        Dostupné: <span class="font-medium text-gray-600 dark:text-gray-300">{{ $operationMaxQuantity }} ks</span>
                                    </p>
                                @endif
                                @error('operationQuantity') <p class="mt-1 text-sm text-error-500">{{ $message }}</p> @enderror
                            </div>

                            {{-- Opration description --}}
                            <div class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 dark:border-gray-700 dark:bg-gray-800/50">
                                <p class="text-theme-xs text-gray-500 dark:text-gray-400">
                                    @if($operationType === 'receipt')
                                        Zboží bude přidáno na <span class="font-medium text-gray-700 dark:text-gray-200">sklad</span>. Pohyb bude zaznamenán do historie.
                                    @elseif($operationType === 'transfer')
                                        Zboží bude přesunuto ze <span class="font-medium text-gray-700 dark:text-gray-200">skladu</span> do <span class="font-medium text-gray-700 dark:text-gray-200">lednice</span>. Pohyb bude zaznamenán do historie.
                                    @else
                                        Zboží bude odepsáno z <span class="font-medium text-gray-700 dark:text-gray-200">{{ $operationLocation === 'warehouse' ? 'skladu' : 'lednice' }}</span>. Pohyb bude zaznamenán do historie jako ztráta.
                                    @endif
                                </p>
                            </div>
                        @endif

                    </div>

                    <div class="mt-6 flex items-center justify-end gap-3">
                        <button type="button" wire:click="closeModal"
                                class="rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-theme-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700">
                            Zrušit
                        </button>
                        <button type="submit"
                                @if(!$operationType) disabled @endif
                                class="rounded-lg px-4 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs transition-colors
                                @if(!$operationType) bg-gray-300 cursor-not-allowed dark:bg-gray-700
                                @elseif($operationType === 'receipt') bg-green-600 hover:bg-green-700 dark:bg-green-500 dark:hover:bg-green-600
                                @elseif($operationType === 'transfer') bg-brand-500 hover:bg-brand-600
                                @else bg-red-600 hover:bg-red-700 dark:bg-red-500 dark:hover:bg-red-600
                                @endif">
                            @if(!$operationType) Vyberte typ pohybu
                            @elseif($operationType === 'receipt') Potvrdit příjem
                            @elseif($operationType === 'transfer') Potvrdit přesun
                            @else Potvrdit ztrátu
                            @endif
                        </button>
                    </div>
                </form>

            </div>
        </div>
    @endif

</div>
