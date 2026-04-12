<div>
    <x-common.page-breadcrumb :pageTitle="'Přehled'" />

    {{-- Stats cards --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4 md:gap-6">

        {{-- Orders this month --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-theme-sm text-gray-500 dark:text-gray-400">Nákupy tento měsíc</span>
                    <h4 class="mt-1 text-2xl font-bold text-gray-800 dark:text-white/90">
                        {{ $ordersThisMonth }}
                    </h4>
                </div>
                <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-brand-50 dark:bg-brand-500/10">
                    <x-heroicon-o-shopping-cart class="w-6 h-6 text-brand-500" />
                </div>
            </div>
        </div>

        {{-- Spent this month --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-theme-sm text-gray-500 dark:text-gray-400">Útrata tento měsíc</span>
                    <h4 class="mt-1 text-2xl font-bold text-gray-800 dark:text-white/90">
                        {{ number_format($spentThisMonth / 100, 2, ',', ' ') }} Kč
                    </h4>
                </div>
                <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-success-50 dark:bg-success-500/10">
                    <x-heroicon-o-banknotes class="w-6 h-6 text-success-500" />
                </div>
            </div>
        </div>

        {{-- Unpaid debt --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-theme-sm text-gray-500 dark:text-gray-400">Nesplacený dluh</span>
                    <h4 class="mt-1 text-2xl font-bold {{ $unpaidDebt > 0 ? 'text-error-500' : 'text-success-500' }}">
                        {{ number_format($unpaidDebt / 100, 2, ',', ' ') }} Kč
                    </h4>
                </div>
                <div class="flex items-center justify-center w-12 h-12 rounded-xl {{ $unpaidDebt > 0 ? 'bg-error-50 dark:bg-error-500/10' : 'bg-success-50 dark:bg-success-500/10' }}">
                    <x-heroicon-o-exclamation-triangle class="w-6 h-6 {{ $unpaidDebt > 0 ? 'text-error-500' : 'text-success-500' }}" />
                </div>
            </div>
            @if($unpaidDebt > 0)
                <a href="{{ route('my-debts') }}" class="mt-3 flex items-center gap-1 text-theme-xs font-medium text-brand-500 hover:text-brand-600">
                    Zobrazit dluhy <x-heroicon-o-arrow-right class="w-3 h-3" />
                </a>
            @endif
        </div>

        {{-- Active lunches --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-theme-sm text-gray-500 dark:text-gray-400">Aktivní obědy</span>
                    <h4 class="mt-1 text-2xl font-bold text-gray-800 dark:text-white/90">
                        {{ $activeLunches }}
                    </h4>
                </div>
                <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-warning-50 dark:bg-warning-500/10">
                    <x-heroicon-o-cake class="w-6 h-6 text-warning-500" />
                </div>
            </div>
        </div>

    </div>

    {{-- Lower section under the cards --}}
    <div class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-2">

        {{-- Top 3 most bought commodities --}}
        <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center gap-3 px-6 py-4 border-b border-gray-200 dark:border-gray-800">
                <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-brand-50 dark:bg-brand-500/10">
                    <x-heroicon-o-trophy class="w-4 h-4 text-brand-500" />
                </span>
                <div>
                    <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">Nejkupovanější produkty</h3>
                    <p class="text-theme-xs text-gray-500 dark:text-gray-400">Tvoje top 3 za celou dobu</p>
                </div>
            </div>

            <div class="px-6 py-4">
                @forelse($topProducts as $index => $item)
                    <div class="flex items-center gap-4 {{ !$loop->last ? 'mb-4' : '' }}">
                        {{-- Order --}}
                        <span class="flex items-center justify-center w-8 h-8 rounded-full text-theme-xs font-bold shrink-0
                            {{ $index === 0 ? 'bg-yellow-100 text-yellow-600 dark:bg-yellow-500/10 dark:text-yellow-400' :
                               ($index === 1 ? 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300' :
                               'bg-orange-100 text-orange-600 dark:bg-orange-500/10 dark:text-orange-400') }}">
                            {{ $index + 1 }}
                        </span>

                        {{-- Image --}}
                        @if($item->commodity?->image_path)
                            <img src="{{ asset('storage/' . $item->commodity->image_path) }}"
                                 alt="{{ $item->commodity->name }}"
                                 class="w-10 h-10 rounded-lg object-cover shrink-0">
                        @else
                            <div class="w-10 h-10 rounded-lg bg-gray-100 dark:bg-gray-800 flex items-center justify-center shrink-0">
                                <x-heroicon-o-cube class="w-5 h-5 text-gray-400" />
                            </div>
                        @endif

                        {{-- Name + count --}}
                        <div class="flex-1 min-w-0">
                            <p class="text-theme-sm font-medium text-gray-800 dark:text-white/90 truncate">
                                {{ $item->commodity?->name ?? 'Smazaný produkt' }}
                            </p>
                            <p class="text-theme-xs text-gray-500 dark:text-gray-400">
                                {{ $item->total_quantity }} {{ $item->total_quantity == 1 ? 'kus' : ($item->total_quantity <= 4 ? 'kusy' : 'kusů') }} celkem
                            </p>
                        </div>

                        {{-- Price for one --}}
                        @if($item->commodity)
                            <span class="text-theme-sm font-medium text-gray-600 dark:text-gray-400 shrink-0">
                                {{ number_format($item->commodity->price / 100, 2, ',', ' ') }} Kč
                            </span>
                        @endif
                    </div>
                @empty
                    <div class="flex flex-col items-center justify-center py-8 text-center">
                        <x-heroicon-o-shopping-cart class="w-10 h-10 text-gray-300 dark:text-gray-600 mb-2" />
                        <p class="text-theme-sm text-gray-500 dark:text-gray-400">Zatím žádné nákupy</p>
                        <a href="{{ route('catalog') }}" class="mt-2 text-theme-xs font-medium text-brand-500 hover:text-brand-600">
                            Přejít do katalogu
                        </a>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Recents lucnhes --}}
        <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-800">
                <div class="flex items-center gap-3">
                    <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-warning-50 dark:bg-warning-500/10">
                        <x-heroicon-o-cake class="w-4 h-4 text-warning-500" />
                    </span>
                    <div>
                        <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">Moje obědy</h3>
                        <p class="text-theme-xs text-gray-500 dark:text-gray-400">Poslední obědy kde se účastníš</p>
                    </div>
                </div>
                <a href="{{ route('lunches') }}" class="text-theme-xs font-medium text-brand-500 hover:text-brand-600">
                    Zobrazit vše
                </a>
            </div>

            <div class="px-6 py-4">
                @forelse($recentLunches as $lunch)
                    <div class="flex items-center gap-4 {{ !$loop->last ? 'mb-4 pb-4 border-b border-gray-100 dark:border-gray-800' : '' }}">
                        <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-warning-50 dark:bg-warning-500/10 shrink-0">
                            <x-heroicon-o-cake class="w-5 h-5 text-warning-500" />
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-theme-sm font-medium text-gray-800 dark:text-white/90 truncate">
                                {{ $lunch->restaurant_name }}
                            </p>
                            <p class="text-theme-xs text-gray-500 dark:text-gray-400">
                                Organizátor: {{ $lunch->organizer->name }}
                                · {{ $lunch->participants->count() }} {{ $lunch->participants->count() == 1 ? 'účastník' : ($lunch->participants->count() <= 4 ? 'účastníci' : 'účastníků') }}
                            </p>
                        </div>
                        <div class="shrink-0">
                            @if($lunch->isSettled())
                                <span class="inline-flex items-center gap-1 rounded-full bg-success-50 px-2.5 py-1 text-theme-xs font-medium text-success-600 dark:bg-success-500/10 dark:text-success-400">
                                    <x-heroicon-o-check-circle class="w-3.5 h-3.5" />
                                    Srovnáno
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 rounded-full bg-warning-50 px-2.5 py-1 text-theme-xs font-medium text-warning-600 dark:bg-warning-500/10 dark:text-warning-400">
                                    <x-heroicon-o-clock class="w-3.5 h-3.5" />
                                    Čeká
                                </span>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="flex flex-col items-center justify-center py-8 text-center">
                        <x-heroicon-o-cake class="w-10 h-10 text-gray-300 dark:text-gray-600 mb-2" />
                        <p class="text-theme-sm text-gray-500 dark:text-gray-400">Zatím žádné obědy</p>
                        <a href="{{ route('lunches.create') }}" class="mt-2 text-theme-xs font-medium text-brand-500 hover:text-brand-600">
                            Vytvořit oběd
                        </a>
                    </div>
                @endforelse
            </div>
        </div>

    </div>
</div>
