@props(['busyLabel' => 'Please wait'])

<button
    type="submit"
    {{ $attributes->merge([
        'class' => 'btn btn-primary inline-flex w-full items-center justify-center gap-2 py-2.5 focus:outline-none focus:ring-2 focus:ring-accent-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-60 disabled:hover:translate-y-0 dark:focus:ring-offset-zinc-900',
    ]) }}
>
    <span data-label-idle>{{ $slot }}</span>
    <span data-label-busy class="items-center gap-2" style="display: none">
        <i class="ph ph-circle-notch animate-spin text-base"></i>
        {{ $busyLabel }}
    </span>
</button>
