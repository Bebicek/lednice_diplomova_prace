{{-- Expandable detail row showing purchased products for a debt --}}
{{-- Usage: @include('livewire.partials.debt-detail-row', ['debt' => $debt, 'colspan' => 5, 'expandVar' => 'expanded_X']) --}}
<tr x-show="{{ $expandVar }}"
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    x-cloak>
    <td colspan="{{ $colspan }}" class="px-0 py-0">
        <div class="border-t border-dashed border-gray-200 bg-gray-50/50 px-6 py-4 dark:border-gray-700 dark:bg-white/[0.02]">
            @if($debt->order && $debt->order->items->count() > 0)
                <div class="mb-2 flex items-center gap-2 text-theme-xs font-semibold text-gray-500 uppercase dark:text-gray-400">
                    <x-heroicon-o-shopping-bag class="w-3.5 h-3.5" />
                    Položky objednávky #{{ $debt->order->id }}
                </div>
                <div class="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700">
                    <table class="w-full text-theme-sm">
                        <thead>
                            <tr class="bg-gray-100/80 dark:bg-white/[0.04]">
                                <th class="px-4 py-2 text-left text-theme-xs font-medium text-gray-500 dark:text-gray-400">Produkt</th>
                                <th class="px-4 py-2 text-center text-theme-xs font-medium text-gray-500 dark:text-gray-400">Množství</th>
                                <th class="px-4 py-2 text-right text-theme-xs font-medium text-gray-500 dark:text-gray-400">Cena/ks</th>
                                <th class="px-4 py-2 text-right text-theme-xs font-medium text-gray-500 dark:text-gray-400">Celkem</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @foreach($debt->order->items as $item)
                                <tr>
                                    <td class="px-4 py-2.5 text-gray-800 dark:text-white/90">
                                        <div class="flex items-center gap-2">
                                            @if($item->commodity && $item->commodity->image_path)
                                                <img src="{{ asset('storage/' . $item->commodity->image_path) }}"
                                                     alt="{{ $item->commodity->name ?? 'Produkt' }}"
                                                     class="h-8 w-8 rounded-lg object-cover border border-gray-200 dark:border-gray-700">
                                            @else
                                                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-gray-100 dark:bg-white/[0.06]">
                                                    <x-heroicon-o-cube class="w-4 h-4 text-gray-400" />
                                                </div>
                                            @endif
                                            <span>{{ $item->commodity->name ?? 'Smazaný produkt' }}</span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-2.5 text-center text-gray-600 dark:text-gray-400">{{ $item->quantity }}×</td>
                                    <td class="px-4 py-2.5 text-right text-gray-600 dark:text-gray-400">{{ number_format($item->price / 100, 2, ',', ' ') }} Kč</td>
                                    <td class="px-4 py-2.5 text-right font-medium text-gray-800 dark:text-white/90">{{ number_format(($item->quantity * $item->price) / 100, 2, ',', ' ') }} Kč</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="bg-gray-100/50 dark:bg-white/[0.03]">
                                <td colspan="3" class="px-4 py-2 text-right text-theme-xs font-semibold text-gray-500 uppercase dark:text-gray-400">Celkem</td>
                                <td class="px-4 py-2 text-right font-bold text-gray-800 dark:text-white/90">{{ number_format($debt->amount / 100, 2, ',', ' ') }} Kč</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @elseif($debt->lunch)
                <div class="mb-2 flex items-center gap-2 text-theme-xs font-semibold text-gray-500 uppercase dark:text-gray-400">
                    <x-heroicon-o-calendar-days class="w-3.5 h-3.5" />
                    Oběd: {{ $debt->lunch->restaurant_name }}
                </div>
                @if($debt->lunch->description)
                    <p class="mb-3 text-theme-xs text-gray-500 dark:text-gray-400">{{ $debt->lunch->description }}</p>
                @endif
                <div class="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700">
                    <table class="w-full text-theme-sm">
                        <thead>
                            <tr class="bg-gray-100/80 dark:bg-white/[0.04]">
                                <th class="px-4 py-2 text-left text-theme-xs font-medium text-gray-500 dark:text-gray-400">Účastník</th>
                                <th class="px-4 py-2 text-left text-theme-xs font-medium text-gray-500 dark:text-gray-400">Položky</th>
                                <th class="px-4 py-2 text-right text-theme-xs font-medium text-gray-500 dark:text-gray-400">Částka</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @foreach($debt->lunch->participants as $participant)
                                <tr class="{{ $participant->user_id === $debt->user_id ? 'bg-brand-50/50 dark:bg-brand-500/5' : '' }}">
                                    <td class="px-4 py-2.5 text-gray-800 dark:text-white/90">
                                        <div class="flex items-center gap-1.5">
                                            @if($participant->user_id === $debt->user_id)
                                                <span class="inline-flex items-center rounded-full bg-brand-100 px-1.5 py-0.5 text-[10px] font-semibold text-brand-700 dark:bg-brand-500/20 dark:text-brand-400">Vy</span>
                                            @endif
                                            {{ $participant->user->name ?? 'Neznámý' }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-2.5 text-gray-600 dark:text-gray-400">
                                        @if($participant->items->count() > 0)
                                            <div class="flex flex-wrap gap-1">
                                                @foreach($participant->items as $lunchItem)
                                                    <span class="inline-flex items-center gap-1 rounded-md bg-gray-100 px-2 py-0.5 text-theme-xs dark:bg-white/[0.06]">
                                                        {{ $lunchItem->name }}
                                                        <span class="text-gray-400 dark:text-gray-500">({{ number_format($lunchItem->price / 100, 2, ',', ' ') }})</span>
                                                    </span>
                                                @endforeach
                                            </div>
                                        @else
                                            <span class="text-theme-xs text-gray-400 italic">Bez položek</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-2.5 text-right font-medium text-gray-800 dark:text-white/90">
                                        {{ number_format($participant->amount / 100, 2, ',', ' ') }} Kč
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        @if($debt->lunch->delivery_cost > 0)
                            <tfoot>
                                <tr class="bg-gray-100/50 dark:bg-white/[0.03]">
                                    <td colspan="2" class="px-4 py-2 text-right text-theme-xs text-gray-500 dark:text-gray-400">Dovoz</td>
                                    <td class="px-4 py-2 text-right text-gray-600 dark:text-gray-400">{{ number_format($debt->lunch->delivery_cost / 100, 2, ',', ' ') }} Kč</td>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            @else
                <div class="flex items-center gap-2 text-theme-sm text-gray-400 dark:text-gray-500">
                    <x-heroicon-o-information-circle class="w-4 h-4" />
                    Detail pro tento dluh není k dispozici.
                </div>
            @endif
        </div>
    </td>
</tr>
