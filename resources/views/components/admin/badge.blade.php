@props(['color' => 'zinc', 'dot' => true])

@php
$colors = [
    'zinc'   => ['bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300', 'bg-zinc-400'],
    'green'  => ['bg-emerald-100 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400', 'bg-emerald-500'],
    'rose'   => ['bg-rose-100 text-rose-700 dark:bg-rose-950/40 dark:text-rose-400', 'bg-rose-500'],
    'amber'  => ['bg-amber-100 text-amber-700 dark:bg-amber-950/40 dark:text-amber-400', 'bg-amber-500'],
    'accent' => ['bg-accent-100 text-accent-700 dark:bg-accent-950/40 dark:text-accent-400', 'bg-accent-600'],
];
[$classes, $dotClass] = $colors[$color] ?? $colors['zinc'];
@endphp

<span class="inline-flex items-center gap-1.5 rounded-md px-2.5 py-1 text-xs font-medium transition-colors duration-150 {{ $classes }}">
    @if ($dot)
        <span class="h-1.5 w-1.5 shrink-0 rounded-full {{ $dotClass }}"></span>
    @endif
    {{ $slot }}
</span>
