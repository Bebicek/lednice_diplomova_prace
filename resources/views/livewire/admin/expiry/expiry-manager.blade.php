<div>
    <x-common.page-breadcrumb :pageTitle="'Datum spotřeby'" />

    {{-- Stats cards --}}
    <div class="grid grid-cols-2 gap-4 xl:grid-cols-4 md:gap-6 mb-6">
        <x-common.stat-card label="Prošlé" :value="$stats['expired']" color="error" icon="x-circle" />
        <x-common.stat-card label="Vyprší do 3 dní" :value="$stats['critical']" color="error" icon="exclamation-triangle" />
        <x-common.stat-card label="Vyprší do 7 dní" :value="$stats['warning']" color="warning" icon="clock" />
        <x-common.stat-card label="Bez data" :value="$stats['none']" icon="question-mark-circle" />
    </div>

    {{-- Product table --}}
    <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">

        {{-- Table header --}}
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 dark:border-gray-800 md:px-6">
            <div>
                <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">Přehled dat spotřeby</h3>
                <p class="mt-0.5 text-xs text-gray-400 dark:text-gray-500">Seřazeno dle data spotřeby</p>
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
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400">Lokace</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400">Množství</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400">Akce</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                @forelse($stocks as $stock)
                    @php
                        $status = $stock->expiry_status;
                        $days = $stock->days_until_expiry;
                    @endphp
                    <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors">

                        {{-- Product name --}}
                        <td class="px-6 py-3.5">
                            <p class="text-sm font-medium text-gray-800 dark:text-white/90">{{ $stock->commodity?->name ?? '-' }}</p>
                        </td>

                        {{-- Category --}}
                        <td class="px-6 py-3.5 whitespace-nowrap">
                            <span class="text-sm text-gray-500 dark:text-gray-400">{{ $stock->commodity?->category?->name ?? '-' }}</span>
                        </td>

                        {{-- Expiry date inline edit --}}
                        <td class="px-6 py-3.5 whitespace-nowrap">
                            @if($editingId === $stock->id)
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
                                <button wire:click="openEdit({{ $stock->id }})"
                                        class="text-sm text-gray-700 dark:text-gray-300 hover:text-brand-500 dark:hover:text-brand-400 transition-colors"
                                        title="Kliknutím upravit datum">
                                    {{ $stock->expires_at?->format('d. m. Y') ?? '-' }}
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

                        {{-- Location --}}
                        <td class="px-6 py-3.5 whitespace-nowrap">
                            @if($stock->location === 'fridge')
                                <span class="inline-flex items-center gap-1 rounded-full bg-brand-50 px-2 py-0.5 text-xs font-medium text-brand-700 dark:bg-brand-500/15 dark:text-brand-400">
                                    Lednice
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600 dark:bg-gray-700 dark:text-gray-300">
                                    Sklad
                                </span>
                            @endif
                        </td>

                        {{-- Qty --}}
                        <td class="px-6 py-3.5 whitespace-nowrap">
                            <span class="text-sm font-medium
                                {{ $stock->quantity <= 0 ? 'text-error-500' : 'text-gray-700 dark:text-gray-300' }}">
                                {{ max(0, $stock->quantity) }} ks
                            </span>
                        </td>

                        {{-- Actions --}}
                        <td class="px-6 py-3.5 whitespace-nowrap text-right">
                            <div class="flex items-center justify-end gap-2">
                                <button wire:click="openEdit({{ $stock->id }})"
                                        class="flex items-center justify-center w-7 h-7 rounded-lg text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-white/5 dark:hover:text-gray-200 transition-colors"
                                        title="Upravit datum">
                                    <x-heroicon-o-pencil class="w-3.5 h-3.5" />
                                </button>
                                @if($stock->expires_at)
                                    <button wire:click="clearExpiry({{ $stock->id }})"
                                            wire:confirm="Odebrat datum spotřeby z tohoto záznamu?"
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
                        <td colspan="8" class="px-6 py-12 text-center">
                            <x-heroicon-o-check-circle class="w-10 h-10 text-gray-300 dark:text-gray-600 mx-auto mb-2" />
                            <p class="text-sm text-gray-500 dark:text-gray-400">Žádné produkty neodpovídají filtru</p>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
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
</div>
