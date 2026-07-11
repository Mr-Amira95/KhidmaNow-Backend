@props(['color' => 'zinc', 'dot' => true])

@php
$colors = [
    'zinc'   => ['bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300', 'bg-zinc-400'],
    'green'  => ['bg-brand-green-50 text-brand-green-800 dark:bg-brand-green-900/25 dark:text-brand-green-400', 'bg-brand-green-500'],
    'orange' => ['bg-brand-orange-50 text-brand-orange-700 dark:bg-brand-orange-900/20 dark:text-brand-orange-400', 'bg-brand-orange-500'],
    'rose'   => ['bg-rose-100 text-rose-700 dark:bg-rose-950/40 dark:text-rose-400', 'bg-rose-500'],
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
