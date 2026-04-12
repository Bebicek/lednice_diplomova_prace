<div class="relative" x-data="{
    dropdownOpen: false,
    toggleDropdown() {
        this.dropdownOpen = !this.dropdownOpen;
    },
    closeDropdown() {
        this.dropdownOpen = false;
    }
}" @click.away="closeDropdown()">
    <!-- User Button -->
    <button
        class="flex items-center gap-2 text-gray-700 dark:text-gray-400"
        @click.prevent="toggleDropdown()"
        type="button"
    >
        <span class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-brand-100 text-brand-600 text-sm font-semibold dark:bg-brand-500/20 dark:text-brand-400 shrink-0">
            {{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}
        </span>

        <span class="hidden sm:block text-theme-sm font-medium">{{ auth()->user()->name }}</span>

        <x-heroicon-o-chevron-down
            class="w-4 h-4 transition-transform duration-200"
            x-bind:class="{ 'rotate-180': dropdownOpen }" />
    </button>

    <!-- Dropdown -->
    <div
        x-show="dropdownOpen"
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="transform opacity-0 scale-95"
        x-transition:enter-end="transform opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="transform opacity-100 scale-100"
        x-transition:leave-end="transform opacity-0 scale-95"
        class="absolute right-0 mt-3 flex w-[260px] flex-col rounded-2xl border border-gray-200 bg-white p-3 shadow-theme-lg dark:border-gray-800 dark:bg-gray-dark z-50"
        style="display: none;"
    >
        {{-- User info header --}}
        <div class="px-1 pb-3 border-b border-gray-100 dark:border-gray-800">
            <span class="block font-medium text-gray-700 text-theme-sm dark:text-white/90">{{ auth()->user()->name }}</span>
            <span class="mt-0.5 block text-theme-xs text-gray-500 dark:text-gray-400">{{ auth()->user()->email }}</span>
        </div>

        {{-- Menu --}}
        <ul class="flex flex-col gap-1 py-3 border-b border-gray-100 dark:border-gray-800">
            <li>
                <a href="{{ route('profile') }}"
                    class="flex items-center gap-3 px-3 py-2 font-medium text-gray-700 rounded-lg group text-theme-sm hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300"
                    @click="closeDropdown()"
                >
                    <span class="text-gray-500 group-hover:text-gray-700 dark:group-hover:text-gray-300">
                        <x-heroicon-o-user-circle class="w-5 h-5" />
                    </span>
                    Profil
                </a>
            </li>
        </ul>

        {{-- Logout --}}
        <form method="POST" action="{{ route('logout') }}" class="mt-3">
            @csrf
            <button
                type="submit"
                class="flex items-center w-full gap-3 px-3 py-2 font-medium text-gray-700 rounded-lg group text-theme-sm hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300"
            >
                <span class="text-gray-500 group-hover:text-gray-700 dark:group-hover:text-gray-300">
                    <x-heroicon-o-arrow-right-on-rectangle class="w-5 h-5" />
                </span>
                Odhlásit se
            </button>
        </form>
    </div>
</div>
