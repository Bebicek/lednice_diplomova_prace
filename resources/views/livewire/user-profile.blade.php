<div>
    <x-common.page-breadcrumb :pageTitle="'Můj profil'" />

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">

        {{--Card 1 Basic info --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
            <h3 class="mb-5 text-base font-semibold text-gray-800 dark:text-white/90">
                Základní informace
            </h3>

            @if(session('profile_success'))
                <div class="mb-4 flex items-center gap-3 rounded-lg bg-success-50 p-4 text-success-700 dark:bg-success-500/10 dark:text-success-400">
                    <x-heroicon-o-check-circle class="w-5 h-5 shrink-0" />
                    {{ session('profile_success') }}
                </div>
            @endif

            <form wire:submit.prevent="saveProfile" class="space-y-4">

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                        Jméno
                    </label>
                    <input
                        type="text"
                        wire:model="name"
                        class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 outline-none focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
                    />
                    @error('name')
                    <p class="mt-1 text-sm text-error-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                        E-mail
                    </label>
                    <input
                        type="email"
                        wire:model="email"
                        class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 outline-none focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
                    />
                    @error('email')
                    <p class="mt-1 text-sm text-error-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="pt-1">
                    <button
                        type="submit"
                        wire:loading.attr="disabled"
                        class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600 disabled:opacity-60"
                    >
                        <span wire:loading.remove wire:target="saveProfile">Uložit změny</span>
                        <span wire:loading wire:target="saveProfile">Ukládám…</span>
                    </button>
                </div>

            </form>
        </div>

        {{-- Card 2 Bank details --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="mb-5 flex items-start justify-between">
                <div>
                    <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">
                        Bankovní údaje
                    </h3>
                    <p class="mt-1 text-theme-xs text-gray-500 dark:text-gray-400">
                        Slouží ke generování QR kódů pro platby od ostatních.
                    </p>
                </div>
                <x-heroicon-o-banknotes class="w-6 h-6 text-gray-400 shrink-0" />
            </div>

            @if(session('bank_success'))
                <div class="mb-4 flex items-center gap-3 rounded-lg bg-success-50 p-4 text-success-700 dark:bg-success-500/10 dark:text-success-400">
                    <x-heroicon-o-check-circle class="w-5 h-5 shrink-0" />
                    {{ session('bank_success') }}
                </div>
            @endif

            <form wire:submit.prevent="saveBankDetails" class="space-y-4">

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                        Číslo účtu
                        <span class="ml-1 text-xs font-normal text-gray-400">např. 123456-1234567890 nebo 1234567890</span>
                    </label>
                    <input
                        type="text"
                        wire:model="bank_number"
                        placeholder="1234567890"
                        class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 outline-none focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
                    />
                    @error('bank_number')
                    <p class="mt-1 text-sm text-error-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                        Kód banky
                        <span class="ml-1 text-xs font-normal text-gray-400">4 číslice, např. 0800 (ČS), 0100 (KB)</span>
                    </label>
                    <input
                        type="text"
                        wire:model="bank_code"
                        placeholder="0800"
                        maxlength="4"
                        class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 outline-none focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
                    />
                    @error('bank_code')
                    <p class="mt-1 text-sm text-error-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="pt-1">
                    <button
                        type="submit"
                        wire:loading.attr="disabled"
                        class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600 disabled:opacity-60"
                    >
                        <span wire:loading.remove wire:target="saveBankDetails">Uložit bankovní údaje</span>
                        <span wire:loading wire:target="saveBankDetails">Ukládám…</span>
                    </button>
                </div>

            </form>
        </div>

        {{-- Card 3 Change password --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
            <h3 class="mb-5 text-base font-semibold text-gray-800 dark:text-white/90">
                Změna hesla
            </h3>

            @if(session('password_success'))
                <div class="mb-4 flex items-center gap-3 rounded-lg bg-success-50 p-4 text-success-700 dark:bg-success-500/10 dark:text-success-400">
                    <x-heroicon-o-check-circle class="w-5 h-5 shrink-0" />
                    {{ session('password_success') }}
                </div>
            @endif

            <form wire:submit.prevent="changePassword" class="space-y-4">

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                        Stávající heslo
                    </label>
                    <input
                        type="password"
                        wire:model="current_password"
                        autocomplete="current-password"
                        class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 outline-none focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
                    />
                    @error('current_password')
                    <p class="mt-1 text-sm text-error-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                        Nové heslo
                        <span class="ml-1 text-xs font-normal text-gray-400">(min. 8 znaků)</span>
                    </label>
                    <input
                        type="password"
                        wire:model="new_password"
                        autocomplete="new-password"
                        class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 outline-none focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
                    />
                    @error('new_password')
                    <p class="mt-1 text-sm text-error-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                        Potvrzení nového hesla
                    </label>
                    <input
                        type="password"
                        wire:model="new_password_confirmation"
                        autocomplete="new-password"
                        class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 outline-none focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
                    />
                </div>

                <div class="pt-1">
                    <button
                        type="submit"
                        wire:loading.attr="disabled"
                        class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600 disabled:opacity-60"
                    >
                        <span wire:loading.remove wire:target="changePassword">Změnit heslo</span>
                        <span wire:loading wire:target="changePassword">Měním…</span>
                    </button>
                </div>

            </form>
        </div>

    </div>
</div>
