@props(['busyLabel' => 'Please wait'])

<button
    type="submit"
    {{ $attributes->merge([
        'class' => 'inline-flex w-full items-center justify-center gap-2 rounded-lg bg-accent-600 py-2.5 text-sm font-semibold text-white transition-all duration-150 hover:bg-accent-700 focus:outline-none focus:ring-2 focus:ring-accent-500 focus:ring-offset-2 active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-60 dark:focus:ring-offset-zinc-900',
    ]) }}
>
    <span data-label-idle>{{ $slot }}</span>
    <span data-label-busy class="items-center gap-2" style="display: none">
        <i class="ph ph-circle-notch animate-spin text-base"></i>
        {{ $busyLabel }}
    </span>
</button>
