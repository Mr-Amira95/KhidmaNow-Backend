@props([
    'name',
    'type' => 'text',
    'label',
    'icon' => null,
    'autocomplete' => 'off',
])

<div class="space-y-1.5">
    <div class="group relative">
        @if ($icon)
            <span class="pointer-events-none absolute start-3.5 top-1/2 z-10 -translate-y-1/2 text-zinc-400 transition-colors duration-200 group-focus-within:text-accent-600 dark:group-focus-within:text-accent-400">
                <i class="ph {{ $icon }} text-lg"></i>
            </span>
        @endif
        <input
            id="{{ $name }}"
            name="{{ $name }}"
            type="{{ $type }}"
            autocomplete="{{ $autocomplete }}"
            placeholder=" "
            {{ $attributes->merge([
                'class' => 'peer w-full rounded-xl border border-zinc-300 dark:border-zinc-700 bg-white/90 dark:bg-zinc-950/70 text-zinc-900 dark:text-zinc-100 '
                    . ($icon ? 'ps-11' : 'ps-4')
                    . ' pe-4 pt-5 pb-2 text-sm focus:outline-none focus:ring-4 focus:ring-accent-500/15 focus:border-accent-500 transition-all duration-200 ease-out hover:border-zinc-400 dark:hover:border-zinc-600 disabled:opacity-60 disabled:cursor-not-allowed',
            ]) }}
        >
        <label
            for="{{ $name }}"
            class="pointer-events-none absolute {{ $icon ? 'start-11' : 'start-4' }} top-4 text-sm text-zinc-400 transition-all duration-200 [transition-timing-function:var(--ease-spring)] peer-focus:top-2 peer-focus:text-[11px] peer-focus:font-medium peer-focus:tracking-wide peer-focus:text-accent-600 peer-[:not(:placeholder-shown)]:top-2 peer-[:not(:placeholder-shown)]:text-[11px] peer-[:not(:placeholder-shown)]:font-medium dark:peer-focus:text-accent-400"
        >{{ $label }}</label>
    </div>
    <p data-error-for="{{ $name }}" class="hidden text-xs text-rose-600 dark:text-rose-400"></p>
</div>
