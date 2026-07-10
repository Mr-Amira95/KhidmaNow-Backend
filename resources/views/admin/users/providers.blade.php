@extends('admin.layouts.app')

@section('title', 'Service Providers')
@section('page', 'users-providers')

@section('content')
    <div class="card-surface">
        <div class="card-header">
            <div class="flex flex-wrap items-center gap-2">
                <input id="providers-search" type="text" placeholder="Search business name, phone..."
                    class="w-64 input-field-sm">
                <select id="providers-verified-filter" class="input-field-sm">
                    <option value="">All providers</option>
                    <option value="1">Verified</option>
                    <option value="0">Pending</option>
                </select>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="table-head-row">
                        <th class="py-3 px-4">Business</th>
                        <th class="py-3 px-4">Owner</th>
                        <th class="py-3 px-4">City</th>
                        <th class="py-3 px-4">Availability</th>
                        <th class="py-3 px-4">Verification</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody id="providers-table-body"></tbody>
            </table>
        </div>

        <div id="providers-pagination" class="flex items-center justify-between gap-3 p-4"></div>
    </div>

    <x-admin.modal id="provider-detail-modal" title="Provider details">
        <div id="provider-detail-body"></div>
    </x-admin.modal>
@endsection
