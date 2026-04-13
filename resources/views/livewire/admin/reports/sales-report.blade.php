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
                    {{ $rows->count() }} {{ $rows->count() === 1 ? 'produkt' : ($rows->count() < 5 ? 'produkty' : 'produktů') }}
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

        {{-- Footer total row --}}
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
    </div>
</div>
