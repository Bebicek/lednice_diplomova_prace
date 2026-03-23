@props([
    'label',
    'value',
    'subtitle' => '',
    'color' => 'gray',
    'icon' => null,
])

@php
    $colorClasses = match($color) {
        'warning' => 'border-warning-200 bg-warning-50 dark:border-warning-500/20 dark:bg-warning-500/10',
        'success' => 'border-success-200 bg-success-50 dark:border-success-500/20 dark:bg-success-500/10',
        'error' => 'border-error-200 bg-error-50 dark:border-error-500/20 dark:bg-error-500/10',
        'brand' => 'border-brand-200 bg-brand-50 dark:border-brand-500/20 dark:bg-brand-500/10',
        default => 'border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]',
    };

    $labelColor = match($color) {
        'warning' => 'text-warning-600 dark:text-warning-400',
        'success' => 'text-success-600 dark:text-success-400',
        'error' => 'text-error-600 dark:text-error-400',
        'brand' => 'text-brand-600 dark:text-brand-400',
        default => 'text-gray-500 dark:text-gray-400',
    };

    $valueColor = match($color) {
        'warning' => 'text-warning-700 dark:text-warning-300',
        'success' => 'text-success-700 dark:text-success-300',
        'error' => 'text-error-700 dark:text-error-300',
        'brand' => 'text-brand-700 dark:text-brand-300',
        default => 'text-gray-800 dark:text-white/90',
    };

    $subtitleColor = match($color) {
        'warning' => 'text-warning-500 dark:text-warning-400',
        'success' => 'text-success-500 dark:text-success-400',
        'error' => 'text-error-500 dark:text-error-400',
        'brand' => 'text-brand-500 dark:text-brand-400',
        default => 'text-gray-400',
    };
@endphp

<div class="rounded-2xl border p-5 {{ $colorClasses }}">
    <div class="flex items-start justify-between">
        <div>
            <p class="text-theme-xs font-medium uppercase tracking-wide {{ $labelColor }}">{{ $label }}</p>
            <p class="mt-1 text-2xl font-bold {{ $valueColor }}">{{ $value }}</p>
            @if($subtitle)
                <p class="mt-0.5 text-theme-xs {{ $subtitleColor }}">{{ $subtitle }}</p>
            @endif
        </div>
        @if($icon)
            <x-dynamic-component :component="'heroicon-o-' . $icon" class="h-8 w-8 shrink-0 {{ $labelColor }}" />
        @endif
    </div>
</div>
