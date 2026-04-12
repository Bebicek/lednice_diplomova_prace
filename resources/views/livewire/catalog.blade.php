<div wire:poll.5s="$refresh">

    @if(session('success'))
        <div class="mb-4 rounded-lg bg-success-50 p-4 text-success-700 dark:bg-success-500/10 dark:text-success-400">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-4 rounded-lg bg-error-50 p-4 text-error-700 dark:bg-error-500/10 dark:text-error-400">
            {{ session('error') }}
        </div>
    @endif

    <div class="flex flex-col lg:flex-row gap-6">

        <aside class="w-full lg:w-64 shrink-0 space-y-6">

            {{-- Search --}}
            <div>
                <h3 class="text-theme-sm font-semibold text-gray-800 dark:text-white/90 mb-3">Vyhledávání</h3>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400 dark:text-gray-500">
                        <x-heroicon-o-magnifying-glass class="w-5 h-5" />
                    </span>
                    <input type="text"
                           wire:model.live.debounce.300ms="search"
                           placeholder="Hledat produkty..."
                           class="h-11 w-full rounded-lg border border-gray-200 bg-white pl-10 pr-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-800 dark:text-white/90 dark:placeholder:text-gray-500 dark:focus:border-brand-800" />
                </div>
            </div>

            {{-- Categories --}}
            <div>
                <h3 class="text-theme-sm font-semibold text-gray-800 dark:text-white/90 mb-3">Kategorie</h3>
                <div class="space-y-1">
                    @foreach($categories as $category)
                        <button wire:click="setCategory({{ $category->id }})"
                                type="button"
                                class="flex w-full items-center justify-between rounded-lg px-3 py-2 text-theme-sm transition-colors
                                    {{ $categoryId === $category->id
                                        ? 'bg-brand-50 text-brand-600 font-medium dark:bg-brand-500/10 dark:text-brand-400'
                                        : 'text-gray-600 hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-white/5' }}">
                            <span>{{ $category->name }}</span>
                            <span class="text-theme-xs {{ $categoryId === $category->id ? 'text-brand-500 dark:text-brand-400' : 'text-gray-400 dark:text-gray-500' }}">
                                {{ $category->commodities_count }}
                            </span>
                        </button>
                    @endforeach
                </div>
            </div>

            {{-- Only in stock --}}
            <div class="flex items-center justify-between">
                <span class="text-theme-sm text-gray-600 dark:text-gray-400">Pouze skladem</span>
                <button wire:click="$toggle('onlyInStock')"
                        type="button"
                        class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-hidden focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900
                            {{ $onlyInStock ? 'bg-brand-500' : 'bg-gray-200 dark:bg-gray-700' }}"
                        role="switch"
                        aria-checked="{{ $onlyInStock ? 'true' : 'false' }}">
                    <span class="pointer-events-none inline-block h-5 w-5 rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out
                        {{ $onlyInStock ? 'translate-x-5' : 'translate-x-0' }}"></span>
                </button>
            </div>

        </aside>

        <div class="flex-1 min-w-0">

            {{-- Header --}}
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-800 dark:text-white/90">Katalog produktů</h1>
                <p class="mt-1 text-theme-sm text-gray-500 dark:text-gray-400">Prohlédněte si náš výběr nápojů, svačin a dalšího</p>
            </div>

            {{-- Toolbar: count + sort + view toggle --}}
            <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
                <p class="text-theme-sm text-gray-500 dark:text-gray-400">
                    {{ $commodities->total() }} {{ trans_choice('produktů|produkt|produkty', $commodities->total()) }}
                </p>

                <div class="flex items-center gap-3">
                    {{-- Sort --}}
                    <select wire:model.live="sortBy"
                            class="h-10 rounded-lg border border-gray-200 bg-white px-3 py-2 pr-8 text-theme-sm text-gray-700 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-800 dark:text-white/90 dark:focus:border-brand-800">
                        <option value="popular">Oblíbené</option>
                        <option value="name">Název</option>
                        <option value="price_asc">Cena: nejnižší</option>
                        <option value="price_desc">Cena: nejvyšší</option>
                    </select>

                    {{-- View toggle --}}
                    <div class="hidden sm:flex items-center rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
                        <button wire:click="$set('viewMode', 'grid')"
                                type="button"
                                class="flex items-center justify-center w-10 h-10 transition-colors
                                    {{ $viewMode === 'grid'
                                        ? 'bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-400'
                                        : 'bg-white text-gray-400 hover:text-gray-600 dark:bg-gray-800 dark:text-gray-500 dark:hover:text-gray-300' }}">
                            <x-heroicon-o-squares-2x2 class="w-5 h-5" />
                        </button>
                        <button wire:click="$set('viewMode', 'list')"
                                type="button"
                                class="flex items-center justify-center w-10 h-10 border-l border-gray-200 dark:border-gray-700 transition-colors
                                    {{ $viewMode === 'list'
                                        ? 'bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-400'
                                        : 'bg-white text-gray-400 hover:text-gray-600 dark:bg-gray-800 dark:text-gray-500 dark:hover:text-gray-300' }}">
                            <x-heroicon-o-bars-3 class="w-5 h-5" />
                        </button>
                    </div>
                </div>
            </div>

            @if($viewMode === 'grid')
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3 md:gap-5">
                    @forelse($commodities as $commodity)
                        @php
                            $fridge     = $commodity->fridge_quantity;
                            $warehouse  = $commodity->warehouse_quantity;
                            $outOfStock = $fridge <= 0;
                        @endphp

                        <div class="group flex flex-col rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] overflow-hidden transition-shadow hover:shadow-md">

                            {{-- Image area --}}
                            <button wire:click="openDetail({{ $commodity->id }})" class="relative block w-full focus:outline-none" type="button">
                                @if($commodity->image_path)
                                    <img src="{{ Storage::url($commodity->image_path) }}"
                                         alt="{{ $commodity->name }}"
                                         class="w-full h-44 object-cover {{ $outOfStock ? 'opacity-50' : '' }}" />
                                @else
                                    <div class="flex items-center justify-center w-full h-44 bg-gray-100 dark:bg-gray-800 {{ $outOfStock ? 'opacity-50' : '' }}">
                                        <x-heroicon-o-photo class="w-12 h-12 text-gray-300 dark:text-gray-600" />
                                    </div>
                                @endif

                                {{-- Stock badge --}}
                                @if($fridge > 0 && $fridge <= 3)
                                    <span class="absolute top-3 left-3 inline-flex items-center gap-1 rounded-full bg-warning-500 px-2.5 py-1 text-xs font-semibold text-white shadow">
                                        Posledních {{ $fridge }} ks
                                    </span>
                                @elseif($fridge > 3)
                                    <span class="absolute top-3 left-3 inline-flex items-center gap-1 rounded-full bg-brand-500 px-2.5 py-1 text-xs font-semibold text-white shadow">
                                        Skladem
                                    </span>
                                @endif

                                @if($outOfStock)
                                    <div class="absolute inset-0 flex items-center justify-center bg-gray-900/40">
                                        <span class="rounded-full bg-error-500 px-3 py-1 text-xs font-semibold text-white shadow">
                                            Není v lednici
                                        </span>
                                    </div>
                                @endif
                            </button>

                            {{-- Card body --}}
                            <div class="flex flex-1 flex-col p-4">
                                <button wire:click="openDetail({{ $commodity->id }})"
                                        type="button"
                                        class="text-left text-theme-sm font-semibold text-gray-800 hover:text-brand-500 dark:text-white/90 dark:hover:text-brand-400 transition-colors line-clamp-2">
                                    {{ $commodity->name }}
                                </button>

                                <div class="mt-auto pt-3 flex items-center justify-between">
                                    <p class="text-base font-bold text-gray-800 dark:text-white/90">
                                        {{ number_format($commodity->price / 100, 2, ',', ' ') }} Kč
                                    </p>

                                    <button wire:click="addToCart({{ $commodity->id }})"
                                            type="button"
                                            {{ $outOfStock ? 'disabled' : '' }}
                                            class="inline-flex items-center gap-1.5 rounded-lg px-3 py-2 text-theme-xs font-medium transition-colors
                                                {{ $outOfStock
                                                    ? 'bg-gray-100 text-gray-400 cursor-not-allowed dark:bg-gray-800 dark:text-gray-600'
                                                    : 'border border-gray-200 text-gray-600 hover:border-brand-300 hover:text-brand-500 hover:bg-brand-50 dark:border-gray-700 dark:text-gray-400 dark:hover:border-brand-500 dark:hover:text-brand-400 dark:hover:bg-brand-500/10' }}">
                                        <x-heroicon-o-plus class="w-4 h-4" />
                                        Přidat
                                    </button>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full py-16 text-center">
                            <x-heroicon-o-magnifying-glass class="mx-auto mb-3 w-12 h-12 text-gray-300 dark:text-gray-600" />
                            <p class="text-theme-sm font-medium text-gray-700 dark:text-white/70">Žádné produkty nebyly nalezeny</p>
                            <p class="mt-1 text-theme-xs text-gray-500 dark:text-gray-400">Zkuste změnit filtr nebo hledaný výraz.</p>
                        </div>
                    @endforelse
                </div>

            @else
                <div class="space-y-3">
                    @forelse($commodities as $commodity)
                        @php
                            $fridge     = $commodity->fridge_quantity;
                            $warehouse  = $commodity->warehouse_quantity;
                            $outOfStock = $fridge <= 0;
                        @endphp

                        <div class="flex items-center gap-4 rounded-2xl border border-gray-200 bg-white p-3 dark:border-gray-800 dark:bg-white/[0.03] transition-shadow hover:shadow-md">
                            {{-- Image --}}
                            <button wire:click="openDetail({{ $commodity->id }})" type="button" class="shrink-0 focus:outline-none">
                                @if($commodity->image_path)
                                    <img src="{{ Storage::url($commodity->image_path) }}"
                                         alt="{{ $commodity->name }}"
                                         class="w-20 h-20 rounded-xl object-cover {{ $outOfStock ? 'opacity-50' : '' }}" />
                                @else
                                    <div class="flex items-center justify-center w-20 h-20 rounded-xl bg-gray-100 dark:bg-gray-800 {{ $outOfStock ? 'opacity-50' : '' }}">
                                        <x-heroicon-o-photo class="w-8 h-8 text-gray-300 dark:text-gray-600" />
                                    </div>
                                @endif
                            </button>

                            {{-- Info --}}
                            <div class="flex-1 min-w-0">
                                <button wire:click="openDetail({{ $commodity->id }})"
                                        type="button"
                                        class="text-left text-theme-sm font-semibold text-gray-800 hover:text-brand-500 dark:text-white/90 dark:hover:text-brand-400 transition-colors truncate block w-full">
                                    {{ $commodity->name }}
                                </button>
                                <span class="text-theme-xs text-gray-400 dark:text-gray-500">{{ $commodity->category->name ?? '' }}</span>

                                {{-- Stock badge --}}
                                <div class="mt-1">
                                    @if($outOfStock)
                                        <span class="inline-flex items-center gap-1 rounded-full bg-error-50 px-2 py-0.5 text-xs font-medium text-error-600 dark:bg-error-500/15 dark:text-error-400">
                                            Není v lednici
                                        </span>
                                    @elseif($fridge <= 3)
                                        <span class="inline-flex items-center gap-1 rounded-full bg-warning-50 px-2 py-0.5 text-xs font-medium text-warning-600 dark:bg-warning-500/15 dark:text-warning-400">
                                            Posledních {{ $fridge }} ks
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 rounded-full bg-success-50 px-2 py-0.5 text-xs font-medium text-success-600 dark:bg-success-500/15 dark:text-success-400">
                                            Skladem: {{ $fridge }} ks
                                        </span>
                                    @endif
                                </div>
                            </div>

                            {{-- Price + Add --}}
                            <div class="flex items-center gap-4 shrink-0">
                                <p class="text-base font-bold text-gray-800 dark:text-white/90">
                                    {{ number_format($commodity->price / 100, 2, ',', ' ') }} Kč
                                </p>
                                <button wire:click="addToCart({{ $commodity->id }})"
                                        type="button"
                                        {{ $outOfStock ? 'disabled' : '' }}
                                        class="inline-flex items-center gap-1.5 rounded-lg px-3 py-2 text-theme-xs font-medium transition-colors
                                            {{ $outOfStock
                                                ? 'bg-gray-100 text-gray-400 cursor-not-allowed dark:bg-gray-800 dark:text-gray-600'
                                                : 'border border-gray-200 text-gray-600 hover:border-brand-300 hover:text-brand-500 hover:bg-brand-50 dark:border-gray-700 dark:text-gray-400 dark:hover:border-brand-500 dark:hover:text-brand-400 dark:hover:bg-brand-500/10' }}">
                                    <x-heroicon-o-plus class="w-4 h-4" />
                                    Přidat
                                </button>
                            </div>
                        </div>
                    @empty
                        <div class="py-16 text-center">
                            <x-heroicon-o-magnifying-glass class="mx-auto mb-3 w-12 h-12 text-gray-300 dark:text-gray-600" />
                            <p class="text-theme-sm font-medium text-gray-700 dark:text-white/70">Žádné produkty nebyly nalezeny</p>
                            <p class="mt-1 text-theme-xs text-gray-500 dark:text-gray-400">Zkuste změnit filtr nebo hledaný výraz.</p>
                        </div>
                    @endforelse
                </div>
            @endif

            {{-- Pagination --}}
            <div class="mt-6">
                {{ $commodities->links() }}
            </div>
        </div>
    </div>

    {{-- Product detail --}}
    @if($selectedCommodity)
        @php
            $sc = $selectedCommodity;
            $scFridge = $sc->fridge_quantity;
            $scWhouse = $sc->warehouse_quantity;
            $scOut = $scFridge <= 0;
        @endphp

        <div
            x-data="{ show: false }"
            x-init="document.body.classList.add('overflow-hidden'); $nextTick(() => show = true)"
            x-on:keydown.escape.window="$wire.closeDetail()"
            x-on:remove="document.body.classList.remove('overflow-hidden')"
            class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4"
        >
            {{-- Backdrop --}}
            <div
                x-show="show"
                x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                wire:click="closeDetail"
                class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm"
            ></div>

            {{-- Modal panel --}}
            <div
                x-show="show"
                x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                @click.stop
                class="relative w-full sm:max-w-2xl rounded-t-3xl sm:rounded-3xl border border-gray-200 bg-white shadow-2xl dark:border-gray-800 dark:bg-gray-dark overflow-hidden flex flex-col max-h-[90dvh]"
            >
                {{-- Close button --}}
                <button
                    wire:click="closeDetail"
                    class="absolute right-4 top-4 z-10 flex h-9 w-9 items-center justify-center rounded-full bg-gray-100 text-gray-500 hover:bg-gray-200 hover:text-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white transition-colors"
                >
                    <x-heroicon-o-x-mark class="w-5 h-5" />
                </button>

                <div class="flex flex-col sm:flex-row flex-1 min-h-0">
                    {{-- Image side --}}
                    <div class="sm:w-2/5 shrink-0 overflow-hidden">
                        @if($sc->image_path)
                            <img src="{{ Storage::url($sc->image_path) }}"
                                 alt="{{ $sc->name }}"
                                 class="w-full h-52 sm:h-full object-cover {{ $scOut ? 'opacity-60' : '' }}" />
                        @else
                            <div class="flex items-center justify-center w-full h-52 sm:h-full min-h-[200px] bg-gray-100 dark:bg-gray-800">
                                <x-heroicon-o-photo class="w-16 h-16 text-gray-300 dark:text-gray-600" />
                            </div>
                        @endif
                    </div>

                    {{-- Content side --}}
                    <div class="flex-1 overflow-y-auto p-6">
                        {{-- Category --}}
                        <span class="text-theme-xs font-semibold uppercase tracking-wide text-brand-500 dark:text-brand-400">
                            {{ $sc->category->name ?? 'Bez kategorie' }}
                        </span>

                        {{-- Name --}}
                        <h2 class="mt-1.5 text-xl font-bold text-gray-900 dark:text-white pr-8">
                            {{ $sc->name }}
                        </h2>

                        {{-- Description --}}
                        @if($sc->description)
                            <p class="mt-3 text-theme-sm text-gray-600 dark:text-gray-400 leading-relaxed">
                                {{ $sc->description }}
                            </p>
                        @endif

                        {{-- Price --}}
                        <p class="mt-4 text-3xl font-bold text-brand-500">
                            {{ number_format($sc->price / 100, 2, ',', ' ') }} Kč
                        </p>

                        <div class="my-4 border-t border-gray-100 dark:border-gray-800"></div>

                        {{-- Stock section --}}
                        <div class="space-y-2.5">
                            <p class="text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                Dostupnost
                            </p>

                            {{-- Fridge row --}}
                            <div class="flex items-center justify-between">
                                <span class="flex items-center gap-2 text-theme-sm text-gray-600 dark:text-gray-400">
                                    <x-heroicon-o-cube class="w-4 h-4" />
                                    V lednici
                                </span>
                                @if($scOut)
                                    <span class="inline-flex items-center gap-1 rounded-full bg-error-50 px-2.5 py-1 text-xs font-semibold text-error-600 dark:bg-error-500/15 dark:text-error-400">
                                        <x-heroicon-o-x-circle class="w-3.5 h-3.5" />
                                        Není v lednici
                                    </span>
                                @elseif($scFridge <= 3)
                                    <span class="inline-flex items-center gap-1 rounded-full bg-warning-50 px-2.5 py-1 text-xs font-semibold text-warning-600 dark:bg-warning-500/15 dark:text-warning-400">
                                        <x-heroicon-o-exclamation-triangle class="w-3.5 h-3.5" />
                                        Posledních {{ $scFridge }} ks
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 rounded-full bg-success-50 px-2.5 py-1 text-xs font-semibold text-success-600 dark:bg-success-500/15 dark:text-success-400">
                                        <x-heroicon-o-check-circle class="w-3.5 h-3.5" />
                                        {{ $scFridge }} ks k dispozici
                                    </span>
                                @endif
                            </div>

                            {{-- Warehouse row --}}
                            <div class="flex items-center justify-between">
                                <span class="flex items-center gap-2 text-theme-sm text-gray-600 dark:text-gray-400">
                                    <x-heroicon-o-archive-box class="w-4 h-4" />
                                    Ve skladu
                                </span>
                                @if($scWhouse > 0)
                                    <span class="inline-flex items-center gap-1 rounded-full bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-600 dark:bg-white/[0.08] dark:text-gray-400">
                                        {{ $scWhouse }} ks v zásobě
                                    </span>
                                @else
                                    <span class="text-theme-xs text-gray-400 dark:text-gray-500">Žádné zásoby</span>
                                @endif
                            </div>
                        </div>

                        <div class="my-4 border-t border-gray-100 dark:border-gray-800"></div>

                        {{-- CTA button --}}
                        @if($scOut)
                            <button disabled
                                    class="w-full rounded-xl bg-gray-100 px-4 py-3 text-sm font-medium text-gray-400 cursor-not-allowed dark:bg-gray-800 dark:text-gray-600">
                                Není v lednici
                            </button>
                        @else
                            <button wire:click="addToCart({{ $sc->id }})"
                                    class="flex w-full items-center justify-center gap-2 rounded-xl bg-brand-500 px-4 py-3 text-sm font-semibold text-white shadow-theme-xs hover:bg-brand-600 transition-colors">
                                <x-heroicon-o-shopping-bag class="w-4 h-4" />
                                Přidat do košíku
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>

    @endif

</div>
