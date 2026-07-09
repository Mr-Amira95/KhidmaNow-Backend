@extends('admin.layouts.app')

@section('title', 'FAQs')
@section('page', 'cms-faqs')

@section('content')
    <div class="rounded-2xl border border-zinc-200/70 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-zinc-200/70 p-4 dark:border-zinc-800">
            <p class="text-sm text-zinc-500 dark:text-zinc-400">Shown to users in the mobile app's help section.</p>
            <button id="new-faq-button" type="button" class="rounded-lg bg-accent-600 px-4 py-2 text-sm font-semibold text-white hover:bg-accent-700">
                <i class="ph ph-plus"></i> New FAQ
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="border-b border-zinc-200/70 text-xs font-semibold uppercase tracking-wide text-zinc-500 dark:border-zinc-800 dark:text-zinc-400">
                        <th class="py-3 px-4">Question</th>
                        <th class="py-3 px-4">Order</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody id="faqs-table-body"></tbody>
            </table>
        </div>
    </div>

    <x-admin.modal id="faq-modal" title="New FAQ">
        <form id="faq-form" class="space-y-4" novalidate>
            <input type="hidden" id="faq-id">
            <div class="grid grid-cols-2 gap-4">
                <x-admin.text-field name="faq_question_en" label="Question (English)" required />
                <x-admin.text-field name="faq_question_ar" label="Question (Arabic)" required />
            </div>
            <div class="grid grid-cols-2 gap-4">
                <x-admin.textarea-field name="faq_answer_en" label="Answer (English)" rows="3" required />
                <x-admin.textarea-field name="faq_answer_ar" label="Answer (Arabic)" rows="3" required />
            </div>
            <div class="grid grid-cols-2 gap-4 items-end">
                <x-admin.text-field name="faq_order" type="number" label="Display Order" placeholder="0" />
                <x-admin.toggle-switch name="faq_is_active" label="Active" :checked="true" />
            </div>
            <x-admin.submit-button>Save FAQ</x-admin.submit-button>
        </form>
    </x-admin.modal>
@endsection
