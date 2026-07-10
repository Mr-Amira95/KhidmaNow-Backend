@props([
    'name',
    'label',
    'placeholder' => '',
    'rows' => 4,
])

<div class="space-y-1.5">
    <label for="{{ $name }}" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ $label }}</label>
    <textarea
        id="{{ $name }}"
        name="{{ $name }}"
        rows="{{ $rows }}"
        placeholder="{{ $placeholder }}"
        {{ $attributes->merge([
            'class' => 'w-full rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-950 text-zinc-900 dark:text-zinc-100 placeholder:text-zinc-400 py-2.5 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-accent-500/60 focus:border-accent-500 transition-all duration-150 hover:border-zinc-400 dark:hover:border-zinc-600 disabled:opacity-60 disabled:cursor-not-allowed',
        ]) }}
    ></textarea>
    <p data-error-for="{{ $name }}" class="hidden text-xs text-rose-600 dark:text-rose-400"></p>
</div>
