<div class="relative z-1 bg-white p-6 sm:p-0 dark:bg-gray-900">
    <div class="relative flex h-screen w-full flex-col justify-center sm:p-0 lg:flex-row dark:bg-gray-900">
        <!-- Form -->
        <div class="flex w-full flex-1 flex-col lg:w-1/2">
            <div class="mx-auto flex w-full max-w-md flex-1 flex-col justify-center">
                <div>
                    <div class="mb-5 sm:mb-8">
                        <h1 class="text-title-sm sm:text-title-md mb-2 font-semibold text-gray-800 dark:text-white/90">
                            Přihlášení
                        </h1>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Zadejte svůj e-mail a heslo pro přihlášení
                        </p>
                    </div>
                    <div>
                        <form wire:submit.prevent="login">
                            <div class="space-y-5">
                                <!-- Email -->
                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                        E-mail<span class="text-error-500">*</span>
                                    </label>
                                    <input type="email" wire:model="email" id="email" name="email" placeholder="vas@email.cz"
                                           class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" />
                                    @error('email')
                                    <p class="mt-1 text-sm text-error-500">{{ $message }}</p>
                                    @enderror
                                </div>
                                <!-- Password -->
                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                        Heslo<span class="text-error-500">*</span>
                                    </label>
                                    <div x-data="{ showPassword: false }" class="relative">
                                        <input :type="showPassword ? 'text' : 'password'"
                                               placeholder="Zadejte heslo" wire:model="password"
                                               class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent py-2.5 pr-11 pl-4 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" />
                                        @error('password')
                                        <p class="mt-1 text-sm text-error-500">{{ $message }}</p>
                                        @enderror
                                        <span @click="showPassword = !showPassword"
                                              class="absolute top-1/2 right-4 z-30 -translate-y-1/2 cursor-pointer text-gray-500 dark:text-gray-400">
                                                <x-heroicon-o-eye x-show="!showPassword" class="w-5 h-5 fill-gray-400" />
                                                <x-heroicon-o-eye-slash x-show="showPassword" class="w-5 h-5 fill-gray-400" />
                                            </span>
                                    </div>
                                </div>
                                <!-- Checkbox -->
                                <div class="flex items-center justify-between">
                                    <div x-data="{ checkboxToggle: false }">
                                        <label for="checkboxLabelOne"
                                               class="flex cursor-pointer items-center text-sm font-normal text-gray-700 select-none dark:text-gray-400">
                                            <div class="relative">
                                                <input type="checkbox" wire:model="remember" id="checkboxLabelOne" class="sr-only" @change="checkboxToggle = !checkboxToggle" />
                                                <div :class="checkboxToggle ? 'border-brand-500 bg-brand-500' :
                                                        'bg-transparent border-gray-300 dark:border-gray-700'"
                                                     class="mr-3 flex h-5 w-5 items-center justify-center rounded-md border-[1.25px]">
                                                        <span :class="checkboxToggle ? '' : 'opacity-0'">
                                                            <svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                <path d="M11.6666 3.5L5.24992 9.91667L2.33325 7" stroke="white" stroke-width="1.94437" stroke-linecap="round" stroke-linejoin="round" />
                                                            </svg>
                                                        </span>
                                                </div>
                                            </div>
                                            Zapamatovat si mě
                                        </label>
                                    </div>
                                    <a href="/reset-password" class="text-brand-500 hover:text-brand-600 dark:text-brand-400 text-sm">
                                        Zapomenuté heslo?
                                    </a>
                                </div>
                                <!-- Submit -->
                                <div>
                                    <button type="submit"
                                            class="flex w-full items-center justify-center rounded-lg bg-brand-500 px-4 py-3 text-sm font-medium text-white shadow-theme-xs hover:bg-brand-600 disabled:opacity-50"
                                            wire:loading.attr="disabled">
                                        <span wire:loading.remove>Přihlásit se</span>
                                        <svg wire:loading class="mr-2 h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                        </svg>
                                        <span wire:loading>Přihlašuji...</span>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-brand-950 relative hidden h-full w-full items-center lg:grid lg:w-1/2 dark:bg-white/5">
            <div class="z-1 flex items-center justify-center">
                <!-- Common Grid Shape Start -->
                <x-common.common-grid-shape/>
                <div class="flex max-w-xs flex-col items-center">
                    <a href="/" class="mb-4 block">
                        <img src="./images/logo/auth-logo.svg" alt="Logo" />
                    </a>
                    <p class="text-center text-gray-400 dark:text-white/60">
                        Systém pro správu kancelářské ledničky
                    </p>
                </div>
            </div>
        </div>
        <!-- Toggler -->
        <div class="fixed right-6 bottom-6 z-50">
            <button
                class="bg-brand-500 hover:bg-brand-600 inline-flex size-14 items-center justify-center rounded-full text-white transition-colors"
                @click.prevent="$store.theme.toggle()">
                <x-heroicon-o-sun class="hidden dark:block fill-current w-5 h-5" />
                <x-heroicon-o-moon class="dark:hidden fill-current w-5 h-5" />
            </button>
        </div>
    </div>
</div>
