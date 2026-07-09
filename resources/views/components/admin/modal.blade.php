@props(['id', 'title'])

<div id="{{ $id }}" class="fixed inset-0 z-50 hidden items-center justify-center bg-zinc-950/50 p-4" data-modal>
    <div class="w-full max-w-lg rounded-2xl border border-zinc-200/70 bg-white p-6 shadow-xl dark:border-zinc-800 dark:bg-zinc-900">
        <div class="mb-5 flex items-center justify-between">
            <h2 id="{{ $id }}-title" class="text-base font-semibold text-zinc-900 dark:text-zinc-50">{{ $title }}</h2>
            <button type="button" class="text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-300" data-modal-close aria-label="Close">
                <i class="ph ph-x text-lg"></i>
            </button>
        </div>
        <div id="{{ $id }}-banner" class="hidden mb-4" role="alert"></div>
        {{ $slot }}
    </div>
</div>
