@extends('admin.layouts.app')

@section('title', 'Countries')
@section('page', 'locations-countries')

@section('content')
    <div class="card-surface">
        <div class="card-header">
            <input id="countries-search" type="text" placeholder="Search countries..."
                class="w-64 input-field-sm">
            <button id="new-country-button" type="button" data-permission="countries.create" class="btn btn-primary">
                <i class="ph ph-plus"></i> New Country
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="table-head-row">
                        <th class="py-3 px-4">Flag</th>
                        <th class="py-3 px-4">Name</th>
                        <th class="py-3 px-4">ISO</th>
                        <th class="py-3 px-4">Phone Code</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody id="countries-table-body"></tbody>
            </table>
        </div>
    </div>

    <x-admin.modal id="country-modal" title="New Country">
        <div id="country-modal-banner" class="hidden" role="alert"></div>
        <form id="country-form" class="space-y-4" novalidate>
            <input type="hidden" id="country-id">
            <div class="grid grid-cols-2 gap-4">
                <x-admin.text-field name="country_name_en" label="Name (English)" required />
                <x-admin.text-field name="country_name_ar" label="Name (Arabic)" required />
            </div>
            <div class="grid grid-cols-3 gap-4">
                <x-admin.text-field name="country_iso" label="ISO Code (3-letter)" maxlength="3" required />
                <x-admin.text-field name="country_phone_code" label="Phone Code" placeholder="+966" required />
                <x-admin.text-field name="country_currency_code" label="Currency Code" maxlength="3" required />
            </div>
            <x-admin.text-field name="country_currency_value" type="number" label="Currency Value (per USD)" required />
            <x-admin.file-field name="country_flag" label="Flag" />
            <x-admin.toggle-switch name="country_is_active" label="Active" :checked="true" />
            <x-admin.submit-button>Save Country</x-admin.submit-button>
        </form>
    </x-admin.modal>
@endsection
