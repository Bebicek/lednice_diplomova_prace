@php use App\Models\Debt; @endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Dashboard' }} | Lednička</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    <!-- Theme Store -->
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.store('theme', {
                init() {
                    const savedTheme = localStorage.getItem('theme');
                    const systemTheme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
                    this.theme = savedTheme || systemTheme;
                    this.updateTheme();
                },
                theme: 'light',
                toggle() {
                    this.theme = this.theme === 'light' ? 'dark' : 'light';
                    localStorage.setItem('theme', this.theme);
                    this.updateTheme();
                },
                updateTheme() {
                    const html = document.documentElement;
                    const body = document.body;
                    if (this.theme === 'dark') {
                        html.classList.add('dark');
                        body.classList.add('dark', 'bg-gray-900');
                    } else {
                        html.classList.remove('dark');
                        body.classList.remove('dark', 'bg-gray-900');
                    }
                }
            });
        });
    </script>

    <!-- Apply dark mode immediately to prevent flash -->
    <script>
        (function() {
            const savedTheme = localStorage.getItem('theme');
            const systemTheme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
            const theme = savedTheme || systemTheme;
            if (theme === 'dark') {
                document.documentElement.classList.add('dark');
                document.body.classList.add('dark', 'bg-gray-900');
            }
        })();
    </script>
</head>

<body x-data class="bg-gray-100 dark:bg-gray-900 min-h-screen">
    {{-- Top Navbar --}}
    <nav class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 shadow-theme-xs sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                {{-- Logo + Nav Links --}}
                <div class="flex items-center">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-2 text-xl font-semibold text-brand-500">
                        <svg class="w-7 h-7" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <rect width="24" height="24" rx="6" fill="currentColor" fill-opacity="0.1"/>
                            <rect x="5" y="3" width="14" height="18" rx="2" stroke="currentColor" stroke-width="1.5"/>
                            <line x1="5" y1="9" x2="19" y2="9" stroke="currentColor" stroke-width="1.5"/>
                            <line x1="9" y1="6" x2="9" y2="7.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                            <line x1="9" y1="13" x2="9" y2="17" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                        </svg>
                        Lednička
                    </a>

                    @auth
                        <div class="hidden md:flex ml-10 space-x-1">
                            <a href="{{ route('dashboard') }}"
                                class="px-3 py-2 rounded-lg text-theme-sm font-medium transition-colors {{ request()->routeIs('dashboard') ? 'bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-400' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-white/5 dark:hover:text-white' }}">
                                Přehled
                            </a>
                            <a href="{{ route('catalog') }}"
                                class="px-3 py-2 rounded-lg text-theme-sm font-medium transition-colors {{ request()->routeIs('catalog') ? 'bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-400' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-white/5 dark:hover:text-white' }}">
                                Katalog
                            </a>
                            <a href="{{ route('cart') }}"
                                class="inline-flex items-center gap-1 px-3 py-2 rounded-lg text-theme-sm font-medium transition-colors {{ request()->routeIs('cart') ? 'bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-400' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-white/5 dark:hover:text-white' }}">
                                Košík
                                <livewire:cart-counter />
                            </a>
                            <a href="{{ route('my-debts') }}"
                                class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-theme-sm font-medium transition-colors {{ request()->routeIs('my-debts') ? 'bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-400' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-white/5 dark:hover:text-white' }}">
                                Moje dluhy
                                @php $desktopDebtCount = Debt::where('user_id', auth()->id())->where('is_paid', false)->where('is_accepted', true)->count(); @endphp
                                @if($desktopDebtCount > 0)
                                    <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-red-500 text-white text-xs font-bold">
                                        {{ $desktopDebtCount }}
                                    </span>
                                @endif
                            </a>
                            <a href="{{ route('lunches') }}"
                                class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-theme-sm font-medium transition-colors {{ request()->routeIs('lunches*') ? 'bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-400' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-white/5 dark:hover:text-white' }}">
                                Obědy
                                @php $desktopPendingLunches = Debt::where('user_id', auth()->id())->where('is_accepted', false)->whereNotNull('lunch_id')->count(); @endphp
                                @if($desktopPendingLunches > 0)
                                    <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-orange-500 text-white text-xs font-bold">
                                        {{ $desktopPendingLunches }}
                                    </span>
                                @endif
                            </a>
                        </div>
                    @endauth
                </div>

                {{-- Right side: theme toggle, user --}}
                <div class="flex items-center gap-3">
                    {{-- Theme toggle --}}
                    <button @click="$store.theme.toggle()" class="p-2 rounded-lg text-gray-500 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-white/5">
                        <template x-if="$store.theme.theme === 'light'">
                            <x-heroicon-o-moon class="w-5 h-5" />
                        </template>
                        <template x-if="$store.theme.theme === 'dark'">
                            <x-heroicon-o-sun class="w-5 h-5" />
                        </template>
                    </button>

                    @auth
                        {{-- Debt Notice --}}
                        <livewire:debt-alert />

                        {{-- Admin link for admins --}}
                        @if(auth()->user()->hasRole('admin'))
                            <a href="{{ route('admin.dashboard') }}"
                                class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-theme-sm font-medium bg-brand-500 text-white hover:bg-brand-600 transition-colors">
                                <x-heroicon-o-cog-6-tooth class="w-4 h-4" />
                                Administrace
                            </a>
                        @endif

                        {{-- User dropdown --}}
                        <div class="relative hidden sm:block" x-data="{ open: false }" @click.away="open = false">
                            <button
                                @click="open = !open"
                                class="flex items-center gap-2 text-gray-700 dark:text-gray-400"
                                type="button"
                            >
                                <span class="inline-flex items-center justify-center w-9 h-9 rounded-full bg-brand-100 text-brand-600 text-sm font-semibold dark:bg-brand-500/20 dark:text-brand-400 shrink-0">
                                    {{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}
                                </span>
                                <span class="text-theme-sm font-medium">{{ auth()->user()->name }}</span>
                                <x-heroicon-o-chevron-down
                                    class="w-4 h-4 transition-transform duration-200"
                                    x-bind:class="{ 'rotate-180': open }" />
                            </button>

                            <div
                                x-show="open"
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
                                            @click="open = false"
                                        >
                                            <span class="text-gray-500 group-hover:text-gray-700 dark:group-hover:text-gray-300">
                                                <x-heroicon-o-user-circle class="w-5 h-5" />
                                            </span>
                                            Můj profil
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
                    @endauth
                </div>
            </div>
        </div>

        {{-- Mobile nav --}}
        @auth
            <div class="md:hidden border-t border-gray-200 dark:border-gray-700 px-4 py-2 flex gap-1 overflow-x-auto">
                <a href="{{ route('dashboard') }}"
                    class="whitespace-nowrap px-3 py-2 rounded-lg text-theme-sm font-medium {{ request()->routeIs('dashboard') ? 'bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-400' : 'text-gray-600 dark:text-gray-300' }}">
                    Přehled
                </a>
                <a href="{{ route('catalog') }}"
                    class="whitespace-nowrap px-3 py-2 rounded-lg text-theme-sm font-medium {{ request()->routeIs('catalog') ? 'bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-400' : 'text-gray-600 dark:text-gray-300' }}">
                    Katalog
                </a>
                <a href="{{ route('cart') }}"
                    class="whitespace-nowrap inline-flex items-center gap-1 px-3 py-2 rounded-lg text-theme-sm font-medium {{ request()->routeIs('cart') ? 'bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-400' : 'text-gray-600 dark:text-gray-300' }}">
                    Košík
                    <livewire:cart-counter />
                </a>
                <a href="{{ route('my-debts') }}"
                    class="whitespace-nowrap inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-theme-sm font-medium {{ request()->routeIs('my-debts') ? 'bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-400' : 'text-gray-600 dark:text-gray-300' }}">
                    Moje dluhy
                    @php $mobileDebtCount = Debt::where('user_id', auth()->id())->where('is_paid', false)->where('is_accepted', true)->count(); @endphp
                    @if($mobileDebtCount > 0)
                        <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-red-500 text-white text-xs font-bold">
                            {{ $mobileDebtCount }}
                        </span>
                    @endif
                </a>
                <a href="{{ route('lunches') }}"
                    class="whitespace-nowrap inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-theme-sm font-medium {{ request()->routeIs('lunches*') ? 'bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-400' : 'text-gray-600 dark:text-gray-300' }}">
                    Obědy
                    @php $mobilePendingLunches = Debt::where('user_id', auth()->id())->where('is_accepted', false)->whereNotNull('lunch_id')->count(); @endphp
                    @if($mobilePendingLunches > 0)
                        <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-orange-500 text-white text-xs font-bold">
                            {{ $mobilePendingLunches }}
                        </span>
                    @endif
                </a>
            </div>
        @endauth
    </nav>

    {{-- Flash zprávy --}}
    @if(session('error'))
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-4">
            <div class="flex items-center gap-3 px-4 py-3 rounded-xl bg-error-50 border border-error-200 text-error-700 dark:bg-error-500/10 dark:border-error-500/20 dark:text-error-400 text-theme-sm">
                <x-heroicon-o-exclamation-triangle class="w-5 h-5 shrink-0" />
                <span>{{ session('error') }}</span>
            </div>
        </div>
    @endif

    @if(session('success'))
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-4">
            <div class="flex items-center gap-3 px-4 py-3 rounded-xl bg-success-50 border border-success-200 text-success-700 dark:bg-success-500/10 dark:border-success-500/20 dark:text-success-400 text-theme-sm">
                <x-heroicon-o-check class="w-5 h-5 shrink-0" />
                <span>{{ session('success') }}</span>
            </div>
        </div>
    @endif

    {{-- Main Content --}}
    <main class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        {{ $slot }}
    </main>

    {{-- Toast notifications --}}
    <div class="fixed bottom-4 right-4 z-50 space-y-2"
        x-data="{
            toasts: [],
            addToast(type, message) {
                const id = Date.now();
                this.toasts.push({ id, type, message });
                setTimeout(() => this.removeToast(id), 4000);
            },
            removeToast(id) {
                this.toasts = this.toasts.filter(t => t.id !== id);
            }
        }"
        @toast-success.window="addToast('success', $event.detail.message || $event.detail)"
        @toast-error.window="addToast('error', $event.detail.message || $event.detail)">
        <template x-for="toast in toasts" :key="toast.id">
            <div class="flex items-center gap-2 px-4 py-3 rounded-lg shadow-lg text-white text-theme-sm"
                :class="toast.type === 'success' ? 'bg-success-500' : 'bg-error-500'">
                <template x-if="toast.type === 'success'">
                    <x-heroicon-o-check class="w-5 h-5 shrink-0" />
                </template>
                <template x-if="toast.type === 'error'">
                    <x-heroicon-o-x-mark class="w-5 h-5 shrink-0" />
                </template>
                <span x-text="toast.message"></span>
            </div>
        </template>
    </div>

    @livewireScripts
</body>

</html>
