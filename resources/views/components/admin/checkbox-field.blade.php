@props([
    'name',
    'label',
    'checked' => false,
])

<label for="{{ $name }}" class="group flex select-none items-center gap-2.5 cursor-pointer">
    <span class="relative flex h-5 w-5 shrink-0 items-center justify-center">
        <input
            id="{{ $name }}"
            name="{{ $name }}"
            type="checkbox"
            @checked($checked)
            {{ $attributes->merge(['class' => 'peer sr-only']) }}
        >
        <span class="absolute inset-0 rounded-md border border-zinc-300 bg-white transition-all duration-200 [transition-timing-function:var(--ease-spring)] peer-checked:border-accent-600 peer-checked:bg-accent-600 peer-focus-visible:ring-2 peer-focus-visible:ring-accent-500/50 peer-focus-visible:ring-offset-1 dark:border-zinc-600 dark:bg-zinc-950"></span>
        <i class="ph ph-check pointer-events-none absolute inset-0 flex scale-50 items-center justify-center text-[13px] font-bold text-white opacity-0 transition-all duration-200 [transition-timing-function:var(--ease-spring)] peer-checked:scale-100 peer-checked:opacity-100"></i>
    </span>
    <span class="text-sm text-zinc-600 transition-colors duration-150 group-hover:text-zinc-900 dark:text-zinc-400 dark:group-hover:text-zinc-200">{{ $label }}</span>
</label>
