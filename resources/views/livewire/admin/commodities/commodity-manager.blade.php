<div>
    <x-common.page-breadcrumb :pageTitle="'Správa produktů'" />

    <!-- Toast notifications -->
    <div x-data="{ show: false, message: '', type: 'success' }"
         @toast-success.window="show = true; message = $event.detail.message; type = 'success'; setTimeout(() => show = false, 3000)"
         @toast-error.window="show = true; message = $event.detail.message; type = 'error'; setTimeout(() => show = false, 3000)">
        <div x-show="show" x-transition class="fixed top-5 right-5 z-50 rounded-lg px-4 py-3 text-white shadow-lg"
             :class="type === 'success' ? 'bg-success-500' : 'bg-error-500'">
            <span x-text="message"></span>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-4 rounded-lg bg-green-50 p-3 text-sm text-green-700 dark:bg-green-500/10 dark:text-green-400">
            {{ session('success') }}
        </div>
    @endif

    <div class="rounded-2xl border border-gray-200 bg-white pt-4 dark:border-gray-800 dark:bg-white/[0.03]">
        <!-- Header: Title + Actions -->
        <div class="flex flex-col gap-2 px-5 mb-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
            <div>
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Produkty</h3>
                <p class="mt-1 text-theme-sm text-gray-500 dark:text-gray-400">Správa produktů v kancelářské ledničce</p>
            </div>
            <div class="flex items-center gap-3">
                @if(count($selected) > 0)
                    <button wire:click="bulkDelete" wire:confirm="Opravdu chcete smazat {{ count($selected) }} vybraných produktů?"
                            class="inline-flex items-center gap-2 rounded-lg bg-error-500 px-4 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs hover:bg-error-600">
                        <x-heroicon-o-trash class="h-4 w-4" />
                        Smazat ({{ count($selected) }})
                    </button>
                @endif
                <!-- Add product -->
                <button wire:click="openModal()"
                        class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs hover:bg-brand-600">
                    <x-heroicon-o-plus class="h-4 w-4" />
                    Přidat produkt
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
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Hledat produkt..."
                       class="h-[42px] w-full rounded-lg border border-gray-300 bg-transparent py-2.5 pl-[42px] pr-4 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-none focus:ring-2 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800 xl:w-[300px]" />
            </div>
            <!-- Filter -->
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open" type="button"
                        class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-theme-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03]">
                    <x-heroicon-o-funnel class="h-4 w-4" />
                    Filtr
                    @if($filterCategoryId)
                        <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-brand-500 text-white text-xs font-bold">1</span>
                    @endif
                </button>
                <div x-show="open" @click.away="open = false" x-cloak x-transition
                     class="absolute right-0 z-50 mt-2 w-64 rounded-2xl border border-gray-200 bg-white p-4 shadow-lg dark:border-gray-800 dark:bg-gray-900">
                    <div class="mb-3">
                        <label class="mb-1.5 block text-theme-xs font-medium text-gray-700 dark:text-gray-400">Kategorie</label>
                        <select wire:model.live="filterCategoryId"
                                class="h-[42px] w-full rounded-lg border border-gray-300 bg-transparent px-3 py-2 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-none focus:ring-2 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-800 dark:text-white/90 dark:focus:border-brand-800">
                            <option value="">Všechny kategorie</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @if($filterCategoryId || $search)
                        <button wire:click="resetFilters" @click="open = false" type="button"
                                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-theme-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03]">
                            Zrušit filtry
                        </button>
                    @endif
                </div>
            </div>
        </div>

        <!-- Table -->
        <div>
            <div class="max-w-full overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                    <tr class="border-gray-200 border-y dark:border-gray-700">
                        <th scope="col" class="px-6 py-3 text-start" x-data="{ allChecked: false }">
                            <div @click="allChecked = !allChecked; $wire.set('selected', allChecked ? @js($commodities->pluck('id')->toArray()) : [])"
                                 class="flex h-5 w-5 cursor-pointer items-center justify-center rounded-md border-[1.25px]"
                                 :class="allChecked ? 'border-brand-500 bg-brand-500' : 'bg-white dark:bg-white/0 border-gray-300 dark:border-gray-700'">
                                <svg :class="allChecked ? 'block' : 'hidden'" width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M11.6668 3.5L5.25016 9.91667L2.3335 7" stroke="white" stroke-width="1.94437" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </div>
                        </th>
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
                        <th scope="col" class="px-6 py-3 font-medium text-gray-500 text-theme-xs dark:text-gray-400 text-start">
                            <button wire:click="sort('price')" class="inline-flex items-center gap-1.5 hover:text-gray-700 dark:hover:text-gray-200">
                                Cena
                                @if($sortBy === 'price')
                                    <svg class="fill-current" width="12" height="12" viewBox="0 0 12 12">
                                        @if($sortDirection === 'asc') <path d="M6 2L10 8H2L6 2Z"/> @else <path d="M6 10L2 4H10L6 10Z"/> @endif
                                    </svg>
                                @else
                                    <svg class="fill-gray-400 dark:fill-gray-600" width="12" height="12" viewBox="0 0 12 12"><path d="M6 2L9 5.5H3L6 2Z"/><path d="M6 10L3 6.5H9L6 10Z"/></svg>
                                @endif
                            </button>
                        </th>
                        <th scope="col" class="px-6 py-3 font-medium text-gray-500 text-theme-xs dark:text-gray-400 text-start">
                            <button wire:click="sort('is_active')" class="inline-flex items-center gap-1.5 hover:text-gray-700 dark:hover:text-gray-200">
                                Stav
                                @if($sortBy === 'is_active')
                                    <svg class="fill-current" width="12" height="12" viewBox="0 0 12 12">
                                        @if($sortDirection === 'asc') <path d="M6 2L10 8H2L6 2Z"/> @else <path d="M6 10L2 4H10L6 10Z"/> @endif
                                    </svg>
                                @else
                                    <svg class="fill-gray-400 dark:fill-gray-600" width="12" height="12" viewBox="0 0 12 12"><path d="M6 2L9 5.5H3L6 2Z"/><path d="M6 10L3 6.5H9L6 10Z"/></svg>
                                @endif
                            </button>
                        </th>
                        <th scope="col" class="px-6 py-3 font-medium text-gray-500 text-theme-xs dark:text-gray-400 text-start">
                            Spotřeba
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
                        <th scope="col" class="pl-6 pr-4 py-3 w-px font-medium text-gray-500 text-theme-xs dark:text-gray-400 text-start">
                            <span class="sr-only">Akce</span>
                        </th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($commodities as $commodity)
                        <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02]">
                            <td class="px-4 sm:px-6 py-3.5">
                                <div @click="
                                            let id = {{ $commodity->id }};
                                            let sel = $wire.get('selected');
                                            if (sel.includes(id)) { sel = sel.filter(i => i !== id); } else { sel.push(id); }
                                            $wire.set('selected', sel);
                                        "
                                     class="flex h-5 w-5 cursor-pointer items-center justify-center rounded-md border-[1.25px]"
                                     :class="$wire.selected.includes({{ $commodity->id }}) ? 'border-brand-500 bg-brand-500' : 'bg-white dark:bg-white/0 border-gray-300 dark:border-gray-700'">
                                    <svg :class="$wire.selected.includes({{ $commodity->id }}) ? 'block' : 'hidden'" width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M11.6668 3.5L5.25016 9.91667L2.3335 7" stroke="white" stroke-width="1.94437" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </div>
                            </td>
                            <td class="px-4 sm:px-6 py-3.5">
                                <div class="flex items-center">
                                    <div class="h-10 w-10 shrink-0 overflow-hidden rounded-lg">
                                        @if($commodity->image_path)
                                            <img src="{{ Storage::url($commodity->image_path) }}" alt="{{ $commodity->name }}" class="h-10 w-10 object-cover" />
                                        @else
                                            <div class="flex h-10 w-10 items-center justify-center bg-gray-100 dark:bg-gray-800">
                                                <x-heroicon-o-photo class="h-5 w-5 text-gray-400 dark:text-gray-600" />
                                            </div>
                                        @endif
                                    </div>
                                    <div class="ml-4">
                                        <p class="text-theme-sm font-medium text-gray-800 dark:text-white/90">{{ $commodity->name }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 sm:px-6 py-3.5 whitespace-nowrap">
                                <span class="text-theme-sm text-gray-500 dark:text-gray-400">{{ $commodity->category->name ?? '-' }}</span>
                            </td>
                            <td class="px-4 sm:px-6 py-3.5 whitespace-nowrap">
                                <span class="text-theme-sm font-medium text-gray-800 dark:text-white/90">{{ number_format($commodity->price / 100, 2, ',', ' ') }} Kč</span>
                            </td>
                            <td class="px-4 sm:px-6 py-3.5 whitespace-nowrap">
                                <button wire:click="toggleActive({{ $commodity->id }})"
                                        class="inline-flex rounded-full px-2 py-0.5 text-theme-xs font-medium {{ $commodity->is_active
                                            ? 'bg-green-50 text-green-700 dark:bg-green-500/15 dark:text-green-500'
                                            : 'bg-red-50 text-red-700 dark:bg-red-500/15 dark:text-red-500' }}">
                                    {{ $commodity->is_active ? 'Aktivní' : 'Neaktivní' }}
                                </button>
                            </td>
                            <td class="px-4 sm:px-6 py-3.5 whitespace-nowrap">
                                @if($commodity->expires_at)
                                    @php $status = $commodity->expiry_status; $days = $commodity->days_until_expiry; @endphp
                                    <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-medium
                                            {{ $status === 'expired'  ? 'bg-error-50 text-error-700 dark:bg-error-500/15 dark:text-error-400' : '' }}
                                            {{ $status === 'critical' ? 'bg-error-50 text-error-700 dark:bg-error-500/15 dark:text-error-400' : '' }}
                                            {{ $status === 'warning'  ? 'bg-warning-50 text-warning-700 dark:bg-warning-500/15 dark:text-warning-400' : '' }}
                                            {{ $status === 'ok'       ? 'bg-gray-50 text-gray-500 dark:bg-white/5 dark:text-gray-400' : '' }}">
                                            @if($status === 'expired')
                                            <x-heroicon-o-x-circle class="w-3 h-3" /> Prošlé
                                        @elseif($status === 'critical')
                                            <x-heroicon-o-exclamation-triangle class="w-3 h-3" /> Za {{ $days }}d
                                        @elseif($status === 'warning')
                                            <x-heroicon-o-clock class="w-3 h-3" /> Za {{ $days }}d
                                        @else
                                            {{ $commodity->expires_at->format('d.m.Y') }}
                                        @endif
                                        </span>
                                @else
                                    <span class="text-theme-sm text-gray-400 dark:text-gray-600">-</span>
                                @endif
                            </td>
                            <td class="pl-4 sm:pl-6 pr-4 py-3.5 w-px whitespace-nowrap text-right">
                                @if($confirmingDeleteId === $commodity->id)
                                    <span class="text-theme-sm text-gray-600 dark:text-gray-400 mr-2">Smazat?</span>
                                    <button wire:click="delete({{ $commodity->id }})" class="text-error-500 hover:text-error-700 text-theme-sm font-medium mr-1">Ano</button>
                                    <button wire:click="cancelDelete" class="text-gray-500 hover:text-gray-700 text-theme-sm font-medium dark:text-gray-400 dark:hover:text-gray-200">Ne</button>
                                @else
                                    <x-common.table-dropdown>
                                        <x-slot name="button">
                                            <button type="button" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                                                <x-heroicon-o-ellipsis-vertical class="w-6 h-6 fill-current" />
                                            </button>
                                        </x-slot>
                                        <x-slot name="content">
                                            <button wire:click="openModal({{ $commodity->id }})"
                                                    class="flex w-full px-3 py-2 font-medium text-left text-gray-500 rounded-lg text-theme-xs hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300">
                                                Upravit
                                            </button>
                                            <button wire:click="confirmDelete({{ $commodity->id }})"
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
                            <td colspan="7" class="px-6 py-12 text-center">
                                <x-heroicon-o-archive-box class="mx-auto mb-3 h-10 w-10 text-gray-300 dark:text-gray-600" />
                                <p class="text-gray-500 dark:text-gray-400">Žádné produkty nebyly nalezeny.</p>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        @if($commodities->hasPages())
            <div class="px-5 py-4 border-t border-gray-200 dark:border-white/[0.05] sm:px-6">
                <div class="flex items-center justify-between">
                    <p class="text-theme-sm text-gray-500 dark:text-gray-400">
                        Zobrazeno <span class="font-medium text-gray-800 dark:text-white/90">{{ $commodities->firstItem() }}</span>
                        až <span class="font-medium text-gray-800 dark:text-white/90">{{ $commodities->lastItem() }}</span>
                        z <span class="font-medium text-gray-800 dark:text-white/90">{{ $commodities->total() }}</span>
                    </p>
                    <div class="flex items-center gap-2">
                        {{-- Previous --}}
                        @if($commodities->onFirstPage())
                            <span class="flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-3 py-2 text-theme-sm font-medium text-gray-400 shadow-theme-xs cursor-not-allowed dark:border-gray-700 dark:bg-gray-800 dark:text-gray-600">
                                <svg width="16" height="16" viewBox="0 0 20 20" fill="none"><path fill-rule="evenodd" clip-rule="evenodd" d="M2.58301 9.99868C2.58272 10.1909 2.65588 10.3833 2.80249 10.53L7.79915 15.5301C8.09194 15.8231 8.56682 15.8233 8.85981 15.5305C9.15281 15.2377 9.15297 14.7629 8.86018 14.4699L5.14009 10.7472L16.6675 10.7472C17.0817 10.7472 17.4175 10.4114 17.4175 9.99715C17.4175 9.58294 17.0817 9.24715 16.6675 9.24715L5.14554 9.24715L8.86017 5.53016C9.15297 5.23717 9.15282 4.7623 8.85983 4.4695C8.56684 4.1767 8.09197 4.17685 7.79917 4.46984L2.84167 9.43049C2.68321 9.568 2.58301 9.77087 2.58301 9.99715C2.58301 9.99766 2.58301 9.99817 2.58301 9.99868Z" fill="currentColor"/></svg>
                                <span class="hidden sm:inline">Předchozí</span>
                            </span>
                        @else
                            <button wire:click="previousPage" class="flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-3 py-2 text-theme-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">
                                <svg width="16" height="16" viewBox="0 0 20 20" fill="none"><path fill-rule="evenodd" clip-rule="evenodd" d="M2.58301 9.99868C2.58272 10.1909 2.65588 10.3833 2.80249 10.53L7.79915 15.5301C8.09194 15.8231 8.56682 15.8233 8.85981 15.5305C9.15281 15.2377 9.15297 14.7629 8.86018 14.4699L5.14009 10.7472L16.6675 10.7472C17.0817 10.7472 17.4175 10.4114 17.4175 9.99715C17.4175 9.58294 17.0817 9.24715 16.6675 9.24715L5.14554 9.24715L8.86017 5.53016C9.15297 5.23717 9.15282 4.7623 8.85983 4.4695C8.56684 4.1767 8.09197 4.17685 7.79917 4.46984L2.84167 9.43049C2.68321 9.568 2.58301 9.77087 2.58301 9.99715C2.58301 9.99766 2.58301 9.99817 2.58301 9.99868Z" fill="currentColor"/></svg>
                                <span class="hidden sm:inline">Předchozí</span>
                            </button>
                        @endif

                        {{-- Page numbers --}}
                        <div class="hidden items-center gap-0.5 sm:flex">
                            @foreach($commodities->getUrlRange(1, $commodities->lastPage()) as $page => $url)
                                @if($page == $commodities->currentPage())
                                    <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-brand-500 text-theme-sm font-medium text-white">{{ $page }}</span>
                                @else
                                    <button wire:click="gotoPage({{ $page }})" class="flex h-9 w-9 items-center justify-center rounded-lg text-theme-sm font-medium text-gray-700 hover:bg-brand-500/[0.08] hover:text-brand-500 dark:text-gray-400 dark:hover:text-brand-500">{{ $page }}</button>
                                @endif
                            @endforeach
                        </div>

                        {{-- Mobile page info --}}
                        <span class="block text-sm font-medium text-gray-700 dark:text-gray-400 sm:hidden">
                            {{ $commodities->currentPage() }} / {{ $commodities->lastPage() }}
                        </span>

                        {{-- Next --}}
                        @if($commodities->hasMorePages())
                            <button wire:click="nextPage" class="flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-3 py-2 text-theme-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">
                                <span class="hidden sm:inline">Další</span>
                                <svg width="16" height="16" viewBox="0 0 20 20" fill="none"><path fill-rule="evenodd" clip-rule="evenodd" d="M17.4175 9.9986C17.4178 10.1909 17.3446 10.3832 17.198 10.53L12.2013 15.5301C11.9085 15.8231 11.4337 15.8233 11.1407 15.5305C10.8477 15.2377 10.8475 14.7629 11.1403 14.4699L14.8604 10.7472L3.33301 10.7472C2.91879 10.7472 2.58301 10.4114 2.58301 9.99715C2.58301 9.58294 2.91879 9.24715 3.33301 9.24715L14.8549 9.24715L11.1403 5.53016C10.8475 5.23717 10.8477 4.7623 11.1407 4.4695C11.4336 4.1767 11.9085 4.17685 12.2013 4.46984L17.1588 9.43049C17.3173 9.568 17.4175 9.77087 17.4175 9.99715C17.4175 9.99763 17.4175 9.99812 17.4175 9.9986Z" fill="currentColor"/></svg>
                            </button>
                        @else
                            <span class="flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-3 py-2 text-theme-sm font-medium text-gray-400 shadow-theme-xs cursor-not-allowed dark:border-gray-700 dark:bg-gray-800 dark:text-gray-600">
                                <span class="hidden sm:inline">Další</span>
                                <svg width="16" height="16" viewBox="0 0 20 20" fill="none"><path fill-rule="evenodd" clip-rule="evenodd" d="M17.4175 9.9986C17.4178 10.1909 17.3446 10.3832 17.198 10.53L12.2013 15.5301C11.9085 15.8231 11.4337 15.8233 11.1407 15.5305C10.8477 15.2377 10.8475 14.7629 11.1403 14.4699L14.8604 10.7472L3.33301 10.7472C2.91879 10.7472 2.58301 10.4114 2.58301 9.99715C2.58301 9.58294 2.91879 9.24715 3.33301 9.24715L14.8549 9.24715L11.1403 5.53016C10.8475 5.23717 10.8477 4.7623 11.1407 4.4695C11.4336 4.1767 11.9085 4.17685 12.2013 4.46984L17.1588 9.43049C17.3173 9.568 17.4175 9.77087 17.4175 9.99715C17.4175 9.99763 17.4175 9.99812 17.4175 9.9986Z" fill="currentColor"/></svg>
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Modal -->
    @if($showModal)
        <div class="fixed inset-0 z-[99999] flex items-center justify-center overflow-y-auto">
            <div class="fixed inset-0 bg-gray-900/50 dark:bg-gray-900/70" wire:click="closeModal"></div>
            <div class="relative w-full max-w-lg mx-4 max-h-[90vh] overflow-y-auto rounded-2xl bg-white p-6 shadow-xl dark:bg-gray-900">
                <h3 class="mb-5 text-lg font-semibold text-gray-800 dark:text-white">
                    {{ $editingId ? 'Upravit produkt' : 'Nový produkt' }}
                </h3>

                <form wire:submit="save">
                    <div class="space-y-4">
                        <div>
                            <label class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Název *</label>
                            <input type="text" wire:model="name" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800" />
                            @error('name') <p class="mt-1 text-sm text-error-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Cena *</label>
                            <div class="relative">
                                <input type="number" wire:model="price" step="0.01" min="0" placeholder="0.00"
                                       class="h-11 w-full rounded-lg border border-gray-300 bg-transparent py-2.5 pl-4 pr-12 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800" />
                                <span class="absolute right-4 top-1/2 -translate-y-1/2 text-theme-sm font-medium text-gray-400 pointer-events-none">Kč</span>
                            </div>
                            <p class="mt-1 text-theme-xs text-gray-400">Zadejte cenu v korunách, např. <span class="font-medium text-gray-500 dark:text-gray-300">39.90</span> nebo <span class="font-medium text-gray-500 dark:text-gray-300">15</span></p>
                            @error('price') <p class="mt-1 text-sm text-error-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Kategorie *</label>
                            <select wire:model="category_id" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
                                <option value="">Vyberte kategorii</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                            @error('category_id') <p class="mt-1 text-sm text-error-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Popis</label>
                            <textarea wire:model="description" rows="3" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800"></textarea>
                            @error('description') <p class="mt-1 text-sm text-error-500">{{ $message }}</p> @enderror
                        </div>

                        {{-- Barcode and expiry date side by side --}}
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Čárový kód</label>
                                <input type="text" wire:model="barcode" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800" />
                                @error('barcode') <p class="mt-1 text-sm text-error-500">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Datum spotřeby</label>
                                <input type="date" wire:model="expiresAt" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800" />
                                @error('expiresAt') <p class="mt-1 text-sm text-error-500">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div>
                            <label class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Obrázek</label>
                            @if($existingImage && !$image)
                                <div class="mb-2 flex items-center gap-2">
                                    <img src="{{ Storage::url($existingImage) }}" class="w-16 h-16 rounded-lg object-cover" />
                                    <button type="button" wire:click="removeImage" class="text-error-500 text-theme-sm">Odebrat</button>
                                </div>
                            @endif
                            <input type="file" wire:model="image" class="w-full text-theme-sm text-gray-500 file:mr-4 file:rounded-lg file:border-0 file:bg-brand-50 file:px-4 file:py-2 file:text-theme-sm file:font-medium file:text-brand-700 hover:file:bg-brand-100 dark:text-gray-400 dark:file:bg-brand-500/10 dark:file:text-brand-400" />
                            @error('image') <p class="mt-1 text-sm text-error-500">{{ $message }}</p> @enderror
                        </div>

                        <div x-data class="flex cursor-pointer select-none items-center gap-3"
                             @click="$wire.set('is_active', !$wire.is_active)">
                            <div class="flex h-5 w-5 items-center justify-center rounded-md border-[1.25px] hover:border-brand-500 dark:hover:border-brand-500"
                                 :class="$wire.is_active
                                    ? 'border-brand-500 bg-brand-500'
                                    : 'bg-transparent border-gray-300 dark:border-gray-700'">
                                <span :class="$wire.is_active ? '' : 'opacity-0'">
                                    <svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M11.6666 3.5L5.24992 9.91667L2.33325 7" stroke="white" stroke-width="1.94437" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </span>
                            </div>
                            <span class="text-theme-sm text-gray-700 dark:text-gray-400">Aktivní produkt</span>
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
