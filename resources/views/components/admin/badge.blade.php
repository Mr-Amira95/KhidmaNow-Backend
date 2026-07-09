@props(['color' => 'zinc'])

@php
$colors = [
    'zinc'   => 'bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300',
    'green'  => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400',
    'rose'   => 'bg-rose-100 text-rose-700 dark:bg-rose-950/40 dark:text-rose-400',
    'amber'  => 'bg-amber-100 text-amber-700 dark:bg-amber-950/40 dark:text-amber-400',
    'accent' => 'bg-accent-100 text-accent-700 dark:bg-accent-950/40 dark:text-accent-400',
];
@endphp

<span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium {{ $colors[$color] ?? $colors['zinc'] }}">
    {{ $slot }}
</span>
