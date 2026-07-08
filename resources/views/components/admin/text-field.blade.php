@props([
    'name',
    'type' => 'text',
    'label',
    'icon' => null,
    'placeholder' => '',
    'autocomplete' => 'off',
])

<div class="space-y-1.5">
    <label for="{{ $name }}" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ $label }}</label>
    <div class="relative">
        @if ($icon)
            <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-zinc-400">
                <i class="ph {{ $icon }} text-lg"></i>
            </span>
        @endif
        <input
            id="{{ $name }}"
            name="{{ $name }}"
            type="{{ $type }}"
            autocomplete="{{ $autocomplete }}"
            placeholder="{{ $placeholder }}"
            {{ $attributes->merge([
                'class' => 'w-full rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-950 text-zinc-900 dark:text-zinc-100 placeholder:text-zinc-400 py-2.5 '
                    . ($icon ? 'pl-10' : 'pl-3')
                    . ' pr-3 text-sm focus:outline-none focus:ring-2 focus:ring-accent-500 focus:border-accent-500 transition-colors disabled:opacity-60 disabled:cursor-not-allowed',
            ]) }}
        >
    </div>
    <p data-error-for="{{ $name }}" class="hidden text-xs text-rose-600 dark:text-rose-400"></p>
</div>
