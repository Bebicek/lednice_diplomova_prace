<div>
    <x-common.page-breadcrumb :pageTitle="'Nastavení systému'" />

    <form wire:submit="save" class="flex flex-col gap-6">

        {{-- Payments settings --}}
        <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center gap-3 px-6 py-4 border-b border-gray-200 dark:border-gray-800">
                <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-brand-50 dark:bg-brand-500/10">
                    <x-heroicon-o-banknotes class="w-4 h-4 text-brand-500" />
                </span>
                <div>
                    <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">Platební údaje</h3>
                    <p class="text-theme-xs text-gray-500 dark:text-gray-400">Bankovní účet pro splácení systémových dluhů</p>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-5 px-6 py-5 sm:grid-cols-2 lg:grid-cols-3">
                <div>
                    <label class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">
                        Číslo účtu
                    </label>
                    <input type="text" wire:model="bank_account_number"
                           placeholder="např. 123456789"
                           class="h-[42px] w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-none focus:ring-2 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                    @error('bank_account_number')
                        <p class="mt-1 text-xs text-error-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">
                        Kód banky
                    </label>
                    <input type="text" wire:model="bank_code"
                           placeholder="např. 0800"
                           class="h-[42px] w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-none focus:ring-2 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                    @error('bank_code')
                        <p class="mt-1 text-xs text-error-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">
                        Název banky <span class="text-gray-400 font-normal">(volitelné)</span>
                    </label>
                    <input type="text" wire:model="bank_name"
                           placeholder="např. Česká spořitelna"
                           class="h-[42px] w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-none focus:ring-2 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                    @error('bank_name')
                        <p class="mt-1 text-xs text-error-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- IBAN preview --}}
            @if($bank_account_number && $bank_code)
                <div class="px-6 pb-5">
                    <div class="flex items-center gap-2 rounded-lg bg-gray-50 dark:bg-gray-800 px-4 py-2.5">
                        <x-heroicon-o-information-circle class="w-4 h-4 text-gray-400 shrink-0" />
                        <span class="text-theme-xs text-gray-600 dark:text-gray-400">
                            Číslo účtu: <span class="font-mono font-medium text-gray-800 dark:text-white/90">{{ $bank_account_number }}/{{ $bank_code }}</span>
                        </span>
                    </div>
                </div>
            @endif
        </div>

        {{-- System settings --}}
        <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center gap-3 px-6 py-4 border-b border-gray-200 dark:border-gray-800">
                <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-brand-50 dark:bg-brand-500/10">
                    <x-heroicon-o-cog-6-tooth class="w-4 h-4 text-brand-500" />
                </span>
                <div>
                    <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">Systém</h3>
                    <p class="text-theme-xs text-gray-500 dark:text-gray-400">Obecná nastavení aplikace</p>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-5 px-6 py-5 sm:grid-cols-2">
                <div>
                    <label class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">
                        Název firmy / systému
                    </label>
                    <input type="text" wire:model="company_name"
                           placeholder="např. Kancelářská lednička s.r.o."
                           class="h-[42px] w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-none focus:ring-2 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                    @error('company_name')
                        <p class="mt-1 text-xs text-error-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">
                        Výchozí měna
                    </label>
                    <select wire:model="default_currency"
                            class="h-[42px] w-full rounded-lg border border-gray-300 bg-transparent px-3 py-2 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-none focus:ring-2 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
                        <option value="CZK">CZK — Česká koruna</option>
                        <option value="EUR">EUR — Euro</option>
                        <option value="USD">USD — Americký dolar</option>
                    </select>
                    @error('default_currency')
                        <p class="mt-1 text-xs text-error-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        {{-- Warehouse --}}
        <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center gap-3 px-6 py-4 border-b border-gray-200 dark:border-gray-800">
                <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-brand-50 dark:bg-brand-500/10">
                    <x-heroicon-o-archive-box class="w-4 h-4 text-brand-500" />
                </span>
                <div>
                    <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">Sklad</h3>
                    <p class="text-theme-xs text-gray-500 dark:text-gray-400">Prahy upozornění pro stav skladu a expirace</p>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-5 px-6 py-5 sm:grid-cols-2">
                <div>
                    <label class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">
                        Práh nízkého stavu skladu <span class="text-gray-400 font-normal">(ks)</span>
                    </label>
                    <input type="number" wire:model="low_stock_threshold"
                           min="0" max="999"
                           class="h-[42px] w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-none focus:ring-2 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800" />
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Produkt se označí jako "nízký sklad" při tomto počtu nebo méně kusů.</p>
                    @error('low_stock_threshold')
                        <p class="mt-1 text-xs text-error-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">
                        Varování před expirací <span class="text-gray-400 font-normal">(dny)</span>
                    </label>
                    <input type="number" wire:model="expiry_warning_days"
                           min="0" max="365"
                           class="h-[42px] w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-none focus:ring-2 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Produkty expirující do tohoto počtu dní se zobrazí v upozorněních.</p>
                    @error('expiry_warning_days')
                        <p class="mt-1 text-xs text-error-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        {{-- Email --}}
        <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center gap-3 px-6 py-4 border-b border-gray-200 dark:border-gray-800">
                <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-brand-50 dark:bg-brand-500/10">
                    <x-heroicon-o-envelope class="w-4 h-4 text-brand-500" />
                </span>
                <div>
                    <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">Automatizace</h3>
                    <p class="text-theme-xs text-gray-500 dark:text-gray-400">Automatické e-mailové notifikace</p>
                </div>
            </div>

            <div class="px-6 py-5">
                <label class="flex items-start gap-3 cursor-pointer">
                    <div class="relative mt-0.5">
                        <input type="checkbox" wire:model="auto_emails_enabled" class="sr-only peer" />
                        <div class="w-10 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-gray-700
                                    peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full
                                    peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px]
                                    after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full
                                    after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-brand-500">
                        </div>
                    </div>
                    <div>
                        <span class="text-theme-sm font-medium text-gray-800 dark:text-white/90">Automatické e-maily</span>
                        <p class="mt-0.5 text-theme-xs text-gray-500 dark:text-gray-400">
                            Odesílat automatické e-maily při vzniku dluhu, zaplacení, expiraci apod.
                        </p>
                    </div>
                </label>
            </div>
        </div>

        {{-- Save button --}}
        <div class="flex justify-end">
            <button type="submit"
                    class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-5 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs hover:bg-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-500/30">
                <x-heroicon-o-check class="w-4 h-4" />
                Uložit nastavení
            </button>
        </div>

    </form>
</div>
