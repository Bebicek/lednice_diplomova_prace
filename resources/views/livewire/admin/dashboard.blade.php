<div>
    <x-common.page-breadcrumb :pageTitle="'Přehled'" />

    {{-- Stats cards --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4 md:gap-6">

        {{-- Total products --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-theme-sm text-gray-500 dark:text-gray-400">Celkem produktů</span>
                    <h4 class="mt-1 text-2xl font-bold text-gray-800 dark:text-white/90">
                        {{ $totalProducts }}
                    </h4>
                </div>
                <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-brand-50 dark:bg-brand-500/10">
                    <x-heroicon-o-cube class="w-6 h-6 text-brand-500" />
                </div>
            </div>
            <a href="{{ route('admin.commodities') }}" class="mt-3 flex items-center gap-1 text-theme-xs font-medium text-brand-500 hover:text-brand-600">
                Spravovat produkty <x-heroicon-o-arrow-right class="w-3 h-3" />
            </a>
        </div>

        {{-- Low stock --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-theme-sm text-gray-500 dark:text-gray-400">Nízký sklad</span>
                    <h4 class="mt-1 text-2xl font-bold {{ $lowStockCount > 0 ? 'text-warning-500' : 'text-success-500' }}">
                        {{ $lowStockCount }}
                    </h4>
                </div>
                <div class="flex items-center justify-center w-12 h-12 rounded-xl {{ $lowStockCount > 0 ? 'bg-warning-50 dark:bg-warning-500/10' : 'bg-success-50 dark:bg-success-500/10' }}">
                    <x-heroicon-o-archive-box class="w-6 h-6 {{ $lowStockCount > 0 ? 'text-warning-500' : 'text-success-500' }}" />
                </div>
            </div>
            <a href="{{ route('admin.stock') }}" class="mt-3 flex items-center gap-1 text-theme-xs font-medium text-brand-500 hover:text-brand-600">
                Zobrazit sklad <x-heroicon-o-arrow-right class="w-3 h-3" />
            </a>
        </div>

        {{-- Active users --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-theme-sm text-gray-500 dark:text-gray-400">Aktivní uživatelé</span>
                    <h4 class="mt-1 text-2xl font-bold text-gray-800 dark:text-white/90">
                        {{ $activeUsers }}
                    </h4>
                </div>
                <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-success-50 dark:bg-success-500/10">
                    <x-heroicon-o-users class="w-6 h-6 text-success-500" />
                </div>
            </div>
            <a href="{{ route('admin.users') }}" class="mt-3 flex items-center gap-1 text-theme-xs font-medium text-brand-500 hover:text-brand-600">
                Spravovat uživatele <x-heroicon-o-arrow-right class="w-3 h-3" />
            </a>
        </div>

        {{-- Expiring commodities --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-theme-sm text-gray-500 dark:text-gray-400">Brzy expiruje</span>
                    <h4 class="mt-1 text-2xl font-bold {{ $expiringCount > 0 ? 'text-error-500' : 'text-success-500' }}">
                        {{ $expiringCount }}
                    </h4>
                </div>
                <div class="flex items-center justify-center w-12 h-12 rounded-xl {{ $expiringCount > 0 ? 'bg-error-50 dark:bg-error-500/10' : 'bg-success-50 dark:bg-success-500/10' }}">
                    <x-heroicon-o-calendar-days class="w-6 h-6 {{ $expiringCount > 0 ? 'text-error-500' : 'text-success-500' }}" />
                </div>
            </div>
            <a href="{{ route('admin.expiry') }}" class="mt-3 flex items-center gap-1 text-theme-xs font-medium text-brand-500 hover:text-brand-600">
                Zobrazit expiraci <x-heroicon-o-arrow-right class="w-3 h-3" />
            </a>
        </div>

    </div>

    {{-- Bottom panels --}}
    <div class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-2">

        {{-- Empty fridge products --}}
        <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-800">
                <div class="flex items-center gap-3">
                    <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-error-50 dark:bg-error-500/10">
                        <x-heroicon-o-archive-box-x-mark class="w-4 h-4 text-error-500" />
                    </span>
                    <div>
                        <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">Produkty s 0 ks v lednici</h3>
                        <p class="text-theme-xs text-gray-500 dark:text-gray-400">
                            {{ $emptyFridgeCount }} {{ $emptyFridgeCount === 1 ? 'produkt' : ($emptyFridgeCount <= 4 ? 'produkty' : 'produktů') }} bez zásoby
                        </p>
                    </div>
                </div>
                <a href="{{ route('admin.stock') }}" class="text-theme-xs font-medium text-brand-500 hover:text-brand-600">
                    Doplnit sklad
                </a>
            </div>

            <div class="px-6 py-4">
                @forelse($emptyFridgeProducts as $product)
                    <div class="flex items-center gap-3 {{ !$loop->last ? 'mb-3 pb-3 border-b border-gray-100 dark:border-gray-800' : '' }}">
                        @if($product->image_path)
                            <img src="{{ asset('storage/' . $product->image_path) }}"
                                 alt="{{ $product->name }}"
                                 class="w-9 h-9 rounded-lg object-cover shrink-0">
                        @else
                            <div class="w-9 h-9 rounded-lg bg-gray-100 dark:bg-gray-800 flex items-center justify-center shrink-0">
                                <x-heroicon-o-cube class="w-4 h-4 text-gray-400" />
                            </div>
                        @endif
                        <p class="flex-1 text-theme-sm font-medium text-gray-800 dark:text-white/90 truncate">
                            {{ $product->name }}
                        </p>
                        <span class="text-theme-xs font-semibold text-error-500">0 ks</span>
                    </div>
                @empty
                    <div class="flex flex-col items-center justify-center py-8 text-center">
                        <x-heroicon-o-check-circle class="w-10 h-10 text-success-400 mb-2" />
                        <p class="text-theme-sm text-gray-500 dark:text-gray-400">Všechny produkty jsou skladem</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Recently paid debts --}}
        <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-800">
                <div class="flex items-center gap-3">
                    <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-success-50 dark:bg-success-500/10">
                        <x-heroicon-o-check-badge class="w-4 h-4 text-success-500" />
                    </span>
                    <div>
                        <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">Nedávno zaplacené dluhy</h3>
                        <p class="text-theme-xs text-gray-500 dark:text-gray-400">Poslední splátky</p>
                    </div>
                </div>
                <a href="{{ route('admin.accounts') }}" class="text-theme-xs font-medium text-brand-500 hover:text-brand-600">
                    Zobrazit vše
                </a>
            </div>

            <div class="px-6 py-4">
                @forelse($recentlyPaidDebts as $debt)
                    <div class="flex items-center gap-4 {{ !$loop->last ? 'mb-4 pb-4 border-b border-gray-100 dark:border-gray-800' : '' }}">
                        <div class="flex items-center justify-center w-9 h-9 rounded-full bg-success-50 dark:bg-success-500/10 shrink-0">
                            <x-heroicon-o-check class="w-4 h-4 text-success-500" />
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-theme-sm font-medium text-gray-800 dark:text-white/90 truncate">
                                {{ $debt->user?->name ?? 'Neznámý' }}
                            </p>
                            <p class="text-theme-xs text-gray-500 dark:text-gray-400">
                                {{ $debt->paid_at?->locale('cs')->diffForHumans() }}
                                @if($debt->creditor)
                                    · věřitel: {{ $debt->creditor->name }}
                                @endif
                            </p>
                        </div>
                        <span class="text-theme-sm font-semibold text-success-600 dark:text-success-400 shrink-0">
                            {{ number_format($debt->amount / 100, 2, ',', ' ') }} Kč
                        </span>
                    </div>
                @empty
                    <div class="flex flex-col items-center justify-center py-8 text-center">
                        <x-heroicon-o-banknotes class="w-10 h-10 text-gray-300 dark:text-gray-600 mb-2" />
                        <p class="text-theme-sm text-gray-500 dark:text-gray-400">Zatím žádné zaplacené dluhy</p>
                    </div>
                @endforelse
            </div>
        </div>

    </div>
</div>
