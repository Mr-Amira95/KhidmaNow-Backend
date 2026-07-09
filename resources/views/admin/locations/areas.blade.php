@extends('admin.layouts.app')

@section('title', 'Areas')
@section('page', 'locations-areas')

@section('content')
    <div class="rounded-2xl border border-zinc-200/70 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-zinc-200/70 p-4 dark:border-zinc-800">
            <div class="flex flex-wrap items-center gap-2">
                <input id="areas-search" type="text" placeholder="Search areas..."
                    class="w-64 rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-950">
                <select id="areas-city-filter" class="rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-950">
                    <option value="">All cities</option>
                </select>
            </div>
            <button id="new-area-button" type="button" class="rounded-lg bg-accent-600 px-4 py-2 text-sm font-semibold text-white hover:bg-accent-700">
                <i class="ph ph-plus"></i> New Area
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="border-b border-zinc-200/70 text-xs font-semibold uppercase tracking-wide text-zinc-500 dark:border-zinc-800 dark:text-zinc-400">
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
