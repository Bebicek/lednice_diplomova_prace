@props([
    'wireModel' => '',
    'placeholder' => 'Vyberte datum...',
    'minDate' => 'today',
])

<div wire:ignore
     x-data="{
         init() {
             flatpickr(this.$refs.picker, {
                 dateFormat: 'Y-m-d',
                 allowInput: true,
                 disableMobile: true,
                 minDate: '{{ $minDate }}',
                 onChange: (dates, dateStr) => {
                     $wire.set('{{ $wireModel }}', dateStr);
                 }
             });
         }
     }"
     x-init="init()">
    <input type="text" x-ref="picker"
           placeholder="{{ $placeholder }}"
           class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800 dark:placeholder:text-white/30" />
</div>
