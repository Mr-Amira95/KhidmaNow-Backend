@extends('admin.layouts.app')

@section('title', 'Categories')
@section('page', 'categories')

@section('content')
    <div class="card-surface">
        <div class="card-header">
            <input id="categories-search" type="text" placeholder="Search categories..."
                class="w-64 input-field-sm">
            <button id="new-category-button" type="button" data-permission="categories.create" class="btn btn-primary">
                <i class="ph ph-plus"></i> New Category
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="table-head-row">
                        <th class="py-3 px-4">Icon</th>
                        <th class="py-3 px-4">Name</th>
                        <th class="py-3 px-4">Sub-categories</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody id="categories-table-body"></tbody>
            </table>
        </div>
    </div>

    <div id="subcategories-panel" class="mt-6 hidden card-surface">
        <div class="card-header">
            <h2 class="text-sm font-semibold">Sub-categories</h2>
            <button id="new-sub-category-button" type="button" data-permission="categories.create" class="btn btn-primary">
                <i class="ph ph-plus"></i> New Sub-category
            </button>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="table-head-row">
                        <th class="py-2 px-4">Icon</th>
                        <th class="py-2 px-4">Name</th>
                        <th class="py-2 px-4">Status</th>
                        <th class="py-2 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody id="subcategories-table-body"></tbody>
            </table>
        </div>
    </div>

    <x-admin.modal id="category-modal" title="New Category">
        <div id="category-modal-banner" class="hidden" role="alert"></div>
        <form id="category-form" class="space-y-4" novalidate>
            <input type="hidden" id="category-id">
            <div class="grid grid-cols-2 gap-4">
                <x-admin.text-field name="category_name_en" label="Name (English)" required />
                <x-admin.text-field name="category_name_ar" label="Name (Arabic)" required />
            </div>
            <div class="grid grid-cols-2 gap-4">
                <x-admin.textarea-field name="category_description_en" label="Description (English)" rows="2" />
                <x-admin.textarea-field name="category_description_ar" label="Description (Arabic)" rows="2" />
            </div>
            <x-admin.file-field name="category_icon" label="Icon" />
            <x-admin.toggle-switch name="category_is_active" label="Active" :checked="true" />
            <x-admin.submit-button>Save Category</x-admin.submit-button>
        </form>
    </x-admin.modal>

    <x-admin.modal id="sub-category-modal" title="New Sub-category">
        <div id="sub-category-modal-banner" class="hidden" role="alert"></div>
        <form id="sub-category-form" class="space-y-4" novalidate>
            <input type="hidden" id="sub-category-id">
            <div class="grid grid-cols-2 gap-4">
                <x-admin.text-field name="sub_category_name_en" label="Name (English)" required />
                <x-admin.text-field name="sub_category_name_ar" label="Name (Arabic)" required />
            </div>
            <div class="grid grid-cols-2 gap-4">
                <x-admin.textarea-field name="sub_category_description_en" label="Description (English)" rows="2" />
                <x-admin.textarea-field name="sub_category_description_ar" label="Description (Arabic)" rows="2" />
            </div>
            <x-admin.file-field name="sub_category_icon" label="Icon" />
            <x-admin.toggle-switch name="sub_category_is_active" label="Active" :checked="true" />
            <x-admin.submit-button>Save Sub-category</x-admin.submit-button>
        </form>
    </x-admin.modal>
@endsection
