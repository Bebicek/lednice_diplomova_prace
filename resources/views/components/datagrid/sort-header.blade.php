@props([
    'field',
    'label',
    'sortBy',
    'sortDirection',
])

<th class="px-6 py-3 font-medium text-gray-500 text-theme-xs dark:text-gray-400 text-start">
    <button wire:click="sort('{{ $field }}')" class="inline-flex items-center gap-1.5 hover:text-gray-700 dark:hover:text-gray-200">
        {{ $label }}
        @if($sortBy === $field)
            <svg class="fill-current" width="12" height="12" viewBox="0 0 12 12">
                @if($sortDirection === 'asc')
                    <path d="M6 2L10 8H2L6 2Z" />
                @else
                    <path d="M6 10L2 4H10L6 10Z" />
                @endif
            </svg>
        @else
            <svg class="fill-gray-400 dark:fill-gray-600" width="12" height="12" viewBox="0 0 12 12">
                <path d="M6 2L9 5.5H3L6 2Z" />
                <path d="M6 10L3 6.5H9L6 10Z" />
            </svg>
        @endif
    </button>
</th>
