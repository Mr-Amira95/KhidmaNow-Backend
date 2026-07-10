@extends('admin.layouts.app')

@section('title', 'FAQs')
@section('page', 'cms-faqs')

@section('content')
    <div class="card-surface">
        <div class="card-header">
            <p class="text-sm text-zinc-500 dark:text-zinc-400">Shown to users in the mobile app's help section.</p>
            <button id="new-faq-button" type="button" class="btn btn-primary">
                <i class="ph ph-plus"></i> New FAQ
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="table-head-row">
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
