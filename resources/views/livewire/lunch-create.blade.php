<div>
    <x-common.page-breadcrumb :pageTitle="'Nový oběd'" />

    @if(session('success'))
        <div class="mb-4 rounded-lg bg-success-50 p-4 text-success-700 dark:bg-success-500/10 dark:text-success-400">
            {{ session('success') }}
        </div>
    @endif

    {{-- Stepper --}}
    <div class="mb-8 flex items-center gap-0">
        @foreach(['Základní info', 'Účastníci', 'Shrnutí'] as $i => $label)
            @php $num = $i + 1; @endphp
            <div class="flex items-center {{ $i < 2 ? 'flex-1' : '' }}">
                <div class="flex items-center gap-2">
                    <div class="flex h-8 w-8 items-center justify-center rounded-full text-sm font-semibold
                        {{ $step > $num ? 'bg-success-500 text-white' : ($step === $num ? 'bg-brand-500 text-white' : 'bg-gray-200 text-gray-500 dark:bg-gray-700 dark:text-gray-400') }}">
                        @if($step > $num)
                            <x-heroicon-o-check class="w-4 h-4" />
                        @else
                            {{ $num }}
                        @endif
                    </div>
                    <span class="hidden sm:block text-theme-sm font-medium
                        {{ $step === $num ? 'text-gray-800 dark:text-white/90' : 'text-gray-400 dark:text-gray-500' }}">
                        {{ $label }}
                    </span>
                </div>
                @if($i < 2)
                    <div class="mx-3 flex-1 h-px {{ $step > $num ? 'bg-success-400' : 'bg-gray-200 dark:bg-gray-700' }}"></div>
                @endif
            </div>
        @endforeach
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">

        {{-- Basic info --}}
        @if($step === 1)
            <h2 class="mb-5 text-lg font-semibold text-gray-800 dark:text-white/90">Základní informace</h2>

            <div class="space-y-4">
                <div>
                    <label class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-300">
                        Restaurace / název oběda <span class="text-error-500">*</span>
                    </label>
                    <input type="text" wire:model="restaurantName"
                           placeholder="např. Pizzerie Da Marco"
                           class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-theme-sm text-gray-800 placeholder:text-gray-400 focus:border-brand-300 focus:outline-none focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:text-white/90 dark:placeholder:text-gray-500 dark:focus:border-brand-800" />
                    @error('restaurantName')
                    <p class="mt-1 text-theme-xs text-error-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-300">
                        Poznámka <span class="text-gray-400 font-normal">(volitelné)</span>
                    </label>
                    <textarea wire:model="description" rows="3"
                              placeholder="Poznámka k obědu…"
                              class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 placeholder:text-gray-400 focus:border-brand-300 focus:outline-none focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:text-white/90 dark:placeholder:text-gray-500 dark:focus:border-brand-800"></textarea>
                </div>
            </div>

            <div class="mt-6 flex justify-end">
                <button wire:click="nextStep"
                        class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-5 py-2.5 text-theme-sm font-semibold text-white hover:bg-brand-600 transition-colors">
                    Pokračovat
                    <x-heroicon-o-arrow-right class="w-4 h-4" />
                </button>
            </div>
        @endif

        {{-- Participants + items --}}
        @if($step === 2)
            <div class="mb-5 flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-800 dark:text-white/90">Účastníci a položky</h2>
                <span class="text-theme-xs text-gray-400">{{ count($participants) }} {{ count($participants) === 1 ? 'účastník' : (count($participants) <= 4 ? 'účastníci' : 'účastníků') }}</span>
            </div>

            @error('participants')
            <div class="mb-4 rounded-lg bg-error-50 p-3 text-theme-sm text-error-600 dark:bg-error-500/10 dark:text-error-400">
                {{ $message }}
            </div>
            @enderror

            {{-- Participant cards --}}
            <div class="space-y-4">
                @foreach($participants as $index => $participant)
                    <div class="rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                        {{-- Participant header --}}
                        <div class="flex items-center justify-between bg-gray-50 px-4 py-3 dark:bg-white/[0.03]">
                            <div class="flex items-center gap-2">
                                <div class="flex h-7 w-7 items-center justify-center rounded-full bg-brand-100 text-brand-600 dark:bg-brand-500/20 dark:text-brand-400">
                                    <x-heroicon-o-user class="w-3.5 h-3.5" />
                                </div>
                                <span class="text-theme-sm font-semibold text-gray-800 dark:text-white/90">
                                    {{ $participant['name'] }}
                                </span>
                                @if($index === 0)
                                    <span class="rounded-full bg-brand-50 px-2 py-0.5 text-theme-xs font-medium text-brand-600 dark:bg-brand-500/10 dark:text-brand-400">
                                        Organizátor
                                    </span>
                                @endif
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-theme-xs font-medium text-gray-500 dark:text-gray-400">
                                    {{ number_format(collect($participant['items'])->sum('price') / 100, 2, ',', ' ') }} Kč
                                </span>
                                @if($index !== 0)
                                    <button wire:click="removeParticipant({{ $index }})"
                                            class="text-gray-400 hover:text-error-500 transition-colors">
                                        <x-heroicon-o-x-mark class="w-4 h-4" />
                                    </button>
                                @endif
                            </div>
                        </div>

                        <div class="p-4">
                            {{-- Item list --}}
                            @if(count($participant['items']) > 0)
                                <div class="mb-3 space-y-1.5">
                                    @foreach($participant['items'] as $itemIndex => $item)
                                        <div class="flex items-center justify-between rounded-lg bg-gray-50 px-3 py-2 dark:bg-white/[0.02]">
                                            <span class="text-theme-sm text-gray-700 dark:text-gray-300">{{ $item['name'] }}</span>
                                            <div class="flex items-center gap-3">
                                                <span class="text-theme-sm font-medium text-gray-800 dark:text-white/90">
                                                    {{ number_format($item['price'] / 100, 2, ',', ' ') }} Kč
                                                </span>
                                                <button wire:click="removeItem({{ $index }}, {{ $itemIndex }})"
                                                        class="text-gray-300 hover:text-error-500 transition-colors">
                                                    <x-heroicon-o-trash class="w-3.5 h-3.5" />
                                                </button>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            @error("item_{$index}")
                            <p class="mb-2 text-theme-xs text-error-500">{{ $message }}</p>
                            @enderror

                            {{-- Add item form alpine local state --}}
                            <div x-data="{ name: '', price: '' }"
                                 @item-added-{{ $index }}.window="name = ''; price = '';"
                                 class="flex gap-2">
                                <input
                                    x-model="name"
                                    type="text"
                                    placeholder="Název položky"
                                    class="h-9 flex-1 min-w-0 rounded-lg border border-gray-300 bg-transparent px-3 text-theme-sm text-gray-800 placeholder:text-gray-400 focus:border-brand-300 focus:outline-none dark:border-gray-600 dark:text-white/90 dark:placeholder:text-gray-500" />
                                <input
                                    x-model="price"
                                    type="number"
                                    step="0.01"
                                    placeholder="Kč"
                                    class="h-9 w-24 rounded-lg border border-gray-300 bg-transparent px-3 text-theme-sm text-gray-800 placeholder:text-gray-400 focus:border-brand-300 focus:outline-none dark:border-gray-600 dark:text-white/90 dark:placeholder:text-gray-500" />
                                <button
                                    @click="$wire.addItem({{ $index }}, name.trim(), price)"
                                    class="inline-flex h-9 items-center gap-1 rounded-lg border border-brand-300 px-3 text-theme-xs font-medium text-brand-600 hover:bg-brand-50 dark:border-brand-600 dark:text-brand-400 dark:hover:bg-brand-500/10 transition-colors">
                                    <x-heroicon-o-plus class="w-3.5 h-3.5" />
                                    Přidat
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Add participant --}}
            <div class="mt-4 flex gap-2">
                <select wire:model="selectedUserId"
                        class="h-10 flex-1 rounded-lg border border-gray-300 bg-transparent px-3 text-theme-sm text-gray-800 focus:border-brand-300 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                    <option value="">- Vyberte účastníka -</option>
                    @foreach($availableUsers as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </select>
                <button wire:click="addParticipant"
                        class="inline-flex h-10 items-center gap-1.5 rounded-lg bg-gray-100 px-4 text-theme-sm font-medium text-gray-700 hover:bg-gray-200 dark:bg-white/[0.06] dark:text-gray-300 dark:hover:bg-white/[0.1] transition-colors">
                    <x-heroicon-o-user-plus class="w-4 h-4" />
                    Přidat účastníka
                </button>
            </div>
            @error('selectedUserId')
            <p class="mt-1 text-theme-xs text-error-500">{{ $message }}</p>
            @enderror

            <div class="mt-6 flex justify-between">
                <button wire:click="prevStep"
                        class="inline-flex items-center gap-2 rounded-lg border border-gray-300 px-5 py-2.5 text-theme-sm font-medium text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-white/[0.04] transition-colors">
                    <x-heroicon-o-arrow-left class="w-4 h-4" />
                    Zpět
                </button>
                <button wire:click="nextStep"
                        class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-5 py-2.5 text-theme-sm font-semibold text-white hover:bg-brand-600 transition-colors">
                    Pokračovat
                    <x-heroicon-o-arrow-right class="w-4 h-4" />
                </button>
            </div>
        @endif

        {{-- delivery + split + preview --}}
        @if($step === 3)
            <h2 class="mb-5 text-lg font-semibold text-gray-800 dark:text-white/90">Doprava a způsob dělení ceny</h2>

            <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2">
                {{-- Delivery cost --}}
                <div>
                    <label class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-300">
                        Cena dopravy (Kč) <span class="text-error-500">*</span>
                    </label>
                    <input type="number" wire:model.live="deliveryCostKc" min="0" step="0.01"
                           placeholder="0"
                           class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-theme-sm text-gray-800 placeholder:text-gray-400 focus:border-brand-300 focus:outline-none focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:text-white/90 dark:placeholder:text-gray-500 dark:focus:border-brand-800 @error('deliveryCostKc') border-error-300 dark:border-error-600 @enderror" />
                    <p class="mt-1 text-theme-xs text-gray-400">Rozdělí se rovnoměrně mezi všechny</p>
                    @error('deliveryCostKc')
                    <p class="mt-1 text-theme-xs text-error-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Split method --}}
                <div>
                    <label class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-300">
                        Způsob dělení ceny <span class="text-error-500">*</span>
                    </label>
                    <div class="flex flex-col gap-2 pt-1">
                        <label class="flex cursor-pointer items-center gap-3">
                            <input type="radio" wire:model.live="splitMethod" value="by_price"
                                   class="accent-brand-500 w-4 h-4" />
                            <span class="text-theme-sm text-gray-700 dark:text-gray-300">
                                <strong>Podle ceny</strong>
                                <span class="text-gray-400"> - každý platí co objednal</span>
                            </span>
                        </label>
                        <label class="flex cursor-pointer items-center gap-3">
                            <input type="radio" wire:model.live="splitMethod" value="equal"
                                   class="accent-brand-500 w-4 h-4" />
                            <span class="text-theme-sm text-gray-700 dark:text-gray-300">
                                <strong>Rovnoměrně</strong>
                                <span class="text-gray-400"> - celková suma / počet lidí</span>
                            </span>
                        </label>
                    </div>
                    @error('splitMethod')
                    <p class="mt-1 text-theme-xs text-error-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Preview table --}}
            <div class="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700">
                <table class="w-full">
                    <thead>
                    <tr class="border-b border-gray-100 bg-gray-50 dark:border-gray-700 dark:bg-white/[0.03]">
                        <th class="px-4 py-3 text-left text-theme-xs font-medium uppercase text-gray-500 dark:text-gray-400">Účastník</th>
                        <th class="px-4 py-3 text-right text-theme-xs font-medium uppercase text-gray-500 dark:text-gray-400">Položky</th>
                        <th class="px-4 py-3 text-right text-theme-xs font-medium uppercase text-gray-500 dark:text-gray-400">Doprava</th>
                        <th class="px-4 py-3 text-right text-theme-xs font-medium uppercase text-gray-500 dark:text-gray-400">Celkem</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @foreach($shares as $i => $share)
                        <tr class="{{ $i === 0 ? 'bg-brand-50/50 dark:bg-brand-500/5' : '' }}">
                            <td class="px-4 py-3 text-theme-sm text-gray-800 dark:text-white/90">
                                {{ $share['name'] }}
                                @if($i === 0)
                                    <span class="ml-1 text-theme-xs text-brand-500">(ty)</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right text-theme-sm text-gray-600 dark:text-gray-400">
                                {{ number_format($share['items_total'] / 100, 2, ',', ' ') }} Kč
                            </td>
                            <td class="px-4 py-3 text-right text-theme-sm text-gray-600 dark:text-gray-400">
                                {{ number_format($share['delivery'] / 100, 2, ',', ' ') }} Kč
                            </td>
                            <td class="px-4 py-3 text-right text-theme-sm font-bold {{ $i === 0 ? 'text-brand-600 dark:text-brand-400' : 'text-gray-800 dark:text-white/90' }}">
                                {{ number_format($share['final'] / 100, 2, ',', ' ') }} Kč
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                    <tfoot>
                    <tr class="border-t-2 border-gray-200 dark:border-gray-600">
                        <td colspan="3" class="px-4 py-3 text-theme-sm font-semibold text-gray-700 dark:text-gray-300">
                            Celková suma
                        </td>
                        <td class="px-4 py-3 text-right text-theme-sm font-bold text-gray-900 dark:text-white">
                            {{ number_format(collect($shares)->sum('final') / 100, 2, ',', ' ') }} Kč
                        </td>
                    </tr>
                    </tfoot>
                </table>
            </div>

            <div class="mt-6 flex justify-between">
                <button wire:click="prevStep"
                        class="inline-flex items-center gap-2 rounded-lg border border-gray-300 px-5 py-2.5 text-theme-sm font-medium text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-white/[0.04] transition-colors">
                    <x-heroicon-o-arrow-left class="w-4 h-4" />
                    Zpět
                </button>
                <button wire:click="submit"
                        wire:loading.attr="disabled"
                        class="inline-flex items-center gap-2 rounded-lg bg-success-500 px-6 py-2.5 text-theme-sm font-semibold text-white hover:bg-success-600 disabled:opacity-60 transition-colors">
                    <x-heroicon-o-check class="w-4 h-4" />
                    <span wire:loading.remove wire:target="submit">Vytvořit oběd</span>
                    <span wire:loading wire:target="submit">Ukládám…</span>
                </button>
            </div>
        @endif

    </div>
</div>
