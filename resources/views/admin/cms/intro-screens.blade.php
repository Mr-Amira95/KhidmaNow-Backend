@extends('admin.layouts.app')

@section('title', 'Intro Screens')
@section('page', 'cms-intro-screens')

@section('content')
    <div class="card-surface">
        <div class="card-header">
            <p class="text-sm text-zinc-500 dark:text-zinc-400">Shown to first-time users when they open the mobile app.</p>
            <button id="new-intro-screen-button" type="button" data-permission="intro_screens.create" class="btn btn-primary">
                <i class="ph ph-plus"></i> New Intro Screen
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="table-head-row">
                        <th class="py-3 px-4">Image</th>
                        <th class="py-3 px-4">Title</th>
                        <th class="py-3 px-4">Order</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody id="intro-screens-table-body"></tbody>
            </table>
        </div>
    </div>

    <x-admin.modal id="intro-screen-modal" title="New Intro Screen">
        <div id="intro-screen-modal-banner" class="hidden" role="alert"></div>
        <form id="intro-screen-form" class="space-y-4" novalidate>
            <input type="hidden" id="intro-screen-id">
            <x-admin.file-field name="intro_screen_image" label="Image" />
            <div class="grid grid-cols-2 gap-4">
                <x-admin.text-field name="intro_screen_title_en" label="Title (English)" required />
                <x-admin.text-field name="intro_screen_title_ar" label="Title (Arabic)" required />
            </div>
            <div class="grid grid-cols-2 gap-4">
                <x-admin.textarea-field name="intro_screen_description_en" label="Description (English)" rows="3" required />
                <x-admin.textarea-field name="intro_screen_description_ar" label="Description (Arabic)" rows="3" required />
            </div>
            <div class="grid grid-cols-2 gap-4 items-end">
                <x-admin.text-field name="intro_screen_order" type="number" label="Display Order" placeholder="0" />
                <x-admin.toggle-switch name="intro_screen_is_active" label="Active" :checked="true" />
            </div>
            <x-admin.submit-button>Save Intro Screen</x-admin.submit-button>
        </form>
    </x-admin.modal>
@endsection
