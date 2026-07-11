@props([
    'name',
    'label',
    'direction' => 'ltr',
])

<div class="space-y-1.5">
    <label for="{{ $name }}" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ $label }}</label>
    <div
        id="{{ $name }}_editor"
        class="html-editor-container rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-950 text-sm"
        data-direction="{{ $direction }}"
    ></div>
    <input type="hidden" id="{{ $name }}" name="{{ $name }}">
    <p data-error-for="{{ $name }}" class="hidden text-xs text-rose-600 dark:text-rose-400"></p>
</div>
