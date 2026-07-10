@extends('admin.layouts.app')

@section('title', 'Areas')
@section('page', 'locations-areas')

@section('content')
    <div class="card-surface">
        <div class="card-header">
            <div class="flex flex-wrap items-center gap-2">
                <input id="areas-search" type="text" placeholder="Search areas..."
                    class="w-64 input-field-sm">
                <select id="areas-city-filter" class="input-field-sm">
                    <option value="">All cities</option>
                </select>
            </div>
            <button id="new-area-button" type="button" class="btn btn-primary">
                <i class="ph ph-plus"></i> New Area
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="table-head-row">
                        <th class="py-3 px-4">Name</th>
                        <th class="py-3 px-4">City</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody id="areas-table-body"></tbody>
            </table>
        </div>
    </div>

    <x-admin.modal id="area-modal" title="New Area">
        <div id="area-modal-banner" class="hidden" role="alert"></div>
        <form id="area-form" class="space-y-4" novalidate>
            <input type="hidden" id="area-id">
            <x-admin.select-field name="area_city_id" label="City" placeholder="Select a city" required />
            <div class="grid grid-cols-2 gap-4">
                <x-admin.text-field name="area_name_en" label="Name (English)" required />
                <x-admin.text-field name="area_name_ar" label="Name (Arabic)" required />
            </div>
            <x-admin.toggle-switch name="area_is_active" label="Active" :checked="true" />
            <x-admin.submit-button>Save Area</x-admin.submit-button>
        </form>
    </x-admin.modal>
@endsection
