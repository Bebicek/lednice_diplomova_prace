<div wire:poll.5s="validateCartStock">
    <x-common.page-breadcrumb :pageTitle="'Košík'" />

    @if(session('error'))
        <div class="mb-4 rounded-lg bg-error-50 p-4 text-error-700 dark:bg-error-500/10 dark:text-error-400">
            {{ session('error') }}
        </div>
    @endif

    @if(session('success'))
        <div class="mb-4 rounded-lg bg-success-50 p-4 text-success-700 dark:bg-success-500/10 dark:text-success-400">
            {{ session('success') }}
        </div>
    @endif

    @if(count($cart) > 0)
        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                    <tr class="border-b border-gray-100 dark:border-gray-800">
                        <th class="px-5 py-3 text-left text-theme-xs font-medium text-gray-500 uppercase dark:text-gray-400">Produkt</th>
                        <th class="px-5 py-3 text-left text-theme-xs font-medium text-gray-500 uppercase dark:text-gray-400">Cena/ks</th>
                        <th class="px-5 py-3 text-center text-theme-xs font-medium text-gray-500 uppercase dark:text-gray-400">Množství</th>
                        <th class="px-5 py-3 text-right text-theme-xs font-medium text-gray-500 uppercase dark:text-gray-400">Celkem</th>
                        <th class="px-5 py-3 text-right text-theme-xs font-medium text-gray-500 uppercase dark:text-gray-400">Akce</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @foreach($cart as $item)
                        <tr>
                            <td class="px-5 py-4 text-theme-sm font-medium text-gray-800 dark:text-white/90">{{ $item['name'] }}</td>
                            <td class="px-5 py-4 text-theme-sm text-gray-600 dark:text-gray-400">{{ number_format($item['price'] / 100, 2, ',', ' ') }} Kč</td>
                            <td class="px-5 py-4">
                                <div class="flex items-center justify-center gap-2">
                                    <button wire:click="decrement({{ $item['id'] }})" class="flex items-center justify-center w-8 h-8 rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-100 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-800">-</button>
                                    <span class="w-8 text-center text-theme-sm font-medium text-gray-800 dark:text-white/90">{{ $item['quantity'] }}</span>
                                    <button wire:click="increment({{ $item['id'] }})" class="flex items-center justify-center w-8 h-8 rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-100 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-800">+</button>
                                </div>
                            </td>
                            <td class="px-5 py-4 text-right text-theme-sm font-medium text-gray-800 dark:text-white/90">
                                {{ number_format(($item['price'] * $item['quantity']) / 100, 2, ',', ' ') }} Kč
                            </td>
                            <td class="px-5 py-4 text-right">
                                <button wire:click="remove({{ $item['id'] }})" class="text-error-500 hover:text-error-700 text-theme-sm font-medium">Odebrat</button>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                    <tfoot>
                    <tr class="border-t-2 border-gray-200 dark:border-gray-700">
                        <td colspan="3" class="px-5 py-4 text-right text-theme-sm font-semibold text-gray-800 dark:text-white">Celkem:</td>
                        <td class="px-5 py-4 text-right text-lg font-bold text-brand-500">{{ number_format($total / 100, 2, ',', ' ') }} Kč</td>
                        <td></td>
                    </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <div class="mt-6 flex items-center justify-between">
            <button wire:click="clearCart" class="rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-theme-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700">
                Vyprázdnit košík
            </button>
            <button wire:click="checkout" class="rounded-lg bg-brand-500 px-6 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs hover:bg-brand-600">
                Objednat
            </button>
        </div>
    @else
        <div class="rounded-2xl border border-gray-200 bg-white p-12 text-center dark:border-gray-800 dark:bg-white/[0.03]">
            <x-heroicon-o-shopping-cart class="mx-auto w-16 h-16 text-gray-300 dark:text-gray-600 mb-4" />
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-2">Košík je prázdný</h3>
            <p class="text-gray-500 dark:text-gray-400 mb-4">Přejděte do katalogu a přidejte produkty.</p>
            <a href="{{ route('catalog') }}" class="inline-flex rounded-lg bg-brand-500 px-4 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs hover:bg-brand-600">
                Přejít do katalogu
            </a>
        </div>
    @endif
</div>
