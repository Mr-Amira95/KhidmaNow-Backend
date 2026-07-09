@props([
    'name',
    'label',
    'accept' => 'image/*',
])

<div class="space-y-1.5">
    <label for="{{ $name }}" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ $label }}</label>
    <div class="flex items-center gap-3">
        <img data-preview-for="{{ $name }}" class="hidden h-12 w-12 rounded-lg border border-zinc-200 object-cover dark:border-zinc-700" alt="">
        <input
            id="{{ $name }}"
            name="{{ $name }}"
            type="file"
            accept="{{ $accept }}"
            {{ $attributes->merge([
                'class' => 'block w-full text-sm text-zinc-600 dark:text-zinc-300 file:mr-3 file:rounded-lg file:border-0 file:bg-accent-50 file:px-3 file:py-2 file:text-sm file:font-medium file:text-accent-700 hover:file:bg-accent-100 dark:file:bg-accent-950/30 dark:file:text-accent-400',
            ]) }}
        >
    </div>
    <p data-error-for="{{ $name }}" class="hidden text-xs text-rose-600 dark:text-rose-400"></p>
</div>
