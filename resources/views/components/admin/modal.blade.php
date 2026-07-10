@props(['id', 'title'])

<div
    id="{{ $id }}"
    class="fixed inset-0 z-50 hidden items-center justify-center bg-zinc-950/60 p-4 opacity-0 backdrop-blur-sm transition-opacity duration-200"
    data-modal
>
    <div class="w-full max-w-lg scale-95 rounded-2xl border border-zinc-200/70 bg-white p-6 opacity-0 shadow-2xl shadow-zinc-950/20 transition-all duration-200 ease-[cubic-bezier(0.22,1,0.36,1)] dark:border-white/10 dark:bg-zinc-900" data-modal-panel>
        <div class="mb-5 flex items-center justify-between">
            <h2 id="{{ $id }}-title" class="text-base font-semibold text-zinc-900 dark:text-zinc-50">{{ $title }}</h2>
            <button type="button" class="rounded-lg p-1 text-zinc-400 transition-colors duration-150 hover:bg-zinc-100 hover:text-zinc-600 dark:hover:bg-zinc-800 dark:hover:text-zinc-300" data-modal-close aria-label="Close">
                <i class="ph ph-x text-lg"></i>
            </button>
        </div>
        <div id="{{ $id }}-banner" class="hidden mb-4" role="alert"></div>
        {{ $slot }}
    </div>
</div>
