@php use App\Models\Debt; @endphp
<div x-data="{ activeTab: 'unpaid', qrOpen: false, qrUrl: '', qrName: '', qrAmount: '', qrIban: '' }">
    <x-common.page-breadcrumb :pageTitle="'Moje dluhy'" />

    @php
        $pendingLunchDebts = Debt::where('user_id', auth()->id())
            ->where('is_accepted', false)
            ->whereNotNull('lunch_id')
            ->count();
    @endphp

    @if($pendingLunchDebts > 0)
        <div class="mb-4 flex items-center justify-between rounded-xl border border-warning-200 bg-warning-50 px-4 py-3 dark:border-warning-500/30 dark:bg-warning-500/10">
            <div class="flex items-center gap-3">
                <x-heroicon-o-exclamation-triangle class="w-5 h-5 shrink-0 text-warning-600 dark:text-warning-400" />
                <span class="text-theme-sm font-medium text-warning-700 dark:text-warning-400">
                    Máte {{ $pendingLunchDebts }} {{ $pendingLunchDebts === 1 ? 'čekající obědový dluh' : ($pendingLunchDebts <= 4 ? 'čekající obědové dluhy' : 'čekajících obědových dluhů') }} k přijetí.
                </span>
            </div>
            <a href="{{ route('lunches') }}"
               class="shrink-0 text-theme-xs font-semibold text-warning-700 underline hover:text-warning-900 dark:text-warning-400 dark:hover:text-warning-200">
                Zobrazit →
            </a>
        </div>
    @endif

    @if(session('success'))
        <div class="mb-4 rounded-lg bg-success-50 p-4 text-success-700 dark:bg-success-500/10 dark:text-success-400">
            {{ session('success') }}
        </div>
    @endif

    {{-- Stats row --}}
    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
        {{-- Unpaid debt card --}}
        <div
            @click="activeTab = 'unpaid'"
            class="cursor-pointer rounded-2xl border p-5 transition-colors"
            :class="activeTab === 'unpaid'
                ? 'border-brand-300 bg-brand-50 dark:border-brand-500/30 dark:bg-brand-500/10'
                : 'border-gray-200 bg-white hover:border-gray-300 dark:border-gray-800 dark:bg-white/[0.03]'"
        >
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-theme-sm text-gray-500 dark:text-gray-400">Nesplacený dluh</span>
                    <div class="mt-1 text-2xl font-bold {{ $totalUnpaid > 0 ? 'text-error-500' : 'text-success-500' }}">
                        {{ number_format($totalUnpaid / 100, 2, ',', ' ') }} Kč
                    </div>
                </div>
                <div class="flex items-center justify-center w-12 h-12 rounded-xl {{ $totalUnpaid > 0 ? 'bg-error-50 text-error-500 dark:bg-error-500/10' : 'bg-success-50 text-success-500 dark:bg-success-500/10' }}">
                    <x-heroicon-o-exclamation-triangle class="w-6 h-6" />
                </div>
            </div>
        </div>

        {{-- Outgoing payments card --}}
        <div
            @click="activeTab = 'outgoing'"
            class="cursor-pointer rounded-2xl border p-5 transition-colors"
            :class="activeTab === 'outgoing'
                ? 'border-brand-300 bg-brand-50 dark:border-brand-500/30 dark:bg-brand-500/10'
                : 'border-gray-200 bg-white hover:border-gray-300 dark:border-gray-800 dark:bg-white/[0.03]'"
        >
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-theme-sm text-gray-500 dark:text-gray-400">Odchozí platby</span>
                    <div class="mt-1 text-2xl font-bold text-gray-800 dark:text-white/90">
                        {{ $outgoingPayments->count() }}
                        <span class="text-base font-normal text-gray-500 dark:text-gray-400">plateb</span>
                    </div>
                </div>
                <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-gray-100 text-gray-500 dark:bg-white/[0.06] dark:text-gray-400">
                    <x-heroicon-o-arrow-up-right class="w-6 h-6" />
                </div>
            </div>
        </div>

        {{-- Incoming payments card --}}
        <div
            @click="activeTab = 'incoming'"
            class="cursor-pointer rounded-2xl border p-5 transition-colors"
            :class="activeTab === 'incoming'
                ? 'border-brand-300 bg-brand-50 dark:border-brand-500/30 dark:bg-brand-500/10'
                : 'border-gray-200 bg-white hover:border-gray-300 dark:border-gray-800 dark:bg-white/[0.03]'"
        >
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-theme-sm text-gray-500 dark:text-gray-400">Příchozí platby</span>
                    <div class="mt-1 text-2xl font-bold text-success-500">
                        {{ number_format($totalIncoming / 100, 2, ',', ' ') }} Kč
                    </div>
                </div>
                <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-success-50 text-success-500 dark:bg-success-500/10">
                    <x-heroicon-o-arrow-down-left class="w-6 h-6" />
                </div>
            </div>
        </div>
    </div>

    {{-- Overview of debts by creditor --}}
    @if($creditorGroups->count() > 0)
        <div class="mb-6">
            <h3 class="mb-3 text-theme-sm font-semibold text-gray-700 dark:text-white/70">Jak zaplatit</h3>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($creditorGroups as $group)
                    <div class="flex flex-col rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">

                        {{-- Creditor + amount --}}
                        <div class="flex items-center gap-3 p-4 pb-3">
                            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-error-50 text-error-500 dark:bg-error-500/10">
                                @if($group['is_system'])
                                    <x-heroicon-o-building-storefront class="w-5 h-5" />
                                @else
                                    <x-heroicon-o-user class="w-5 h-5" />
                                @endif
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="text-theme-sm font-semibold text-gray-800 dark:text-white/90 truncate">
                                    {{ $group['creditor_name'] }}
                                </div>
                                <div class="text-theme-xs text-gray-500 dark:text-gray-400">
                                    {{ $group['count'] }} {{ $group['count'] === 1 ? 'nesplacený dluh' : ($group['count'] <= 4 ? 'nesplacené dluhy' : 'nesplacených dluhů') }}
                                </div>
                            </div>
                            <div class="text-right shrink-0">
                                <div class="text-base font-bold text-error-600 dark:text-error-400">
                                    {{ number_format($group['total'] / 100, 2, ',', ' ') }} Kč
                                </div>
                            </div>
                        </div>

                        {{-- Steps --}}
                        <div class="px-4 pb-3 space-y-1.5">
                            {{-- Step 1 QR or info --}}
                            <div class="flex items-center gap-2.5">
                                <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-brand-100 text-theme-xs font-bold text-brand-600 dark:bg-brand-500/20 dark:text-brand-400">1</span>
                                @if($group['qr_url'])
                                    <button
                                        @click="
                                    qrUrl    = {{ json_encode($group['qr_url']) }};
                                    qrName   = {{ json_encode($group['creditor_name']) }};
                                    qrAmount = {{ json_encode(number_format($group['total'] / 100, 2, ',', ' ') . ' Kč') }};
                                    qrIban   = {{ json_encode($group['iban'] ?? '') }};
                                    qrOpen   = true;
                                "
                                        class="inline-flex items-center gap-1.5 text-theme-xs font-medium text-brand-600 hover:text-brand-800 dark:text-brand-400 dark:hover:text-brand-300 underline underline-offset-2"
                                    >
                                        <x-heroicon-o-qr-code class="w-4 h-4 shrink-0" />
                                        Zobrazit QR kód a zaplatit přes bankovní aplikaci
                                    </button>
                                @else
                                    @if($group['is_system'])
                                        <span class="text-theme-xs text-gray-400 dark:text-gray-500 italic">
                                    QR platba nedostupná — admin nemá nastaven bankovní účet
                                </span>
                                    @else
                                        <span class="text-theme-xs text-gray-500 dark:text-gray-400">
                                    Věřitel nemá nastavený bankovní účet
                                </span>
                                    @endif
                                @endif
                            </div>

                            {{-- Step 2 Mark as paid --}}
                            <div class="flex items-center gap-2.5">
                                <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-brand-100 text-theme-xs font-bold text-brand-600 dark:bg-brand-500/20 dark:text-brand-400">2</span>
                                <span class="text-theme-xs text-gray-500 dark:text-gray-400">Po odeslání platby potvrďte v aplikaci:</span>
                            </div>
                        </div>

                        {{-- CTA button --}}
                        <div class="border-t border-gray-100 p-3 dark:border-gray-800">
                            <button
                                wire:click="markAllPaidForCreditor('{{ $group['creditor_id_key'] }}')"
                                wire:loading.attr="disabled"
                                wire:target="markAllPaidForCreditor('{{ $group['creditor_id_key'] }}')"
                                class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-success-500 px-4 py-2.5 text-theme-sm font-semibold text-white shadow-sm hover:bg-success-600 active:bg-success-700 transition-colors disabled:opacity-60"
                            >
                        <span wire:loading.remove wire:target="markAllPaidForCreditor('{{ $group['creditor_id_key'] }}')">
                            <x-heroicon-o-check-circle class="w-4 h-4 inline -mt-0.5" />
                        </span>
                                <span wire:loading wire:target="markAllPaidForCreditor('{{ $group['creditor_id_key'] }}')">
                            <svg class="w-4 h-4 inline animate-spin -mt-0.5" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                            </svg>
                        </span>
                                Uhradil jsem – {{ number_format($group['total'] / 100, 2, ',', ' ') }} Kč
                            </button>
                        </div>

                    </div>
                @endforeach
            </div>

            <p class="mt-2 text-theme-xs text-gray-400 dark:text-gray-500">
                <x-heroicon-o-information-circle class="w-3.5 h-3.5 inline -mt-0.5" />
                Tlačítko potvrzuje, že jste platbu odeslali. Věřitel ji ověří.
            </p>
        </div>
    @endif

    {{-- QR Modal --}}
    <div
        x-show="qrOpen"
        x-cloak
        @click.self="qrOpen = false"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
    >
        <div class="w-full max-w-xs rounded-2xl border border-gray-200 bg-white p-6 text-center shadow-xl dark:border-gray-800 dark:bg-gray-dark">
            <div class="mb-4 flex items-center justify-between">
                <h3 class="text-theme-sm font-semibold text-gray-800 dark:text-white/90">QR Platba</h3>
                <button @click="qrOpen = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                    <x-heroicon-o-x-mark class="w-5 h-5" />
                </button>
            </div>

            <p class="mb-0.5 text-theme-xs text-gray-500 dark:text-gray-400">Platba pro</p>
            <p class="mb-4 text-theme-sm font-semibold text-gray-800 dark:text-white/90" x-text="qrName"></p>

            <div class="mb-4 flex justify-center">
                <img :src="qrUrl" alt="QR kód platby" class="rounded-xl" width="200" height="200" />
            </div>

            <p class="text-lg font-bold text-error-600 dark:text-error-400" x-text="qrAmount"></p>

            <template x-if="qrIban">
                <p class="mt-2 break-all font-mono text-theme-xs text-gray-500 dark:text-gray-400" x-text="qrIban"></p>
            </template>

            <p class="mt-3 text-theme-xs text-gray-400 dark:text-gray-500">
                Naskenujte kód v mobilní bankovní aplikaci
            </p>
        </div>
    </div>

    {{-- Tab panel --}}
    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">

        {{-- Tab navigation --}}
        <div class="flex border-b border-gray-100 dark:border-gray-800">
            <button
                @click="activeTab = 'unpaid'"
                :class="activeTab === 'unpaid'
                    ? 'border-b-2 border-brand-500 text-brand-500'
                    : 'border-b-2 border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'"
                class="flex items-center gap-2 px-5 py-3.5 text-theme-sm font-medium transition-colors"
            >
                <x-heroicon-o-exclamation-triangle class="w-4 h-4" />
                Nezaplacené
                @if($unpaidDebts->count() > 0)
                    <span class="inline-flex items-center justify-center rounded-full bg-error-50 px-1.5 py-0.5 text-theme-xs font-semibold text-error-600 dark:bg-error-500/15 dark:text-error-400">
                        {{ $unpaidDebts->count() }}
                    </span>
                @endif
            </button>

            <button
                @click="activeTab = 'outgoing'"
                :class="activeTab === 'outgoing'
                    ? 'border-b-2 border-brand-500 text-brand-500'
                    : 'border-b-2 border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'"
                class="flex items-center gap-2 px-5 py-3.5 text-theme-sm font-medium transition-colors"
            >
                <x-heroicon-o-arrow-up-right class="w-4 h-4" />
                Odchozí platby
                @if($outgoingPayments->count() > 0)
                    <span class="inline-flex items-center justify-center rounded-full bg-gray-100 px-1.5 py-0.5 text-theme-xs font-semibold text-gray-600 dark:bg-white/[0.08] dark:text-gray-400">
                        {{ $outgoingPayments->count() }}
                    </span>
                @endif
            </button>

            <button
                @click="activeTab = 'incoming'"
                :class="activeTab === 'incoming'
                    ? 'border-b-2 border-brand-500 text-brand-500'
                    : 'border-b-2 border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'"
                class="flex items-center gap-2 px-5 py-3.5 text-theme-sm font-medium transition-colors"
            >
                <x-heroicon-o-arrow-down-left class="w-4 h-4" />
                Příchozí platby
                @if($incomingPayments->count() > 0)
                    <span class="inline-flex items-center justify-center rounded-full bg-success-50 px-1.5 py-0.5 text-theme-xs font-semibold text-success-600 dark:bg-success-500/15 dark:text-success-400">
                        {{ $incomingPayments->count() }}
                    </span>
                @endif
            </button>
        </div>

        {{-- Tab 1 Unpaid debts --}}
        <div x-show="activeTab === 'unpaid'" class="overflow-x-auto">
            <table class="w-full">
                <thead>
                <tr class="border-b border-gray-100 dark:border-gray-800">
                    <th class="px-5 py-3 text-left text-theme-xs font-medium text-gray-500 uppercase dark:text-gray-400">Zdroj</th>
                    <th class="px-5 py-3 text-left text-theme-xs font-medium text-gray-500 uppercase dark:text-gray-400">Věřitel</th>
                    <th class="px-5 py-3 text-left text-theme-xs font-medium text-gray-500 uppercase dark:text-gray-400">Datum vzniku</th>
                    <th class="px-5 py-3 text-left text-theme-xs font-medium text-gray-500 uppercase dark:text-gray-400">Částka</th>
                    <th class="px-5 py-3 text-right text-theme-xs font-medium text-gray-500 uppercase dark:text-gray-400">Akce</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                @forelse($unpaidDebts as $debt)
                    <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02]">
                        <td class="px-5 py-4 text-theme-sm text-gray-800 dark:text-white/90">
                            @if($debt->order)
                                <span class="flex items-center gap-1.5">
                                        <x-heroicon-o-shopping-bag class="w-4 h-4 text-gray-400 shrink-0" />
                                        Objednávka #{{ $debt->order->id }}
                                    </span>
                            @elseif($debt->lunch)
                                <span class="flex items-center gap-1.5">
                                        <x-heroicon-o-calendar-days class="w-4 h-4 text-gray-400 shrink-0" />
                                        Oběd: {{ $debt->lunch->restaurant_name }}
                                    </span>
                            @else
                                <span class="flex items-center gap-1.5">
                                        <x-heroicon-o-banknotes class="w-4 h-4 text-gray-400 shrink-0" />
                                        Jiný dluh
                                    </span>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-theme-sm text-gray-600 dark:text-gray-400">
                            {{ $debt->creditor?->name ?? 'Lednička' }}
                        </td>
                        <td class="px-5 py-4 text-theme-sm text-gray-600 dark:text-gray-400">
                            {{ $debt->created_at->format('d.m.Y H:i') }}
                        </td>
                        <td class="px-5 py-4 text-theme-sm font-semibold text-error-600 dark:text-error-400">
                            {{ number_format($debt->amount / 100, 2, ',', ' ') }} Kč
                        </td>
                        <td class="px-5 py-4 text-right">
                            <button
                                wire:click="markAsPaid({{ $debt->id }})"
                                wire:loading.attr="disabled"
                                wire:target="markAsPaid({{ $debt->id }})"
                                class="inline-flex items-center gap-1 text-brand-500 hover:text-brand-700 text-theme-sm font-medium disabled:opacity-50"
                            >
                                <x-heroicon-o-check class="w-4 h-4" />
                                Označit jako zaplaceno
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-5 py-12 text-center">
                            <x-heroicon-o-check-circle class="mx-auto mb-3 w-12 h-12 text-success-400" />
                            <p class="text-theme-sm font-medium text-gray-700 dark:text-white/70">Žádné nesplacené dluhy</p>
                            <p class="mt-1 text-theme-xs text-gray-500 dark:text-gray-400">Všechny vaše dluhy jsou vyrovnané. Skvělé!</p>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        {{-- Tab 2 Outgoing payments --}}
        <div x-show="activeTab === 'outgoing'" class="overflow-x-auto">
            <table class="w-full">
                <thead>
                <tr class="border-b border-gray-100 dark:border-gray-800">
                    <th class="px-5 py-3 text-left text-theme-xs font-medium text-gray-500 uppercase dark:text-gray-400">Zdroj</th>
                    <th class="px-5 py-3 text-left text-theme-xs font-medium text-gray-500 uppercase dark:text-gray-400">Zaplaceno komu</th>
                    <th class="px-5 py-3 text-left text-theme-xs font-medium text-gray-500 uppercase dark:text-gray-400">Datum platby</th>
                    <th class="px-5 py-3 text-left text-theme-xs font-medium text-gray-500 uppercase dark:text-gray-400">Částka</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                @forelse($outgoingPayments as $debt)
                    <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02]">
                        <td class="px-5 py-4 text-theme-sm text-gray-800 dark:text-white/90">
                            @if($debt->order)
                                <span class="flex items-center gap-1.5">
                                        <x-heroicon-o-shopping-bag class="w-4 h-4 text-gray-400 shrink-0" />
                                        Objednávka #{{ $debt->order->id }}
                                    </span>
                            @elseif($debt->lunch)
                                <span class="flex items-center gap-1.5">
                                        <x-heroicon-o-calendar-days class="w-4 h-4 text-gray-400 shrink-0" />
                                        Oběd: {{ $debt->lunch->restaurant_name }}
                                    </span>
                            @else
                                <span class="flex items-center gap-1.5">
                                        <x-heroicon-o-banknotes class="w-4 h-4 text-gray-400 shrink-0" />
                                        Jiný dluh
                                    </span>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-theme-sm text-gray-600 dark:text-gray-400">
                            {{ $debt->creditor?->name ?? 'Lednička' }}
                        </td>
                        <td class="px-5 py-4 text-theme-sm text-gray-600 dark:text-gray-400">
                            {{ ($debt->paid_at ?? $debt->updated_at)->format('d.m.Y H:i') }}
                        </td>
                        <td class="px-5 py-4 text-theme-sm font-semibold text-gray-800 dark:text-white/90">
                            {{ number_format($debt->amount / 100, 2, ',', ' ') }} Kč
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-5 py-12 text-center">
                            <x-heroicon-o-arrow-up-right class="mx-auto mb-3 w-12 h-12 text-gray-300 dark:text-gray-600" />
                            <p class="text-theme-sm font-medium text-gray-700 dark:text-white/70">Žádné odchozí platby</p>
                            <p class="mt-1 text-theme-xs text-gray-500 dark:text-gray-400">Historie zaplacených dluhů se zobrazí zde.</p>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        {{--Tab 3 Incoming payments --}}
        <div x-show="activeTab === 'incoming'" class="overflow-x-auto">
            <table class="w-full">
                <thead>
                <tr class="border-b border-gray-100 dark:border-gray-800">
                    <th class="px-5 py-3 text-left text-theme-xs font-medium text-gray-500 uppercase dark:text-gray-400">Zdroj</th>
                    <th class="px-5 py-3 text-left text-theme-xs font-medium text-gray-500 uppercase dark:text-gray-400">Zaplaceno kým</th>
                    <th class="px-5 py-3 text-left text-theme-xs font-medium text-gray-500 uppercase dark:text-gray-400">Datum platby</th>
                    <th class="px-5 py-3 text-left text-theme-xs font-medium text-gray-500 uppercase dark:text-gray-400">Přijatá částka</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                @forelse($incomingPayments as $debt)
                    <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02]">
                        <td class="px-5 py-4 text-theme-sm text-gray-800 dark:text-white/90">
                            @if($debt->order)
                                <span class="flex items-center gap-1.5">
                                        <x-heroicon-o-shopping-bag class="w-4 h-4 text-gray-400 shrink-0" />
                                        Objednávka #{{ $debt->order->id }}
                                    </span>
                            @elseif($debt->lunch)
                                <span class="flex items-center gap-1.5">
                                        <x-heroicon-o-calendar-days class="w-4 h-4 text-gray-400 shrink-0" />
                                        Oběd: {{ $debt->lunch->restaurant_name }}
                                    </span>
                            @else
                                <span class="flex items-center gap-1.5">
                                        <x-heroicon-o-banknotes class="w-4 h-4 text-gray-400 shrink-0" />
                                        Jiný dluh
                                    </span>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-theme-sm text-gray-600 dark:text-gray-400">
                            {{ $debt->user->name }}
                        </td>
                        <td class="px-5 py-4 text-theme-sm text-gray-600 dark:text-gray-400">
                            {{ ($debt->paid_at ?? $debt->updated_at)->format('d.m.Y H:i') }}
                        </td>
                        <td class="px-5 py-4 text-theme-sm font-semibold text-success-600 dark:text-success-400">
                            +{{ number_format($debt->amount / 100, 2, ',', ' ') }} Kč
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-5 py-12 text-center">
                            <x-heroicon-o-arrow-down-left class="mx-auto mb-3 w-12 h-12 text-gray-300 dark:text-gray-600" />
                            <p class="text-theme-sm font-medium text-gray-700 dark:text-white/70">Žádné příchozí platby</p>
                            <p class="mt-1 text-theme-xs text-gray-500 dark:text-gray-400">Platby přijaté od ostatních se zobrazí zde.</p>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

    </div>
</div>
