<div>
    <x-common.page-breadcrumb :pageTitle="'Přehled prodejů'" />

    {{-- Date range bar --}}
    <div class="mb-6 flex flex-wrap items-center gap-3 rounded-2xl border border-gray-200 bg-white px-5 py-3.5 dark:border-gray-800 dark:bg-white/[0.03]">
        <span class="text-theme-sm font-medium text-gray-600 dark:text-gray-400">Období:</span>

        {{-- Preset buttons --}}
        <div class="flex flex-wrap gap-2">
            @foreach(['month' => 'Tento měsíc', 'quarter' => 'Čtvrtletí', 'year' => 'Tento rok'] as $preset => $label)
                <button wire:click="setPreset('{{ $preset }}')"
                        class="rounded-lg px-3 py-1.5 text-theme-xs font-medium transition-colors
                            {{ $datePreset === $preset
                                ? 'bg-brand-500 text-white'
                                : 'border border-gray-300 bg-white text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:bg-transparent dark:text-gray-400 dark:hover:bg-white/[0.04]' }}">
                    {{ $label }}
                </button>
            @endforeach
        </div>

        <span class="text-gray-300 dark:text-gray-700 select-none">|</span>

        {{-- Flatpickr range picker.
        wire:key forces re-init when dates change --}}
        <div wire:ignore
             wire:key="datepicker-{{ $datePreset }}-{{ $from->format('Ymd') }}-{{ $to->format('Ymd') }}"
             x-data="{
                 init() {
                     const wire = $wire;
                     flatpickr(this.$refs.rangeInput, {
                         mode: 'range',
                         dateFormat: 'Y-m-d',
                         altInput: true,
                         altFormat: 'd. m. Y',
                         disableMobile: true,
                         defaultDate: ['{{ $from->format('Y-m-d') }}', '{{ $to->format('Y-m-d') }}'],
                         onChange: function(dates) {
                             if (dates.length === 2) {
                                 var fmt = function(d) {
                                     return [
                                         d.getFullYear(),
                                         String(d.getMonth()+1).padStart(2,'0'),
                                         String(d.getDate()).padStart(2,'0')
                                     ].join('-');
                                 };
                                 wire.call('setCustomRange', fmt(dates[0]), fmt(dates[1]));
                             }
                         }
                     });
                 }
             }">
            <div class="relative">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none z-10">
                    <x-heroicon-o-calendar-days class="w-4 h-4 text-gray-400" />
                </span>
                <input type="text" x-ref="rangeInput"
                       placeholder="Vyberte vlastní rozmezí..."
                       class="h-9 w-64 cursor-pointer rounded-lg border pl-9 pr-3 text-theme-xs shadow-theme-xs focus:outline-none focus:ring-2 focus:ring-brand-500/10
                           {{ $datePreset === 'custom'
                               ? 'border-brand-400 bg-brand-50 text-brand-700 dark:border-brand-600 dark:bg-brand-500/10 dark:text-brand-300'
                               : 'border-gray-300 bg-transparent text-gray-700 hover:border-gray-400 dark:border-gray-700 dark:text-gray-300' }}" />
            </div>
        </div>
    </div>

    {{-- Table --}}
    <div class="rounded-2xl border border-gray-200 bg-white pt-4 dark:border-gray-800 dark:bg-white/[0.03]">

        {{-- Toolbar --}}
        <div class="flex flex-col gap-3 px-5 mb-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
            <div>
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Prodeje produktů</h3>
                <p class="mt-0.5 text-theme-sm text-gray-500 dark:text-gray-400">
                    {{ $totalCount }} {{ $totalCount === 1 ? 'produkt' : ($totalCount < 5 ? 'produkty' : 'produktů') }}
                    · celkem {{ $grandQty }} ks · {{ number_format($grandTotal / 100, 2, ',', ' ') }} Kč
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-2">

                {{-- Search --}}
                <div class="relative">
                    <span class="absolute -translate-y-1/2 left-3 top-1/2 pointer-events-none">
                        <x-heroicon-o-magnifying-glass class="w-4 h-4 text-gray-400" />
                    </span>
                    <input type="text" wire:model.live.debounce.300ms="search"
                           placeholder="Hledat produkt..."
                           class="h-[38px] w-48 rounded-lg border border-gray-300 bg-transparent py-2 pl-9 pr-3 text-sm text-gray-800 placeholder:text-gray-400 focus:border-brand-300 focus:outline-none focus:ring-2 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
                </div>

                <button wire:click="exportExcel"
                        class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-theme-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03]">
                    <x-heroicon-o-table-cells class="h-4 w-4" />
                    Excel
                </button>
                <button wire:click="exportCsv"
                        class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-theme-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03]">
                    <x-heroicon-o-arrow-down-tray class="h-4 w-4" />
                    CSV
                </button>
            </div>
        </div>

        {{-- Table --}}
        <div class="max-w-full overflow-x-auto">
            <table class="min-w-full">
                <thead>
                    <tr class="border-y border-gray-200 dark:border-gray-700">
                        <th class="px-5 py-3 text-left text-theme-xs font-medium text-gray-500 dark:text-gray-400">Produkt</th>
                        <th class="px-5 py-3 text-right text-theme-xs font-medium text-gray-500 dark:text-gray-400">Počet prodaných ks</th>
                        <th class="px-5 py-3 text-right text-theme-xs font-medium text-gray-500 dark:text-gray-400">Cena za kus</th>
                        <th class="px-5 py-3 text-right text-theme-xs font-medium text-gray-500 dark:text-gray-400">Celková tržba</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($rows as $row)
                        <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02]">

                            {{-- Product --}}
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-3">
                                    @if($row->commodity?->image_path)
                                        <img src="{{ asset('storage/' . $row->commodity->image_path) }}"
                                             alt="{{ $row->commodity->name }}"
                                             class="w-9 h-9 rounded-lg object-cover shrink-0">
                                    @else
                                        <div class="w-9 h-9 rounded-lg bg-gray-100 dark:bg-gray-800 flex items-center justify-center shrink-0">
                                            <x-heroicon-o-cube class="w-4 h-4 text-gray-400" />
                                        </div>
                                    @endif
                                    <span class="text-theme-sm font-medium text-gray-800 dark:text-white/90">
                                        {{ $row->commodity?->name ?? 'Smazaný produkt' }}
                                    </span>
                                </div>
                            </td>

                            {{-- Qty --}}
                            <td class="px-5 py-3.5 text-right whitespace-nowrap">
                                <span class="text-theme-sm font-semibold text-gray-800 dark:text-white/90">
                                    {{ $row->total_qty }} ks
                                </span>
                            </td>

                            {{-- Unit price --}}
                            <td class="px-5 py-3.5 text-right whitespace-nowrap">
                                <span class="text-theme-sm text-gray-600 dark:text-gray-400">
                                    {{ number_format($row->unit_price / 100, 2, ',', ' ') }} Kč
                                </span>
                            </td>

                            {{-- Total revenue --}}
                            <td class="px-5 py-3.5 text-right whitespace-nowrap">
                                <span class="text-theme-sm font-semibold text-brand-600 dark:text-brand-400">
                                    {{ number_format($row->total_revenue / 100, 2, ',', ' ') }} Kč
                                </span>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-16 text-center">
                                <x-heroicon-o-shopping-cart class="mx-auto mb-3 h-10 w-10 text-gray-300 dark:text-gray-600" />
                                <p class="text-theme-sm font-medium text-gray-700 dark:text-white/70">Žádné prodeje v tomto období</p>
                                <p class="mt-1 text-theme-xs text-gray-500 dark:text-gray-400">Zkuste změnit vybrané období.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Footer total row + pagination --}}
        @if($rows->isNotEmpty())
            <div class="flex items-center justify-between border-t border-gray-100 px-5 py-3.5 sm:px-6 dark:border-gray-800">
                <span class="text-theme-sm font-semibold text-gray-800 dark:text-white/90">
                    Celkem: <span class="text-gray-500 dark:text-gray-400 font-normal">{{ $grandQty }} ks</span>
                </span>
                <span class="text-theme-sm font-semibold text-brand-600 dark:text-brand-400">
                    {{ number_format($grandTotal / 100, 2, ',', ' ') }} Kč
                </span>
            </div>
        @endif

        @if($rows->hasPages())
            <div class="px-5 py-4 border-t border-gray-200 dark:border-white/[0.05] sm:px-6">
                <div class="flex items-center justify-between">
                    <p class="text-theme-sm text-gray-500 dark:text-gray-400">
                        Zobrazeno <span class="font-medium text-gray-800 dark:text-white/90">{{ $rows->firstItem() }}</span>
                        až <span class="font-medium text-gray-800 dark:text-white/90">{{ $rows->lastItem() }}</span>
                        z <span class="font-medium text-gray-800 dark:text-white/90">{{ $rows->total() }}</span>
                    </p>
                    <div class="flex items-center gap-2">
                        @if($rows->onFirstPage())
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
                        <div class="hidden items-center gap-0.5 sm:flex">
                            @foreach($rows->getUrlRange(1, $rows->lastPage()) as $page => $url)
                                @if($page == $rows->currentPage())
                                    <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-brand-500 text-theme-sm font-medium text-white">{{ $page }}</span>
                                @else
                                    <button wire:click="gotoPage({{ $page }})" class="flex h-9 w-9 items-center justify-center rounded-lg text-theme-sm font-medium text-gray-700 hover:bg-brand-500/[0.08] hover:text-brand-500 dark:text-gray-400 dark:hover:text-brand-500">{{ $page }}</button>
                                @endif
                            @endforeach
                        </div>
                        <span class="block text-sm font-medium text-gray-700 dark:text-gray-400 sm:hidden">{{ $rows->currentPage() }} / {{ $rows->lastPage() }}</span>
                        @if($rows->hasMorePages())
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
</div>
