@props([
    'name',
    'label',
    'checked' => false,
])

<label class="flex items-center gap-3">
    <input
        id="{{ $name }}"
        name="{{ $name }}"
        type="checkbox"
        value="1"
        @checked($checked)
        {{ $attributes->merge(['class' => 'peer sr-only']) }}
    >
    <span class="relative h-6 w-11 shrink-0 rounded-full bg-zinc-300 transition-colors duration-200 peer-checked:bg-accent-600 peer-focus-visible:ring-2 peer-focus-visible:ring-accent-500/60 peer-focus-visible:ring-offset-2 dark:bg-zinc-700 dark:peer-focus-visible:ring-offset-zinc-900">
        <span class="absolute left-0.5 top-0.5 h-5 w-5 rounded-full bg-white shadow transition-transform duration-200 ease-[cubic-bezier(0.22,1,0.36,1)] peer-checked:translate-x-5"></span>
    </span>
    <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ $label }}</span>
</label>
