@extends('admin.layouts.app')

@section('title', 'Cities')
@section('page', 'locations-cities')

@section('content')
    <div class="card-surface">
        <div class="card-header">
            <div class="flex flex-wrap items-center gap-2">
                <input id="cities-search" type="text" placeholder="Search cities..."
                    class="w-64 input-field-sm">
                <select id="cities-country-filter" class="input-field-sm">
                    <option value="">All countries</option>
                </select>
            </div>
            <button id="new-city-button" type="button" class="btn btn-primary">
                <i class="ph ph-plus"></i> New City
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="table-head-row">
                        <th class="py-3 px-4">Name</th>
                        <th class="py-3 px-4">Country</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody id="cities-table-body"></tbody>
            </table>
        </div>
    </div>

    <x-admin.modal id="city-modal" title="New City">
        <div id="city-modal-banner" class="hidden" role="alert"></div>
        <form id="city-form" class="space-y-4" novalidate>
            <input type="hidden" id="city-id">
            <x-admin.select-field name="city_country_id" label="Country" placeholder="Select a country" required />
            <div class="grid grid-cols-2 gap-4">
                <x-admin.text-field name="city_name_en" label="Name (English)" required />
                <x-admin.text-field name="city_name_ar" label="Name (Arabic)" required />
            </div>
            <x-admin.toggle-switch name="city_is_active" label="Active" :checked="true" />
            <x-admin.submit-button>Save City</x-admin.submit-button>
        </form>
    </x-admin.modal>
@endsection
