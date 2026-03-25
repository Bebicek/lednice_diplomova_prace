<div>
    <x-common.page-breadcrumb :pageTitle="'Obědy'" />

    {{-- Stats cards --}}
    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">

        <x-common.stat-card label="Celkem obědů" :value="$stats['total']" subtitle="v systému" icon="clipboard-document-list" />
        <x-common.stat-card label="Nevyrovnané" :value="$stats['unsettled']" subtitle="s nezaplacenými dluhy" color="warning" icon="exclamation-triangle" />
        <x-common.stat-card label="Vyrovnané" :value="$stats['settled']" subtitle="plně zaplaceno" color="success" icon="check-circle" />
        <x-common.stat-card label="Celková hodnota" :value="number_format($stats['totalAmount'] / 100, 2, ',', ' ') . ' Kč'" subtitle="suma všech obědů" color="brand" icon="banknotes" />
    </div>

    {{-- Lunches table --}}
    <div class="rounded-2xl border border-gray-200 bg-white pt-4 dark:border-gray-800 dark:bg-white/[0.03]">

        {{-- Toolbar --}}
        <div class="flex flex-col gap-3 px-5 mb-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
            <div>
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Přehled obědů</h3>
                <p class="mt-0.5 text-theme-sm text-gray-500 dark:text-gray-400">Všechny obědy vytvořené v systému</p>
            </div>
        </div>

        {{-- Filters --}}
        <div class="flex flex-wrap gap-3 px-5 mb-4 sm:px-6">
            <div class="relative flex-1 min-w-[180px]">
                <span class="absolute -translate-y-1/2 left-4 top-1/2 pointer-events-none">
                    <x-heroicon-o-magnifying-glass class="w-4 h-4 text-gray-400" />
                </span>
                <input type="text" wire:model.live.debounce.300ms="search"
                       placeholder="Hledat restauraci nebo organizátora..."
                       class="h-[42px] w-full rounded-lg border border-gray-300 bg-transparent py-2.5 pl-10 pr-4 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-none focus:ring-2 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800" />
            </div>
            <select wire:model.live="filterStatus"
                    class="h-[42px] rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-700 shadow-theme-xs focus:border-brand-300 focus:outline-none focus:ring-2 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                <option value="">Všechny stavy</option>
                <option value="unsettled">Nevyrovnané</option>
                <option value="settled">Vyrovnané</option>
            </select>
        </div>

        {{-- Table --}}
        <div class="overflow-hidden">
            <div class="max-w-full overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                    <tr class="border-gray-200 border-y dark:border-gray-700">
                        <x-datagrid.sort-header field="restaurant_name" label="Restaurace" :sort-by="$sortBy" :sort-direction="$sortDirection" />
                        <x-datagrid.sort-header field="organizer_name" label="Organizátor" :sort-by="$sortBy" :sort-direction="$sortDirection" />
                        <x-datagrid.sort-header field="participants_count" label="Účastníci" :sort-by="$sortBy" :sort-direction="$sortDirection" />
                        <x-datagrid.sort-header field="total_amount" label="Částka" :sort-by="$sortBy" :sort-direction="$sortDirection" />
                        <x-datagrid.sort-header field="split_method" label="Rozdělení" :sort-by="$sortBy" :sort-direction="$sortDirection" />
                        <x-datagrid.sort-header field="is_settled" label="Stav" :sort-by="$sortBy" :sort-direction="$sortDirection" />
                        <x-datagrid.sort-header field="created_at" label="Datum" :sort-by="$sortBy" :sort-direction="$sortDirection" />
                        <th class="px-5 py-3 w-px"><span class="sr-only">Akce</span></th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($lunches as $lunch)
                        @php $settled = $lunch->isSettled(); @endphp
                        <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02]">

                            {{-- Restaurant --}}
                            <td class="px-5 py-3.5 whitespace-nowrap">
                                    <span class="text-theme-sm font-medium text-gray-800 dark:text-white/90">
                                        {{ $lunch->restaurant_name }}
                                    </span>
                                @if($lunch->description)
                                    <p class="mt-0.5 text-theme-xs text-gray-400 max-w-[200px] truncate">{{ $lunch->description }}</p>
                                @endif
                            </td>

                            {{-- Organizer --}}
                            <td class="px-5 py-3.5 whitespace-nowrap">
                                    <span class="text-theme-sm text-gray-600 dark:text-gray-300">
                                        {{ $lunch->organizer?->name ?? '—' }}
                                    </span>
                            </td>

                            {{-- Participant count --}}
                            <td class="px-5 py-3.5 whitespace-nowrap text-center">
                                    <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-gray-100 text-xs font-semibold text-gray-600 dark:bg-white/[0.06] dark:text-gray-400">
                                        {{ $lunch->participants_count }}
                                    </span>
                            </td>

                            {{-- Total amount --}}
                            <td class="px-5 py-3.5 whitespace-nowrap text-right">
                                    <span class="text-theme-sm font-semibold text-gray-800 dark:text-white/90">
                                        {{ number_format($lunch->total_amount / 100, 2, ',', ' ') }} Kč
                                    </span>
                                @if($lunch->delivery_cost > 0)
                                    <p class="mt-0.5 text-theme-xs text-gray-400">
                                        + {{ number_format($lunch->delivery_cost / 100, 2, ',', ' ') }} Kč doprava
                                    </p>
                                @endif
                            </td>

                            {{-- Split method --}}
                            <td class="px-5 py-3.5 whitespace-nowrap">
                                @if($lunch->split_method === 'equal')
                                    <span class="inline-flex items-center gap-1 rounded-full bg-blue-50 px-2 py-0.5 text-xs font-medium text-blue-700 dark:bg-blue-500/15 dark:text-blue-400">
                                            Rovným dílem
                                        </span>
                                @else
                                    <span class="inline-flex items-center gap-1 rounded-full bg-purple-50 px-2 py-0.5 text-xs font-medium text-purple-700 dark:bg-purple-500/15 dark:text-purple-400">
                                            Dle objednávky
                                        </span>
                                @endif
                            </td>

                            {{-- Status --}}
                            <td class="px-5 py-3.5 whitespace-nowrap">
                                @if($settled)
                                    <span class="inline-flex items-center gap-1 rounded-full bg-success-50 px-2 py-0.5 text-xs font-medium text-success-700 dark:bg-success-500/15 dark:text-success-400">
                                            <x-heroicon-o-check-circle class="w-3 h-3" />
                                            Vyrovnán
                                        </span>
                                @else
                                    @php
                                        $unpaidDebts = $lunch->debts->where('is_paid', false)->count();
                                        $pendingDebts = $lunch->debts->where('is_accepted', false)->count();
                                    @endphp
                                    <span class="inline-flex items-center gap-1 rounded-full bg-warning-50 px-2 py-0.5 text-xs font-medium text-warning-700 dark:bg-warning-500/15 dark:text-warning-400">
                                            <x-heroicon-o-clock class="w-3 h-3" />
                                            Nevyrovnán
                                        </span>
                                    <p class="mt-0.5 text-theme-xs text-gray-400">
                                        {{ $unpaidDebts }}× nezaplaceno
                                        @if($pendingDebts > 0)
                                            · {{ $pendingDebts }}× čeká na přijetí
                                        @endif
                                    </p>
                                @endif
                            </td>

                            {{-- Date --}}
                            <td class="px-5 py-3.5 whitespace-nowrap">
                                    <span class="text-theme-xs text-gray-500 dark:text-gray-400">
                                        {{ $lunch->created_at->format('d. m. Y') }}
                                    </span>
                            </td>

                            {{-- Actions --}}
                            <td class="px-5 py-3.5 whitespace-nowrap text-right w-px">
                                <x-common.table-dropdown>
                                    <x-slot name="button">
                                        <button type="button" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                                            <x-heroicon-o-ellipsis-vertical class="w-6 h-6" />
                                        </button>
                                    </x-slot>
                                    <x-slot name="content">
                                        <button wire:click="openDetail({{ $lunch->id }})"
                                                class="flex w-full px-3 py-2 text-left text-theme-xs font-medium text-gray-500 rounded-lg hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300">
                                            <x-heroicon-o-eye class="w-3.5 h-3.5 mr-2 mt-px" />
                                            Zobrazit detail
                                        </button>
                                        @if(!$settled)
                                            <button wire:click="closeAllDebts({{ $lunch->id }})"
{{--                                                    TODO: REPLACE THE CONFIRM WITH SOMETHING BETTER OR STYLISH BECAUASE NOT IT LOOKS TERRIBLE HOW IT IS--}}
                                                    wire:confirm="Označit všechny nezaplacené dluhy tohoto oběda jako zaplacené?"
                                                    class="flex w-full px-3 py-2 text-left text-theme-xs font-medium text-success-600 rounded-lg hover:bg-success-50 hover:text-success-700 dark:text-success-400 dark:hover:bg-success-500/10">
                                                <x-heroicon-o-check-circle class="w-3.5 h-3.5 mr-2 mt-px" />
                                                Uzavřít oběd (zaplatit vše)
                                            </button>
                                        @endif
                                        @if($settled)
                                            <button wire:click="deleteLunch({{ $lunch->id }})"
{{--                                        TODO: REPLACE THE CONFIRM WITH SOMETHING BETTER OR STYLISH BECAUASE NOT IT LOOKS TERRIBLE HOW IT IS--}}
                                                    wire:confirm="Opravdu smazat tento oběd? Akce je nevratná."
                                                    class="flex w-full px-3 py-2 text-left text-theme-xs font-medium text-error-600 rounded-lg hover:bg-error-50 hover:text-error-700 dark:text-error-400 dark:hover:bg-error-500/10">
                                                <x-heroicon-o-trash class="w-3.5 h-3.5 mr-2 mt-px" />
                                                Smazat
                                            </button>
                                        @endif
                                    </x-slot>
                                </x-common.table-dropdown>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-16 text-center">
                                <x-heroicon-o-cake class="mx-auto mb-3 h-10 w-10 text-gray-300 dark:text-gray-600" />
                                <p class="text-theme-sm font-medium text-gray-700 dark:text-white/70">Žádné obědy nebyly nalezeny</p>
                                <p class="mt-1 text-theme-xs text-gray-500 dark:text-gray-400">Zkuste změnit filtr nebo hledaný výraz.</p>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Footer --}}
        @if($lunches->isNotEmpty())
            <div class="px-5 py-3 sm:px-6 border-t border-gray-100 dark:border-gray-800">
                <p class="text-theme-xs text-gray-400">
                    Zobrazeno {{ $lunches->count() }} {{ $lunches->count() === 1 ? 'oběd' : ($lunches->count() < 5 ? 'obědy' : 'obědů') }}
                </p>
            </div>
        @endif
    </div>

    {{-- Modal: Lunch detail --}}
    @if($hasDetailOpen && $detailLunch !== null)
        <div class="fixed inset-0 z-[99999] flex items-center justify-center">
            <div class="fixed inset-0 bg-gray-900/50 dark:bg-gray-900/70" wire:click="closeDetail"></div>
            <div class="relative w-full max-w-2xl mx-4 max-h-[90vh] overflow-y-auto rounded-2xl bg-white p-6 shadow-xl dark:bg-gray-900">

                {{-- Header --}}
                <div class="mb-5 flex items-start justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-white">
                            {{ $detailLunch->restaurant_name }}
                        </h3>
                        <p class="mt-0.5 text-theme-xs text-gray-500">
                            Organizátor: <span class="font-medium text-gray-700 dark:text-gray-300">{{ $detailLunch->organizer?->name }}</span>
                            · {{ $detailLunch->created_at->format('d. m. Y') }}
                            · {{ $detailLunch->split_method === 'equal' ? 'Rovným dílem' : 'Dle objednávky' }}
                        </p>
                        @if($detailLunch->description)
                            <p class="mt-1 text-theme-xs text-gray-500 dark:text-gray-400">{{ $detailLunch->description }}</p>
                        @endif
                    </div>
                    <button wire:click="closeDetail" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                        <x-heroicon-o-x-mark class="w-5 h-5" />
                    </button>
                </div>

                {{-- Summary row --}}
                <div class="mb-4 flex flex-wrap gap-3 rounded-xl bg-gray-50 p-3 dark:bg-white/[0.03]">
                    <div class="text-center px-3">
                        <p class="text-theme-xs text-gray-400">Celková částka</p>
                        <p class="mt-0.5 text-theme-sm font-bold text-gray-800 dark:text-white">
                            {{ number_format($detailLunch->total_amount / 100, 2, ',', ' ') }} Kč
                        </p>
                    </div>
                    @if($detailLunch->delivery_cost > 0)
                        <div class="text-center px-3 border-l border-gray-200 dark:border-gray-700">
                            <p class="text-theme-xs text-gray-400">Doprava</p>
                            <p class="mt-0.5 text-theme-sm font-bold text-gray-800 dark:text-white">
                                {{ number_format($detailLunch->delivery_cost / 100, 2, ',', ' ') }} Kč
                            </p>
                        </div>
                    @endif
                    <div class="text-center px-3 border-l border-gray-200 dark:border-gray-700">
                        <p class="text-theme-xs text-gray-400">Účastníků</p>
                        <p class="mt-0.5 text-theme-sm font-bold text-gray-800 dark:text-white">
                            {{ $detailLunch->participants->count() }}
                        </p>
                    </div>
                    <div class="text-center px-3 border-l border-gray-200 dark:border-gray-700">
                        <p class="text-theme-xs text-gray-400">Stav</p>
                        <p class="mt-0.5 text-theme-sm font-bold {{ $detailLunch->isSettled() ? 'text-success-600 dark:text-success-400' : 'text-warning-600 dark:text-warning-400' }}">
                            {{ $detailLunch->isSettled() ? 'Vyrovnán' : 'Nevyrovnán' }}
                        </p>
                    </div>
                </div>

                {{-- Participants --}}
                <div class="divide-y divide-gray-100 rounded-xl border border-gray-200 dark:divide-gray-800 dark:border-gray-700">
                    @foreach($detailLunch->participants as $participant)
                        @php
                            $isOrganizer = $participant->user_id === $detailLunch->organizer_id;
                            $debt = $detailLunch->debts->firstWhere('user_id', $participant->user_id);
                        @endphp
                        <div class="px-4 py-3">
                            {{-- Participant header --}}
                            <div class="flex items-center justify-between gap-3">
                                <div class="flex items-center gap-2">
                                    <span class="text-theme-sm font-medium text-gray-800 dark:text-white/90">
                                        {{ $participant->user?->name ?? '—' }}
                                    </span>
                                    @if($isOrganizer)
                                        <span class="inline-flex items-center gap-1 rounded-full bg-brand-50 px-2 py-0.5 text-xs font-medium text-brand-700 dark:bg-brand-500/15 dark:text-brand-400">
                                            Organizátor
                                        </span>
                                    @else
                                        @if($debt === null)
                                            <span class="inline-flex items-center gap-1 rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-500 dark:bg-white/[0.06] dark:text-gray-400">
                                                Bez dluhu
                                            </span>
                                        @elseif(!$debt->is_accepted)
                                            <span class="inline-flex items-center gap-1 rounded-full bg-orange-50 px-2 py-0.5 text-xs font-medium text-orange-700 dark:bg-orange-500/15 dark:text-orange-400">
                                                <x-heroicon-o-clock class="w-3 h-3" />
                                                Čeká na přijetí
                                            </span>
                                        @elseif($debt->is_paid)
                                            <span class="inline-flex items-center gap-1 rounded-full bg-success-50 px-2 py-0.5 text-xs font-medium text-success-700 dark:bg-success-500/15 dark:text-success-400">
                                                <x-heroicon-o-check-circle class="w-3 h-3" />
                                                Zaplaceno {{ $debt->paid_at?->format('d. m. Y') }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 rounded-full bg-error-50 px-2 py-0.5 text-xs font-medium text-error-700 dark:bg-error-500/15 dark:text-error-400">
                                                Nezaplaceno
                                            </span>
                                        @endif
                                    @endif
                                </div>

                                <span class="text-theme-sm font-semibold text-gray-800 dark:text-white/90">
                                    {{ number_format($participant->amount / 100, 2, ',', ' ') }} Kč
                                </span>
                            </div>

                            {{-- Items --}}
                            @if($participant->items->isNotEmpty())
                                <ul class="mt-2 space-y-0.5 pl-2">
                                    @foreach($participant->items as $item)
                                        <li class="flex items-center justify-between text-theme-xs text-gray-500 dark:text-gray-400">
                                            <span>{{ $item->name }}</span>
                                            <span>{{ number_format($item->price / 100, 2, ',', ' ') }} Kč</span>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    @endforeach
                </div>

                {{-- Footer --}}
                <div class="mt-5 flex items-center justify-between gap-3">
                    @php $unpaidCount = $detailLunch->debts->where('is_paid', false)->count(); @endphp
                    @if($unpaidCount > 0)
                        <button wire:click="closeAllDebts({{ $detailLunch->id }})"
{{--                    TODO: REPLACE THE CONFIRM WITH SOMETHING BETTER OR STYLISH BECAUASE NOT IT LOOKS TERRIBLE HOW IT IS--}}
                                wire:confirm="Označit všechny nezaplacené dluhy tohoto oběda jako zaplacené?"
                                class="inline-flex items-center gap-2 rounded-lg bg-success-500 px-4 py-2.5 text-theme-sm font-medium text-white hover:bg-success-600">
                            <x-heroicon-o-check-circle class="w-4 h-4" />
                            Uzavřít oběd ({{ $unpaidCount }}× nezaplaceno)
                        </button>
                    @else
                        <span class="inline-flex items-center gap-1.5 text-theme-sm text-success-600 dark:text-success-400">
                            <x-heroicon-o-check-circle class="w-4 h-4" />
                            Oběd je plně vyrovnán
                        </span>
                    @endif
                    <button wire:click="closeDetail"
                            class="rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-theme-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400">
                        Zavřít
                    </button>
                </div>

            </div>
        </div>
    @endif

</div>
