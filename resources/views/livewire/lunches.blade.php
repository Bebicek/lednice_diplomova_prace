<div x-data="{ activeTab: '{{ $pendingDebts->count() > 0 ? 'pending' : 'mine' }}' }">
    <x-common.page-breadcrumb :pageTitle="'Obědy'" />

    @if(session('success'))
        <div class="mb-4 rounded-lg bg-success-50 p-4 text-success-700 dark:bg-success-500/10 dark:text-success-400">
            {{ session('success') }}
        </div>
    @endif

    {{-- Header action --}}
    <div class="mb-6 flex items-center justify-between">
        <div class="flex items-center gap-4">
            {{-- Stats --}}
            <span class="text-theme-sm text-gray-500 dark:text-gray-400">
                {{ $myLunches->count() + $participatedLunches->count() }} {{ ($myLunches->count() + $participatedLunches->count()) === 1 ? 'oběd' : 'obědů' }} celkem
            </span>
            @if($pendingDebts->count() > 0)
                <span class="inline-flex items-center gap-1 rounded-full bg-warning-50 px-2.5 py-1 text-theme-xs font-semibold text-warning-700 dark:bg-warning-500/15 dark:text-warning-400">
                    <x-heroicon-o-exclamation-triangle class="w-3.5 h-3.5" />
                    {{ $pendingDebts->count() }} čeká na přijetí
                </span>
            @endif
        </div>
        <a href="{{ route('lunches.create') }}"
           class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-theme-sm font-semibold text-white hover:bg-brand-600 transition-colors">
            <x-heroicon-o-plus class="w-4 h-4" />
            Nový oběd
        </a>
    </div>

    {{-- Tab panel --}}
    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">

        {{-- Tabs --}}
        <div class="flex border-b border-gray-100 dark:border-gray-800">
            <button @click="activeTab = 'pending'"
                    :class="activeTab === 'pending'
                    ? 'border-b-2 border-warning-500 text-warning-600 dark:text-warning-400'
                    : 'border-b-2 border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'"
                    class="flex items-center gap-2 px-5 py-3.5 text-theme-sm font-medium transition-colors">
                <x-heroicon-o-bell class="w-4 h-4" />
                Čekající přijetí
                @if($pendingDebts->count() > 0)
                    <span class="inline-flex items-center justify-center rounded-full bg-warning-50 px-1.5 py-0.5 text-theme-xs font-semibold text-warning-700 dark:bg-warning-500/15 dark:text-warning-400">
                        {{ $pendingDebts->count() }}
                    </span>
                @endif
            </button>

            <button @click="activeTab = 'mine'"
                    :class="activeTab === 'mine'
                    ? 'border-b-2 border-brand-500 text-brand-500'
                    : 'border-b-2 border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'"
                    class="flex items-center gap-2 px-5 py-3.5 text-theme-sm font-medium transition-colors">
                <x-heroicon-o-user class="w-4 h-4" />
                Moje obědy
                @if($myLunches->count() > 0)
                    <span class="inline-flex items-center justify-center rounded-full bg-gray-100 px-1.5 py-0.5 text-theme-xs font-semibold text-gray-600 dark:bg-white/[0.08] dark:text-gray-400">
                        {{ $myLunches->count() }}
                    </span>
                @endif
            </button>

            <button @click="activeTab = 'participated'"
                    :class="activeTab === 'participated'
                    ? 'border-b-2 border-brand-500 text-brand-500'
                    : 'border-b-2 border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'"
                    class="flex items-center gap-2 px-5 py-3.5 text-theme-sm font-medium transition-colors">
                <x-heroicon-o-users class="w-4 h-4" />
                Účastnil jsem se
                @if($participatedLunches->count() > 0)
                    <span class="inline-flex items-center justify-center rounded-full bg-gray-100 px-1.5 py-0.5 text-theme-xs font-semibold text-gray-600 dark:bg-white/[0.08] dark:text-gray-400">
                        {{ $participatedLunches->count() }}
                    </span>
                @endif
            </button>
        </div>

        {{-- Waiting for confirmation --}}
        <div x-show="activeTab === 'pending'">
            @forelse($pendingDebts as $debt)
                @php
                    $myItems = $debt->lunch?->participants->first()?->items ?? collect();
                @endphp
                <div class="border-b border-gray-100 p-5 dark:border-gray-800 last:border-0">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex-1">
                            {{-- Lunch info --}}
                            <div class="flex items-center gap-2 mb-2">
                                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-warning-50 text-warning-600 dark:bg-warning-500/10">
                                    <x-heroicon-o-calendar-days class="w-4 h-4" />
                                </div>
                                <div>
                                    <p class="text-theme-sm font-semibold text-gray-800 dark:text-white/90">
                                        {{ $debt->lunch?->restaurant_name }}
                                    </p>
                                    <p class="text-theme-xs text-gray-500 dark:text-gray-400">
                                        Organizoval/a: {{ $debt->creditor?->name }} · {{ $debt->created_at->format('d.m.Y') }}
                                    </p>
                                </div>
                            </div>

                            {{-- Items --}}
                            @if($myItems->count() > 0)
                                <div class="ml-11 mb-3 space-y-0.5">
                                    @foreach($myItems as $item)
                                        <div class="flex items-center justify-between text-theme-xs text-gray-600 dark:text-gray-400">
                                            <span>{{ $item->name }}</span>
                                            <span>{{ number_format($item->price / 100, 2, ',', ' ') }} Kč</span>
                                        </div>
                                    @endforeach
                                    @if($debt->lunch?->delivery_cost > 0)
                                        <div class="flex items-center justify-between text-theme-xs text-gray-400 dark:text-gray-500 border-t border-dashed border-gray-200 dark:border-gray-700 pt-0.5 mt-0.5">
                                            <span>Doprava (podíl)</span>
                                            <span>{{ number_format(($debt->lunch->delivery_cost / $debt->lunch->participants->count()) / 100, 2, ',', ' ') }} Kč</span>
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>

                        {{-- Amount + action --}}
                        <div class="text-right shrink-0">
                            <p class="text-lg font-bold text-error-600 dark:text-error-400 mb-2">
                                {{ number_format($debt->amount / 100, 2, ',', ' ') }} Kč
                            </p>
                            <button wire:click="acceptDebt({{ $debt->id }})"
                                    wire:loading.attr="disabled"
                                    wire:target="acceptDebt({{ $debt->id }})"
                                    class="inline-flex items-center gap-1.5 rounded-lg bg-success-500 px-3 py-2 text-theme-xs font-semibold text-white hover:bg-success-600 disabled:opacity-60 transition-colors">
                                <x-heroicon-o-check class="w-3.5 h-3.5" />
                                Přijmout dluh
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="px-5 py-16 text-center">
                    <x-heroicon-o-check-circle class="mx-auto mb-3 w-12 h-12 text-success-400" />
                    <p class="text-theme-sm font-medium text-gray-700 dark:text-white/70">Žádné čekající dluhy</p>
                    <p class="mt-1 text-theme-xs text-gray-500 dark:text-gray-400">Pokud vás někdo přidá do oběda, zobrazí se zde.</p>
                </div>
            @endforelse
        </div>

        {{-- My launches --}}
        <div x-show="activeTab === 'mine'">
            @forelse($myLunches as $lunch)
                @php
                    $allPaid    = $lunch->debts->every(fn($d) => $d->is_paid);
                    $allAccepted = $lunch->debts->every(fn($d) => $d->is_accepted);
                    $totalOwed  = $lunch->debts->where('is_paid', false)->where('is_accepted', true)->sum('amount');
                @endphp
                <div class="border-b border-gray-100 p-5 dark:border-gray-800 last:border-0">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-1">
                                <p class="text-theme-sm font-semibold text-gray-800 dark:text-white/90">
                                    {{ $lunch->restaurant_name }}
                                </p>
                                @if($allPaid)
                                    <span class="rounded-full bg-success-50 px-2 py-0.5 text-theme-xs font-medium text-success-600 dark:bg-success-500/10 dark:text-success-400">
                                        Vyrovnáno
                                    </span>
                                @elseif(!$allAccepted)
                                    <span class="rounded-full bg-warning-50 px-2 py-0.5 text-theme-xs font-medium text-warning-600 dark:bg-warning-500/10 dark:text-warning-400">
                                        Čeká na přijetí
                                    </span>
                                @else
                                    <span class="rounded-full bg-error-50 px-2 py-0.5 text-theme-xs font-medium text-error-600 dark:bg-error-500/10 dark:text-error-400">
                                        Čeká na platbu
                                    </span>
                                @endif
                            </div>
                            <p class="text-theme-xs text-gray-500 dark:text-gray-400">
                                {{ $lunch->created_at->format('d.m.Y') }} · {{ $lunch->participants->count() }} účastníků
                                @if($lunch->split_method === 'equal') · rovnoměrně @else · podle ceny @endif
                            </p>

                            {{-- Participants summary --}}
                            <div class="mt-3 space-y-1">
                                @foreach($lunch->participants as $participant)
                                    @php
                                        $pDebt = $lunch->debts->firstWhere('user_id', $participant->user_id);
                                    @endphp
                                    <div class="flex items-center justify-between text-theme-xs">
                                        <span class="text-gray-600 dark:text-gray-400">
                                            {{ $participant->user?->name }}
                                            @if($participant->user_id === auth()->id())
                                                <span class="text-gray-400">(ty)</span>
                                            @endif
                                        </span>
                                        <div class="flex items-center gap-2">
                                            <span class="font-medium text-gray-700 dark:text-gray-300">
                                                {{ number_format($participant->amount / 100, 2, ',', ' ') }} Kč
                                            </span>
                                            @if($participant->user_id === auth()->id())
                                                <span class="text-gray-400">—</span>
                                            @elseif($pDebt && $pDebt->is_paid)
                                                <span class="text-success-500"><x-heroicon-o-check-circle class="w-3.5 h-3.5" /></span>
                                            @elseif($pDebt && !$pDebt->is_accepted)
                                                <span class="text-warning-500"><x-heroicon-o-clock class="w-3.5 h-3.5" /></span>
                                            @else
                                                <span class="text-error-400"><x-heroicon-o-x-circle class="w-3.5 h-3.5" /></span>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="text-right shrink-0">
                            @if(!$allPaid && $totalOwed > 0)
                                <p class="text-theme-xs text-gray-400 dark:text-gray-500">Zbývá vybrat</p>
                                <p class="text-theme-sm font-bold text-gray-800 dark:text-white/90">
                                    {{ number_format($totalOwed / 100, 2, ',', ' ') }} Kč
                                </p>
                            @elseif($allPaid)
                                <x-heroicon-o-check-circle class="w-7 h-7 text-success-400" />
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="px-5 py-16 text-center">
                    <x-heroicon-o-calendar-days class="mx-auto mb-3 w-12 h-12 text-gray-300 dark:text-gray-600" />
                    <p class="text-theme-sm font-medium text-gray-700 dark:text-white/70">Zatím žádné obědy</p>
                    <p class="mt-1 text-theme-xs text-gray-500 dark:text-gray-400">Vytvořte nový oběd pomocí tlačítka výše.</p>
                </div>
            @endforelse
        </div>

        {{-- I prarticipated --}}
        <div x-show="activeTab === 'participated'">
            @forelse($participatedLunches as $lunch)
                @php
                    $myParticipant = $lunch->participants->first();
                    $myDebt = \App\Models\Debt::where('lunch_id', $lunch->id)
                        ->where('user_id', auth()->id())
                        ->first();
                @endphp
                <div class="border-b border-gray-100 p-5 dark:border-gray-800 last:border-0">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <p class="text-theme-sm font-semibold text-gray-800 dark:text-white/90">
                                    {{ $lunch->restaurant_name }}
                                </p>
                                @if($myDebt?->is_paid)
                                    <span class="rounded-full bg-success-50 px-2 py-0.5 text-theme-xs font-medium text-success-600 dark:bg-success-500/10 dark:text-success-400">Zaplaceno</span>
                                @elseif($myDebt && !$myDebt->is_accepted)
                                    <span class="rounded-full bg-warning-50 px-2 py-0.5 text-theme-xs font-medium text-warning-600 dark:bg-warning-500/10 dark:text-warning-400">Čeká na přijetí</span>
                                @else
                                    <span class="rounded-full bg-error-50 px-2 py-0.5 text-theme-xs font-medium text-error-600 dark:bg-error-500/10 dark:text-error-400">Nezaplaceno</span>
                                @endif
                            </div>
                            <p class="text-theme-xs text-gray-500 dark:text-gray-400">
                                Organizoval/a: {{ $lunch->organizer?->name }} · {{ $lunch->created_at->format('d.m.Y') }}
                            </p>

                            {{-- My items --}}
                            @if($myParticipant && $myParticipant->items->count() > 0)
                                <div class="mt-2 space-y-0.5">
                                    @foreach($myParticipant->items as $item)
                                        <div class="flex items-center gap-2 text-theme-xs text-gray-500 dark:text-gray-400">
                                            <x-heroicon-o-minus class="w-3 h-3 shrink-0" />
                                            {{ $item->name }} — {{ number_format($item->price / 100, 2, ',', ' ') }} Kč
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                        <div class="text-right shrink-0">
                            <p class="text-theme-xs text-gray-400 dark:text-gray-500">Tvůj podíl</p>
                            <p class="text-theme-sm font-bold {{ $myDebt?->is_paid ? 'text-success-600' : 'text-error-600' }} dark:{{ $myDebt?->is_paid ? 'text-success-400' : 'text-error-400' }}">
                                {{ number_format(($myParticipant?->amount ?? 0) / 100, 2, ',', ' ') }} Kč
                            </p>
                        </div>
                    </div>
                </div>
            @empty
                <div class="px-5 py-16 text-center">
                    <x-heroicon-o-users class="mx-auto mb-3 w-12 h-12 text-gray-300 dark:text-gray-600" />
                    <p class="text-theme-sm font-medium text-gray-700 dark:text-white/70">Zatím žádné obědy</p>
                    <p class="mt-1 text-theme-xs text-gray-500 dark:text-gray-400">Obědy, ve kterých jste účastníkem, se zobrazí zde.</p>
                </div>
            @endforelse
        </div>

    </div>
</div>
