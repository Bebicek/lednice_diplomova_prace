<div>
    <x-common.page-breadcrumb :pageTitle="'Datum spotřeby'" />

    {{-- Stats cards --}}
    <div class="grid grid-cols-2 gap-4 xl:grid-cols-4 md:gap-6 mb-6">

        {{-- Expired --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-sm text-gray-500 dark:text-gray-400">Prošlé</span>
                    <h4 class="mt-2 text-2xl font-bold text-gray-800 dark:text-white/90">{{ $stats['expired'] }}</h4>
                </div>
                <div class="flex items-center justify-center w-12 h-12 rounded-xl
                    {{ $stats['expired'] > 0 ? 'bg-error-50 text-error-500 dark:bg-error-500/10' : 'bg-gray-100 text-gray-400 dark:bg-white/5' }}">
                    <x-heroicon-o-x-circle class="w-6 h-6" />
                </div>
            </div>
        </div>

        {{-- Critical (≤ 3 days) --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-sm text-gray-500 dark:text-gray-400">Vyprší do 3 dní</span>
                    <h4 class="mt-2 text-2xl font-bold text-gray-800 dark:text-white/90">{{ $stats['critical'] }}</h4>
                </div>
                <div class="flex items-center justify-center w-12 h-12 rounded-xl
                    {{ $stats['critical'] > 0 ? 'bg-error-50 text-error-500 dark:bg-error-500/10' : 'bg-gray-100 text-gray-400 dark:bg-white/5' }}">
                    <x-heroicon-o-exclamation-triangle class="w-6 h-6" />
                </div>
            </div>
        </div>

        {{-- Warning (≤ 7 days) --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-sm text-gray-500 dark:text-gray-400">Vyprší do 7 dní</span>
                    <h4 class="mt-2 text-2xl font-bold text-gray-800 dark:text-white/90">{{ $stats['warning'] }}</h4>
                </div>
                <div class="flex items-center justify-center w-12 h-12 rounded-xl
                    {{ $stats['warning'] > 0 ? 'bg-warning-50 text-warning-500 dark:bg-warning-500/10' : 'bg-gray-100 text-gray-400 dark:bg-white/5' }}">
                    <x-heroicon-o-clock class="w-6 h-6" />
                </div>
            </div>
        </div>

        {{-- No date --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-sm text-gray-500 dark:text-gray-400">Bez data</span>
                    <h4 class="mt-2 text-2xl font-bold text-gray-800 dark:text-white/90">{{ $stats['none'] }}</h4>
                </div>
                <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-gray-100 text-gray-400 dark:bg-white/5">
                    <x-heroicon-o-calendar-days class="w-6 h-6" />
                </div>
            </div>
        </div>
    </div>

    {{-- Product table --}}
    <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">

        {{-- Table header --}}
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 dark:border-gray-800 md:px-6">
            <div>
                <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">Přehled dat spotřeby</h3>
                <p class="mt-0.5 text-xs text-gray-400 dark:text-gray-500">Seřazeno dle data spotřeby - nejdříve vypršené</p>
            </div>

            {{-- Filter --}}
            <select wire:model.live="filterStatus"
                    class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                <option value="all">Všechny</option>
                <option value="expired">Prošlé</option>
                <option value="critical">Do 3 dní</option>
                <option value="warning">Do 7 dní</option>
                <option value="ok">V pořádku</option>
                <option value="none">Bez data</option>
            </select>
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead class="border-b border-gray-100 dark:border-gray-800">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400">Produkt</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400">Kategorie</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400">Datum spotřeby</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400">Zbývá</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400">Stav</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400">Ks v lednici</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400">Akce</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                @forelse($commodities as $commodity)
                    @php
                        $status = $commodity->expiry_status;
                        $days = $commodity->days_until_expiry;
                    @endphp
                    <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors">

                        {{-- Product name --}}
                        <td class="px-6 py-3.5">
                            <p class="text-sm font-medium text-gray-800 dark:text-white/90">{{ $commodity->name }}</p>
                        </td>

                        {{-- Category --}}
                        <td class="px-6 py-3.5 whitespace-nowrap">
                            <span class="text-sm text-gray-500 dark:text-gray-400">{{ $commodity->category?->name ?? '-' }}</span>
                        </td>

                        {{-- Expiry date inline edit --}}
                        <td class="px-6 py-3.5 whitespace-nowrap">
                            @if($editingId === $commodity->id)
                                {{-- Inline date input --}}
                                <div class="flex items-center gap-2">
                                    <input type="date" wire:model="editDate"
                                           class="rounded-lg border border-gray-300 bg-white px-2 py-1 text-sm text-gray-800 focus:border-brand-300 focus:outline-hidden focus:ring-2 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
                                    <button wire:click="saveEdit" class="text-success-500 hover:text-success-600" title="Uložit">
                                        <x-heroicon-o-check class="w-4 h-4" />
                                    </button>
                                    <button wire:click="cancelEdit" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200" title="Zrušit">
                                        <x-heroicon-o-x-mark class="w-4 h-4" />
                                    </button>
                                </div>
                            @else
                                <button wire:click="openEdit({{ $commodity->id }})"
                                        class="text-sm text-gray-700 dark:text-gray-300 hover:text-brand-500 dark:hover:text-brand-400 transition-colors"
                                        title="Kliknutím upravit datum">
                                    {{ $commodity->expires_at?->format('d. m. Y') ?? '-' }}
                                </button>
                            @endif
                        </td>

                        {{-- Days remaining --}}
                        <td class="px-6 py-3.5 whitespace-nowrap">
                            @if($days === null)
                                <span class="text-sm text-gray-400 dark:text-gray-600">-</span>
                            @elseif($days < 0)
                                <span class="text-sm font-medium text-error-500">{{ abs($days) }} dní zpět</span>
                            @elseif($days === 0)
                                <span class="text-sm font-medium text-error-500">Dnes!</span>
                            @else
                                <span class="text-sm font-medium
                                        {{ $status === 'critical' ? 'text-error-500' : ($status === 'warning' ? 'text-warning-500' : 'text-gray-600 dark:text-gray-400') }}">
                                        {{ $days }} dní
                                    </span>
                            @endif
                        </td>

                        {{-- Status badge --}}
                        <td class="px-6 py-3.5 whitespace-nowrap">
                            @if($status === 'expired')
                                <span class="inline-flex items-center gap-1 rounded-full bg-error-50 px-2 py-0.5 text-xs font-medium text-error-700 dark:bg-error-500/15 dark:text-error-400">
                                        <x-heroicon-o-x-circle class="w-3 h-3" /> Prošlé
                                    </span>
                            @elseif($status === 'critical')
                                <span class="inline-flex items-center gap-1 rounded-full bg-error-50 px-2 py-0.5 text-xs font-medium text-error-700 dark:bg-error-500/15 dark:text-error-400">
                                        <x-heroicon-o-exclamation-triangle class="w-3 h-3" /> Kritické
                                    </span>
                            @elseif($status === 'warning')
                                <span class="inline-flex items-center gap-1 rounded-full bg-warning-50 px-2 py-0.5 text-xs font-medium text-warning-700 dark:bg-warning-500/15 dark:text-warning-400">
                                        <x-heroicon-o-clock class="w-3 h-3" /> Brzy vyprší
                                    </span>
                            @elseif($status === 'ok')
                                <span class="inline-flex items-center gap-1 rounded-full bg-success-50 px-2 py-0.5 text-xs font-medium text-success-700 dark:bg-success-500/15 dark:text-success-400">
                                        <x-heroicon-o-check-circle class="w-3 h-3" /> V pořádku
                                    </span>
                            @else
                                <span class="text-xs text-gray-400 dark:text-gray-600">-</span>
                            @endif
                        </td>

                        {{-- Fridge qty --}}
                        <td class="px-6 py-3.5 whitespace-nowrap">
                                <span class="text-sm font-medium
                                    {{ $commodity->fridge_quantity <= 0 ? 'text-error-500' : 'text-gray-700 dark:text-gray-300' }}">
                                    {{ max(0, $commodity->fridge_quantity) }} ks
                                </span>
                        </td>

                        {{-- Actions --}}
                        <td class="px-6 py-3.5 whitespace-nowrap text-right">
                            <div class="flex items-center justify-end gap-2">
                                <button wire:click="openEdit({{ $commodity->id }})"
                                        class="flex items-center justify-center w-7 h-7 rounded-lg text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-white/5 dark:hover:text-gray-200 transition-colors"
                                        title="Upravit datum">
                                    <x-heroicon-o-pencil class="w-3.5 h-3.5" />
                                </button>
                                @if($commodity->expires_at)
                                    <button wire:click="clearExpiry({{ $commodity->id }})"
                                            wire:confirm="Odebrat datum spotřeby z tohoto produktu?"
                                            class="flex items-center justify-center w-7 h-7 rounded-lg text-gray-400 hover:bg-error-50 hover:text-error-500 dark:hover:bg-error-500/10 transition-colors"
                                            title="Odebrat datum">
                                        <x-heroicon-o-trash class="w-3.5 h-3.5" />
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center">
                            <x-heroicon-o-check-circle class="w-10 h-10 text-success-400 mx-auto mb-2" />
                            <p class="text-sm text-gray-500 dark:text-gray-400">Žádné produkty neodpovídají filtru</p>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
