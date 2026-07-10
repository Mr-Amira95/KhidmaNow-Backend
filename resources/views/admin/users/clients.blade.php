@extends('admin.layouts.app')

@section('title', 'Clients')
@section('page', 'users-clients')

@section('content')
    <div class="card-surface">
        <div class="card-header">
            <div class="flex flex-wrap items-center gap-2">
                <input id="clients-search" type="text" placeholder="Search name, phone, email..."
                    class="w-64 input-field-sm">
                <select id="clients-status-filter" class="input-field-sm">
                    <option value="">All statuses</option>
                    <option value="active">Active</option>
                    <option value="blocked">Blocked</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="table-head-row">
                        <th class="py-3 px-4">Name</th>
                        <th class="py-3 px-4">Phone</th>
                        <th class="py-3 px-4">Email</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4">Joined</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody id="clients-table-body"></tbody>
            </table>
        </div>

        <div id="clients-pagination" class="flex items-center justify-between gap-3 p-4"></div>
    </div>

    <x-admin.modal id="client-detail-modal" title="Client details">
        <div id="client-detail-body"></div>
    </x-admin.modal>
@endsection
